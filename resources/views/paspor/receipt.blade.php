<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pembayaran - {{ $paspor->id_transaksi }}</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f0f0f0;
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            color: #000;
        }
        .receipt-container {
            width: 80mm;
            margin: 0 auto;
            background-color: #fff;
            padding: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .border-dashed { border-bottom: 1px dashed #000; margin: 10px 0; }
        
        .header h3, .header p {
            margin: 2px 0;
            line-height: 1.2;
        }
        .header h3 { font-size: 14px; }
        
        .info-table { width: 100%; margin-bottom: 10px; }
        .info-table td { padding: 2px 0; vertical-align: top; }
        .info-table td:first-child { width: 110px; }
        
        .item-table { width: 100%; margin-bottom: 10px; }
        .item-table th, .item-table td { padding: 4px 0; text-align: left; }
        .item-table .price { text-align: right; }
        
        .total-section { font-size: 14px; font-weight: bold; }
        .footer { font-size: 10px; margin-top: 15px; }
        
        @media print {
            body { background-color: #fff; }
            .receipt-container { width: 100%; box-shadow: none; padding: 0; margin: 0; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="receipt-container">
        <!-- Header -->
        <div class="header text-center border-dashed" style="padding-bottom: 10px;">
            <h3>KPRI KANTOR IMIGRASI<br>KELAS I NON TPI BOGOR</h3>
            <p>Badan Hukum: AHU-0003826.AH.01.38.2021</p>
            <br>
            <p>Jl. Jend. A. Yani No. 19, Tanah Sareal<br>
               Email: kppdk.kanimbogor@gmail.com<br>
               Bogor (16161)</p>
        </div>

        <div class="text-center font-bold" style="margin: 15px 0;">
            KWITANSI PEMBAYARAN (OFFICIAL RECEIPT)
        </div>

        <!-- Info -->
        <table class="info-table">
            <tr>
                <td>No. Resi/Kwitansi</td>
                <td>: {{ $paspor->no_resi ?? $paspor->no_struk }}</td>
            </tr>
            <tr>
                <td>Tanggal</td>
                <td>: {{ $paspor->tanggal_transaksi ? $paspor->tanggal_transaksi->format('d/m/Y') : '-' }}</td>
            </tr>
            <tr>
                <td>Nama Pelanggan</td>
                <td>: {{ strtoupper($paspor->nama_penerima) }}</td>
            </tr>
            <tr>
                <td>No. HP</td>
                <td>: {{ $paspor->no_hp ?? '-' }}</td>
            </tr>
            <tr>
                <td>Ekspedisi</td>
                <td>: {{ strtoupper($paspor->jenis_kurir ?? 'POS') }} ({{ $paspor->layanan_kurir ?? 'Reguler' }})</td>
            </tr>
        </table>

        <div class="border-dashed"></div>
        <div class="text-center font-bold" style="margin-bottom:10px;">
            ITEM / LAYANAN<br>SUBTOTAL
        </div>
        <div class="border-dashed"></div>

        <!-- Items -->
        <table class="item-table">
            <tr>
                <td>1. Layanan Kurir & Tracking</td>
                <td class="price">Rp {{ number_format($paspor->biaya_kurir ?? 0, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>2. Safe Packaging</td>
                <td class="price">Rp {{ number_format($paspor->biaya_modal_packaging ?? 0, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>3. Handling Fee</td>
                <td class="price">Rp {{ number_format($paspor->handling_fee ?? 0, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>4. Bea Materai</td>
                <td class="price">Rp {{ number_format($paspor->harga_materai ?? 0, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>5. Surat Kuasa</td>
                <td class="price">Rp {{ number_format($paspor->harga_surat_kuasa ?? 0, 0, ',', '.') }}</td>
            </tr>

        </table>

        <div class="border-dashed"></div>

        <!-- Total -->
        <table class="item-table total-section">
            <tr>
                <td>TOTAL PEMBAYARAN:</td>
                <td class="price">Rp {{ number_format($paspor->total_pembayaran_pemohon ?? 0, 0, ',', '.') }}</td>
            </tr>
        </table>

        <div class="text-center" style="margin-top: 15px;">
            Metode Pembayaran<br>
            [{{ strtoupper($paspor->metode_pembayaran ?? 'Cash') }}]
        </div>

        <div class="text-center font-bold" style="margin-top: 10px; font-size: 14px;">
            Status: LUNAS
        </div>

        <div class="border-dashed"></div>

        <!-- Footer -->
        <div class="footer">
            <p>PERHATIAN:<br>
            1. Dokumen paspor yang sudah dikirim menjadi tanggung jawab ekspedisi. Silakan cek berkala status kiriman.<br>
            2. Mohon Cek berkala ke nomor Hotline Kami di 082121240900 (wa only)</p>
            <br>
            <p>Terima kasih telah menggunakan jasa<br>
            KPRI Imigrasi Bogor</p>
        </div>
    </div>
</body>
</html>
