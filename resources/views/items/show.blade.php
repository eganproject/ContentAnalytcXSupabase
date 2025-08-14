<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Statistik - Item {{ $itemId }}</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Konfigurasi Tailwind (Opsional, untuk font) -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                }
            }
        }
    </script>

    <style>
        /* Styling tambahan untuk scrollbar yang lebih baik (opsional) */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        ::-webkit-scrollbar-thumb {
            background: #94a3b8;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #64748b;
        }
    </style>
</head>

<body class="bg-slate-100 text-slate-800 antialiased">

    <main class="container mx-auto p-4 md:p-6 lg:p-8">

        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-slate-900">Dashboard Statistik</h1>
            <p class="text-slate-500">Analisis performa untuk Item ID: <span
                    class="font-mono bg-slate-200 text-slate-700 px-2 py-1 rounded-md">{{ $itemId }}</span></p>
        </div>


        @if (isset($chartData) && !empty($chartData['labels']))
            <!-- Ringkasan Statistik -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">

                <!-- Card Play -->
                <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200">
                    <h3 class="text-sm font-medium text-slate-500">Total Play</h3>
                    <p class="text-2xl font-bold text-blue-600">{{ number_format($latestItem['play_count']) }}</p>
                </div>
                <!-- Card Like -->
                <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200">
                    <h3 class="text-sm font-medium text-slate-500">Total Like</h3>
                    <p class="text-2xl font-bold text-red-500">{{ number_format($latestItem['like_count']) }}</p>
                </div>
                <!-- Card Komentar -->
                <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200">
                    <h3 class="text-sm font-medium text-slate-500">Total Komentar</h3>
                    <p class="text-2xl font-bold text-teal-500">{{ number_format($latestItem['comment_count']) }}</p>
                </div>
                <!-- Card Share -->
                <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200">
                    <h3 class="text-sm font-medium text-slate-500">Total Share</h3>
                    <p class="text-2xl font-bold text-purple-500">{{ number_format($latestItem['share_count']) }}</p>
                </div>
                <!-- Card Favorit -->
                <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200">
                    <h3 class="text-sm font-medium text-slate-500">Total Favorit</h3>
                    <p class="text-2xl font-bold text-amber-500">{{ number_format($latestItem['favorite_count']) }}</p>
                </div>
            </div>

            <!-- Grafik Utama -->
            <div class="bg-white p-4 sm:p-6 rounded-xl shadow-lg border border-slate-200">
                <h2 class="text-lg font-semibold text-slate-800 mb-4">Tren Statistik Harian</h2>
                <div class="h-96 w-full">
                    <canvas id="myChart"></canvas>
                </div>
            </div>
        @else
            <!-- Pesan Jika Data Tidak Ada -->
            <div class="bg-white p-10 rounded-xl shadow-lg border border-slate-200 text-center">
                <h2 class="text-xl font-semibold text-slate-800 mb-2">Data Tidak Ditemukan</h2>
                <p class="text-slate-500">Kami tidak dapat menemukan data statistik untuk ditampilkan. Silakan coba lagi
                    nanti.</p>
            </div>
        @endif
        <div class="mt-4 flex justify-center">

            <a href="/"
                class="mb-4 inline-flex items-center px-6 py-3 border border-transparent shadow-lg text-sm font-semibold rounded-lg text-white bg-gradient-to-r from-purple-500 to-indigo-600 hover:from-pink-500 hover:to-purple-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-500 ease-in-out transform hover:-translate-y-1 hover:scale-105">
                Kembali
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M7.707 14.707a1 1 0 01-1.414 0l-4 4a1 1 0 010 1.414l4-4a1 1 0 111.414-1.414L9.414 18.172a1 1 0 01-1.414 0z"
                        clip-rule="evenodd" />
                </svg>
            </a>
        </div>
    </main>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // Ambil data dari Blade, pastikan tidak null
        const chartData = @json($chartData ?? null);

        // Hanya jalankan jika data chart ada
        if (chartData && chartData.labels && chartData.labels.length > 0) {
            const ctx = document.getElementById('myChart').getContext('2d');

            new Chart(ctx, {
                type: 'line',
                data: chartData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#e2e8f0', // Warna grid sumbu Y
                                drawBorder: false,
                            },
                            ticks: {
                                color: '#64748b' // Warna teks sumbu Y
                            }
                        },
                        x: {
                            grid: {
                                display: false // Sembunyikan grid sumbu X
                            },
                            ticks: {
                                color: '#64748b' // Warna teks sumbu X
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            align: 'start',
                            labels: {
                                usePointStyle: true,
                                boxWidth: 8,
                                padding: 25,
                                color: '#334155'
                            }
                        },
                        tooltip: {
                            enabled: true,
                            mode: 'index',
                            intersect: false,
                            backgroundColor: '#ffffff',
                            titleColor: '#1e293b',
                            bodyColor: '#475569',
                            borderColor: '#e2e8f0',
                            borderWidth: 1,
                            padding: 12,
                            cornerRadius: 8,
                            displayColors: true,
                            boxPadding: 4
                        }
                    },
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                }
            });
        }
    </script>

</body>

</html>
