<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grafik Penerbitan Surat</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
    @vite('resources/css/app.css')
</head>
<body class="p-16 text-center flex items-center justify-center flex-col">
    <h1 class="font-bold">Data Statistik Surat</h1>
 
    <div x-data="{
            labels: @js($labels),
            penelitian: @js($penelitian),
            aktif: @js($aktif),
            aktif_pns: @js($aktif_pns),
            observasi: @js($observasi),
            cuti: @js($cuti),
            pindah: @js($pindah),
            pengunduran: @js($pengunduran),
            bebas_pustaka: @js($bebas_pustaka),
            total: @js($total),
            init() {
                const ctx = this.$refs.canvas.getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: this.labels,
                        datasets: [
                            {
                                label: 'Surat Penelitian',
                                data: this.penelitian,
                                backgroundColor: 'rgb(54, 162, 235)',
                            },
                            {
                                label: 'Surat Keterangan Aktif',
                                data: this.aktif,
                                backgroundColor: 'rgb(255, 159, 64)',
                            },
                            {
                                label: 'Surat Keterangan Aktif PNS',
                                data: this.aktif_pns,
                                backgroundColor: 'rgba(118, 255, 64, 1)',
                            },
                            {
                                label: 'Surat Observasi',
                                data: this.observasi,
                                backgroundColor: 'rgba(255, 93, 64, 1)',
                            },
                            {
                                label: 'Surat Pengajuan Cuti',
                                data: this.cuti,
                                backgroundColor: 'rgba(93, 64, 255, 1)',
                            },
                            {
                                label: 'Surat Pindah Kuliah',
                                data: this.pindah,
                                backgroundColor: 'rgba(255, 64, 64, 1)',
                            },
                            {
                                label: 'Surat Pengunduran Diri',
                                data: this.pengunduran,
                                backgroundColor: 'rgba(255, 64, 182, 1)',
                            },
                            {
                                label: 'Surat Bebas Pustaka',
                                data: this.bebas_pustaka,
                                backgroundColor: 'rgba(255, 64, 112, 1)',
                            },
                            {
                                label: 'Total Semua Surat',
                                data: this.total,
                                backgroundColor: 'rgb(75, 192, 192)',
                            }
                        ]
                    },
                    options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
                });
            }
        }" class="w-full max-w-7xl mt-4 mx-4">
        <canvas x-ref="canvas"></canvas>
    </div>
</body>
</html>
