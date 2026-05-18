<?php

namespace App\Services;

use App\Models\InventoryStock;
use App\Models\StockMutation;
use App\Models\StockMutationDetail;
use App\Models\StockTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class InventoryService
{
    /**
     * Create a new Stock Mutation Request
     */
    public function createMutationRequest(array $data)
    {
        return DB::transaction(function () use ($data) {
            $mutation = StockMutation::create([
                'reference_no' => 'MUT-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3))),
                'work_order_id' => $data['work_order_id'] ?? null,
                'from_warehouse_id' => $data['from_warehouse_id'],
                'to_warehouse_id' => $data['to_warehouse_id'],
                'status' => 'PENDING',
                'note' => $data['note'] ?? null,
                'user_id' => Auth::id(),
            ]);

            foreach ($data['items'] as $item) {
                StockMutationDetail::create([
                    'stock_mutation_id' => $mutation->id,
                    'item_id' => $item['item_id'],
                    'quantity' => $item['quantity'],
                ]);
            }

            return $mutation;
        });
    }

    /**
     * Process / Approve a Mutation (Locking Stock)
     */
    public function approveMutation(int $id)
    {
        return DB::transaction(function () use ($id) {
            $mutation = StockMutation::with('details')->findOrFail($id);
            
            if ($mutation->status !== 'PENDING') {
                throw new \Exception("Hanya mutasi yang berstatus PENDING yang dapat disetujui.");
            }

            foreach ($mutation->details as $detail) {
                // Lock stock in source warehouse
                StockTransaction::create([
                    'item_id' => $detail->item_id,
                    'warehouse_id' => $mutation->from_warehouse_id,
                    'type' => 'LOCK_IN',
                    'quantity' => $detail->quantity,
                    'reference_no' => 'MUTATION-' . $mutation->reference_no,
                    'note' => 'Booking Mutasi ke ' . $mutation->toWarehouse->name,
                    'user_id' => Auth::id(),
                ]);
            }

            $mutation->update([
                'status' => 'APPROVED',
                'approved_by' => Auth::id(),
                'approved_at' => now()
            ]);

            return $mutation;
        });
    }

    /**
     * Process / Complete a Mutation (Transferring Stock)
     */
    public function completeMutation(int $id)
    {
        return DB::transaction(function () use ($id) {
            $mutation = StockMutation::with('details')->findOrFail($id);
            
            if (!in_array($mutation->status, ['APPROVED', 'SENDING'])) {
                throw new \Exception("Hanya mutasi yang berstatus APPROVED atau SENDING yang dapat diselesaikan.");
            }

            foreach ($mutation->details as $detail) {
                // 0. Release Lock from source
                StockTransaction::create([
                    'item_id' => $detail->item_id,
                    'warehouse_id' => $mutation->from_warehouse_id,
                    'type' => 'LOCK_OUT',
                    'quantity' => $detail->quantity,
                    'reference_no' => 'MUTATION-' . $mutation->reference_no,
                    'note' => 'Pelepasan Booking Mutasi ke ' . $mutation->toWarehouse->name,
                    'user_id' => Auth::id(),
                ]);

                // 1. Reduce from source
                StockTransaction::create([
                    'item_id' => $detail->item_id,
                    'warehouse_id' => $mutation->from_warehouse_id,
                    'type' => 'OUT',
                    'quantity' => $detail->quantity,
                    'reference_no' => 'MUTATION-' . $mutation->reference_no,
                    'note' => 'Mutation to ' . $mutation->toWarehouse->name,
                    'user_id' => Auth::id(),
                ]);

                // 2. Add to destination
                StockTransaction::create([
                    'item_id' => $detail->item_id,
                    'warehouse_id' => $mutation->to_warehouse_id,
                    'type' => 'IN',
                    'quantity' => $detail->quantity,
                    'reference_no' => 'MUTATION-' . $mutation->reference_no,
                    'note' => 'Mutation from ' . $mutation->fromWarehouse->name,
                    'user_id' => Auth::id(),
                ]);
            }

            $mutation->update([
                'status' => 'COMPLETED',
                'completed_at' => now()
            ]);

            return $mutation;
        });
    }

    /**
     * Process Partial Stock Mutation Shipment (Fulfillment)
     */
    public function deliverPartialMutation(int $id, array $items)
    {
        return DB::transaction(function () use ($id, $items) {
            $mutation = StockMutation::with(['details', 'deliveries', 'toWarehouse', 'fromWarehouse'])->findOrFail($id);

            if (!in_array($mutation->status, ['APPROVED', 'SENDING'])) {
                throw new \Exception("Hanya mutasi berstatus APPROVED atau SENDING yang dapat diproses kirim.");
            }

            // Loop items sent in this batch
            foreach ($items as $sentItem) {
                $itemId = $sentItem['item_id'];
                $quantity = floatval($sentItem['quantity']);

                if ($quantity <= 0) continue;

                // Find associated detail line
                $detail = $mutation->details->where('item_id', $itemId)->first();
                if (!$detail) {
                    throw new \Exception("Item ID {$itemId} tidak ada dalam dokumen permintaan mutasi.");
                }

                // Check sisa kekurangan yang bisa dikirim
                $alreadySent = $mutation->deliveries->where('item_id', $itemId)->sum('quantity');
                $maxPossible = $detail->quantity - $alreadySent;

                if ($quantity > $maxPossible) {
                    throw new \Exception("Jumlah kirim ({$quantity}) melebihi sisa kekurangan yang ada ({$maxPossible}).");
                }

                // 1. Pelepasan Booking Mutasi (LOCK_OUT) dari gudang asal senilai Qty kiriman
                StockTransaction::create([
                    'item_id' => $itemId,
                    'warehouse_id' => $mutation->from_warehouse_id,
                    'type' => 'LOCK_OUT',
                    'quantity' => $quantity,
                    'reference_no' => 'MUTATION-' . $mutation->reference_no,
                    'note' => 'Pelepasan Booking Mutasi Cicilan ke ' . $mutation->toWarehouse->name,
                    'user_id' => Auth::id(),
                ]);

                // 2. Mengurangi stok fisik (OUT) dari gudang asal senilai Qty kiriman
                StockTransaction::create([
                    'item_id' => $itemId,
                    'warehouse_id' => $mutation->from_warehouse_id,
                    'type' => 'OUT',
                    'quantity' => $quantity,
                    'reference_no' => 'MUTATION-' . $mutation->reference_no,
                    'note' => 'Mutasi Cicilan ke ' . $mutation->toWarehouse->name,
                    'user_id' => Auth::id(),
                ]);

                // 3. Menambahkan stok fisik (IN) di gudang tujuan senilai Qty kiriman
                StockTransaction::create([
                    'item_id' => $itemId,
                    'warehouse_id' => $mutation->to_warehouse_id,
                    'type' => 'IN',
                    'quantity' => $quantity,
                    'reference_no' => 'MUTATION-' . $mutation->reference_no,
                    'note' => 'Mutasi Cicilan dari ' . $mutation->fromWarehouse->name,
                    'user_id' => Auth::id(),
                ]);

                // 4. Catat ke tabel pengiriman
                \App\Models\StockMutationDelivery::create([
                    'stock_mutation_id' => $mutation->id,
                    'item_id' => $itemId,
                    'quantity' => $quantity,
                    'delivered_by' => Auth::id(),
                    'delivered_at' => now(),
                ]);
            }

            // Refresh deliveries to check if all are fully satisfied
            $mutation->load('deliveries');

            $allCompleted = true;
            foreach ($mutation->details as $detail) {
                $totalDelivered = $mutation->deliveries->where('item_id', $detail->item_id)->sum('quantity');
                if ($totalDelivered < $detail->quantity) {
                    $allCompleted = false;
                    break;
                }
            }

            if ($allCompleted) {
                $mutation->update([
                    'status' => 'COMPLETED',
                    'received_by' => Auth::id(),
                    'received_at' => now()
                ]);
            } else {
                $mutation->update([
                    'status' => 'SENDING',
                    'sent_by' => Auth::id(),
                    'sent_at' => now()
                ]);
            }

            return $mutation;
        });
    }

    /**
     * Get Current Stock for an Item in a Warehouse
     */
    public function getStockBalance(int $itemId, int $warehouseId)
    {
        $stock = InventoryStock::where('item_id', $itemId)
            ->where('warehouse_id', $warehouseId)
            ->first();

        return $stock ? $stock->available_stock : 0;
    }
}
