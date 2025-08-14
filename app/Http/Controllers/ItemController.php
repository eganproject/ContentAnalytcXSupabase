<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Http;

/**
 * Controller yang menampilkan data item dari Supabase.
 *
 * Controller ini menggunakan API Supabase untuk mengambil data dari tabel
 * `item_aggregate` dan `item_list` lalu menggabungkan keduanya berdasarkan
 * kolom `item_id`. Data yang sudah digabungkan kemudian difilter berdasarkan
 * teks pencarian dan dipaginasi sebelum dikirim ke tampilan Blade.
 */
class ItemController extends Controller
{
    public function index(Request $request)
    {
        // API key Supabase. Gunakan publishable key sesuai instruksi.
        $apiKey = env('SUPABASE_KEY', 'sb_publishable_JmZdi-uJarf2oAqEXKQjcA_x4qtnwQS');
        $baseUrl = 'https://pvicsclyiisznezakjmm.supabase.co/rest/v1';

        // Ambil data agregasi
        $aggregateResponse = Http::withHeaders([
            'apikey'       => $apiKey,
            'Authorization' => 'Bearer ' . $apiKey,
        ])->get($baseUrl . '/item_aggregate', [
            'select' => 'item_id,date,comment_count,favorite_count,like_count,play_count,share_count',
            'order'  => 'date.desc',
            'apikey' => $apiKey,
        ]);

        
        $aggregateItems = collect($aggregateResponse->json() ?? []);
       

        // Kumpulkan item_id untuk query tabel item_list
        $itemIds = $aggregateItems->pluck('item_id')->filter()->all();

        // Jika tidak ada data, kembalikan tampilan kosong
        if (empty($itemIds)) {
            return view('items.index', ['items' => new LengthAwarePaginator([], 0, 10), 'search' => $request->query('search')]);
        }

        // Ambil data item_list sesuai daftar item_id yang ada
        $idList = '(' . implode(',', $itemIds) . ')';
        $listResponse = Http::withHeaders([
            'apikey'       => $apiKey,
            'Authorization' => 'Bearer ' . $apiKey,
        ])->get($baseUrl . '/item_list', [
            'select' => 'item_id,desc,cover_url',
            'item_id' => 'in.' . $idList,
            'apikey'  => $apiKey,
        ]);

        $itemList = collect($listResponse->json() ?? [])->keyBy('item_id');

        // Gabungkan kedua data berdasarkan item_id
        $items = $aggregateItems->map(function (array $agg) use ($itemList) {
            $list = $itemList->get($agg['item_id'], []);
            $description = $list['desc'] ?? '';
            $cover = null;
            // cover_url merupakan array, gunakan elemen pertama jika ada
            if (!empty($list['cover_url']) && is_array($list['cover_url'])) {
                $cover = $list['cover_url'][0] ?? null;
            }
            return array_merge($agg, [
                'desc'      => $description,
                'cover_url' => $cover,
                'like_conversion' => number_format(($agg['like_count'] / max(1, $agg['play_count'])) * 100, 2),
                'comment_conversion' =>  number_format(($agg['comment_count'] / max(1, $agg['play_count'])) * 100, 2),
                'share_conversion' =>  number_format(($agg['share_count'] / max(1, $agg['play_count'])) * 100, 2),
            ]);
        });
        $avgLikeConversion = $items->avg('like_conversion');
        $avgComment_conversion = $items->avg('comment_conversion');
        $avgShare_conversion = $items->avg('share_conversion');

        // Filter data berdasarkan item_id terbaru
        $items = $items->groupBy('item_id')
            ->map(function ($group) {
                return $group->sortByDesc('date')->first();
            })
            ->values();

        // Filter pencarian jika ada query 'search'
        $search = $request->query('search');
        if ($search) {
            $items = $items->filter(function ($item) use ($search) {
                return stripos($item['desc'] ?? '', $search) !== false;
            });
        }

        // Pagination manual: 12 item per halaman
        $perPage = 12;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentItems = $items->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $paginator = new LengthAwarePaginator($currentItems, $items->count(), $perPage, $currentPage, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);

 

        return view('items.index', [
            'items'  => $paginator,
            'search' => $search,
            'avgLikeConversion' => $avgLikeConversion,
            'avgComment_conversion' => $avgComment_conversion,
            'avgShare_conversion' => $avgShare_conversion
        ]);
    }

  public function show($id)
    {
        $apiKey = env('SUPABASE_KEY', 'sb_publishable_JmZdi-uJarf2oAqEXKQjcA_x4qtnwQS');
        $baseUrl = 'https://pvicsclyiisznezakjmm.supabase.co/rest/v1';

        $aggregateResponse = Http::withHeaders([
            'apikey'        => $apiKey,
            'Authorization' => 'Bearer ' . $apiKey,
        ])->get($baseUrl . '/item_aggregate', [
            'select'  => 'item_id,date,comment_count,favorite_count,like_count,play_count,share_count',
            'item_id' => 'eq.' . $id,
            'order'   => 'date.asc',
            'apikey'  => $apiKey,
        ]);

        if ($aggregateResponse->failed() || empty($aggregateResponse->json())) {
            return view('items.show_error', ['itemId' => $id]);
        }

        $aggregateItems = collect($aggregateResponse->json());
        
        $latestItem = $aggregateItems->sortByDesc('date')->first();
       
        // --- PENDEKATAN BARU DIMULAI DI SINI ---

        // Siapkan data dalam satu struktur array yang siap pakai untuk Chart.js
        $chartData = [
            // Sumbu-X: Semua tanggal yang sudah diformat
            'labels' => $aggregateItems->pluck('date')->map(function ($date) {
                return Carbon::parse($date)->format('d M');
            }),
            // Sumbu-Y: Kumpulan semua data set
            'datasets' => [
                [
                    'label'           => 'Jumlah Play',
                    'data'            => $aggregateItems->pluck('play_count'),
                    'borderColor'     => '#3b82f6', // Biru
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'tension'         => 0.2,
                    'fill'            => true,
                ],
                [
                    'label'           => 'Jumlah Like',
                    'data'            => $aggregateItems->pluck('like_count'),
                    'borderColor'     => '#ef4444', // Merah
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                    'tension'         => 0.2,
                    'fill'            => true,
                ],
                [
                    'label'           => 'Jumlah Komentar',
                    'data'            => $aggregateItems->pluck('comment_count'),
                    'borderColor'     => '#14b8a6', // Hijau
                    'backgroundColor' => 'rgba(20, 184, 166, 0.1)',
                    'tension'         => 0.2,
                    'fill'            => true,
                ],
                [
                    'label'           => 'Jumlah Share',
                    'data'            => $aggregateItems->pluck('share_count'),
                    'borderColor'     => '#8b5cf6', // Ungu
                    'backgroundColor' => 'rgba(139, 92, 246, 0.1)',
                    'tension'         => 0.2,
                    'fill'            => true,
                ],
                [
                    'label'           => 'Jumlah Favorit',
                    'data'            => $aggregateItems->pluck('favorite_count'),
                    'borderColor'     => '#f59e0b', // Kuning
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                    'tension'         => 0.2,
                    'fill'            => true,
                ],
            ]
        ];

        // Kirim itemId dan satu variabel $chartData ke view
        return view('items.show', [
            'itemId' => $id,
            'chartData' => $chartData,
            'latestItem' => $latestItem,
        ]);
    }
}
