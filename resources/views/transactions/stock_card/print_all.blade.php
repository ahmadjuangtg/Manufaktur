<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Stock Card - {{ $warehouse_name }}</title>
    <style>
        @page { size: A4 landscape; margin: 15mm; }
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; font-size: 10px; color: #1e293b; line-height: 1.5; margin: 0; padding: 0; background-color: #fff; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 20px; }
        .title-area h1 { margin: 0; font-size: 20px; font-weight: 800; color: #0f172a; letter-spacing: -0.5px; }
        .title-area p { margin: 4px 0 0 0; color: #64748b; font-size: 11px; font-style: italic; }
        .meta-area { text-align: right; font-size: 10px; color: #475569; }
        .meta-area div { margin-bottom: 3px; }
        .meta-area strong { color: #0f172a; }
        
        .stock-summary { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 25px; }
        .stock-box { border: 1px solid #cbd5e1; padding: 12px; border-radius: 8px; background-color: #f8fafc; }
        .stock-box .label { font-size: 8px; color: #64748b; text-transform: uppercase; font-weight: 700; display: block; margin-bottom: 4px; letter-spacing: 0.5px; }
        .stock-box .value { font-size: 16px; font-weight: 800; color: #0f172a; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        table th { background: #f1f5f9; border: 1px solid #cbd5e1; padding: 8px 6px; text-align: left; text-transform: uppercase; font-size: 8px; font-weight: 800; color: #475569; letter-spacing: 0.5px; }
        table td { border: 1px solid #cbd5e1; padding: 8px 6px; font-size: 9px; color: #334155; }
        table tr:nth-child(even) { background-color: #f8fafc; }
        
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: 700; }
        .font-black { font-weight: 900; }
        
        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .stock-box { border: 1px solid #94a3b8 !important; background-color: #f8fafc !important; }
            table th { background-color: #f1f5f9 !important; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="header">
        <div class="title-area">
            <h1>Laporan Stock Card - {{ $warehouse_name }}</h1>
            <p>Laporan saldo stock seluruh master item</p>
        </div>
        <div class="meta-area">
            <div>Tanggal Export: <strong>{{ now()->format('d/m/Y H:i:s') }}</strong></div>
            <div>Filter Gudang: <strong>{{ $warehouse_name }}</strong></div>
        </div>
    </div>

    <div class="stock-summary">
        <div class="stock-box">
            <span class="label">Current Stock</span>
            <span class="value">{{ number_format($total_current) }}</span>
        </div>
        <div class="stock-box">
            <span class="label">Lock Stock</span>
            <span class="value" style="color: #e11d48;">{{ number_format($total_lock) }}</span>
        </div>
        <div class="stock-box">
            <span class="label">Shadow Stock</span>
            <span class="value" style="color: #d97706;">{{ number_format($total_shadow) }}</span>
        </div>
        <div class="stock-box">
            <span class="label">Available Stock</span>
            <span class="value" style="color: #059669;">{{ number_format($total_available) }}</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Nama Gudang</th>
                <th>SKU</th>
                <th>Nama Barang</th>
                <th class="text-center">Tanggal</th>
                <th class="text-center">Tipe Transaksi</th>
                <th class="text-center">Masuk</th>
                <th class="text-center">Keluar</th>
                <th class="text-center">Current Stock</th>
                <th class="text-center">Lock Stock</th>
                <th class="text-center">Shadow Stock</th>
                <th class="text-center">Available Stock</th>
                <th>Reference Number</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $i)
            <tr>
                <td>{{ $warehouse_name }}</td>
                <td class="font-bold">{{ $i->code }}</td>
                <td>{{ $i->name }}</td>
                <td class="text-center">-</td>
                <td class="text-center font-bold" style="color: #475569;">SUMMARY</td>
                <td class="text-center">-</td>
                <td class="text-center">-</td>
                <td class="text-center font-bold">{{ number_format($i->current_stock) }}</td>
                <td class="text-center font-bold" style="color: #e11d48;">{{ number_format($i->lock_stock) }}</td>
                <td class="text-center font-bold" style="color: #d97706;">{{ number_format($i->shadow_stock) }}</td>
                <td class="text-center font-black" style="color: #059669;">{{ number_format(max(0, $i->current_stock - $i->lock_stock)) }}</td>
                <td>-</td>
                <td style="color: #64748b; font-style: italic;">Saldo stock saat export</td>
            </tr>
            @empty
            <tr>
                <td colspan="13" class="text-center" style="padding: 20px; color: #64748b;">Tidak ada data barang.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
