<table>
    @php $no = 1; @endphp
    @foreach($laporanData as $index => $data)
        @php 
            // Setiap produk memakan 4 baris: 1 Header, 1 Subheader, 1 Data, 1 Kosong
            $currentRow = ($index * 4) + 3; 
        @endphp
        <!-- Product Header (Merged across 22 columns) -->
        <tr>
            <td colspan="22" style="text-align: center; font-weight: bold; background-color: #FFFF00;">{{ strtoupper($data['nama_produk']) }}</td>
        </tr>
        
        <!-- Column Headers -->
        <tr>
            <th style="background-color: #FFFF00; border: 1px solid #000000; font-weight: bold;">NO</th>
            <th style="background-color: #FFFF00; border: 1px solid #000000; font-weight: bold;">Tanggal</th>
            <th style="background-color: #FFFF00; border: 1px solid #000000; font-weight: bold;">STOK AWAL</th>
            <th style="background-color: #FFFF00; border: 1px solid #000000; font-weight: bold;">MASUK</th>
            <th style="background-color: #FFFF00; border: 1px solid #000000; font-weight: bold;">KELUAR</th>
            <th style="background-color: #FFFF00; border: 1px solid #000000; font-weight: bold;">STOK AKHIR</th>
            <th style="background-color: #FFFF00; border: 1px solid #000000; font-weight: bold;">HPP LAMA</th>
            <th style="background-color: #FFFF00; border: 1px solid #000000; font-weight: bold;">QTY TERJUAL HPP LAMA</th>
            <th style="background-color: #FFFF00; border: 1px solid #000000; font-weight: bold;">HPP BARU</th>
            <th style="background-color: #FFFF00; border: 1px solid #000000; font-weight: bold;">QTY TERJUAL HPP BARU</th>
            <th style="background-color: #FFFF00; border: 1px solid #000000; font-weight: bold;">HARGA JUAL</th>
            <th style="background-color: #FFFF00; border: 1px solid #000000; font-weight: bold;">SELISIH HPP LAMA</th>
            <th style="background-color: #FFFF00; border: 1px solid #000000; font-weight: bold;">SELISIH HPP BARU</th>
            <th style="background-color: #FFFF00; border: 1px solid #000000; font-weight: bold;">TOTAL PENJUALAN</th>
            <th style="background-color: #FFFF00; border: 1px solid #000000; font-weight: bold;">LABA KOTOR</th>
            <th style="background-color: #FFFF00; border: 1px solid #000000; font-weight: bold;">LABA BERSIH (70%)</th>
            <th style="background-color: #FFFF00; border: 1px solid #000000; font-weight: bold;">BAGIAN BU AJAT (30%)</th>
            <th style="background-color: #FFFF00; border: 1px solid #000000; font-weight: bold;">TOTAL PENJUALAN BESAR</th>
            <th style="background-color: #FFFF00; border: 1px solid #000000; font-weight: bold;">TOTAL LABA KOTOR</th>
            <th style="background-color: #FFFF00; border: 1px solid #000000; font-weight: bold;">TOTAL LABA BESAR (70%)</th>
            <th style="background-color: #FFFF00; border: 1px solid #000000; font-weight: bold;">TOTAL BAGIAN BU AJAT (30%)</th>
            <th style="background-color: #FFFF00; border: 1px solid #000000; font-weight: bold;">PEMBELIAN BARANG DAGANGAN</th>
        </tr>

        <!-- Data Row -->
        <tr>
            <td style="border: 1px solid #000000;">{{ $no++ }}</td>
            <td style="border: 1px solid #000000;">{{ $data['tanggal'] }}</td>
            <td style="border: 1px solid #000000;">{{ $data['stok_awal'] }}</td>
            <td style="border: 1px solid #000000;">{{ $data['masuk'] }}</td>
            <td style="border: 1px solid #000000;">{{ $data['keluar'] }}</td>
            
            <!-- F: STOK AKHIR = STOK AWAL + MASUK - KELUAR -->
            <td style="border: 1px solid #000000;">=C{{$currentRow}}+D{{$currentRow}}-E{{$currentRow}}</td>
            
            <td style="border: 1px solid #000000;">{{ $data['hpp_lama'] }}</td>
            <td style="border: 1px solid #000000;">{{ $data['qty_hpp_lama'] }}</td>
            
            <td style="border: 1px solid #000000;">{{ $data['hpp_baru'] ?: 0 }}</td>
            <td style="border: 1px solid #000000;">{{ $data['qty_hpp_baru'] ?: 0 }}</td>
            
            <td style="border: 1px solid #000000;">{{ $data['harga_jual'] }}</td>
            
            <!-- L: SELISIH HPP LAMA = HARGA JUAL - HPP LAMA -->
            <td style="border: 1px solid #000000;">=K{{$currentRow}}-G{{$currentRow}}</td>
            
            <!-- M: SELISIH HPP BARU = HARGA JUAL - HPP BARU (Jika kolom HPP Baru 0, hasil tetap aman karena pengalinya QTY HPP BARU akan 0) -->
            <td style="border: 1px solid #000000;">=IF(I{{$currentRow}}>0, K{{$currentRow}}-I{{$currentRow}}, 0)</td>
            
            <!-- N: TOTAL PENJUALAN = KELUAR * HARGA JUAL -->
            <td style="border: 1px solid #000000;">=E{{$currentRow}}*K{{$currentRow}}</td>
            
            <!-- O: LABA KOTOR = (QTY LAMA * SELISIH LAMA) + (QTY BARU * SELISIH BARU) -->
            <td style="border: 1px solid #000000;">=(H{{$currentRow}}*L{{$currentRow}})+(J{{$currentRow}}*M{{$currentRow}})</td>
            
            <!-- P: LABA BERSIH = LABA KOTOR * 0.70 -->
            <td style="border: 1px solid #000000;">=O{{$currentRow}}*0.7</td>
            
            <!-- Q: BAGIAN BU AJAT = LABA KOTOR * 0.30 -->
            <td style="border: 1px solid #000000;">=O{{$currentRow}}*0.3</td>
            
            <!-- R: TOTAL PENJUALAN BESAR (Copy of N) -->
            <td style="border: 1px solid #000000;">=N{{$currentRow}}</td>
            
            <!-- S: TOTAL LABA KOTOR (Copy of O) -->
            <td style="border: 1px solid #000000;">=O{{$currentRow}}</td>
            
            <!-- T: TOTAL LABA BESAR 70% (Copy of P) -->
            <td style="border: 1px solid #000000;">=P{{$currentRow}}</td>
            
            <!-- U: TOTAL BAGIAN BU AJAT 30% (Copy of Q) -->
            <td style="border: 1px solid #000000;">=Q{{$currentRow}}</td>
            
            <!-- V: PEMBELIAN BARANG DAGANGAN -->
            <td style="border: 1px solid #000000;">{{ $data['pembelian_barang'] }}</td>
        </tr>
        
        <!-- Baris Kosong sebagai Spasi antar Produk -->
        <tr>
            <td colspan="22"></td>
        </tr>
    @endforeach
</table>
