<?php
use App\Models\PurchaseOrder;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$po = PurchaseOrder::where('po_no', 'PO-20260505-0005')->with('details')->first();
if ($po) {
    echo "PO: " . $po->po_no . "\n";
    echo "Total Amount in DB: " . $po->total_amount . "\n";
    foreach ($po->details as $d) {
        echo "- Item ID: " . $d->item_id . ", Qty: " . $d->quantity . ", Price: " . $d->price . "\n";
    }
} else {
    echo "PO not found\n";
}
