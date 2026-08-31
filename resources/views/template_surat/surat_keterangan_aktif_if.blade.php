<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Keterangan Aktif Kuliah</title>
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
        }

        .left {
            width: 1px;
            position: relative;
            right: 60px;
            top: -25px;
            float: left;
        }

        .table2 {
            margin-top: -30px;
            text-align: center;
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
    </style>
</head>

<body>
    <center>
        <table border="0">
            <tr>
                <img class="right" src="{{  public_path('storage/ttd/kop.png') }}" width="102%" alt="">
            </tr>
        </table>
        <br>
        <br>

        <table border="0" width="450" class="table2">
            <tr>
                <td align="center">
                    <font size="3">SURAT KETERANGAN MAHASISWA AKTIF</font>
                </td>
            </tr>
            <tr>
                <td>
                    <font size="3">Nomor : {{ $data['nomor_surat'] }}</font>
                </td>
            </tr>
        </table>

        <br>

        <table border="0" width="450">
            <tr>
                <td>
                    <font size="3">Dekan Fakultas Ilmu Komputer Universitas Singaperbangsa Karawang dengan ini
                        menerangkan :</font>
                </td>
            </tr>
        </table>

        <br>

        <table border="0" width="450" style="text-align: justify;">
            <tr>
                <td>
                    <font size="3">Nama</font>
                </td>
                <td>:</td>
                <td>
                    <font size="3">{{ $data['nama_mhs'] }}</font>
                </td>
            </tr>
            <tr>
                <td>
                    <font size="3">NPM</font>
                </td>
                <td>:</td>
                <td>
                    <font size="3">{{ $data['npm'] }}</font>
                </td>
            </tr>
            <tr>
                <td>
                    <font size="3">Semester</font>
                </td>
                <td>:</td>
                <td>
                    <font size="3">{{ $data['semester'] }}</font>
                </td>
            </tr>
            <tr>
                <td width="170">
                    <font size="3"> Fakultas / Program Studi</font>
                </td>
                <td>:</td>
                <td>
                    <font size="3">Ilmu Komputer/S1 - {{ $data['prodi'] }}</font>
                </td>
            </tr>
            <tr>
                <td>
                    <font size="3">Tempat, Tanggal Lahir</font>
                </td>
                <td>:</td>
                <td>
                    <font size="3">{{ $data['tempat_lahir'] }}, {{ \Carbon\Carbon::parse($data['tgl_lahir'])->locale('id_ID')->isoFormat('D MMMM Y') }}</font>
                </td>
            </tr>
            <td>
                <font size="3">Alamat</font>
            </td>
            <td>:</td>
            <td>
                <font size="3">{{$data['alamat']}}</font>
            </td>
        </table>

        <br>
        <table border="0" width="450" style="text-align: justify;">
            <tr>
                <td>
                    <font size="3">Adalah benar mahasiswa Program Studi S1 - {{ $data['prodi'] }} Fakultas Ilmu Komputer
                        Universitas Singaperbangsa Karawang, yang bersangkutan aktif mengikuti perkuliahan pada tahun
                        akademik {{ $data['tahun_akademik'] }} duduk pada Semester {{ $data['semester'] }}.</font>
                </td>
            </tr>
        </table>
        <br>

        <table border="0" width="450" style="text-align: justify;">
            <tr>
                <td>
                    <font size="3">Demikian Surat Keterangan ini kami buat dengan sebenarnya, untuk dipergunakan
                        sebagaimana mestinya.
                    </font>
                </td>
            </tr>
        </table>

        <br>
        <br>
        <br>

        <table border="0" width="450">
            <tr>
                <td width="280"></td>
                <td align="left">
                    <font size="3">Karawang, {{ \Carbon\Carbon::parse($data['updated_at'])->locale('id_ID')->isoFormat('D MMMM Y') }} </font>
                    <br>
                    <font size="3" class="indent">{!! nl2br($ttdPimpinanDataIF[0]->penanda_tangan ?? $defaultTtdData['penanda_tangan'] ?? '') !!},</font>
                    <br>
                    <div class="container">
                        <div class="left">
                            @if (isset($ttdPimpinanDataIF) && $ttdPimpinanDataIF->isNotEmpty() && isset($ttdPimpinanDataIF[0]->ttd_image))
                            <img src="{{ public_path('storage/ttd/terbaru/' . $ttdPimpinanDataIF[0]->ttd_image) }}" width="180" alt="">
                            @else
                            <img src="{{ public_path('storage/ttd/'. $defaultTtdData['ttd_image']) }}" width="180" alt="">
                            @endif
                        </div>
                    </div>
                    <br>
                    <br>
                    <br>
                    <br>

                    <font size="3">{{ $ttdPimpinanDataIF[0]->nama_pimpinan ?? $defaultTtdData['nama_pimpinan'] ?? '' }}
                    </font>
                    <br>
                    <font size="3">
                        {{ $ttdPimpinanDataIF[0]->nomor_induk ?? $defaultTtdData['nomor_induk'] ?? '' }}
                    </font>

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
