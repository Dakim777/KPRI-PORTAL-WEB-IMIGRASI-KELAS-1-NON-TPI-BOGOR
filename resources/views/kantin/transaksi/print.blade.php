<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Struk #{{ str_pad($transaksi->id, 6, '0', STR_PAD_LEFT) }}</title>
    <style>
        /* ── Reset & Base ─────────────────────────────────────────── */
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            background: #fff;
            color: #000;
            width: 80mm;
            margin: 0 auto;
            padding: 4mm 4mm 10mm;
        }

        /* ── Layout Helpers ──────────────────────────────────────── */
        .center  { text-align: center; }
        .right   { text-align: right; }
        .left    { text-align: left; }
        .bold    { font-weight: bold; }
        .sm      { font-size: 10px; }
        .lg      { font-size: 14px; }
        .xl      { font-size: 16px; }

        .divider-solid  { border: none; border-top: 1px solid #000; margin: 3mm 0; }
        .divider-dashed { border: none; border-top: 1px dashed #666; margin: 3mm 0; }

        /* ── Row: two-column flex ────────────────────────────────── */
        .row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1mm;
        }
        .row .label { flex: 0 0 auto; max-width: 50%; }
        .row .value { flex: 0 0 auto; text-align: right; }

        /* ── Item row: name left, subtotal right, qty below name ── */
        .item { margin-bottom: 2mm; }
        .item-name { font-weight: bold; }
        .item-sub  { color: #333; font-size: 11px; }

        /* ── Total block ─────────────────────────────────────────── */
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5mm 0;
        }
        .grand-total {
            font-size: 14px;
            font-weight: bold;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            padding: 1.5mm 0;
            margin: 1mm 0;
        }

        /* ── Print CSS ───────────────────────────────────────────── */
        @media print {
            @page {
                margin: 0;
                size: 80mm auto;
            }
            body {
                width: 80mm;
                padding: 3mm 3mm 8mm;
            }
            .no-print { display: none !important; }
        }

        /* ── Screen preview wrapper ──────────────────────────────── */
        @media screen {
            html { background: #f0f0f0; }
            body {
                border: 1px solid #ccc;
                margin: 20px auto;
                box-shadow: 0 2px 8px rgba(0,0,0,0.15);
                min-height: 100px;
            }
            .no-print {
                text-align: center;
                margin: 10px 0 4px;
            }
            .no-print button {
                font-family: inherit;
                font-size: 11px;
                padding: 4px 14px;
                background: #0D1F3C;
                color: #fff;
                border: none;
                border-radius: 4px;
                cursor: pointer;
            }
        }
    </style>
</head>
<body>

    {{-- ── Screen-only print button ─────────────────────────────── --}}
    <div class="no-print">
        <button onclick="window.print()">Cetak Struk</button>
    </div>

    {{-- ── Store Header ──────────────────────────────────────────── --}}
    <p class="center bold xl">KPRI</p>
    <p class="center sm">Kantor Imigrasi Bogor</p>
    <p class="center sm">Kelas 1 Non TPI Bogor</p>

    <hr class="divider-solid" />

    {{-- ── Transaction Info ─────────────────────────────────────── --}}
    <div class="row">
        <span class="label">No. Struk</span>
        <span class="value bold">#{{ str_pad($transaksi->id, 6, '0', STR_PAD_LEFT) }}</span>
    </div>
    <div class="row">
        <span class="label">Tanggal</span>
        <span class="value">{{ $transaksi->created_at->format('d/m/Y') }}</span>
    </div>
    <div class="row">
        <span class="label">Waktu</span>
        <span class="value">{{ $transaksi->created_at->setTimezone('Asia/Jakarta')->format('H:i:s') }} WIB</span>
    </div>
    <div class="row">
        <span class="label">Kasir</span>
        <span class="value">{{ $transaksi->kasir->name ?? '-' }}</span>
    </div>

    <hr class="divider-dashed" />

    {{-- ── Item Lines ────────────────────────────────────────────── --}}
    @foreach($transaksi->details as $detail)
    <div class="item">
        <p class="item-name">{{ $detail->produk->nama_produk ?? '—' }}</p>
        <div class="row">
            <span class="item-sub">{{ $detail->qty }} x Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}</span>
            <span class="bold">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</span>
        </div>
    </div>
    @endforeach

    <hr class="divider-dashed" />

    {{-- ── Totals ────────────────────────────────────────────────── --}}
    <div class="total-row">
        <span>Subtotal</span>
        <span>Rp {{ number_format($transaksi->total_harga, 0, ',', '.') }}</span>
    </div>
    <div class="total-row grand-total">
        <span>TOTAL</span>
        <span>Rp {{ number_format($transaksi->total_harga, 0, ',', '.') }}</span>
    </div>
    <div class="total-row">
        <span>Metode Bayar</span>
        <span style="text-transform: uppercase;">{{ $transaksi->metode_bayar ?? 'CASH' }}</span>
    </div>
    @if(strtolower($transaksi->metode_bayar) === 'qris')
    <div class="total-row">
        <span>Dibayar (QRIS)</span>
        <span>Rp {{ number_format($transaksi->total_harga, 0, ',', '.') }}</span>
    </div>
    @else
    <div class="total-row">
        <span>Tunai</span>
        <span>Rp {{ number_format($transaksi->jumlah_bayar, 0, ',', '.') }}</span>
    </div>
    <div class="total-row bold">
        <span>Kembali</span>
        <span>Rp {{ number_format($transaksi->kembalian, 0, ',', '.') }}</span>
    </div>
    @endif

    <hr class="divider-solid" />

    {{-- ── Footer ───────────────────────────────────────────────── --}}
    <p class="center sm" style="margin-top:3mm;">Barang yang sudah dibeli</p>
    <p class="center sm">tidak dapat dikembalikan</p>
    <p class="center bold sm" style="margin-top:3mm;">Silakan Datang Kembali</p>

    {{-- ── Auto-Print Script ────────────────────────────────────── --}}
    <script>
        window.addEventListener('load', function () {
            // Short delay to ensure styles are applied before print dialog opens
            setTimeout(function () {
                window.print();
                // Close the tab after the print dialog is dismissed
                window.addEventListener('afterprint', function () {
                    window.close();
                });
            }, 400);
        });
    </script>

</body>
</html>
