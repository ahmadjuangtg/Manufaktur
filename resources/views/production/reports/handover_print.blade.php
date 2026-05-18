<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Serah Terima - {{ $transfer->reference_no }}</title>
    <style>
        @page { size: A4; margin: 15mm; }
        body { font-family: 'Inter', system-ui, sans-serif; font-size: 10px; color: #333; line-height: 1.4; margin: 0; padding: 0; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .logo-area h1 { margin: 0; font-size: 20px; font-weight: 900; letter-spacing: -1px; }
        .logo-area p { margin: 0; color: #666; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; font-size: 8px; }
        .ref-area { text-align: right; }
        .ref-area h2 { margin: 0; font-size: 14px; font-weight: 900; color: #000; }
        .ref-area p { margin: 0; color: #666; font-weight: bold; font-size: 9px; }
        
        .info-grid { display: grid; grid-template-columns: 1.5fr 1fr; gap: 30px; margin-bottom: 20px; }
        .info-box h3 { border-bottom: 1px solid #ddd; padding-bottom: 3px; margin-bottom: 8px; font-size: 9px; text-transform: uppercase; color: #666; }
        .info-box p { margin: 2px 0; font-weight: bold; font-size: 11px; }

        .type-badge { display: inline-block; padding: 2px 8px; background: #000; color: #fff; font-weight: 900; border-radius: 4px; font-size: 10px; margin-bottom: 5px; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table th { background: #f8f9fa; border: 1px solid #ddd; padding: 8px; text-align: left; text-transform: uppercase; font-size: 8px; color: #666; }
        table td { border: 1px solid #ddd; padding: 8px; font-size: 10px; }
        
        .footer-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-top: 40px; text-align: center; }
        .sig-box { height: 80px; display: flex; flex-direction: column; justify-content: space-between; }
        .sig-box p { margin: 0; font-weight: bold; text-transform: uppercase; font-size: 8px; color: #666; }
        .sig-line { border-bottom: 1px solid #000; margin-bottom: 5px; }
        .sig-name { font-weight: bold; font-size: 10px; color: #000; }

        .watermark { position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-45deg); font-size: 80px; color: rgba(0,0,0,0.03); font-weight: 900; z-index: -1; white-space: nowrap; }
    </style>
</head>
<body onload="window.print()">
    <div class="watermark">{{ $transfer->type }} - {{ $transfer->status }}</div>

    <div class="header">
        <div class="logo-area">
            <h1>AORI MANUFACTURE</h1>
            <p>Production & Inventory System</p>
        </div>
        <div class="ref-area">
            <div class="type-badge">{{ $transfer->type === 'PHP' ? 'PENYERAHAN HASIL PRODUKSI (PHP)' : 'NOTA PENYERAHAN BARANG (NPB)' }}</div>
            <h2>{{ $transfer->reference_no }}</h2>
            <p>Tanggal: {{ $transfer->created_at->format('d/m/Y H:i') }}</p>
        </div>
    </div>

    <div class="info-grid">
        <div class="info-box">
            <h3>Informasi Produksi</h3>
            <p>Work Order: {{ $transfer->workOrder->wo_number }}</p>
            <p style="font-weight: normal; color: #666;">Customer: {{ $transfer->workOrder->customer->name ?? '-' }}</p>
            <p style="font-weight: normal; color: #666;">Line: {{ $transfer->workOrder->production_line }}</p>
        </div>
        <div class="info-box">
            <h3>Alur Barang</h3>
            <p style="font-size: 9px; color: #666;">DARI:</p>
            <p>{{ $transfer->fromWarehouse->name }}</p>
            <p style="font-size: 9px; color: #666; margin-top: 5px;">KE:</p>
            <p>{{ $transfer->toWarehouse->name }}</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th>Kode Barang</th>
                <th>Nama Barang / Produk</th>
                <th style="text-align: right;">Kuantitas</th>
                <th style="width: 60px;">Satuan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transfer->workOrder->products as $index => $prod)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td style="font-family: monospace;">{{ $prod->item->code }}</td>
                <td style="font-weight: bold;">{{ $prod->item->name }}</td>
                <td style="text-align: right; font-weight: 900; font-size: 12px;">{{ number_format($transfer->quantity, 2) }}</td>
                <td>{{ $prod->item->unit->name ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-bottom: 20px;">
        <p style="font-size: 9px; color: #666; text-transform: uppercase; font-weight: bold; margin-bottom: 5px;">Keterangan / Catatan:</p>
        <div style="border: 1px solid #ddd; padding: 10px; min-height: 40px; border-radius: 4px;">
            {{ $transfer->notes ?: 'Tidak ada catatan tambahan.' }}
        </div>
    </div>

    <div class="footer-grid">
        <div class="sig-box">
            <p>Diserahkan Oleh (Produksi)</p>
            <div>
                <div class="sig-line"></div>
                <p class="sig-name">{{ $transfer->requester->name }}</p>
            </div>
        </div>
        <div class="sig-box">
            <p>Diterima Oleh (Gudang)</p>
            <div>
                <div class="sig-line"></div>
                <p class="sig-name">..................................</p>
            </div>
        </div>
        <div class="sig-box">
            <p>Diverifikasi Oleh</p>
            <div>
                <div class="sig-line"></div>
                <p class="sig-name">{{ $transfer->verifier->name ?? '..................................' }}</p>
            </div>
        </div>
    </div>

    <div style="margin-top: 40px; font-size: 8px; color: #999; text-align: center; border-top: 1px dashed #ddd; padding-top: 10px;">
        Dokumen ini dihasilkan secara otomatis oleh sistem Aori pada {{ now()->format('d/m/Y H:i:s') }}. 
        Status: <strong>{{ $transfer->status }}</strong>
    </div>
</body>
</html>
