<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StockMutation;
use App\Models\StockMutationDetail;
use App\Models\Item;
use App\Models\Warehouse;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class StockMutationSeeder extends Seeder
{
    public function run()
    {
        $user = User::first();
        $items = Item::limit(5)->get();
        $warehouses = Warehouse::limit(2)->get();

        if ($warehouses->count() < 2 || $items->isEmpty()) return;

        $wh1 = $warehouses[0];
        $wh2 = $warehouses[1];

        // 1. PENDING (3 Data)
        for ($i = 1; $i <= 3; $i++) {
            $m = StockMutation::create([
                'reference_no' => 'MUT-PENDING-00' . $i,
                'from_warehouse_id' => $wh1->id,
                'to_warehouse_id' => $wh2->id,
                'status' => 'PENDING',
                'note' => 'Simulasi permintaan pending ' . $i,
                'user_id' => $user->id,
            ]);
            StockMutationDetail::create(['stock_mutation_id' => $m->id, 'item_id' => $items[0]->id, 'quantity' => 10]);
        }

        // 2. APPROVED (3 Data)
        for ($i = 1; $i <= 3; $i++) {
            $m = StockMutation::create([
                'reference_no' => 'MUT-APPROVED-00' . $i,
                'from_warehouse_id' => $wh1->id,
                'to_warehouse_id' => $wh2->id,
                'status' => 'APPROVED',
                'note' => 'Simulasi permintaan approved ' . $i,
                'user_id' => $user->id,
                'approved_by' => $user->id,
                'approved_at' => now(),
            ]);
            StockMutationDetail::create(['stock_mutation_id' => $m->id, 'item_id' => $items[1]->id, 'quantity' => 20]);
        }

        // 3. SENDING (3 Data)
        for ($i = 1; $i <= 3; $i++) {
            $m = StockMutation::create([
                'reference_no' => 'MUT-SENDING-00' . $i,
                'from_warehouse_id' => $wh1->id,
                'to_warehouse_id' => $wh2->id,
                'status' => 'SENDING',
                'note' => 'Simulasi permintaan sending ' . $i,
                'user_id' => $user->id,
                'approved_by' => $user->id,
                'approved_at' => now(),
                'sent_by' => $user->id,
                'sent_at' => now(),
            ]);
            StockMutationDetail::create(['stock_mutation_id' => $m->id, 'item_id' => $items[2]->id, 'quantity' => 30]);
        }

        // 4. COMPLETED (3 Data)
        for ($i = 1; $i <= 3; $i++) {
            $m = StockMutation::create([
                'reference_no' => 'MUT-COMPLETED-00' . $i,
                'from_warehouse_id' => $wh1->id,
                'to_warehouse_id' => $wh2->id,
                'status' => 'COMPLETED',
                'note' => 'Simulasi permintaan completed ' . $i,
                'user_id' => $user->id,
                'approved_by' => $user->id,
                'approved_at' => now(),
                'sent_by' => $user->id,
                'sent_at' => now(),
                'received_by' => $user->id,
                'received_at' => now(),
            ]);
            StockMutationDetail::create(['stock_mutation_id' => $m->id, 'item_id' => $items[3]->id, 'quantity' => 40]);
        }
    }
}
