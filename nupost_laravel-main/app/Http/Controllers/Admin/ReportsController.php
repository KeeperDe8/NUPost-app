<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PostRequest;
use App\Http\Controllers\Admin\FacebookAnalyticsController;

class ReportsController extends Controller
{
    public function index()
    {
        return view('admin.reports');
    }

    public function export()
    {
        $type            = request('type', 'details'); // details or performance
        $filter_status   = request('status', 'all');
        $filter_category = request('category', 'all');
        $filter_priority = request('priority', 'all');
        $date_from       = request('date_from', '');
        $date_to         = request('date_to', '');

        $query = PostRequest::query();
        if ($filter_status !== 'all') {
            $status_map = [
                'pending'=>'Pending Review','review'=>'Under Review',
                'approved'=>'Approved','posted'=>'Posted','rejected'=>'Rejected',
            ];
            if (isset($status_map[$filter_status])) $query->where('status',$status_map[$filter_status]);
        }
        if ($filter_category !== 'all') $query->where('category',$filter_category);
        if ($filter_priority !== 'all') $query->where('priority',ucfirst($filter_priority));
        if ($date_from) $query->whereDate('created_at','>=',$date_from);
        if ($date_to)   $query->whereDate('created_at','<=',$date_to);

        if ($type === 'performance') {
            $postsData = [];
            $source = 'Simulation';

            try {
                $fbController = new FacebookAnalyticsController();
                $fbData = $fbController->getData();
                if (empty($fbData['error']) && !empty($fbData['posts'])) {
                    $source = 'Facebook Graph API';
                    foreach ($fbData['posts'] as $post) {
                        $likes = $post['likes']['summary']['total_count'] ?? 0;
                        $comments = $post['comments']['summary']['total_count'] ?? 0;
                        $shares = $post['shares']['count'] ?? 0;
                        $engagement = $likes + $comments + $shares;

                        $postsData[] = [
                            'request_id' => $post['id'],
                            'title' => $post['message'] ?? $post['story'] ?? 'N/A',
                            'category' => 'Social Media Post',
                            'likes' => $likes,
                            'comments' => $comments,
                            'shares' => $shares,
                            'engagement' => $engagement,
                        ];
                    }
                }
            } catch (\Exception $e) {
                // Fallback to database simulation
            }

            if (empty($postsData)) {
                $requests = $query->whereIn('status', ['Approved', 'Posted'])->get();
                foreach ($requests as $req) {
                    $likes = ($req->id * 23) % 450 + 20;
                    $comments = ($req->id * 11) % 120 + 5;
                    $shares = ($req->id * 7) % 60 + 1;
                    $engagement = $likes + $comments + $shares;

                    $postsData[] = [
                        'request_id' => $req->request_id ?? 'REQ-' . str_pad($req->id, 5, '0', STR_PAD_LEFT),
                        'title' => $req->title,
                        'category' => $req->category,
                        'likes' => $likes,
                        'comments' => $comments,
                        'shares' => $shares,
                        'engagement' => $engagement,
                    ];
                }
            }

            usort($postsData, function ($a, $b) {
                return $b['engagement'] <=> $a['engagement'];
            });

            $filename = 'nupost_performance_report_' . date('Y-m-d') . '.csv';
            $headers  = [
                'Content-Type'        => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
            ];

            $callback = function() use ($postsData, $source) {
                $handle = fopen('php://output', 'w');
                fputs($handle, "\xEF\xBB\xBF");
                fputcsv($handle, ['Top Performing Posts (Engagement Metrics)']);
                fputcsv($handle, ['Source', $source]);
                fputcsv($handle, []);
                fputcsv($handle, ['Rank', 'Post ID / Request ID', 'Title / Content', 'Category', 'Likes', 'Comments', 'Shares', 'Total Engagement']);

                $rank = 1;
                foreach ($postsData as $post) {
                    fputcsv($handle, [
                        $rank++,
                        $post['request_id'],
                        $post['title'],
                        $post['category'],
                        $post['likes'],
                        $post['comments'],
                        $post['shares'],
                        $post['engagement'],
                    ]);
                }
                fclose($handle);
            };

            return response()->stream($callback, 200, $headers);
        } else {
            $requests = $query->with('activities')->orderByDesc('created_at')->get();

            $filename = 'nupost_details_report_' . date('Y-m-d') . '.csv';
            $headers  = [
                'Content-Type'        => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
            ];

            $callback = function() use ($requests) {
                $handle = fopen('php://output', 'w');
                fputs($handle, "\xEF\xBB\xBF");
                fputcsv($handle, ['ID','Request ID','Title','Requestor','Category','Priority','Status','Platforms','Preferred Date','Caption','Submitted','Activity History']);
                foreach ($requests as $req) {
                    $plats = implode(', ', $req->platforms_array ?? []);
                    
                    $history = $req->activities->map(function ($act) {
                        return "[" . $act->created_at->format('Y-m-d H:i') . "] " . $act->actor . ": " . $act->action;
                    })->implode('; ');

                    fputcsv($handle, [
                        $req->id,
                        $req->request_id ?? 'N/A',
                        $req->title,
                        $req->requester,
                        $req->category,
                        $req->priority,
                        $req->status,
                        $plats,
                        $req->preferred_date ? $req->preferred_date->format('Y-m-d') : '',
                        $req->caption ?? '',
                        $req->created_at->format('Y-m-d H:i:s'),
                        $history,
                    ]);
                }
                fclose($handle);
            };

            return response()->stream($callback, 200, $headers);
        }
    }
}