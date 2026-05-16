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
