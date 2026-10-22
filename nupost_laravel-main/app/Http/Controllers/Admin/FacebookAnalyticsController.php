<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class FacebookAnalyticsController extends Controller
{
    private string $token;
    private string $base = 'https://graph.facebook.com/v21.0';

    public function __construct()
    {
        $this->token = env('FB_PAGE_ACCESS_TOKEN', '');
    }

    /**
     * Build an array of the last 24 months for the dropdown.
     */
    public static function getAvailableMonths(): array
    {
        $months = [];
        for ($i = 0; $i < 24; $i++) {
            $m = Carbon::now()->subMonths($i)->startOfMonth();
            $months[] = [
                'value' => $m->format('Y-m'),
                'label' => $m->format('F Y'),
            ];
        }
        return $months;
    }

    public function index(Request $request)
    {
        // Default to current month
        $selectedMonth = $request->query('month', Carbon::now()->format('Y-m'));

        // Validate format (YYYY-MM)
        if (!preg_match('/^\d{4}-\d{2}$/', $selectedMonth)) {
            $selectedMonth = Carbon::now()->format('Y-m');
        }

        $monthCarbon = Carbon::createFromFormat('Y-m', $selectedMonth)->startOfMonth();

        $fb = $this->getData($selectedMonth);
        $fb['selected_month']    = $selectedMonth;
        $fb['month_label']       = $monthCarbon->format('F Y');
        $fb['available_months']  = self::getAvailableMonths();

        return view('admin.analytics', compact('fb'));
    }

    /**
     * Reusable Facebook HTTP client with SSL bypass for local/XAMPP environments.
     */
    private function fbHttp()
    {
        return Http::withoutVerifying()->timeout(15);
    }

    public function getData(string $selectedMonth = null): array
    {
        $isDashboard = empty($selectedMonth);

        if ($isDashboard) {
            // Dashboard mode: Last 28 days matching Facebook Professional Dashboard (e.g. Aug 29 - Sep 25)
            $since = Carbon::now()->subDays(28)->format('Y-m-d');
            $until = Carbon::now()->format('Y-m-d');
            $sinceTs = Carbon::now()->subDays(28)->timestamp;
            $untilTs = Carbon::now()->timestamp;
            $cacheSuffix = '28d_v4';
        } else {
            // Specific calendar month mode (for Analytics page)
            $monthCarbon = Carbon::createFromFormat('Y-m', $selectedMonth)->startOfMonth();
            $since = $monthCarbon->copy()->startOfMonth()->format('Y-m-d');
            $until = $monthCarbon->copy()->endOfMonth()->isFuture()
                ? Carbon::now()->format('Y-m-d')
                : $monthCarbon->copy()->endOfMonth()->format('Y-m-d');
            $sinceTs = $monthCarbon->copy()->startOfMonth()->timestamp;
            $untilTs = $monthCarbon->copy()->endOfMonth()->isFuture()
                ? Carbon::now()->timestamp
                : $monthCarbon->copy()->endOfMonth()->timestamp;
            $cacheSuffix = "{$selectedMonth}_v4";
        }

        if (!$this->token) {
            return [
                'error'    => 'FB_PAGE_ACCESS_TOKEN missing in .env',
                'pageInfo' => null,
                'metrics'  => null,
                'posts'    => [],
            ];
        }

        try {
            // ── 1. Page basic info ─────────────────────────────────────────
            $pageInfo = Cache::remember('fb_page_info_v2', 300, function () {
                $response = $this->fbHttp()->get("{$this->base}/me", [
                    'fields'       => 'name,id,fan_count,followers_count,link,picture.type(large)',
                    'access_token' => $this->token,
                ]);
                Log::debug('FB Page Info Response', ['status' => $response->status(), 'body' => $response->json()]);
                return $response->json();
            });

            if (isset($pageInfo['error'])) {
                Cache::forget('fb_page_info_v2');
                throw new \Exception($pageInfo['error']['message'] ?? 'Unknown Facebook API error');
            }

            // ── 2. Posts for the period ────────────────────────────────────
            $cacheKey = "fb_posts_{$cacheSuffix}";
            $postsRes = Cache::remember($cacheKey, 300, function () use ($sinceTs, $untilTs) {
                $response = $this->fbHttp()->get("{$this->base}/me/posts", [
                    'fields'       => 'id,message,story,created_time,full_picture,permalink_url,reactions.summary(true),likes.summary(true),comments.summary(true),shares',
                    'since'        => $sinceTs,
                    'until'        => $untilTs,
                    'limit'        => 100,
                    'access_token' => $this->token,
                ]);
                Log::debug('FB Posts Response', ['status' => $response->status()]);
                return $response->json();
            });

            if (isset($postsRes['error'])) {
                Cache::forget($cacheKey);
                Log::warning('FB Posts API Error', $postsRes['error']);
            }

            $posts = collect($postsRes['data'] ?? [])->values()->all();

            // ── 3. Page Insights for the period ────────────────────────────
            // Query Views and Engagements independently so a deprecation/metric change in one
            // cannot cause Graph API to fail or zero out the other.
            
            // 3a. Views Metric (tries page_media_view first, falls back to page_views_total)
            $viewsCacheKey = "fb_views_{$cacheSuffix}";
            $viewsData = Cache::remember($viewsCacheKey, 300, function () use ($since, $until) {
                // Try Meta's unified Views metric first
                $response = $this->fbHttp()->get("{$this->base}/me/insights", [
                    'metric'       => 'page_media_view',
                    'period'       => 'day',
                    'since'        => $since,
                    'until'        => $until,
                    'access_token' => $this->token,
                ]);
                $json = $response->json();
                if (!empty($json['data'])) {
                    return $json;
                }

                // Try page_total_media_view_unique
                $response = $this->fbHttp()->get("{$this->base}/me/insights", [
                    'metric'       => 'page_total_media_view_unique',
                    'period'       => 'day',
                    'since'        => $since,
                    'until'        => $until,
                    'access_token' => $this->token,
                ]);
                $json = $response->json();
                if (!empty($json['data'])) {
                    return $json;
                }

                // Fallback to page_views_total
                $response = $this->fbHttp()->get("{$this->base}/me/insights", [
                    'metric'       => 'page_views_total',
                    'period'       => 'day',
                    'since'        => $since,
                    'until'        => $until,
                    'access_token' => $this->token,
                ]);
                return $response->json();
            });

            // 3b. Engagement Metric (page_post_engagements)
            $engCacheKey = "fb_eng_{$cacheSuffix}";
            $engData = Cache::remember($engCacheKey, 300, function () use ($since, $until) {
                $response = $this->fbHttp()->get("{$this->base}/me/insights", [
                    'metric'       => 'page_post_engagements',
                    'period'       => 'day',
                    'since'        => $since,
                    'until'        => $until,
                    'access_token' => $this->token,
                ]);
                return $response->json();
            });

            // Parse Views & Engagements
            $totalReach      = 0;
            $totalEngagement = 0;
            $dailyReachMap   = [];
            $dailyEngMap     = [];

            if (!empty($viewsData['data'])) {
                foreach ($viewsData['data'] as $metric) {
                    foreach ($metric['values'] ?? [] as $v) {
                        $date = $v['end_time'] ?? '';
                        $val  = $v['value'] ?? 0;
                        $totalReach += $val;
                        $dailyReachMap[$date] = ($dailyReachMap[$date] ?? 0) + $val;
                    }
                }
            }

            if (!empty($engData['data'])) {
                foreach ($engData['data'] as $metric) {
                    foreach ($metric['values'] ?? [] as $v) {
                        $date = $v['end_time'] ?? '';
                        $val  = $v['value'] ?? 0;
                        $totalEngagement += $val;
                        $dailyEngMap[$date] = ($dailyEngMap[$date] ?? 0) + $val;
                    }
                }
            }

            // Align daily arrays
            $allDates = collect(array_keys($dailyReachMap))
                ->merge(array_keys($dailyEngMap))
                ->unique()
                ->sort()
                ->values();

            $dailyReach = [];
            $dailyEngagement = [];
            foreach ($allDates as $d) {
                $dailyReach[]      = ['date' => $d, 'value' => $dailyReachMap[$d] ?? 0];
                $dailyEngagement[] = ['date' => $d, 'value' => $dailyEngMap[$d] ?? 0];
            }

            // ── 4. Aggregate post metrics ──────────────────────────────────
            $totalLikes    = 0;
            $totalComments = 0;
            $totalShares   = 0;

            foreach ($posts as $p) {
                $pReactions = $p['reactions']['summary']['total_count'] ?? $p['likes']['summary']['total_count'] ?? 0;
                $pComments  = $p['comments']['summary']['total_count'] ?? 0;
                $pShares    = $p['shares']['count']                    ?? 0;

                $totalLikes    += $pReactions;
                $totalComments += $pComments;
                $totalShares   += $pShares;
            }

            $metrics = [
                'page_fans'        => ['total' => $pageInfo['fan_count']       ?? 0, 'daily' => []],
                'followers'        => ['total' => $pageInfo['followers_count'] ?? 0, 'daily' => []],
                'total_reach'      => ['total' => $totalReach,      'daily' => $dailyReach],
                'total_engagement' => ['total' => $totalEngagement, 'daily' => $dailyEngagement],
                'total_likes'      => ['total' => $totalLikes,      'daily' => []],
                'total_comments'   => ['total' => $totalComments,   'daily' => []],
                'total_shares'     => ['total' => $totalShares,     'daily' => []],
                'total_posts'      => ['total' => count($posts),    'daily' => []],
            ];

            return [
                'error'          => null,
                'insights_error' => $viewsData['error']['message'] ?? $engData['error']['message'] ?? null,
                'pageInfo'       => $pageInfo,
                'metrics'        => $metrics,
                'posts'          => $posts,
            ];

        } catch (\Exception $e) {
            Log::error('Facebook API Error: ' . $e->getMessage());
            return [
                'error'    => $e->getMessage(),
                'pageInfo' => null,
                'metrics'  => null,
                'posts'    => [],
            ];
        }
    }

    /**
     * Export the current month's FB data as CSV.
     */
    public function exportCsv(Request $request)
    {
        $selectedMonth = $request->query('month', Carbon::now()->format('Y-m'));
        if (!preg_match('/^\d{4}-\d{2}$/', $selectedMonth)) {
            $selectedMonth = Carbon::now()->format('Y-m');
        }

        $fb = $this->getData($selectedMonth);
        $monthLabel = Carbon::createFromFormat('Y-m', $selectedMonth)->format('F_Y');

        $filename = "facebook_analytics_{$monthLabel}.csv";

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($fb, $selectedMonth) {
            $handle = fopen('php://output', 'w');

            // ── Summary section ──────────────────────────────────────────
            fputcsv($handle, ['Facebook Analytics Export']);
            fputcsv($handle, ['Month', Carbon::createFromFormat('Y-m', $selectedMonth)->format('F Y')]);
            fputcsv($handle, ['Page', $fb['pageInfo']['name'] ?? 'N/A']);
            fputcsv($handle, ['Page Likes', $fb['pageInfo']['fan_count'] ?? 0]);
            fputcsv($handle, ['Followers', $fb['pageInfo']['followers_count'] ?? 0]);
            fputcsv($handle, []);

            // ── Metrics summary ──────────────────────────────────────────
            fputcsv($handle, ['Metric', 'Value']);
            fputcsv($handle, ['Total Reach',      $fb['metrics']['total_reach']['total']      ?? 0]);
            fputcsv($handle, ['Total Engagements',$fb['metrics']['total_engagement']['total'] ?? 0]);
            fputcsv($handle, ['Total Likes',      $fb['metrics']['total_likes']['total']      ?? 0]);
            fputcsv($handle, ['Total Comments',   $fb['metrics']['total_comments']['total']   ?? 0]);
            fputcsv($handle, ['Total Shares',     $fb['metrics']['total_shares']['total']     ?? 0]);
            fputcsv($handle, ['Total Posts',      $fb['metrics']['total_posts']['total']      ?? 0]);
            fputcsv($handle, []);

            // ── Daily reach ──────────────────────────────────────────────
            fputcsv($handle, ['Daily Reach (Views)']);
            fputcsv($handle, ['Date', 'Reach', 'Engagements']);
            $reachByDate      = collect($fb['metrics']['total_reach']['daily'] ?? [])->keyBy('date');
            $engByDate        = collect($fb['metrics']['total_engagement']['daily'] ?? [])->keyBy('date');
            $allDates         = $reachByDate->keys()->merge($engByDate->keys())->unique()->sort();
            foreach ($allDates as $date) {
                fputcsv($handle, [
                    Carbon::parse($date)->format('Y-m-d'),
                    $reachByDate->get($date)['value'] ?? 0,
                    $engByDate->get($date)['value']   ?? 0,
                ]);
            }
            fputcsv($handle, []);

            // ── Posts breakdown ──────────────────────────────────────────
            fputcsv($handle, ['Posts Breakdown']);
            fputcsv($handle, ['Date', 'Caption', 'Likes', 'Comments', 'Shares', 'URL']);
            foreach ($fb['posts'] ?? [] as $post) {
                fputcsv($handle, [
                    Carbon::parse($post['created_time'])->format('Y-m-d'),
                    mb_substr($post['message'] ?? $post['story'] ?? '', 0, 100),
                    $post['likes']['summary']['total_count']    ?? 0,
                    $post['comments']['summary']['total_count'] ?? 0,
                    $post['shares']['count']                    ?? 0,
                    $post['permalink_url'] ?? '',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function refresh(Request $request)
    {
        $selectedMonth = $request->query('month', Carbon::now()->format('Y-m'));

        Cache::forget('fb_page_info');
        Cache::forget('fb_page_info_v2');
        Cache::forget("fb_posts_{$selectedMonth}");
        Cache::forget("fb_posts_{$selectedMonth}_v2");
        Cache::forget("fb_posts_{$selectedMonth}_v4");
        Cache::forget("fb_insights_{$selectedMonth}");
        Cache::forget("fb_views_{$selectedMonth}_v4");
        Cache::forget("fb_eng_{$selectedMonth}_v4");
        Cache::forget('fb_posts_28d_v4');
        Cache::forget('fb_views_28d_v4');
        Cache::forget('fb_eng_28d_v4');

        return redirect()->back()->with('success', '✅ Facebook analytics refreshed!');
    }
}