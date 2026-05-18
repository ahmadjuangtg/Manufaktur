<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Jalan - {{ $packing->packing_no }}</title>
    <style>
        @page { size: A4; margin: 15mm; }
        body { font-family: 'Inter', system-ui, sans-serif; font-size: 11px; color: #333; line-height: 1.5; margin: 0; padding: 0; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .logo-area h1 { margin: 0; font-size: 22px; font-weight: 900; letter-spacing: -0.5px; }
        .logo-area p { margin: 2px 0 0 0; color: #666; font-size: 9px; text-transform: uppercase; font-weight: bold; letter-spacing: 1px; }
        .logo-area span { font-size: 8px; color: #999; display: block; margin-top: 2px; }
        
        .ref-area { text-align: right; }
        .ref-area h2 { margin: 0; font-size: 16px; font-weight: 900; color: #1e3a8a; }
        .ref-area p { margin: 2px 0 0 0; font-weight: bold; font-size: 11px; }
        .ref-area span { font-size: 9px; color: #666; display: block; margin-top: 2px; }

        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 25px; }
        .info-box { border: 1px solid #ddd; padding: 12px; border-radius: 6px; background: #fcfcfc; }
        .info-box h3 { border-bottom: 1px solid #eee; padding-bottom: 5px; margin: 0 0 8px 0; font-size: 9px; text-transform: uppercase; color: #555; font-weight: 800; letter-spacing: 0.5px; }
        .info-box p { margin: 2px 0; font-size: 11px; }
        .info-box strong { font-size: 12px; color: #111; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        table th { background: #f8f9fa; border: 1px solid #ddd; padding: 8px; text-align: left; text-transform: uppercase; font-size: 9px; font-weight: 800; }
        table td { border: 1px solid #ddd; padding: 8px; font-size: 10px; }

        .footer-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-top: 50px; text-align: center; }
        .sig-box { height: 100px; display: flex; flex-direction: column; justify-content: space-between; }
        .sig-box p { margin: 0; font-weight: bold; text-transform: uppercase; font-size: 8px; color: #555; }
        .sig-line { border-bottom: 1px solid #000; margin-bottom: 5px; width: 80%; margin-left: auto; margin-right: auto; }
        .sig-name { font-size: 10px; font-weight: bold; color: #111; }
    </style>
</head>
<body onload="window.print()">
    <div class="header">
        <div class="logo-area">
            <h1>PT. AORI SUMBER REZEKI</h1>
            <p>Aori Manufacture & Logistics</p>
            <span>Kawasan Industri Jl. Gatot Subroto No. 45, Tangerang</span>
        </div>
        <div class="ref-area">
            <h2>SURAT JALAN</h2>
            <p>No: {{ $packing->packing_no }}</p>
            <span>Tanggal: {{ $packing->created_at->format('d/m/Y') }}</span>
        </div>
    </div>

    <div class="info-grid">
        <div class="info-box">
            <h3>Penerima / Alamat Kirim</h3>
            <p><strong>{{ $packing->customer->name ?? 'Manual / Tanpa Nama' }}</strong></p>
            <p style="margin-top: 6px; font-size: 10.5px; color: #444;">{{ $packing->customer->address ?? 'Tanpa Alamat' }}</p>
            <p style="margin-top: 6px; font-size: 9.5px; color: #666;">Telp: {{ $packing->customer->phone ?? '-' }}</p>
        </div>
        <div class="info-box">
            <h3>Informasi Pengiriman</h3>
            <p>No. Batch Mobil: <strong>{{ $packing->deliveryBatch->batch_no ?? '-' }}</strong></p>
            <p style="margin-top: 4px;">Nama Supir: <strong>{{ $packing->deliveryBatch->driver_name ?? '-' }}</strong></p>
            <p style="margin-top: 4px;">No. Kendaraan: <strong>{{ $packing->deliveryBatch->vehicle_no ?? '-' }}</strong></p>
            <p style="margin-top: 4px; font-size: 9.5px; color: #666;">Catatan: {{ $packing->note ?? '-' }}</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 30px; text-align: center;">No</th>
                <th style="width: 100px;">Kode Barang</th>
                <th>Nama Barang</th>
                <th style="text-align: right; width: 60px;">Qty</th>
                <th style="width: 50px; text-align: center;">Satuan</th>
                <th style="width: 90px; text-align: center;">Kemasan</th>
                <th style="width: 80px; text-align: center;">No. Kemasan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($packing->details as $index => $d)
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td style="font-weight: bold;">{{ $d->item->code }}</td>
                <td>{{ $d->item->name }}</td>
                <td style="text-align: right; font-weight: bold;">{{ $d->quantity + 0 }}</td>
                <td style="text-align: center; color: #555;">{{ $d->item->unit->name }}</td>
                <td style="text-align: center;">{{ $d->package_type }}</td>
                <td style="text-align: center; color: #666;">{{ $d->package_number ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center; padding: 20px; color: #999; font-style: italic;">
                    Tidak ada item barang dalam Surat Jalan ini.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div style="font-size: 10px; line-height: 1.6; color: #555; background: #fafafa; border: 1px solid #eee; padding: 10px; border-radius: 4px;">
        <strong>Perhatian:</strong>
        <ol style="margin: 4px 0 0 0; padding-left: 15px;">
            <li>Barang yang sudah diterima dengan baik tidak dapat ditukar atau dikembalikan kecuali ada perjanjian tertulis sebelumnya.</li>
            <li>Segala klaim kekurangan/kerusakan barang wajib dilaporkan paling lambat 1x24 jam sejak barang diterima.</li>
        </ol>
    </div>

    <div class="footer-grid">
        <div class="sig-box">
            <p>Penerima Barang</p>
            <div>
                <div class="sig-line"></div>
                <p class="sig-name">Tanda Tangan & Cap</p>
            </div>
        </div>
        <div class="sig-box">
            <p>Pengemudi / Sopir</p>
            <div>
                <div class="sig-line"></div>
                <p class="sig-name">{{ $packing->deliveryBatch->driver_name ?? '........................' }}</p>
            </div>
        </div>
        <div class="sig-box">
            <p>Petugas Gudang</p>
            <div>
                <div class="sig-line"></div>
                <p class="sig-name">{{ $packing->user->name ?? '........................' }}</p>
            </div>
        </div>
        <div class="sig-box">
            <p>Hormat Kami,</p>
            <div>
                <div class="sig-line"></div>
                <p class="sig-name">Kepala Logistik</p>
            </div>
        </div>
    </div>

    <div style="margin-top: 50px; font-size: 8px; color: #999; text-align: center; border-top: 1px dashed #ddd; padding-top: 10px;">
        Dokumen Surat Jalan ini sah dan diproses secara otomatis oleh sistem logistik Aori pada {{ now()->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>
