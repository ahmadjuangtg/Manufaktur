<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: sans-serif; }
        .title { font-size: 16px; font-weight: bold; }
        .subtitle { font-size: 11px; color: #555555; font-style: italic; }
        .meta-table td { font-size: 10px; }
        .stat-label { font-size: 9px; color: #555555; text-transform: uppercase; font-weight: bold; }
        .stat-value { font-size: 14px; font-weight: bold; }
        table.data-table { border-collapse: collapse; width: 100%; }
        table.data-table th { background-color: #f1f5f9; border: 1px solid #cbd5e1; font-size: 9px; font-weight: bold; color: #475569; padding: 6px; }
        table.data-table td { border: 1px solid #cbd5e1; font-size: 10px; padding: 6px; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <!-- Title -->
    <table>
        <tr>
            <td colspan="5" class="title">Laporan Stock Card - {{ $warehouse_name }}</td>
            <td colspan="4"></td>
            <td colspan="4" class="text-right" style="font-size: 9px;">Tanggal Export: <b>{{ now()->format('d/m/Y H:i:s') }}</b></td>
        </tr>
        <tr>
            <td colspan="5" class="subtitle">Laporan saldo stock seluruh master item</td>
            <td colspan="4"></td>
            <td colspan="4" class="text-right" style="font-size: 9px;">Filter Gudang: <b>{{ $warehouse_name }}</b></td>
        </tr>
    </table>

    <br>

    <!-- Stats summary boxes styled for Excel grid -->
    <table border="1" style="border-collapse: collapse;">
        <tr style="background-color: #f8fafc;">
            <th style="padding: 10px; text-align: left; width: 150px;">CURRENT STOCK</th>
            <th style="padding: 10px; text-align: left; width: 150px; color: #e11d48;">LOCK STOCK</th>
            <th style="padding: 10px; text-align: left; width: 150px; color: #d97706;">SHADOW STOCK</th>
            <th style="padding: 10px; text-align: left; width: 150px; color: #059669;">AVAILABLE STOCK</th>
        </tr>
        <tr>
            <td style="font-size: 16px; font-weight: bold; padding: 10px;">{{ number_format($total_current) }}</td>
            <td style="font-size: 16px; font-weight: bold; padding: 10px; color: #e11d48;">{{ number_format($total_lock) }}</td>
            <td style="font-size: 16px; font-weight: bold; padding: 10px; color: #d97706;">{{ number_format($total_shadow) }}</td>
            <td style="font-size: 16px; font-weight: bold; padding: 10px; color: #059669;">{{ number_format($total_available) }}</td>
        </tr>
    </table>

    <br><br>

    <!-- Main Data Table -->
    <table border="1" class="data-table">
        <thead>
            <tr style="background-color: #f1f5f9;">
                <th style="font-weight: bold;">Nama Gudang</th>
                <th style="font-weight: bold;">SKU</th>
                <th style="font-weight: bold;">Nama Barang</th>
                <th style="font-weight: bold; text-align: center;">Tanggal</th>
                <th style="font-weight: bold; text-align: center;">Tipe Transaksi</th>
                <th style="font-weight: bold; text-align: center;">Masuk</th>
                <th style="font-weight: bold; text-align: center;">Keluar</th>
                <th style="font-weight: bold; text-align: center;">Current Stock</th>
                <th style="font-weight: bold; text-align: center;">Lock Stock</th>
                <th style="font-weight: bold; text-align: center;">Shadow Stock</th>
                <th style="font-weight: bold; text-align: center;">Available Stock</th>
                <th style="font-weight: bold;">Reference Number</th>
                <th style="font-weight: bold;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $i)
            <tr>
                <td>{{ $warehouse_name }}</td>
                <td style="font-weight: bold;">{{ $i->code }}</td>
                <td>{{ $i->name }}</td>
                <td class="text-center">-</td>
                <td class="text-center" style="font-weight: bold; color: #475569;">SUMMARY</td>
                <td class="text-center">-</td>
                <td class="text-center">-</td>
                <td class="text-center" style="font-weight: bold;">{{ $i->current_stock }}</td>
                <td class="text-center" style="font-weight: bold; color: #e11d48;">{{ $i->lock_stock }}</td>
                <td class="text-center" style="font-weight: bold; color: #d97706;">{{ $i->shadow_stock }}</td>
                <td class="text-center" style="font-weight: bold; color: #059669;">{{ max(0, $i->current_stock - $i->lock_stock) }}</td>
                <td>-</td>
                <td style="color: #64748b; font-style: italic;">Saldo stock saat export</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
