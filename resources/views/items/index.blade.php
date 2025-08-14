<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bos Otomotif</title>

    <!-- Memuat Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Memuat Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Memuat Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Konfigurasi Kustom Tailwind & Animasi -->
    <style>
        /* Menggunakan font Inter sebagai default */
        body {
            font-family: 'Inter', sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* Animasi untuk card yang muncul */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fadeInUp {
            animation: fadeInUp 0.5s ease-out forwards;
        }
        
        /* Styling untuk pagination bawaan Laravel */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
        }
        .pagination a, .pagination span {
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            transition: all 0.2s ease-in-out;
            color: #475569; /* slate-600 */
            background-color: #f1f5f9; /* slate-100 */
        }
        .pagination a:hover {
            background-color: #e2e8f0; /* slate-200 */
        }
        .pagination .active span {
            background-color: #0f172a; /* slate-900 */
            color: #ffffff;
            font-weight: 600;
        }
        .dark .pagination a, .dark .pagination span {
            color: #d1d5db; /* gray-300 */
            background-color: #374151; /* gray-700 */
        }
        .dark .pagination a:hover {
            background-color: #4b5563; /* gray-600 */
        }
        .dark .pagination .active span {
            background-color: #f9fafb; /* gray-50 */
            color: #1f2937; /* gray-800 */
        }
    </style>
</head>

<body class="bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200">

    <div class="container mx-auto p-4 sm:p-6 lg:p-8">

        <!-- Header dan Form Pencarian -->
        <header class="mb-8">
            <h1 class="text-3xl sm:text-4xl font-bold text-slate-900 dark:text-white mb-2">Jelajahi Item</h1>
            <p class="text-slate-600 dark:text-slate-400 mb-6">Temukan konten menarik dari berbagai kreator.</p>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">
                <div class="bg-white dark:bg-slate-800 p-6 rounded-lg shadow-md">
                    <div class="flex items-center mb-4">
                        <i data-lucide="heart" class="w-6 h-6 text-red-500 mr-2"></i>
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Avg. Like Conversion</h3>
                    </div>
                    <p class="text-2xl font-semibold text-slate-900 dark:text-white">{{ number_format($avgLikeConversion, 2) }}%</p>
                </div>
                <div class="bg-white dark:bg-slate-800 p-6 rounded-lg shadow-md">
                    <div class="flex items-center mb-4">
                        <i data-lucide="message-square" class="w-6 h-6 text-yellow-500 mr-2"></i>
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Avg. Comment Conversion</h3>
                    </div>
                    <p class="text-2xl font-semibold text-slate-900 dark:text-white">{{ number_format($avgComment_conversion, 2) }}%</p>
                </div>
                <div class="bg-white dark:bg-slate-800 p-6 rounded-lg shadow-md">
                    <div class="flex items-center mb-4">
                        <i data-lucide="share" class="w-6 h-6 text-green-500 mr-2"></i>
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Avg. Share Conversion</h3>
                    </div>
                    <p class="text-2xl font-semibold text-slate-900 dark:text-white">{{ number_format($avgShare_conversion, 2) }}%</p>
                </div>
            </div>
            <form method="GET" action="" class="relative">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}" 
                    placeholder="Cari berdasarkan deskripsi atau ID..."
                    class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg py-3 pl-12 pr-4 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow"
                >
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <i data-lucide="search" class="w-5 h-5 text-slate-400"></i>
                </div>
            </form>
        </header>

        <!-- Grid Card Responsif -->
        <main>
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @forelse ($items as $index => $item)
                    <!-- Card Item -->
                    <a href="/{{ $item['item_id'] }}" class="group block animate-fadeInUp" style="animation-delay: {{ $index * 50 }}ms;">
                        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-md hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden h-full flex flex-col">
                            
                            <!-- Gambar Cover -->
                            <div class="relative overflow-hidden h-48">
                                <img 
                                    src="{{ $item['cover_url'] }}" 
                                    alt="Cover Image for {{ \Illuminate\Support\Str::limit($item['desc'] ?? 'Item', 30) }}"
                                    class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                                    onerror="this.onerror=null;this.src='https://placehold.co/600x400/e2e8f0/64748b?text=Image+Error';"
                                >
                                <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
                            </div>
                            
                            <!-- Konten Card -->
                            <div class="p-5 flex-grow flex flex-col">
                                <!-- Deskripsi -->
                                <h2 class="font-bold text-base text-slate-900 dark:text-white mb-3 flex-grow">
                                    {{ \Illuminate\Support\Str::limit($item['desc'] ?? 'Tidak ada deskripsi.', 85) }}
                                </h2>
                                
                                <!-- Statistik -->
                                <div class="mt-auto pt-3 border-t border-slate-100 dark:border-slate-700 space-y-3">
                                    <div class="flex items-center justify-between text-sm text-slate-500 dark:text-slate-400">
                                        <span class="flex items-center gap-1.5" title="Jumlah Putar">
                                            <i data-lucide="play-circle" class="w-4 h-4"></i>
                                            {{ number_format($item['play_count']) }}
                                        </span>
                                        <span class="flex items-center gap-1.5" title="Jumlah Suka (Konversi)">
                                            <i data-lucide="heart" class="w-4 h-4"></i>
                                            {{ number_format($item['like_count']) }} <span class="text-xs text-slate-400">({{ $item['like_conversion'] .'%' }})</span>
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between text-sm text-slate-500 dark:text-slate-400">
                                        <span class="flex items-center gap-1.5" title="Jumlah Komentar (Konversi)">
                                            <i data-lucide="message-square" class="w-4 h-4"></i>
                                            {{ number_format($item['comment_count']) }} <span class="text-xs text-slate-400">({{ $item['comment_conversion'] .'%' }})</span>
                                        </span>
                                        <span class="flex items-center gap-1.5" title="Jumlah Bagikan (Konversi)">
                                            <i data-lucide="share-2" class="w-4 h-4"></i>
                                            {{ number_format($item['share_count']) }} <span class="text-xs text-slate-400">({{ $item['share_conversion'] .'%' }})</span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                @empty
                    <!-- Tampilan Jika Tidak Ada Data -->
                    <div class="col-span-full bg-white dark:bg-slate-800 rounded-xl p-8 sm:p-12 flex flex-col items-center justify-center text-center shadow-md animate-fadeInUp">
                        <i data-lucide="folder-search" class="w-16 h-16 text-slate-400 mb-4"></i>
                        <h3 class="text-xl font-semibold text-slate-800 dark:text-white">Data Tidak Ditemukan</h3>
                        <p class="text-slate-500 dark:text-slate-400 mt-2">
                            Coba gunakan kata kunci lain atau hapus filter pencarian Anda.
                        </p>
                    </div>
                @endforelse
            </div>
        </main>

        <!-- Pagination -->
        <footer class="mt-10">
            <div class="pagination">
                {{-- Laravel akan merender link pagination di sini. Styling diatur di tag <style> --}}
                {{ $items->links() }}
            </div>
        </footer>

    </div>

    <!-- Script untuk mengaktifkan Lucide Icons -->
    <script>
        lucide.createIcons();
    </script>
</body>

</html>
