<table style="border: 1px solid #000;">
    <thead>
        <tr>
            <th style="font-weight: bold; text-align: center;">NO</th>
            <th style="font-weight: bold; text-align: center;">TANGGAL TRANSAKSI</th>
            <th style="font-weight: bold; text-align: center;">NO STRUK</th>
            <th style="font-weight: bold; text-align: center;">NAMA PENERIMA</th>
            <th style="font-weight: bold; text-align: center;">NO RESI</th>
            <th style="font-weight: bold; text-align: center;">JUMLAH PASPOR</th>
            <th style="font-weight: bold; text-align: center;">TOTAL PEMBAYARAN PEMOHON</th>
            <th style="font-weight: bold; text-align: center;">BIAYA KURIR (RESI)</th>
            <th style="font-weight: bold; text-align: center;">TANGGAL PENGIRIMAN PASPOR</th>
            <th style="font-weight: bold; text-align: center;">TANGGAL PASPOR DITERIMA</th>
        </tr>
    </thead>
    <tbody>
        @foreach($paspors as $index => $paspor)
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td>{{ $paspor->tanggal_transaksi ? $paspor->tanggal_transaksi->format('d/m/Y') : '' }}</td>
                <td style="text-align: center;">{{ $paspor->no_struk }}</td>
                <td>{{ $paspor->nama_penerima }}</td>
                <td>{{ $paspor->no_resi }}</td>
                <td style="text-align: center;">{{ $paspor->jumlah_paspor }}</td>
                <td style="text-align: right;">{{ $paspor->total_pembayaran_pemohon }}</td>
                <td style="text-align: right;">{{ $paspor->biaya_kurir }}</td>
                <td>{{ $paspor->tanggal_pengiriman_paspor ? $paspor->tanggal_pengiriman_paspor->format('d/m/Y') : '' }}</td>
                <td>{{ $paspor->tanggal_paspor_diterima ? $paspor->tanggal_paspor_diterima->format('d/m/Y') : '' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
