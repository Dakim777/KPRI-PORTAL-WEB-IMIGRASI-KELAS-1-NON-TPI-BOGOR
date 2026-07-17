<table>
    <tr>
        <td colspan="8">REKAPITULASI PENDAPATAN GLOBAL KANTIN</td>
    </tr>
    <tr>
        <td colspan="8">PERIODE: {{ count($laporanData) > 0 ? strtoupper($laporanData[0]['tanggal']) : 'SEMUA WAKTU' }}</td>
    </tr>
    <tr>
        <td colspan="8"></td>
    </tr>
    <tr>
        <th>NO</th>
        <th>NAMA PRODUK</th>
        <th>TERJUAL (QTY)</th>
        <th>TOTAL OMSET (PENJUALAN KOTOR)</th>
        <th>TOTAL MODAL (HPP)</th>
        <th>LABA KOTOR</th>
        <th>PENDAPATAN KOPERASI (70%)</th>
        <th>BAGIAN BU AJAT (30%)</th>
    </tr>

    @php 
        $no = 1;
        $currentRow = 5; // Data starts at row 5
    @endphp

    @foreach($laporanData as $data)
        @php
            $qty = $data['keluar'];
            $omset = $qty * $data['harga_jual'];
            $modal = ($data['qty_hpp_lama'] * $data['hpp_lama']) + ($data['qty_hpp_baru'] * $data['hpp_baru']);
        @endphp
        <tr>
            <td>{{ $no++ }}</td>
            <td>{{ $data['nama_produk'] }}</td>
            <td>{{ $qty }}</td>
            <td>{{ $omset }}</td>
            <td>{{ $modal }}</td>
            
            <!-- LABA KOTOR: D - E -->
            <td>=D{{$currentRow}}-E{{$currentRow}}</td>
            
            <!-- KOPERASI 70%: F * 0.7 -->
            <td>=F{{$currentRow}}*0.7</td>
            
            <!-- BU AJAT 30%: F * 0.3 -->
            <td>=F{{$currentRow}}*0.3</td>
        </tr>
        @php $currentRow++; @endphp
    @endforeach

    @php 
        $lastDataRow = $currentRow - 1; 
        $totalRow = $currentRow; 
    @endphp

    <tr>
        <td colspan="2" style="text-align: right;">TOTAL KESELURUHAN:</td>
        <td>=SUM(C4:C{{$lastDataRow}})</td>
        <td>=SUM(D4:D{{$lastDataRow}})</td>
        <td>=SUM(E4:E{{$lastDataRow}})</td>
        <td>=SUM(F4:F{{$lastDataRow}})</td>
        <td>=SUM(G4:G{{$lastDataRow}})</td>
        <td>=SUM(H4:H{{$lastDataRow}})</td>
    </tr>
</table>
