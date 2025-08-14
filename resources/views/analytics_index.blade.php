<!--
    Blade view for the daily increment analytics dashboard.  This view is
    designed to be responsive and easy to read using Tailwind CSS.  It
    displays a dropdown to choose from the top items (by maximum daily
    increase), and for each item it renders two line charts—one showing
    daily increases in views (play_count) and the other showing daily
    increases in comments.  Chart.js is loaded from a CDN.
-->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Kenaikan Per Hari</title>
    <!-- Tailwind CSS via CDN for styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Chart.js via CDN for charts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-50 font-sans">
    <div class="container mx-auto px-4 py-6">
        <h1 class="text-3xl font-bold mb-6 text-gray-800">Analitik Kenaikan Per Hari</h1>
        <!-- Item selection -->
        <div class="mb-6">
            <label for="itemSelect" class="block font-semibold mb-1">Pilih Konten (item_id):</label>
            <select id="itemSelect" class="block w-full md:w-1/3 p-2 border rounded">
                @foreach($topItems as $index => $item)
                    <option value="{{ $index }}">{{ $item['item_id'] }}</option>
                @endforeach
            </select>
            <p class="text-sm text-gray-500 mt-1">Menampilkan 5 konten dengan kenaikan view harian tertinggi.</p>
        </div>

        <!-- Chart containers for each item; hidden by default except the first -->
        <div id="chartContainer">
            @foreach($topItems as $index => $item)
                <div data-index="{{ $index }}" class="item-section {{ $index !== 0 ? 'hidden' : '' }}">
                    <div class="mb-6">
                        <h2 class="text-xl font-semibold text-gray-700 mb-2">Item {{ $item['item_id'] }}</h2>
                        <div class="grid gap-4 sm:grid-cols-1 md:grid-cols-2">
                            <!-- Views chart -->
                            <div class="bg-white p-4 rounded shadow">
                                <h3 class="font-medium mb-3">Kenaikan Views per Hari</h3>
                                <canvas id="playChart{{ $index }}" class="w-full h-64"></canvas>
                            </div>
                            <!-- Comments chart -->
                            <div class="bg-white p-4 rounded shadow">
                                <h3 class="font-medium mb-3">Kenaikan Komentar per Hari</h3>
                                <canvas id="commentChart{{ $index }}" class="w-full h-64"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <script>
    // Parse the PHP data into JavaScript
    const itemsData = {!! json_encode($topItems) !!};

    // Initialize charts for each item
    itemsData.forEach((item, idx) => {
        const labels = item.data.map(row => row.date);
        const playDiff = item.data.map(row => row.play_diff);
        const commentDiff = item.data.map(row => row.comment_diff);

        // Create views (play count) line chart
        new Chart(document.getElementById('playChart' + idx).getContext('2d'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Kenaikan Views (Play Count)',
                    data: playDiff,
                    borderWidth: 2,
                    fill: false,
                    tension: 0.3,
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Kenaikan Views'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Tanggal'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.parsed.y + ' views';
                            }
                        }
                    }
                }
            }
        });

        // Create comments line chart
        new Chart(document.getElementById('commentChart' + idx).getContext('2d'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Kenaikan Komentar',
                    data: commentDiff,
                    borderWidth: 2,
                    fill: false,
                    tension: 0.3,
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Kenaikan Komentar'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Tanggal'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.parsed.y + ' komentar';
                            }
                        }
                    }
                }
            }
        });
    });

    // Handle item selection change
    const itemSelect = document.getElementById('itemSelect');
    itemSelect.addEventListener('change', function() {
        const selectedIndex = this.value;
        document.querySelectorAll('.item-section').forEach(section => {
            section.classList.add('hidden');
        });
        document.querySelector(`.item-section[data-index="${selectedIndex}"]`).classList.remove('hidden');
    });
    </script>
</body>
</html>