<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * Controller to build a daily increment analytics dashboard from the Supabase
 * `item_aggregate` table.  This controller fetches all rows from the
 * `item_aggregate` endpoint, computes per‑day increases for each metric, and
 * passes a curated selection of items to a Blade view which renders charts.
 *
 * The Supabase project is queried using a publishable API key.  You can
 * configure the key by setting the `SUPABASE_KEY` environment variable in
 * your .env file.  If the key is not set, a default key will be used.
 */
class AnalyticsController extends Controller
{
    /**
     * Display the analytics dashboard.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Build the Supabase API URL.  We request all columns from the
        // `item_aggregate` table.  In production you should consider
        // filtering or paginating the request if the dataset grows large.
        $endpoint = 'https://pvicsclyiisznezakjmm.supabase.co/rest/v1/item_aggregate?select=*';

        // Read the API key from the environment or fall back to the
        // publishable key provided by the user.  The API key must have
        // permission to read from the `item_aggregate` table.
        $apiKey = env('SUPABASE_KEY', 'sb_publishable_JmZdi-uJarf2oAqEXKQjcA_x4qtnwQS');

        // Fetch the data from Supabase.  If the request fails for any
        // reason, an empty collection will be used instead of crashing.
        $response = Http::withHeaders([
            'apikey'       => $apiKey,
            'Authorization' => 'Bearer ' . $apiKey,
            'Accept'       => 'application/json',
        ])->get($endpoint);

        $records = [];
        if ($response->successful()) {
            $records = $response->json();
        }

        // Group the records by item_id and compute daily differences.
        // The `updated_at` timestamp is used as the marker for when the
        // metrics were recorded.  We convert the timestamp to a date
        // (YYYY‑MM‑DD) and calculate differences between consecutive rows.
        $grouped = collect($records)->groupBy('item_id');
        $items = [];
        foreach ($grouped as $itemId => $rows) {
            // Sort rows chronologically by updated_at
            $sorted = $rows->sortBy(function ($row) {
                return $row['updated_at'];
            })->values();

            $previous = null;
            $diffs = [];
            foreach ($sorted as $row) {
                // Format the date part of updated_at (YYYY‑MM‑DD)
                $date = \Carbon\Carbon::parse($row['updated_at'])->toDateString();
                if ($previous === null) {
                    // First observation has zero increments
                    $diffs[] = [
                        'date'          => $date,
                        'play_diff'     => 0,
                        'comment_diff'  => 0,
                        'like_diff'     => 0,
                        'favorite_diff' => 0,
                        'share_diff'    => 0,
                    ];
                } else {
                    $diffs[] = [
                        'date'          => $date,
                        'play_diff'     => $row['play_count']     - $previous['play_count'],
                        'comment_diff'  => $row['comment_count']  - $previous['comment_count'],
                        'like_diff'     => $row['like_count']     - $previous['like_count'],
                        'favorite_diff' => $row['favorite_count'] - $previous['favorite_count'],
                        'share_diff'    => $row['share_count']    - $previous['share_count'],
                    ];
                }
                $previous = $row;
            }
            $items[$itemId] = $diffs;
        }

        // Determine which items to show on the dashboard.  We rank items by
        // their maximum daily increase in play_count (views) and take the
        // top five.  This makes the dashboard easy to understand and
        // automatically focuses on content that shows meaningful movement.
        $topItems = [];
        foreach ($items as $id => $diffData) {
            $maxDiff = collect($diffData)->max('play_diff');
            $topItems[] = [
                'item_id'    => $id,
                'max_play_diff' => $maxDiff,
                'data'       => $diffData,
            ];
        }
        $topItems = collect($topItems)
            ->sortByDesc('max_play_diff')
            // ->take(5)
            ->values()
            ->all();

        return view('analytics_index', [
            'topItems' => $topItems,
        ]);
    }
}