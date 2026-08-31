<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Keterangan Aktif Kuliah Orang Tua PNS</title>
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
            margin-top: -15px;
        }

        .left {
            width: 1px;
            position: relative;
            right: 60px;
            top: -25px;
            float: left;
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

        .table2 {
            margin-top: -30px;
            text-align: center;
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
                    <font size="3">SURAT PERNYATAAN MASIH AKTIF KULIAH</font>
                </td>
            </tr>
            <tr>
                <td>
                    <font size="3">Nomor : {{ $data['nomor_surat'] }}</font>
                </td>
            </tr>
        </table>

        <br>

        <!-- yg bertanda tangan -->
        <table border="0" width="450" cellpadding="0">
            <tr>
                <td>
                    <font size="3">Yang bertanda tangan dibawah ini:</font>
                </td>
            </tr>
        </table>
        <br>
        <table border="0" width="450" cellpadding="0" style="text-align:justify;">
            <tr>
                <td>
                    <font size="3">Nama</font>
                </td>
                <td>:</td>
                <td>
                    <font size="3">{{$ttdPimpinanDataIF[0]->nama_pimpinan ?? $defaultTtdData['nama_pimpinan']}}</font>
                </td>
            </tr>
            <tr>
                <td>
                    <font size="3">NIDN/NIP</font>
                </td>
                <td>:</td>
                <td>
                    <font size="3">{{ isset($ttdPimpinanDataIF[0]) ? $nomor_induk_only : $defaultTtdData['nomor_induk_only'] }}</font>
                </td>
            </tr>
            <tr>
                <td>
                    <font size="3">Jabatan</font>
                </td>
                <td>:</td>
                <td>
                    <font size="3">Ketua Jurusan</font>
                </td>
            </tr>
            <tr>
                <td width="170">
                    <font size="3">Fakultas</font>
                </td>
                <td>:</td>
                <td>
                    <font size="3">Ilmu Komputer</font>
                </td>
            </tr>
        </table>
        <br>

        <!-- menyatakan bahwa -->
        <table border="0" width="450">
            <tr>
                <td>
                    <font size="3">Dengan ini menyatakan bahwa:</font>
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
                    <font size="3">Program Studi</font>
                </td>
                <td>:</td>
                <td>
                    <font size="3">S1 - {{ $data['prodi'] }}</font>
                </td>
            </tr>
            <tr>
                <td>
                    <font size="3">Nomor Pokok</font>
                </td>
                <td>:</td>
                <td>
                    <font size="3">{{ $data['npm'] }}</font>
                </td>
            </tr>
            <tr>
                <td width="170">
                    <font size="3">Semester/Program</font>
                </td>
                <td>:</td>
                <td>
                    <font size="3">{{ $data['semester'] }}/Sarjana (S1)</font>
                </td>
            </tr>
            <tr>
                <td>
                    <font size="3">Alamat</font>
                </td>
                <td>:</td>
                <td>
                    <font size="3">{{ $data['alamat'] }}</font>
                </td>
            </tr>
        </table>
        <br>
        <!-- wali/ortu -->
        <table border="0" width="450">
            <tr>
                <td>
                    <font size="3">Wali/Orang Tua mahasiswa tersebut:</font>
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
                    <font size="3">{{ $data['nama_ortu'] }}</font>
                </td>
            </tr>
            <tr>
                <td>
                    <font size="3">NIP/NRP/No.Pensiun</font>
                </td>
                <td>:</td>
                <td>
                    <font size="3">{{ $data['nomor_induk_ortu'] }}</font>
                </td>
            </tr>
            <tr>
                <td>
                    <font size="3">Pangkat/Golongan</font>
                </td>
                <td>:</td>
                <td>
                    <font size="3">{{ $data['jabatan_ortu'] }}</font>
                </td>
            </tr>
            <tr>
                <td width="170">
                    <font size="3">Instansi</font>
                </td>
                <td>:</td>
                <td>
                    <font size="3">{{ $data['instansi'] }}</font>
                </td>
            </tr>
        </table>
        <br>
        <br>
        <table border="0" width="450" style="text-align:justify;">
            <tr>
                <td>
                    <font size="3">Adalah benar mahasiswa kami yang masih aktif pada tahun akademik {{ $data['tahun_akademik'] }}. Apabila
                        dikemudian hari surat pernyataan ini terdapat kesalahan yang mengakibatkan kerugian terhadap
                        Negara Republik Indonesia, maka kerugian tersebut menjadi tanggung jawab orang tua/wali
                        mahasiswa tersebut diatas.</font>
                </td>
            </tr>
        </table>
        <br>

        <table border="0" width="450" style="text-align:justify;">
            <tr>
                <td>
                    <font size="3">Demikian Surat Keterangan ini kami buat dengan sesungguhnya, untuk dapat dipergunakan
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
                    <font size="3" class="indent">
                        {!! nl2br(
                            $ttdPimpinanDataWadek[0]->penanda_tangan
                                ?? ($ttdPimpinanDataIF[0]->penanda_tangan
                                    ?? $defaultTtdData['penanda_tangan'])
                        ) !!},
                    </font>
                    <br>
                    <div class="container">
                        <br>
                        <div class="left">
                            @if (isset($ttdPimpinanDataWadek) && $ttdPimpinanDataWadek->isNotEmpty() && isset($ttdPimpinanDataWadek[0]->ttd_image))
                            <img src="{{ public_path('storage/ttd/terbaru/' . $ttdPimpinanDataWadek[0]->ttd_image) }}" width="180" alt="">
                            @else
                                @if (isset($ttdPimpinanDataIF) && $ttdPimpinanDataIF->isNotEmpty() && isset($ttdPimpinanDataIF[0]->ttd_image))
                                    <img src="{{ public_path('storage/ttd/terbaru/' . $ttdPimpinanDataIF[0]->ttd_image) }}" width="180" alt="">
                                @else
                                    <img src="{{ public_path('storage/ttd/'. $defaultTtdData['ttd_image']) }}" width="180" alt="">
                                @endif
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
                    <font size="3"  class="nama_pimpinan">{{ $ttdPimpinanDataIF[0]->nomor_induk ?? $defaultTtdData['nomor_induk'] ?? '' }}</font>

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