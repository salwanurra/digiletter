<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Surat Rekomendasi Mengikuti SEP-T</title>
    <style>
        @page {
            margin: 20px 28px 30px;
        }

        body {
            color: #000;
            font-family: "Times New Roman", Times, serif;
            font-size: 12pt;
            line-height: 1.35;
            margin: 0;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        td {
            padding: 0;
            vertical-align: top;
        }

        .letterhead {
            display: block;
            margin: 0 auto 14px;
            width: 100%;
        }

        .content {
            margin: 0 auto;
            width: 450pt;
        }

        .meta {
            margin-bottom: 30px;
        }

        .meta-label {
            width: 62px;
        }

        .meta-separator {
            width: 14px;
        }

        .date {
            text-align: right;
            white-space: nowrap;
        }

        .recipient {
            margin-bottom: 24px;
        }

        p {
            margin: 0 0 16px;
            text-align: justify;
        }

        .student-data {
            margin: 4px 0 22px;
        }

        .student-data .label {
            width: 110px;
        }

        .student-data .separator {
            width: 14px;
        }

        .signature {
            margin-left: 64%;
            margin-top: 64px;
            width: 36%;
        }

        .signature-heading {
            white-space: nowrap;
        }

        .signature-space {
            height: 100px;
        }

        .signature-space img {
            display: block;
            height: auto;
            width: 180px;
        }

        .blu {
            bottom: 20px;
            height: 52px;
            position: fixed;
            right: 22px;
            width: 52px;
        }
    </style>
</head>

<body>
    <img class="letterhead" src="{{ public_path('storage/ttd/kop.png') }}" alt="Kop Fakultas Ilmu Komputer">

    <div class="content">
    <table class="meta">
        <tr>
            <td class="meta-label">Nomor</td>
            <td class="meta-separator">:</td>
            <td>{{ $data->nomor_surat }}</td>
            <td class="date">Karawang, {{ \Carbon\Carbon::parse($data->updated_at)->locale('id_ID')->isoFormat('D MMMM Y') }}</td>
        </tr>
        <tr>
            <td class="meta-label">Perihal</td>
            <td class="meta-separator">:</td>
            <td colspan="2">Rekomendasi Mengikuti Sep-T</td>
        </tr>
    </table>

    <div class="recipient">
        Kepada Yth.<br>
        Kepala UPA Bahasa Unsika<br>
        di<br>
        Tempat
    </div>

    <p>
        Sehubungan akan dilaksanakan kegiatan Sidang Tugas Akhir Fakultas Ilmu Komputer Universitas
        Singaperbangsa Karawang, yang salah satu persyaratan kelulusannya adalah memiliki kemampuan bahasa
        Inggris dengan skor SEP-T lebih dari 425, dengan ini kami ajukan permohonan keikutsertaan SEP-T yang
        akan diselenggarakan oleh UPA Bahasa Universitas Singaperbangsa Karawang, untuk mahasiswa sebagai berikut:
    </p>

    <table class="student-data">
        <tr>
            <td class="label">Nama</td>
            <td class="separator">:</td>
            <td>{{ $data->nama_mhs }}</td>
        </tr>
        <tr>
            <td class="label">NPM</td>
            <td class="separator">:</td>
            <td>{{ $data->npm }}</td>
        </tr>
        <tr>
            <td class="label">Program Studi</td>
            <td class="separator">:</td>
            <td>{{ $data->prodi }}</td>
        </tr>
        <tr>
            <td class="label">Nomor HP</td>
            <td class="separator">:</td>
            <td>{{ $data->no_hp }}</td>
        </tr>
    </table>

    <p>
        Demikian disampaikan, atas perhatian dan kerjasamanya kami sampaikan terima kasih yang sebesar-besarnya.
    </p>

    <div class="signature">
        <span class="signature-heading">a.n Dekan Fakultas Ilmu Komputer</span><br>
        Ketua Jurusan
        <div class="signature-space">
            <img src="{{ public_path('storage/ttd/terbaru/ttd_Carudin, S.Kom., M.Kom.png') }}" alt="Tanda tangan Carudin">
        </div>
        Carudin, M.Kom.<br>
        NIP. 198804132022031002
    </div>
    </div>

    <img class="blu" src="{{ public_path('storage/ttd/blu.png') }}" alt="BLU">
</body>

</html>
