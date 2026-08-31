<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Bebas Pustaka</title>
    <style>
        table tr td {
            font-size: 13px;
        }

        body {
            margin-left: 5px;
        }

        table {
            margin-left: auto;
            margin-right: auto;
            margin-top: -10px;
        }

        .left {
            width: 1px;
            position: relative;
            left: -5px;
            top: -15px;
            float: left;
        }

        .table2 {
            margin-top: -30px;
        }

        .img-blu {
            position: absolute;
            right: 30;
            bottom: 30;
        }

        .container {
            display: flex;
            justify-content: flex-end;
        }

        .indent {
            margin-left: -24px;
        }

        .right {
            position: relative;
            right: 7px;
        }

        table .table-ttd {
            width: 100%;
        }
        
        .table-ttd table {
            width: auto;
            margin-left: auto;
            margin-right: 0;
        }
    </style>
</head>

<body>
    <center>
        <table border="0">
            <tr>
                <img class="right" src="{{ public_path('storage/ttd/kop.png') }}" width="102%" alt="">
            </tr>
        </table>

        <br>
        <br>

        <table border="0" width="450" class="table2">
            <tr>
                <td>
                    <font size="3">Nomor</font>
                </td>
                <td>:</td>
                <td>
                    <font size="3">{{ $data['nomor_surat'] }}</font>
                </td>
                <td style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; text-align: right;">
                    <font size="3">Karawang, {{ \Carbon\Carbon::parse($data['updated_at'])->locale('id_ID')->isoFormat('D MMMM Y') }}</font>
                </td>
            </tr>
            <tr>
                <td>
                    <font size="3">Perihal</font>
                </td>
                <td>:</td>
                <td>
                    <font size="3">Surat Pengantar Permohonan Bebas Pustaka</font>
                </td>
            </tr>
        </table>

        <br>
        <table border="0" width="450">
            <tr>
                <td>
                    <font size="3">Yth. <br> Kepala Perpustakaan<br>Universitas Singaperbangsa Karawang <br> di <br>
                        Tempat
                    </font>
                </td>
            </tr>
        </table>
        <br>

        <table border="0" width="450">
            <tr>
                <td align="justify">
                    <font size="3">Sehubungan dengan Surat Izin Cuti Akademik dari Universitas Singaperbangsa
                        Karawang
                        yang diajukan oleh mahasiswa kami, selanjutnya kami mohon untuk dibuatkan surat keterangan bebas
                        pustaka pada nama di bawah ini:</font>
                </td>
            </tr>
        </table>

        <br>

        <table border="0" width="450">
            <tr>
                <td width="170">
                    <font size="3">Nama</font>
                </td>
                <td>:</td>
                <td>
                    <font size="3"> {{ $data['nama_mhs'] }}</font>
                </td>
            </tr>
            <tr>
                <td>
                    <font size="3">NPM</font>
                </td>
                <td>:</td>
                <td>
                    <font size="3"> {{ $data['npm'] }}</font>
                </td>
            </tr>
            <tr>
                <td>
                    <font size="3">Program Studi</font>
                </td>
                <td>:</td>
                <td>
                    <font size="3"> S1 - {{ $data['prodi'] }}</font>
                </td>
            </tr>
            <tr>
                <td>
                    <font size="3">Jenjang Pendidikan</font>
                </td>
                <td>:</td>
                <td>
                    <font size="3"> Strata (S-1)</font>
                </td>
            </tr>
        </table>

        <br>
        <table border="0" width="450">
            <tr>
                <td>
                    <font size="3">Demikian disampaikan, atas perhatian dan perkenannya kami ucapkan terima kasih.
                    </font>
                </td>
            </tr>
        </table>

        <br>
        <br>
        <br>
        <table border="0" width="500" class="table-ttd">
            <tr>
                <td>
                    <table border="0" >
                        <tr>
                            <td>
                                <font size="3" class="indent">{!! nl2br($ttdPimpinanDataSI[0]->penanda_tangan ?? $defaultTtdData['penanda_tangan'] ?? '') !!}</font>
                                <br>
                                <div class="">
                                    <div class="left">
                                        @if (isset($ttdPimpinanDataSI) && $ttdPimpinanDataSI->isNotEmpty() && isset($ttdPimpinanDataSI[0]->ttd_image))
                                        <img src="{{ public_path('storage/ttd/terbaru/' . $ttdPimpinanDataSI[0]->ttd_image) }}" width="180" alt="">
                                        @else
                                        <img src="{{ public_path('storage/ttd/'. $defaultTtdData['ttd_image']) }}" width="180" alt="">
                                        @endif
                                    </div>
                                </div>

                                <br>
                                <br>
                                <br>
                                <br>
            
                                <font size="3">{{ $ttdPimpinanDataSI[0]->nama_pimpinan ?? $defaultTtdData['nama_pimpinan'] ?? '' }}</font>
                                <br>
                                <font size="3">{{ $ttdPimpinanDataSI[0]->nomor_induk ?? $defaultTtdData['nomor_induk'] ?? '' }}</font>
                            </td>
                        </tr>
                    </table>

                    <br>
                    <br>
                    <br>
                </td>
            </tr>
        </table>
    </center>
    <img class='img-blu' src="{{ public_path('storage/ttd/blu.png') }}" width="60" height="60" alt="">
</body>

</html>
