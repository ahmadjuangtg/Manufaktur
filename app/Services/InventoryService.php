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

            // Count existing unique shipments to generate the next shipment number
            $count = \App\Models\StockMutationDelivery::where('stock_mutation_id', $mutation->id)
                ->whereNotNull('shipment_no')
                ->distinct()
                ->count('shipment_no') + 1;

            $shipmentNo = $mutation->reference_no . '-DEL-' . $count;

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

                // Check sisa kekurangan yang bisa dikirim (requested - successfully received - in transit)
                $itemDeliveries = $mutation->deliveries->where('item_id', $itemId);
                $totalReceived = $itemDeliveries->whereNotNull('received_at')->sum('received_quantity');
                $inTransit = $itemDeliveries->whereNull('received_at')->sum('quantity');
                $alreadyAccountedFor = $totalReceived + $inTransit;
                $maxPossible = $detail->quantity - $alreadyAccountedFor;
                $maxPossible = $maxPossible < 0 ? 0 : $maxPossible;

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

                // 3. Catat ke tabel pengiriman (Destination IN will be performed on physical receipt)
                \App\Models\StockMutationDelivery::create([
                    'stock_mutation_id' => $mutation->id,
                    'item_id' => $itemId,
                    'quantity' => $quantity,
                    'delivered_by' => Auth::id(),
                    'delivered_at' => now(),
                    'shipment_no' => $shipmentNo,
                ]);
            }

            // Update mutation status to SENDING as we have an active shipment in transit
            $mutation->update([
                'status' => 'SENDING',
                'sent_by' => Auth::id(),
                'sent_at' => now()
            ]);

            return $mutation;
        });
    }

    /**
     * Process Physical Verification and Receipt of a Partial Shipment
     */
    public function receivePartialMutation(int $id, string $shipmentNo, array $items)
    {
        return DB::transaction(function () use ($id, $shipmentNo, $items) {
            $mutation = StockMutation::with(['details', 'deliveries', 'toWarehouse', 'fromWarehouse'])->findOrFail($id);

            if ($mutation->status !== 'SENDING') {
                throw new \Exception("Hanya mutasi berstatus SENDING yang dapat diverifikasi penerimaannya.");
            }

            // Find all deliveries for this shipment
            $deliveries = \App\Models\StockMutationDelivery::where('stock_mutation_id', $id)
                ->where('shipment_no', $shipmentNo)
                ->get();

            if ($deliveries->isEmpty()) {
                throw new \Exception("Pengiriman dengan nomor {$shipmentNo} tidak ditemukan.");
            }

            if ($deliveries->first()->received_at !== null) {
                throw new \Exception("Pengiriman dengan nomor {$shipmentNo} sudah pernah diterima sebelumnya.");
            }

            // Process receipt for each item in the shipment
            foreach ($deliveries as $delivery) {
                // Find received quantity in the input array
                $receivedQty = 0;
                foreach ($items as $item) {
                    if ($item['item_id'] == $delivery->item_id) {
                        $receivedQty = floatval($item['quantity']);
                        break;
                    }
                }

                if ($receivedQty < 0) {
                    throw new \Exception("Jumlah diterima tidak boleh negatif.");
                }

                if ($receivedQty > $delivery->quantity) {
                    throw new \Exception("Jumlah diterima untuk item ID {$delivery->item_id} ({$receivedQty}) tidak boleh melebihi jumlah yang dikirim ({$delivery->quantity}).");
                }

                // Update the delivery record with receipt information
                $delivery->update([
                    'received_quantity' => $receivedQty,
                    'received_by' => Auth::id(),
                    'received_at' => now(),
                ]);

                // Create IN transaction in the destination warehouse if received quantity > 0
                if ($receivedQty > 0) {
                    StockTransaction::create([
                        'item_id' => $delivery->item_id,
                        'warehouse_id' => $mutation->to_warehouse_id,
                        'type' => 'IN',
                        'quantity' => $receivedQty,
                        'reference_no' => 'MUTATION-' . $mutation->reference_no,
                        'note' => 'Penerimaan Fisik Mutasi (' . $shipmentNo . ') dari ' . $mutation->fromWarehouse->name,
                        'user_id' => Auth::id(),
                    ]);
                }
            }

            // Refresh deliveries to evaluate status of the mutation
            $mutation->load('deliveries');

            // Check if there are any outstanding shipments in transit
            $hasInTransit = $mutation->deliveries->whereNull('received_at')->isNotEmpty();

            // Check if all requested items are fully sent
            $allSentSatisfied = true;
            foreach ($mutation->details as $detail) {
                $totalSent = $mutation->deliveries->where('item_id', $detail->item_id)->sum('quantity');
                if ($totalSent < $detail->quantity) {
                    $allSentSatisfied = false;
                    break;
                }
            }

            // Check if all requested items are fully received
            $allReceivedSatisfied = true;
            foreach ($mutation->details as $detail) {
                $totalReceived = $mutation->deliveries->where('item_id', $detail->item_id)->sum('received_quantity');
                if ($totalReceived < $detail->quantity) {
                    $allReceivedSatisfied = false;
                    break;
                }
            }

            // Complete the stock mutation if all shipments in transit are received
            // and the requested quantity is fully met
            if (!$hasInTransit && $allReceivedSatisfied) {
                $mutation->update([
                    'status' => 'COMPLETED',
                    'received_by' => Auth::id(),
                    'received_at' => now()
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
