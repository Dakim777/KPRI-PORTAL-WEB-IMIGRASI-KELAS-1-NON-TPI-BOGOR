<table style="border: 1px solid #000;">
    <thead>
        <tr>
            <th style="font-weight: bold; text-align: center; background-color: #FFA500;">NO</th>
            <th style="font-weight: bold; text-align: center; background-color: #FFA500;">TANGGAL TRANSAKSI</th>
            <th style="font-weight: bold; text-align: center; background-color: #FFA500;">NO STRUK</th>
            <th style="font-weight: bold; text-align: center; background-color: #FFA500;">NAMA PENERIMA</th>
            <th style="font-weight: bold; text-align: center; background-color: #FFA500;">NO RESI</th>
            <th style="font-weight: bold; text-align: center; background-color: #FFA500;">JUMLAH PASPOR</th>
            <th style="font-weight: bold; text-align: center; background-color: #FFA500;">TOTAL PEMBAYARAN PEMOHON</th>
            <th style="font-weight: bold; text-align: center; background-color: #FFA500;">BIAYA KURIR (RESI)</th>
            <th style="font-weight: bold; text-align: center; background-color: #FFA500;">BIAYA MODAL PACKAGING</th>
            <th style="font-weight: bold; text-align: center; background-color: #FFA500;">HANDLING FEE</th>
            <th style="font-weight: bold; text-align: center; background-color: #FFA500;">BIAYA MATERAI</th>
            <th style="font-weight: bold; text-align: center; background-color: #FFA500;">BIAYA SURAT KUASA</th>
            <th style="font-weight: bold; text-align: center; background-color: #FFA500;">PENDAPATAN</th>
            <th style="font-weight: bold; text-align: center; background-color: #FFA500;">FEE PETUGAS</th>
            <th style="font-weight: bold; text-align: center; background-color: #FFA500;">PENDAPATAN KOPERASI BERSIH</th>
            <th style="font-weight: bold; text-align: center; background-color: #FFA500;">TANGGAL PENGIRIMAN PASPOR</th>
            <th style="font-weight: bold; text-align: center; background-color: #FFA500;">TANGGAL PASPOR DITERIMA</th>
        </tr>
    </thead>
    <tbody>
        @foreach($paspors as $index => $paspor)
            @php 
                $row = $index + 2; 
                $totalPembayaran = (float) ($paspor->total_pembayaran_pemohon ?? 0);
                $biayaKurir      = (float) ($paspor->biaya_kurir ?? 0);
                $biayaPackaging  = (float) ($paspor->biaya_modal_packaging ?? 0);
                $handlingFee     = (float) ($paspor->handling_fee ?? 0);
                $hargaMaterai    = (float) ($paspor->harga_materai ?? 0);
                $hargaSuratKuasa = (float) ($paspor->harga_surat_kuasa ?? 0);
                $pendapatan      = $totalPembayaran - ($biayaKurir + $biayaPackaging + $handlingFee + $hargaMaterai + $hargaSuratKuasa);
                $feePetugas      = 0;
                $pendapatanBersih= $pendapatan - $feePetugas;

                $tglTransaksi = $paspor->tanggal_transaksi instanceof \Carbon\CarbonInterface 
                    ? $paspor->tanggal_transaksi->format('d/m/Y') 
                    : ($paspor->tanggal_transaksi ? (string)$paspor->tanggal_transaksi : '');

                $tglPengiriman = $paspor->tanggal_pengiriman_paspor instanceof \Carbon\CarbonInterface 
                    ? $paspor->tanggal_pengiriman_paspor->format('d/m/Y') 
                    : ($paspor->tanggal_pengiriman_paspor ? (string)$paspor->tanggal_pengiriman_paspor : '');

                $tglDiterima = $paspor->tanggal_paspor_diterima instanceof \Carbon\CarbonInterface 
                    ? $paspor->tanggal_paspor_diterima->format('d/m/Y') 
                    : ($paspor->tanggal_paspor_diterima ? (string)$paspor->tanggal_paspor_diterima : '');
            @endphp
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td>{{ $tglTransaksi }}</td>
                <td style="text-align: center;">{{ $paspor->no_struk }}</td>
                <td>{{ $paspor->nama_penerima }}</td>
                <td>{{ $paspor->no_resi }}</td>
                <td style="text-align: center;">{{ $paspor->jumlah_paspor }}</td>
                <td style="text-align: right;">{{ $totalPembayaran }}</td>
                <td style="text-align: right; {{ $biayaKurir == 0 ? 'background-color: #FFFF00;' : '' }}">{{ $biayaKurir }}</td>
                <td style="text-align: right; {{ $biayaPackaging == 0 ? 'background-color: #FFFF00;' : '' }}">{{ $biayaPackaging }}</td>
                <td style="text-align: right; {{ $handlingFee == 0 ? 'background-color: #FFFF00;' : '' }}">{{ $handlingFee }}</td>
                <td style="text-align: right; {{ $hargaMaterai == 0 ? 'background-color: #FFFF00;' : '' }}">{{ $hargaMaterai }}</td>
                <td style="text-align: right; {{ $hargaSuratKuasa == 0 ? 'background-color: #FFFF00;' : '' }}">{{ $hargaSuratKuasa }}</td>
                <td style="text-align: right;">=G{{ $row }}-(H{{ $row }}+I{{ $row }}+J{{ $row }}+K{{ $row }}+L{{ $row }})</td>
                <td style="text-align: right;">0</td>
                <td style="text-align: right;">=M{{ $row }}-N{{ $row }}</td>
                <td>{{ $tglPengiriman }}</td>
                <td>{{ $tglDiterima }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
