<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Pengantar Magang</title>
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

        td {
            text-align: justify;
        }

        .table2 {
            margin-top: -30px;
        }

        .img-blu {
            position: absolute;
            right: 30;
            bottom: 30;
        }
        
        .nama_pimpinan {
            position: relative;
            right: 28px;
        }

        .container {
            display: flex;
            justify-content: flex-end;
        }

        textarea {
            white-space: pre-line;
        }

        .indent {
            margin-left: -24px;
        }

        .right {
            position: relative;
            right: 7px;
        }

        .align-title td {
            vertical-align: top;
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
                <td>
                    <font size="3">Nomor</font>
                </td>
                <td>: <font size="3">{{ $data->nomor_surat}}</font>
                </td>
                <td style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; text-align: right;">
                    <font size="3">Karawang, {{ \Carbon\Carbon::parse($data['updated_at'])->locale('id_ID')->isoFormat('D MMMM Y') }}</font>
                </td>
            </tr>
            <tr>
                <td>
                    <font size="3">Perihal</font>
                </td>
                <td>: <font size="3">Permohonan Izin Magang</font>
                </td>
            </tr>
        </table>

        <br>
        <table border="0" width="450">
            <tr>
                <td>
                    <font size="3">Yth. <br> {{ $data['tujuan_surat'] }} <br> {{ $data['tujuan_instansi'] }} <br> di <br> {{ $data['domisili_instansi'] }}
                    </font>
                </td>
            </tr>
        </table>
        <br>

        <table border="0" width="450" style="text-align:justify;">
            <tr>
                <td>
                    <font size="3">Disampaikan Dengan Hormat,</font>
                </td>
            </tr>
        </table>
        <table border="0" width="450" style="text-align:justify;">
            <tr>
                <td>
                    <font size="3">Sehubungan dengan adanya kewajiban untuk memenuhi kurikulum pada Fakultas Ilmu Komputer Universitas Singaperbangsa Karawang yang harus dituntaskan, mahasiswa diwajibkan melaksanakan Magang di luar kampus Fakultas Ilmu Komputer Universitas Singaperbangsa Karawang, maka dengan ini kami memohon izin untuk mahasiswa di bawah ini:</font>
                </td>
            </tr>
        </table>

        <br>

        <table border="0" width="450" style="text-align:justify;">
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
                    <font size="3"> {{ $data['npm'] }}</font>
                </td>
            </tr>
            <tr>
                <td>
                    <font size="3">Tempat, Tanggal Lahir</font>
                </td>
                <td>:</td>
                <td>
                    <font size="3"> {{ $data['tempat_lahir'] }}, {{ \Carbon\Carbon::parse($data['tanggal_lahir'])->locale('id_ID')->isoFormat('D MMMM Y') }}</font>
                </td>
            </tr>
            <tr>
                <td>
                    <font size="3">Agama</font>
                </td>
                <td>:</td>
                <td>
                    <font size="3"> {{ $data['agama'] }}</font>
                </td>
            </tr>
            <tr>
                <td>
                    <font size="3">Alamat</font>
                </td>
                <td>:</td>
                <td>
                    <font size="3"> {{ $data['alamat'] }}</font>
                </td>
            </tr>
            <tr>
                <td>
                    <font size="3">Email</font>
                </td>
                <td>:</td>
                <td>
                    <font size="3"> {{ $data['email'] }}</font>
                </td>
            </tr>
            <tr>
                <td>
                    <font size="3">Program Studi</font>
                </td>
                <td>:</td>
                <td>
                    <font size="3"> {{ $data['prodi'] }}</font>
                </td>
            </tr>
            <tr>
                <td width="170">
                    <font size="3">Jenjang Pendidikan</font>
                </td>
                <td>:</td>
                <td>
                    <font size="3"> Sarjana (S-1)</font>
                </td>
            </tr>
            <tr class="align-title">
                <td>
                    <font size="3">Nomor Kontak</font>
                </td>
                <td>:</td>
                <td>
                    <font size="3"> {{ $data['no_hp'] }}</font>
                </td>
            </tr>
        </table>

        <br>
        <table border="0" width="450" style="text-align:justify;">
            <tr>
                <td>
                    <font size="3">Dengan ini kami mengajukan permohonan Magang/Praktek Kerja Lapangan di lembaga/perusahaan yang Bapak/Ibu pimpin. Demikian disampaikan, atas perhatian dan perkenannya kami ucapkan terima kasih.
                    </font>
                </td>
            </tr>
        </table>

        <br>
        <br>
        <br>

        <table border="0" width="450">
            <tr>
                <td width="300"></td>
                <td align="left">
                    {{--A.n Dekan, <br> Koor. Program Studi--}}

                    <br>
                    <font size="3" class="indent">{!! nl2br($ttdPimpinanDataIF[0]->penanda_tangan ?? $defaultTtdData['penanda_tangan'] ?? '') !!}</font>
                    <br>
                    <div class="container">
                        <br>
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
                    <br>
                    <br>

                    <font size="3">{{ $ttdPimpinanDataIF[0]->nama_pimpinan ?? $defaultTtdData['nama_pimpinan'] ?? '' }}</font>
                    {{-- <font size="3">E.Haodudin Nurkifli, S.T., M.Cs., Phd.</font> --}}
                    <br>
                    {{-- <font size="3">NIDN : 003048503</font> --}}
                    <font size="3">{{ $ttdPimpinanDataIF[0]->nomor_induk ?? $defaultTtdData['nomor_induk'] ?? '' }}</font>

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
