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
            @php $row = $index + 2; @endphp
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td>{{ $paspor->tanggal_transaksi ? $paspor->tanggal_transaksi->format('d/m/Y') : '' }}</td>
                <td style="text-align: center;">{{ $paspor->no_struk }}</td>
                <td>{{ $paspor->nama_penerima }}</td>
                <td>{{ $paspor->no_resi }}</td>
                <td style="text-align: center;">{{ $paspor->jumlah_paspor }}</td>
                <td style="text-align: right;">{{ $paspor->total_pembayaran_pemohon }}</td>
                <td style="text-align: right; {{ empty($paspor->biaya_kurir) || $paspor->biaya_kurir == 0 ? 'background-color: #FFFF00;' : '' }}">{{ $paspor->biaya_kurir ?? 0 }}</td>
                <td style="text-align: right; {{ empty($paspor->biaya_modal_packaging) || $paspor->biaya_modal_packaging == 0 ? 'background-color: #FFFF00;' : '' }}">{{ $paspor->biaya_modal_packaging ?? 0 }}</td>
                <td style="text-align: right; {{ empty($paspor->handling_fee) || $paspor->handling_fee == 0 ? 'background-color: #FFFF00;' : '' }}">{{ $paspor->handling_fee ?? 0 }}</td>
                <td style="text-align: right; {{ empty($paspor->harga_materai) || $paspor->harga_materai == 0 ? 'background-color: #FFFF00;' : '' }}">{{ $paspor->harga_materai ?? 0 }}</td>
                <td style="text-align: right; {{ empty($paspor->harga_surat_kuasa) || $paspor->harga_surat_kuasa == 0 ? 'background-color: #FFFF00;' : '' }}">{{ $paspor->harga_surat_kuasa ?? 0 }}</td>
                <td style="text-align: right;">=G{{ $row }}-(H{{ $row }}+I{{ $row }}+J{{ $row }}+K{{ $row }}+L{{ $row }})</td>
                <td style="text-align: right;">=0</td>
                <td style="text-align: right;">=M{{ $row }}-N{{ $row }}</td>
                <td>{{ $paspor->tanggal_pengiriman_paspor ? $paspor->tanggal_pengiriman_paspor->format('d/m/Y') : '' }}</td>
                <td>{{ $paspor->tanggal_paspor_diterima ? $paspor->tanggal_paspor_diterima->format('d/m/Y') : '' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
