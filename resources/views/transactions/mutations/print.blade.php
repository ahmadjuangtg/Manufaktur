<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Mutasi - {{ $mutation->reference_no }}</title>
    <style>
        @page { size: A4; margin: 20mm; }
        body { font-family: 'Inter', system-ui, sans-serif; font-size: 11px; color: #333; line-height: 1.5; margin: 0; padding: 0; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #000; pb: 10px; mb: 20px; }
        .logo-area h1 { margin: 0; font-size: 24px; font-weight: 900; letter-spacing: -1px; }
        .logo-area p { margin: 0; color: #666; font-weight: bold; text-transform: uppercase; letter-spacing: 2px; font-size: 9px; }
        .ref-area { text-align: right; }
        .ref-area h2 { margin: 0; font-size: 14px; font-weight: 900; }
        .ref-area p { margin: 0; color: #666; font-weight: bold; }
        
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 30px; }
        .info-box h3 { border-bottom: 1px solid #ddd; padding-bottom: 5px; margin-bottom: 10px; font-size: 10px; text-transform: uppercase; color: #666; }
        .info-box p { margin: 2px 0; font-weight: bold; font-size: 12px; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        table th { background: #f8f9fa; border: 1px solid #ddd; padding: 10px; text-align: left; text-transform: uppercase; font-size: 9px; }
        table td { border: 1px solid #ddd; padding: 10px; }
        
        .footer-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-top: 50px; text-align: center; }
        .sig-box { height: 100px; display: flex; flex-direction: column; justify-content: space-between; }
        .sig-box p { margin: 0; font-weight: bold; text-transform: uppercase; font-size: 9px; }
        .sig-line { border-bottom: 1px solid #000; margin-bottom: 5px; }

        .note-box { margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #ddd; }
        .note-box strong { display: block; font-size: 9px; text-transform: uppercase; color: #666; margin-bottom: 5px; }
    </style>
</head>
<body onload="window.print()">
    <div class="header">
        <div class="logo-area">
            <h1>AORI MANUFACTURE</h1>
            <p>Inventory Management System</p>
        </div>
        <div class="ref-area">
            <p>DOKUMEN MUTASI STOK</p>
            <h2>{{ $mutation->reference_no }}</h2>
            <p>{{ $mutation->created_at->format('d F Y, H:i') }}</p>
        </div>
    </div>

    <div class="info-grid">
        <div class="info-box">
            <h3>Gudang Pengirim</h3>
            <p>{{ $mutation->fromWarehouse->name }}</p>
            <span style="font-size: 9px; color: #666;">{{ $mutation->fromWarehouse->address }}</span>
        </div>
        <div class="info-box">
            <h3>Gudang Penerima</h3>
            <p>{{ $mutation->toWarehouse->name }}</p>
            <span style="font-size: 9px; color: #666;">{{ $mutation->toWarehouse->address }}</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 40px;">No</th>
                <th>Kode Barang</th>
                <th>Nama Barang</th>
                <th style="text-align: right;">Kuantitas</th>
                <th style="width: 60px;">Satuan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($mutation->details as $index => $d)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td style="font-family: monospace;">{{ $d->item->code }}</td>
                <td style="font-weight: bold;">{{ $d->item->name }}</td>
                <td style="text-align: right; font-weight: 900;">{{ $d->quantity + 0 }}</td>
                <td>{{ $d->item->unit->name ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if($mutation->note)
    <div class="note-box">
        <strong>Catatan Mutasi:</strong>
        {{ $mutation->note }}
    </div>
    @endif

    @if($mutation->rejection_reason)
    <div class="note-box" style="border-left-color: #e63946; background: #fff5f5;">
        <strong style="color: #e63946;">Alasan Penolakan:</strong>
        {{ $mutation->rejection_reason }}
    </div>
    @endif

    <div class="footer-grid">
        <div class="sig-box">
            <p>Pemohon</p>
            <div>
                <div class="sig-line"></div>
                <p>{{ $mutation->user->name }}</p>
            </div>
        </div>
        <div class="sig-box">
            <p>Disetujui Oleh</p>
            <div>
                <div class="sig-line"></div>
                <p>{{ $mutation->approver->name ?? '........................' }}</p>
            </div>
        </div>
        <div class="sig-box">
            <p>Pengirim</p>
            <div>
                <div class="sig-line"></div>
                <p>{{ $mutation->sender->name ?? '........................' }}</p>
            </div>
        </div>
        <div class="sig-box">
            <p>Penerima</p>
            <div>
                <div class="sig-line"></div>
                <p>{{ $mutation->receiver->name ?? '........................' }}</p>
            </div>
        </div>
    </div>

    <div style="margin-top: 50px; font-size: 8px; color: #999; text-align: center; border-top: 1px dashed #ddd; pt: 10px;">
        Dicetak secara sistem oleh {{ Auth::user()->name }} pada {{ now()->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>
