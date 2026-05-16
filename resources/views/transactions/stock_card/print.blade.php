<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Stok - {{ $item->code }}</title>
    <style>
        @page { size: A4; margin: 20mm; }
        body { font-family: 'Inter', system-ui, sans-serif; font-size: 11px; color: #333; line-height: 1.5; margin: 0; padding: 0; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .logo-area h1 { margin: 0; font-size: 24px; font-weight: 900; letter-spacing: -1px; }
        .logo-area p { margin: 0; color: #666; font-weight: bold; text-transform: uppercase; letter-spacing: 2px; font-size: 9px; }
        .ref-area { text-align: right; }
        .ref-area h2 { margin: 0; font-size: 14px; font-weight: 900; }
        .ref-area p { margin: 0; color: #666; font-weight: bold; }
        
        .info-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 40px; margin-bottom: 30px; }
        .info-box h3 { border-bottom: 1px solid #ddd; padding-bottom: 5px; margin-bottom: 10px; font-size: 10px; text-transform: uppercase; color: #666; }
        .info-box p { margin: 2px 0; font-weight: bold; font-size: 14px; }
        .info-box span { font-size: 10px; color: #666; display: block; margin-top: 4px; }

        .stock-summary { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 20px; }
        .stock-box { border: 1px solid #ddd; padding: 10px; text-align: center; border-radius: 4px; }
        .stock-box .label { font-size: 8px; color: #666; text-transform: uppercase; font-weight: bold; display: block; margin-bottom: 5px; }
        .stock-box .value { font-size: 16px; font-weight: 900; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        table th { background: #f8f9fa; border: 1px solid #ddd; padding: 8px; text-align: left; text-transform: uppercase; font-size: 9px; }
        table td { border: 1px solid #ddd; padding: 8px; font-size: 10px; }
        
        .footer-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-top: 50px; text-align: center; }
        .sig-box { height: 100px; display: flex; flex-direction: column; justify-content: space-between; }
        .sig-box p { margin: 0; font-weight: bold; text-transform: uppercase; font-size: 9px; }
        .sig-line { border-bottom: 1px solid #000; margin-bottom: 5px; width: 60%; margin-left: auto; margin-right: auto; }

        .type-badge { padding: 2px 4px; border: 1px solid #ddd; border-radius: 2px; font-size: 8px; font-weight: bold; text-transform: uppercase; }
        .text-green { color: #10b981; }
        .text-red { color: #f43f5e; }
        .text-orange { color: #f59e0b; }
    </style>
</head>
<body onload="window.print()">
    <div class="header">
        <div class="logo-area">
            <h1>AORI MANUFACTURE</h1>
            <p>Inventory Management System</p>
        </div>
        <div class="ref-area">
            <p>LAPORAN KARTU STOK</p>
            <h2>{{ now()->format('d F Y') }}</h2>
            <p>{{ now()->format('H:i') }}</p>
        </div>
    </div>

    <div class="info-grid">
        <div class="info-box">
            <h3>Informasi Barang</h3>
            <p>{{ $item->name }}</p>
            <span>SKU: {{ $item->code }} | Kategori: {{ $item->category->name ?? '-' }} | Satuan: {{ $item->unit->name ?? '-' }}</span>
        </div>
        <div class="info-box">
            <h3>Total Current Stock Saat Ini</h3>
            <p style="font-size: 24px;">{{ number_format($current_stock) }} <small style="font-size: 12px; font-weight: normal;">{{ $item->unit->name ?? '' }}</small></p>
        </div>
    </div>

    <div class="stock-summary">
        <div class="stock-box">
            <span class="label">Current Stock</span>
            <span class="value">{{ number_format($current_stock) }}</span>
        </div>
        <div class="stock-box" style="border-color: #fca5a5;">
            <span class="label" style="color: #f43f5e;">Lock Stock</span>
            <span class="value text-red">{{ number_format($lock_stock) }}</span>
        </div>
        <div class="stock-box" style="border-color: #fcd34d;">
            <span class="label" style="color: #f59e0b;">Shadow Stock</span>
            <span class="value text-orange">{{ number_format($shadow_stock) }}</span>
        </div>
        <div class="stock-box" style="border-color: #6ee7b7;">
            <span class="label" style="color: #10b981;">Available Stock</span>
            <span class="value text-green">{{ number_format($available_stock) }}</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 80px;">Waktu</th>
                <th>Gudang</th>
                <th>Tipe Trx</th>
                <th style="text-align: right;">Masuk/Keluar</th>
                <th>Keterangan</th>
                <th style="text-align: right; width: 80px;">Saldo Current</th>
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
                <td>
                    <div style="font-weight: bold;">{{ $t->created_at->format('d/m/Y') }}</div>
                    <div style="font-size: 9px; color: #666;">{{ $t->created_at->format('H:i') }}</div>
                </td>
                <td style="font-weight: bold;">{{ $t->warehouse->name }}</td>
                <td>
                    <span class="type-badge">{{ str_replace('_', ' ', $t->type) }}</span>
                </td>
                <td style="text-align: right;">
                    @if(in_array($t->type, ['IN', 'SHADOW_IN', 'LOCK_IN']))
                        <span class="text-green font-bold">+{{ number_format($t->quantity) }}</span>
                    @else
                        <span class="text-red font-bold">-{{ number_format($t->quantity) }}</span>
                    @endif
                </td>
                <td>
                    <div style="font-weight: bold;">{{ $t->reference_no }}</div>
                    <div style="font-size: 9px; color: #666;">{{ $t->note ?? '-' }}</div>
                </td>
                <td style="text-align: right; font-weight: 900; background: #f8f9fa;">
                    {{ number_format($t->running_balance) }}
                </td>
            </tr>
            @empty
            <tr><td colspan="6" style="text-align: center; padding: 20px; color: #666;">Belum ada pergerakan stok.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer-grid">
        <div class="sig-box">
            <p>Diperiksa Oleh</p>
            <div>
                <div class="sig-line"></div>
                <p>Kepala Gudang</p>
            </div>
        </div>
        <div class="sig-box">
            <p>Dicetak Oleh</p>
            <div>
                <div class="sig-line"></div>
                <p>{{ Auth::user()->name }}</p>
            </div>
        </div>
    </div>

    <div style="margin-top: 50px; font-size: 8px; color: #999; text-align: center; border-top: 1px dashed #ddd; padding-top: 10px;">
        Dokumen ini digenerate secara otomatis oleh sistem Aori pada {{ now()->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>
