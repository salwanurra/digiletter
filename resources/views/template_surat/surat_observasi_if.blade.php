<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Observasi</title>
    <style>
        .anggota th, .anggota td {
            border: 1px solid #000;
            padding: 6px 8px;
        }
        table tr td {
            font-size: 13px;
        }

        body {
            margin-left: 5px;
            height: 100vh;
            width: 100vw;
            position: relative;
        }

        table {
            margin-left: auto;
            margin-right: auto;
            margin-top: -10px;
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
                    <font size="3">Permohonan Izin Observasi</font>
                </td>
            </tr>
        </table>

        <br>
        <table border="0" width="450">
            <tr>
                <td>
                    <font size="3">Yth. <br> {{ $data['tujuan_surat'] }}<br>{{ $data['tujuan_instansi'] }} <br> di <br>
                        {{ $data['domisili_instansi'] }}
                    </font>
                </td>
            </tr>
        </table>
        <br>

        <table border="0" width="450">
            <tr>
                <td align="justify">
                    <font size="3" >Dipermaklumkan dengan hormat, sehubungan dengan adanya tugas dari mata kuliah 
                        {{ $data['mata_kuliah'] }} yang harus dilaksanakan di luar kampus 
                        Fakultas Ilmu Komputer Universitas Singaperbangsa Karawang, kami mohon bagi mahasiswa di bawah ini :</font>
                </td>
            </tr>
        </table>

        <br>

        <table class="anggota" border="1" width="450" cellspacing="0" cellpadding="5">
            <thead>
                <tr>
                    <td align="center" size="3"><b>No</b></td>
                    <td align="center" size="3"><b>Nama</b></td>
                    <td align="center" size="3"><b>NPM</b></td>
                    <td align="center" size="3"><b>Program Studi</b></td>
                    <td align="center" size="3"><b>Nomor WhatsApp</b></td>
                </tr>
            </thead>
            <tbody>
                @foreach ($data->anggota as $i => $anggota)
                <tr>
                    <td align="center" size="3">{{ $i + 1 }}</td>
                    <td size="3">{{ $anggota->nama }}</td>
                    <td align="center" size="3">{{ $anggota->npm }}</td>
                    <td align="center" size="3">S1 - {{ $data->prodi }}</td>
                    <td align="center" size="3">{{ $anggota->no_hp }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <br>
        <table border="0" width="450">
            <tr>
                <td align="justify">
                    <font size="3">Bermaksud mengajukan permohonan untuk mengadakan observasi/wawancara dengan judul atau tema {{ $data['judul_observasi'] }}. Besar harapan kami agar Bapak/Ibu berkenan untuk memberikan informasi atau data yang dibutuhkan oleh mahasiswa tersebut di atas.
                    </font>
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
        <table border="0" width="450">
            <tr>
                <td width="300"></td>
                <td align="left">
                    {{--A.n Dekan, <br> Koor. Program Studi--}}

                    <br>
                    <font size="3" class="indent">{!! nl2br($defaultTtdData['penanda_tangan']) !!}</font>
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

                    <font size="3" class="nama_pimpinan">{{ $ttdPimpinanDataIF[0]->nama_pimpinan ?? $defaultTtdData['nama_pimpinan'] ?? '' }}</font>
                    {{-- <font size="3">E.Haodudin Nurkifli, S.T., M.Cs., Phd.</font> --}}
                    <br>
                    {{-- <font size="3">NIDN : 003048503</font> --}}
                    <font size="3" class="nama_pimpinan">{{ $ttdPimpinanDataIF[0]->nomor_induk ?? $defaultTtdData['nomor_induk'] ?? '' }}</font>

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
