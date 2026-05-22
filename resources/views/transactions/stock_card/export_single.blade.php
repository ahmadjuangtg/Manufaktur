<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: sans-serif; }
        .title { font-size: 16px; font-weight: bold; }
        .subtitle { font-size: 11px; color: #555555; font-style: italic; }
        table.data-table { border-collapse: collapse; width: 100%; }
        table.data-table th { background-color: #f1f5f9; border: 1px solid #cbd5e1; font-size: 9px; font-weight: bold; color: #475569; padding: 6px; }
        table.data-table td { border: 1px solid #cbd5e1; font-size: 10px; padding: 6px; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-green { color: #10b981; font-weight: bold; }
        .text-red { color: #f43f5e; font-weight: bold; }
        .text-orange { color: #f59e0b; font-weight: bold; }
    </style>
</head>
<body>
    <!-- Title Section -->
    <table>
        <tr>
            <td colspan="4" class="title">Laporan Detail Kartu Stok (Stock Card Detail)</td>
            <td colspan="3"></td>
            <td colspan="2" class="text-right" style="font-size: 9px;">Tanggal Export: <b>{{ now()->format('d/m/Y H:i:s') }}</b></td>
        </tr>
        <tr>
            <td colspan="4" class="subtitle">Detail histori pergerakan stok barang</td>
            <td colspan="3"></td>
            <td colspan="2" class="text-right" style="font-size: 9px;">Filter Gudang: <b>{{ $warehouse_name }}</b></td>
        </tr>
    </table>

    <br>

    <!-- Item Information Grid inside Excel -->
    <table border="1" style="border-collapse: collapse;">
        <tr style="background-color: #f1f5f9;">
            <th colspan="2" style="padding: 6px; font-weight: bold; text-align: left;">INFORMASI ITEM</th>
        </tr>
        <tr>
            <td style="padding: 6px; width: 150px;">Nama Item</td>
            <td style="padding: 6px; font-weight: bold; width: 300px;">{{ $item->name }}</td>
        </tr>
        <tr>
            <td style="padding: 6px;">SKU / Kode</td>
            <td style="padding: 6px; font-weight: bold;">{{ $item->code }}</td>
        </tr>
        <tr>
            <td style="padding: 6px;">Satuan</td>
            <td style="padding: 6px; font-weight: bold;">{{ $item->unit->name ?? '-' }}</td>
        </tr>
    </table>

    <br>

    <!-- Metrics Summary -->
    <table border="1" style="border-collapse: collapse;">
        <tr style="background-color: #f8fafc;">
            <th style="padding: 10px; text-align: center; width: 140px;">CURRENT STOCK</th>
            <th style="padding: 10px; text-align: center; width: 140px; color: #f43f5e;">LOCK STOCK</th>
            <th style="padding: 10px; text-align: center; width: 140px; color: #f59e0b;">SHADOW STOCK</th>
            <th style="padding: 10px; text-align: center; width: 140px; color: #10b981;">AVAILABLE STOCK</th>
        </tr>
        <tr>
            <td style="font-size: 16px; font-weight: bold; padding: 10px; text-align: center;">{{ number_format($current_stock) }}</td>
            <td style="font-size: 16px; font-weight: bold; padding: 10px; text-align: center; color: #f43f5e;">{{ number_format($lock_stock) }}</td>
            <td style="font-size: 16px; font-weight: bold; padding: 10px; text-align: center; color: #f59e0b;">{{ number_format($shadow_stock) }}</td>
            <td style="font-size: 16px; font-weight: bold; padding: 10px; text-align: center; color: #10b981;">{{ number_format($available_stock) }}</td>
        </tr>
    </table>

    <br><br>

    <!-- Transaction History Table -->
    <table border="1" class="data-table">
        <thead>
            <tr style="background-color: #f1f5f9;">
                <th style="font-weight: bold; width: 120px; text-align: center;">Tanggal</th>
                <th style="font-weight: bold; width: 220px;">Gudang</th>
                <th style="font-weight: bold; width: 120px; text-align: center;">Tipe Transaksi</th>
                <th style="font-weight: bold; width: 100px; text-align: center;">Masuk</th>
                <th style="font-weight: bold; width: 100px; text-align: center;">Keluar</th>
                <th style="font-weight: bold; width: 80px; text-align: center;">Satuan</th>
                <th style="font-weight: bold; width: 160px;">No. Referensi</th>
                <th style="font-weight: bold; width: 240px;">Keterangan</th>
                <th style="font-weight: bold; width: 120px; text-align: center;">Saldo Current</th>
            </tr>
        </thead>
        <tbody>
            @php $running_balance = 0; @endphp
            {{-- Reverse to calculate running balance correctly from start --}}
            @foreach($transactions->reverse() as $t)
                @php 
                    if($t->type == 'IN') {
                        $running_balance += $t->quantity;
                    } elseif ($t->type == 'OUT') {
                        $running_balance -= $t->quantity;
                    }
                    $t->running_balance = $running_balance;
                @endphp
            @endforeach

            @forelse($transactions as $t)
            <tr>
                <td class="text-center">{{ $t->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ $t->warehouse->name }}</td>
                <td class="text-center" style="font-weight: bold;">{{ str_replace('_', ' ', $t->type) }}</td>
                <td class="text-center">
                    @if(in_array($t->type, ['IN', 'SHADOW_IN', 'LOCK_IN']))
                        <span class="text-green">+{{ number_format($t->quantity) }}</span>
                    @else
                        -
                    @endif
                </td>
                <td class="text-center">
                    @if(!in_array($t->type, ['IN', 'SHADOW_IN', 'LOCK_IN']))
                        <span class="text-red">-{{ number_format($t->quantity) }}</span>
                    @else
                        -
                    @endif
                </td>
                <td class="text-center">{{ $item->unit->name ?? '-' }}</td>
                <td>{{ $t->reference_no }}</td>
                <td style="color: #555555; font-style: italic;">{{ $t->note ?? '-' }}</td>
                <td class="text-center" style="font-weight: bold; background-color: #f8fafc;">
                    {{ number_format($t->running_balance) }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center" style="color: #666666; padding: 20px; font-style: italic;">Belum ada history transaksi.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
