<?php

namespace App\Http\Controllers\Requestor;

use App\Http\Controllers\Controller;
use App\Models\PostRequest;
use App\Models\RequestComment;
use App\Models\RequestActivity;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RequestController extends Controller
{
    /**
     * Helper to get unread notification count
     */
    private function getUnreadCount(): int
    {
        return Notification::where('user_id', session('user_id'))
                           ->where('is_read', false)
                           ->count();
    }

    /**
     * Normalize text for fuzzy duplicate & anti-spam detection:
     * - Lowercase
     * - Strips 4-digit years (e.g. 2024, 2025, 2026, 2027)
     * - Strips version/edition tags (e.g. v2, part 1, copy, new, draft)
     * - Strips punctuation/symbols and collapses whitespace
     */
    private function cleanTextForComparison(string $text): string
    {
        $text = strtolower(trim($text));
        $text = (string) preg_replace('/\b20[2-3]\d\b/', '', $text);
        $text = (string) preg_replace('/\b(v\d+|part\s*\d+|copy|new|draft|edit|final)\b/i', '', $text);
        $text = (string) preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text);
        return trim((string) preg_replace('/\s+/', ' ', $text));
    }

    /**
     * Extract normalized word tokens (filtering minor stop-words)
     */
    private function extractWordTokens(string $text): array
    {
        $clean = $this->cleanTextForComparison($text);
        if ($clean === '') {
            return [];
        }
        $stopWords = ['the', 'of', 'and', 'for', 'in', 'on', 'at', 'to', 'a', 'an', 'with', 'by'];
        $words = array_values(array_filter(explode(' ', $clean), function ($w) use ($stopWords) {
            return $w !== '' && !in_array($w, $stopWords);
        }));
        if (empty($words)) {
            $words = array_values(array_filter(explode(' ', $clean)));
        }
        return $words;
    }

    /**
     * Token-sort text: sorts words alphabetically so word-order variations
     * (e.g. "College Week Ceremony" vs "College Ceremony Week") produce identical strings.
     */
    private function tokenSortedText(string $text): string
    {
        $words = $this->extractWordTokens($text);
        sort($words);
        return implode(' ', $words);
    }

    /**
     * Calculate token overlap ratio (percentage of matching words between two texts)
     */
    private function tokenOverlapRatio(array $wordsA, array $wordsB): float
    {
        if (empty($wordsA) || empty($wordsB)) {
            return 0.0;
        }
        $intersect = array_intersect($wordsA, $wordsB);
        $maxCount = max(count($wordsA), count($wordsB));
        return $maxCount > 0 ? (count($intersect) / $maxCount) * 100.0 : 0.0;
    }

    /**
     * Display list of requests
     */
    public function index(Request $request)
    {
        $user_name = session('name');
        $search    = trim($request->input('search', ''));
        $filter    = $request->input('filter', 'all');

        $status_map = [
            'pending'  => 'Pending Review',
            'seen'     => 'Under Review',
            'approved' => 'Approved',
            'posted'   => 'Posted',
        ];

        $user_id   = session('user_id');
        $query = PostRequest::where(function ($q) use ($user_name, $user_id) {
            if (\Illuminate\Support\Facades\Schema::hasColumn('post_requests', 'user_id') && $user_id) {
                $q->where('user_id', $user_id)->orWhere('requester', $user_name);
            } else {
                $q->where('requester', $user_name);
            }
        });

        if ($filter !== 'all') {
            if ($filter === 'pending') {
                $query->whereIn('status', ['Pending Review', 'Pending']);
            } elseif (isset($status_map[$filter])) {
                $query->where('status', $status_map[$filter]);
            }
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%$search%")
                  ->orWhere('description', 'like', "%$search%")
                  ->orWhere('category', 'like', "%$search%");
            });
        }

        $requests     = $query->orderBy('created_at', 'desc')->get();
        $total        = $requests->count();
        $unread_count = $this->getUnreadCount();

        return view('requestor.requests', compact(
            'requests', 'total', 'search', 'filter', 'unread_count'
        ));
    }

    /**
     * Show create request form
     */
    public function create()
    {
        $unread_count = $this->getUnreadCount();
        return view('requestor.create_request', compact('unread_count'));
    }

    /**
     * Store new request
     */
    public function store(Request $request)
    {
        $user_name = session('name');
        $user_id   = session('user_id');

        $userIdentifier = $user_id ? "id_{$user_id}" : ($user_name ? "name_{$user_name}" : 'ip_' . $request->ip());

        // 1. One-time idempotency token check (prevents browser double-submits / refresh duplicates)
        $submissionToken = $request->input('submission_token');
        if (!empty($submissionToken)) {
            $tokenKey = 'post_req_token_' . md5($submissionToken);
            if (!Cache::add($tokenKey, true, now()->addHours(2))) {
                return redirect()->route('requestor.requests')->with('success', 'Your request has already been submitted successfully!');
            }
        }

        // 2. Anti-spam submission cooldown (prevents rapid-fire request flooding)
        $cooldownKey = 'post_req_cooldown_' . md5($userIdentifier);
        if (!app()->runningUnitTests() && Cache::has($cooldownKey)) {
            $expiresAt = (int) Cache::get($cooldownKey);
            $remaining = max(1, $expiresAt - time());
            $msg = "Anti-spam protection: Please wait {$remaining} second(s) before submitting another request.";
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['success' => false, 'message' => $msg], 429);
            }
            return back()->withInput()->with('error', $msg);
        }

        // 3. Concurrency lock (prevents concurrent race conditions when clicking submit rapidly)
        $lockKey = 'post_req_lock_' . md5($userIdentifier);
        if (!Cache::add($lockKey, time(), now()->addSeconds(15))) {
            $msg = 'A request submission is already in progress. Please wait a moment.';
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['success' => false, 'message' => $msg], 429);
            }
            return back()->withInput()->with('error', $msg);
        }

        try {
            $request->validate([
                'title'       => 'required|string|min:5|max:255',
                'description' => 'required|string|min:15',
                'category'    => 'required|in:Checking of Materials,Posting with Ready-Made PubMat,Template-Based PubMat,Standard PubMat,Multiple Collaterals / Tarpaulins,New Campaign / Creative Concept,Event Documentation',
                'priority'    => 'required|in:Low,Medium,High,Urgent',
                'platforms'   => 'required|array|min:1',
                'post_date'   => 'required|date|after:today',
            ]);

            $title = trim((string) $request->input('title', ''));
            $description = trim((string) $request->input('description', ''));
            $cleanTitle = $this->cleanTextForComparison($title);
            $cleanDesc  = $this->cleanTextForComparison($description);

            // 4. Smart Fuzzy & Semantic Duplicate Detection (within 48h)
            $selectCols = ['id', 'request_id', 'title', 'description', 'status', 'requester', 'category', 'preferred_date'];
            if (\Illuminate\Support\Facades\Schema::hasColumn('post_requests', 'user_id')) {
                $selectCols[] = 'user_id';
            }

            $recentDuplicates = PostRequest::where('status', '!=', 'Rejected')
                ->where('created_at', '>=', now()->subHours(48))
                ->select($selectCols)
                ->get();

            $targetDate = $request->input('post_date');

            foreach ($recentDuplicates as $dup) {
                $dupTitleRaw = trim((string) ($dup->title ?? ''));
                $dupDescRaw  = trim((string) ($dup->description ?? ''));
                $cleanDupTitle = $this->cleanTextForComparison($dupTitleRaw);
                $cleanDupDesc  = $this->cleanTextForComparison($dupDescRaw);

                $isSameUser = false;
                if ($user_id && isset($dup->user_id) && !empty($dup->user_id)) {
                    $isSameUser = ((int) $dup->user_id === (int) $user_id);
                }
                if (!$isSameUser && !empty($user_name) && !empty($dup->requester)) {
                    $isSameUser = (strcasecmp($dup->requester, $user_name) === 0);
                }

                // Token-sorted strings (handles word permutation / reordering, e.g. "College Week Ceremony" vs "College Ceremony Week")
                $sortedTitle = $this->tokenSortedText($title);
                $sortedDupTitle = $this->tokenSortedText($dupTitleRaw);
                $sortedMatch = ($sortedTitle !== '' && $sortedTitle === $sortedDupTitle);

                // Word tokens & overlap ratio
                $wordsTitle = $this->extractWordTokens($title);
                $wordsDupTitle = $this->extractWordTokens($dupTitleRaw);
                $tokenOverlap = $this->tokenOverlapRatio($wordsTitle, $wordsDupTitle);

                // Title normalized match
                $titleNormalizedMatch = ($cleanTitle !== '' && $cleanTitle === $cleanDupTitle) || $sortedMatch;

                // Title fuzzy similarity
                $titleSim = 0.0;
                if (!$titleNormalizedMatch && $cleanTitle !== '' && $cleanDupTitle !== '') {
                    $simRaw = 0.0;
                    $simSorted = 0.0;
                    similar_text($cleanTitle, $cleanDupTitle, $simRaw);
                    similar_text($sortedTitle, $sortedDupTitle, $simSorted);
                    $titleSim = max($simRaw, $simSorted, $tokenOverlap);
                } elseif ($titleNormalizedMatch) {
                    $titleSim = 100.0;
                }

                // Description fuzzy similarity
                $descSim = 0.0;
                if ($cleanDesc !== '' && $cleanDupDesc !== '') {
                    if ($cleanDesc === $cleanDupDesc) {
                        $descSim = 100.0;
                    } else {
                        similar_text($cleanDesc, $cleanDupDesc, $descSim);
                    }
                }

                // Rule A (Same user):
                // - Title match (ignoring years, versions, punctuation, or word-order permutation)
                // - OR Title similarity >= 75%
                // - OR Token overlap >= 75% (e.g. 3 of 4 words match)
                // - OR Description is identical or nearly identical (>= 80%)
                // - OR Both title >= 60% and description >= 60%
                $isUserDuplicate = $isSameUser && (
                    $titleNormalizedMatch ||
                    $sortedMatch ||
                    $titleSim >= 75.0 ||
                    $tokenOverlap >= 75.0 ||
                    $descSim >= 80.0 ||
                    ($titleSim >= 60.0 && $descSim >= 60.0)
                );

                // Rule B (System-wide cross-account):
                // Stops users creating alt accounts to bypass checks:
                // - Exact or normalized/token-sorted title match WITH description >= 60%
                // - OR Same target date + same category AND (titleSim >= 70% OR tokenOverlap >= 70%)
                // - OR Same target date AND (titleSim >= 80% OR tokenOverlap >= 80%)
                // - OR High overall similarity (titleSim >= 80% AND (descSim >= 70% OR tokenOverlap >= 75%))
                // - OR Content hash match
                $dupDate = $dup->preferred_date ? \Carbon\Carbon::parse($dup->preferred_date)->format('Y-m-d') : null;
                $sameTargetDate = ($targetDate && $dupDate && $targetDate === $dupDate);
                $sameCategory = (strcasecmp((string)$request->input('category'), (string)($dup->category ?? '')) === 0);

                $isSystemDuplicate = (
                    (($titleNormalizedMatch || $sortedMatch) && $descSim >= 60.0) ||
                    ($sameTargetDate && $sameCategory && ($titleSim >= 70.0 || $tokenOverlap >= 70.0)) ||
                    ($sameTargetDate && ($titleSim >= 80.0 || $tokenOverlap >= 80.0)) ||
                    ($titleSim >= 80.0 && ($descSim >= 70.0 || $tokenOverlap >= 75.0)) ||
                    (md5(strtolower($title) . '|' . strtolower($description)) === md5(strtolower($dupTitleRaw) . '|' . strtolower($dupDescRaw)))
                );

                if ($isUserDuplicate || $isSystemDuplicate) {
                    $statusLabel = $dup->status ?: 'Pending Review';
                    $existingTitleSnippet = strlen($dupTitleRaw) > 35 ? substr($dupTitleRaw, 0, 35) . '…' : $dupTitleRaw;

                    $msg = "Duplicate request detected: A very similar request (\"{$existingTitleSnippet}\") was already submitted recently (Status: {$statusLabel}).";
                    if ($isSameUser && in_array(strtolower($statusLabel), ['pending', 'pending review'])) {
                        $msg .= " If you need to make changes, please edit your existing pending request instead of submitting a duplicate.";
                    }

                    if ($request->expectsJson() || $request->is('api/*')) {
                        return response()->json(['success' => false, 'message' => $msg], 422);
                    }
                    return back()->withInput()->with('error', $msg);
                }
            }

            // 5. Media SHA-256 Fingerprinting (Blocks re-uploaded identical images across active requests)
            if ($request->hasFile('media')) {
                $uploadedFiles = $request->file('media');
                if (!is_array($uploadedFiles)) {
                    $uploadedFiles = [$uploadedFiles];
                }

                $incomingHashes = [];
                foreach ($uploadedFiles as $file) {
                    if ($file && $file->isValid()) {
                        $incomingHashes[] = hash_file('sha256', $file->getRealPath());
                    }
                }

                if (!empty($incomingHashes)) {
                    $activeMediaRequests = PostRequest::whereIn('status', ['Pending', 'Pending Review', 'Under Review'])
                        ->where('created_at', '>=', now()->subDays(14))
                        ->whereNotNull('media_file')
                        ->where('media_file', '!=', '')
                        ->select('id', 'request_id', 'title', 'media_file')
                        ->get();

                    foreach ($activeMediaRequests as $amr) {
                        $existingFilenames = array_filter(array_map('trim', explode(',', $amr->media_file)));
                        foreach ($existingFilenames as $efn) {
                            $filePath = public_path('uploads/' . $efn);
                            if (file_exists($filePath)) {
                                $existingHash = Cache::remember('media_sha256_' . $efn, 86400, function () use ($filePath) {
                                    return hash_file('sha256', $filePath);
                                });

                                if (in_array($existingHash, $incomingHashes, true)) {
                                    $existingSnippet = strlen($amr->title) > 35 ? substr($amr->title, 0, 35) . '…' : $amr->title;
                                    $reqCode = $amr->request_id ?: ('REQ-' . str_pad((string)$amr->id, 5, '0', STR_PAD_LEFT));
                                    $msg = "Duplicate publication material detected: The uploaded image matches an active request (\"{$existingSnippet}\", {$reqCode}). Please edit your existing request instead of submitting duplicate materials.";

                                    if ($request->expectsJson() || $request->is('api/*')) {
                                        return response()->json(['success' => false, 'message' => $msg], 422);
                                    }
                                    return back()->withInput()->with('error', $msg);
                                }
                            }
                        }
                    }
                }
            }

            $media_file = '';
            if ($request->hasFile('media')) {
                $upload_dir    = public_path('uploads');
                $altUploadDir  = base_path('public_html/uploads');
                $mirrorNeeded  = is_dir($altUploadDir) && realpath($altUploadDir) !== realpath($upload_dir);
                $uploaded      = [];

                foreach (array_slice($request->file('media'), 0, 10) as $file) {
                    if (!$file->isValid()) continue;
                    $origExt   = strtolower($file->getClientOriginalExtension());
                    // Normalize jfif to standard jpg so web templates and web servers serve it as image/jpeg
                    $saveExt   = ($origExt === 'jfif') ? 'jpg' : $origExt;
                    $filename  = 'media_' . uniqid() . '.' . $saveExt;
                    $file->move($upload_dir, $filename);

                    // If public_html/uploads exists and is separate from public_path('uploads'), mirror file
                    if ($mirrorNeeded) {
                        @copy($upload_dir . '/' . $filename, $altUploadDir . '/' . $filename);
                    }

                    // Image Optimization
                    if (in_array($saveExt, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                        \App\Services\ImageOptimizer::optimize($upload_dir . '/' . $filename);
                        if ($mirrorNeeded) {
                            @copy($upload_dir . '/' . $filename, $altUploadDir . '/' . $filename);
                        }
                    }

                    $uploaded[] = $filename;
                }
                $media_file = implode(',', $uploaded);
            }

            $payload = [
                'title'          => $request->title,
                'requester'      => $user_name,
                'category'       => $request->category,
                'priority'       => $request->priority,
                'status'         => 'Pending Review',
                'description'    => $request->description,
                'platform'       => implode(',', $request->input('platforms', [])),
                'caption'        => $request->caption,
                'preferred_date' => $request->post_date ?: null,
                'media_file'     => $media_file,
            ];
            if (\Illuminate\Support\Facades\Schema::hasColumn('post_requests', 'user_id') && session('user_id')) {
                $payload['user_id'] = session('user_id');
            }

            $req = PostRequest::create($payload);

            if (\Illuminate\Support\Facades\Schema::hasColumn('post_requests', 'request_id') && empty($req->request_id)) {
                $reqCode = 'REQ-' . str_pad((string) $req->id, 5, '0', STR_PAD_LEFT);
                $req->update(['request_id' => $reqCode]);
            }

            // Set 8-second cooldown after successful creation to prevent rapid spam flooding
            if (!app()->runningUnitTests()) {
                Cache::put($cooldownKey, time() + 8, now()->addSeconds(8));
            }

            \App\Models\AuditLog::record('post_request_created');

            RequestActivity::create([
                'request_id' => $req->id,
                'actor'      => $user_name,
                'action'     => 'Request submitted',
            ]);

            // Create notification for requestor
            if ($user_id) {
                try {
                    $reqNotifData = [
                        'user_id' => $user_id,
                        'title'   => 'Request Submitted',
                        'message' => "Your request \"{$req->title}\" has been submitted and is pending review.",
                        'type'    => 'new_request',
                        'is_read' => false,
                    ];
                    if (\Illuminate\Support\Facades\Schema::hasColumn('notifications', 'request_id')) {
                        $reqNotifData['request_id'] = $req->id;
                    }
                    \App\Models\Notification::create($reqNotifData);
                } catch (\Throwable $e) {}
            }

            // Create notification for all admins
            $admins = \App\Models\User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                try {
                    $adminNotifData = [
                        'user_id' => $admin->id,
                        'title'   => 'New Request: ' . \Illuminate\Support\Str::limit($req->title, 40),
                        'message' => ($user_name ?: 'A requestor') . ' submitted a new post request for review.',
                        'type'    => 'new_request',
                        'is_read' => false,
                    ];
                    if (\Illuminate\Support\Facades\Schema::hasColumn('notifications', 'request_id')) {
                        $adminNotifData['request_id'] = $req->id;
                    }
                    \App\Models\Notification::create($adminNotifData);
                } catch (\Throwable $e) {}
            }

            // Send email to all admins
            $admins = \App\Models\User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                if ($admin->email) {
                    try {
                        $html = $this->getNewRequestAdminEmailHtml($admin->name, $req->title, $user_name);
                        \Illuminate\Support\Facades\Mail::send([], [], function ($msg) use ($admin, $html) {
                            $msg->to($admin->email, $admin->name)
                                ->subject("NUPost Admin: New Request Submitted")
                                ->html($html);
                        });
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error('[NUPost] New request admin email failed: ' . $e->getMessage());
                    }
                }
            }

            return redirect()->route('requestor.requests')->with('success', 'Request submitted successfully!');

        } finally {
            Cache::forget($lockKey);
        }
    }

    /**
     * View Request Tracking (The "Show" page)
     * FIXED: Added chat_messages to prevent Undefined Variable error
     */
    public function show($id)
    {
        $user_name = session('name');
        
        $req = PostRequest::where('id', $id)
                          ->where('requester', $user_name)
                          ->firstOrFail();

        $chat_messages = RequestComment::where('request_id', $id)
                                       ->orderBy('created_at', 'asc')
                                       ->get();
                                   
        $activities = RequestActivity::where('request_id', $id)
                                     ->orderBy('created_at', 'desc')
                                     ->get();
                                     
        $unread_count = $this->getUnreadCount();

        return view('requestor.request_tracking', compact('req', 'activities', 'unread_count', 'chat_messages'));
    }

 /**
     * Show Edit Form
     * Only accessible if the status is exactly 'Pending Review'
     */
    public function edit($id)
    {
        $user_name = session('name');
        
        // Gagamit tayo ng firstOrFail para kung hindi 'Pending Review', 
        // automatic 404 error para hindi ma-bypass ang lock.
        $req = PostRequest::where('id', $id)
                          ->where('requester', $user_name)
                          ->where('status', 'Pending Review')
                          ->firstOrFail();
                                
        $unread_count = $this->getUnreadCount();
        
        return view('requestor.edit_request', compact('req', 'unread_count'));
    }

    /**
     * Update existing request
     */
    public function update(Request $request, $id)
    {
        $user_name = session('name');
        
        // Security check: Siguraduhin na ang ina-update ay 'Pending Review' pa rin
        $req = PostRequest::where('id', $id)
                          ->where('requester', $user_name)
                          ->where('status', 'Pending Review')
                          ->firstOrFail();

        // Basic Validation para hindi mag-crash ang database
        $request->validate([
            'title'       => 'required|string|min:5|max:255',
            'description' => 'required|string|min:15',
            'category'    => 'required|in:Checking of Materials,Posting with Ready-Made PubMat,Template-Based PubMat,Standard PubMat,Multiple Collaterals / Tarpaulins,New Campaign / Creative Concept,Event Documentation',
            'priority'    => 'required|in:Low,Medium,High,Urgent',
            'platforms'   => 'required|array|min:1',
            'post_date'   => 'required|date|after:today',
        ]);

        $data = [
            'title'          => $request->title,
            'description'    => $request->description,
            'category'       => $request->category,
            'priority'       => $request->priority,
            'caption'        => $request->caption,
            'platform'       => implode(',', $request->input('platforms', [])),
            'preferred_date' => $request->post_date ?: null,
        ];

        // Media Upload Logic (Limit to 10 files)
        if ($request->hasFile('media')) {
            $uploaded = [];
            // Burahin ang lumang files kung gusto mo ng fresh (Optional)
            // if($req->media_file) { /* delete logic here */ }

            $upload_dir    = public_path('uploads');
            $altUploadDir  = base_path('public_html/uploads');
            $mirrorNeeded  = is_dir($altUploadDir) && realpath($altUploadDir) !== realpath($upload_dir);

            foreach (array_slice($request->file('media'), 0, 10) as $file) {
                if ($file->isValid()) {
                    $origExt   = strtolower($file->getClientOriginalExtension());
                    $saveExt   = ($origExt === 'jfif') ? 'jpg' : $origExt;
                    $filename  = 'media_' . uniqid() . '.' . $saveExt;
                    $file->move($upload_dir, $filename);

                    if ($mirrorNeeded) {
                        @copy($upload_dir . '/' . $filename, $altUploadDir . '/' . $filename);
                    }

                    // Image Optimization
                    if (in_array($saveExt, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                        \App\Services\ImageOptimizer::optimize($upload_dir . '/' . $filename);
                        if ($mirrorNeeded) {
                            @copy($upload_dir . '/' . $filename, $altUploadDir . '/' . $filename);
                        }
                    }

                    $uploaded[] = $filename;
                }
            }
            
            if (!empty($uploaded)) {
                $data['media_file'] = implode(',', $uploaded);
            }
        }

        // Execute Update
        $req->update($data);

        \App\Models\AuditLog::record('post_request_edited');

        // Record Activity
        RequestActivity::create([
            'request_id' => $id,
            'actor'      => $user_name,
            'action'     => 'Request details updated',
        ]);

        return redirect()->route('requestor.requests')
                         ->with('success', 'Request updated successfully!');
    }

    /**
     * Delete request
     */
    public function destroy($id)
    {
        $user_name = session('name');
        $req       = PostRequest::where('id', $id)
                                ->where('requester', $user_name)
                                ->where('status', 'Pending Review')
                                ->firstOrFail();

        if ($req->media_file) {
            foreach (explode(',', $req->media_file) as $file) {
                $path = public_path('uploads/' . trim($file));
                if (file_exists($path)) unlink($path);
            }
        }

        $req->delete();
        return redirect()->route('requestor.requests')->with('success', 'Request deleted successfully!');
    }

    /**
     * Request Chat Page
     */
    public function chat($id)
    {
        $user_name     = session('name');
        $req           = PostRequest::where('id', $id)->where('requester', $user_name)->firstOrFail();
        $chat_messages = RequestComment::where('request_id', $id)->orderBy('created_at', 'asc')->get();
        $unread_count  = $this->getUnreadCount();

        return view('requestor.request_chat', compact('req', 'chat_messages', 'unread_count'));
    }

    /**
     * Send Chat Message
     */
    public function sendChat(Request $request, $id)
    {
        $user_name = session('name');
        $userId    = session('user_id') ?: auth()->id();
        $req       = PostRequest::where('id', $id)
            ->where(function ($q) use ($user_name, $userId) {
                if ($user_name) $q->where('requester', $user_name);
                if ($userId && \Illuminate\Support\Facades\Schema::hasColumn('post_requests', 'user_id')) {
                    $q->orWhere('user_id', $userId);
                }
            })
            ->firstOrFail();

        $message = trim($request->input('chat_message', ''));
        if ($message !== '') {
            // Deduplication guard: duplicate message submitted within 5 seconds
            $duplicate = RequestComment::where('request_id', $id)
                ->where('sender_role', 'requestor')
                ->where('message', $message)
                ->where('created_at', '>=', now()->subSeconds(5))
                ->exists();

            if ($duplicate) {
                return redirect()->route('requestor.requests.chat', $id);
            }

            RequestComment::create([
                'request_id'  => $id,
                'sender_role' => 'requestor',
                'sender_name' => $user_name ?: ($req->requester ?: 'Requestor'),
                'message'     => $message,
                'admin_seen'  => false,
            ]);

            // Mark post request as needing attention
            $req->update(['admin_seen' => false]);

            RequestActivity::create([
                'request_id' => $id,
                'actor'      => $user_name ?: ($req->requester ?: 'Requestor'),
                'action'     => 'Sent a message to admin',
            ]);

            // Create notification for all admins
            $admins = \App\Models\User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                try {
                    $chatNotifData = [
                        'user_id' => $admin->id,
                        'title'   => 'New Message from ' . ($user_name ?: $req->requester),
                        'message' => '"' . \Illuminate\Support\Str::limit($req->title, 35) . '": ' . \Illuminate\Support\Str::limit($message, 80),
                        'type'    => 'comment',
                        'is_read' => false,
                    ];
                    if (\Illuminate\Support\Facades\Schema::hasColumn('notifications', 'request_id')) {
                        $chatNotifData['request_id'] = $id;
                    }
                    \App\Models\Notification::create($chatNotifData);
                } catch (\Throwable $e) {}
            }

            // Send email to all admins
            foreach ($admins as $admin) {
                if ($admin->email) {
                    try {
                        $html = $this->getNewChatAdminEmailHtml($admin->name, $req->title, $user_name ?: $req->requester, $message);
                        \Illuminate\Support\Facades\Mail::send([], [], function ($msg) use ($admin, $req, $html) {
                            $msg->to($admin->email, $admin->name)
                                ->subject("NUPost Admin: New chat from " . $req->requester)
                                ->html($html);
                        });
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error('[NUPost] Admin chat email failed: ' . $e->getMessage());
                    }
                }
            }
        }

        return redirect()->route('requestor.requests.chat', $id);
    }

    /**
     * Calendar View
     */
    public function calendar(Request $request)
{
    $user_name = session('name');
    $month     = (int)($request->input('month', date('n')));
    $year      = (int)($request->input('year',  date('Y')));

    // Toggle public/private mode
    if ($request->has('toggle_public')) {
        $current = session('cal_public', false);
        session(['cal_public' => !$current]);
        return redirect()->route('requestor.calendar', [
            'month' => $month,
            'year'  => $year,
        ]);
    }

    $is_public = (bool)session('cal_public', false);

    if ($month < 1)  { $month = 12; $year--; }
    if ($month > 12) { $month = 1;  $year++; }

    $prev_month = $month - 1; $prev_year = $year;
    if ($prev_month < 1)  { $prev_month = 12; $prev_year--; }
    $next_month = $month + 1; $next_year = $year;
    if ($next_month > 12) { $next_month = 1;  $next_year++; }

    $query = PostRequest::whereNotNull('preferred_date')
                        ->whereMonth('preferred_date', $month)
                        ->whereYear('preferred_date', $year);

    if (!$is_public) {
        $query->where('requester', $user_name);
    }

    $all_events = $query->orderBy('preferred_date')->get();

    $events = [];
    foreach ($all_events as $ev) {
        $day = (int)date('j', strtotime($ev->preferred_date));
        $events[$day][] = $ev;
    }

    $upcoming_query = PostRequest::whereNotNull('preferred_date')
        ->where('preferred_date', '>=', now()->startOfDay())
        ->where('preferred_date', '<=', now()->addDays(7)->endOfDay());

    if (!$is_public) {
        $upcoming_query->where('requester', $user_name);
    }

    $upcoming = $upcoming_query->orderBy('preferred_date', 'asc')->get();

    $unread_count  = $this->getUnreadCount();
    $today_day     = (int)date('j');
    $today_month   = (int)date('n');
    $today_year    = (int)date('Y');
    $first_day     = (int)date('w', mktime(0, 0, 0, $month, 1, $year));
    $days_in_month = (int)date('t', mktime(0, 0, 0, $month, 1, $year));
    $days_in_prev  = (int)date('t', mktime(0, 0, 0, $prev_month, 1, $prev_year));
    $month_name    = date('F Y', mktime(0, 0, 0, $month, 1, $year));

    return view('requestor.calendar', compact(
        'events', 'month', 'year', 'month_name',
        'prev_month', 'prev_year', 'next_month', 'next_year',
        'today_day', 'today_month', 'today_year',
        'first_day', 'days_in_month', 'days_in_prev',
        'is_public', 'unread_count', 'upcoming'
    ));
}
    /**
     * Get requests by specific date (AJAX)
     */
    public function requestsByDate(Request $request)
    {
        $date = trim($request->query('date', ''));
        if (!$date) return response()->json(['total' => 0, 'requests' => []]);

        $requests = PostRequest::whereDate('preferred_date', $date)
                                ->select('title', 'status')
                                ->get();

        return response()->json([
            'total'    => $requests->count(),
            'requests' => $requests,
        ]);
    }

    private function getNewRequestAdminEmailHtml($adminName, $requestTitle, $requestorName)
    {
        return "<!DOCTYPE html><html><body style='margin:0;padding:0;background:#f5f6fa;font-family:Arial,sans-serif;'>
        <table width='100%' cellpadding='0' cellspacing='0' style='background:#f5f6fa;padding:40px 0;'>
        <tr><td align='center'>
        <table width='520' cellpadding='0' cellspacing='0' style='background:white;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);'>
        <tr><td style='background:#002366;padding:28px 40px;text-align:center;'>
            <div style='font-size:22px;font-weight:700;color:white;'>NUPost Admin</div>
            <div style='font-size:13px;color:rgba(255,255,255,0.7);margin-top:4px;'>New Request Submitted</div>
        </td></tr>
        <tr><td style='padding:32px 40px;'>
            <p style='font-size:14px;color:#374151;margin:0 0 16px;'>Hi <strong>{$adminName}</strong>,</p>
            <p style='font-size:14px;color:#374151;margin:0 0 20px;'>A new social media request has been submitted by <strong>{$requestorName}</strong>:</p>
            <div style='background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:16px 20px;margin-bottom:16px;'>
                <div style='font-size:15px;font-weight:600;color:#111827;'>{$requestTitle}</div>
            </div>
            <p style='font-size:13px;color:#6b7280;margin:0;'>Log in to the NUPost Admin Panel to review this request.</p>
        </td></tr>
        </table></td></tr></table></body></html>";
    }

    private function getNewChatAdminEmailHtml($adminName, $requestTitle, $requestorName, $messageText)
    {
        $escapedMessage = htmlspecialchars($messageText, ENT_QUOTES, 'UTF-8');
        return "<!DOCTYPE html><html><body style='margin:0;padding:0;background:#f5f6fa;font-family:Arial,sans-serif;'>
        <table width='100%' cellpadding='0' cellspacing='0' style='background:#f5f6fa;padding:40px 0;'>
        <tr><td align='center'>
        <table width='520' cellpadding='0' cellspacing='0' style='background:white;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);'>
        <tr><td style='background:#002366;padding:28px 40px;text-align:center;'>
            <div style='font-size:22px;font-weight:700;color:white;'>NUPost Admin</div>
            <div style='font-size:13px;color:rgba(255,255,255,0.7);margin-top:4px;'>New Chat Message</div>
        </td></tr>
        <tr><td style='padding:32px 40px;'>
            <p style='font-size:14px;color:#374151;margin:0 0 16px;'>Hi <strong>{$adminName}</strong>,</p>
            <p style='font-size:14px;color:#374151;margin:0 0 20px;'><strong>{$requestorName}</strong> has posted a new comment on a request:</p>
            <div style='background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:16px 20px;margin-bottom:16px;'>
                <div style='font-size:13px;color:#6b7280;margin-bottom:4px;'>Request</div>
                <div style='font-size:15px;font-weight:600;color:#111827;'>{$requestTitle}</div>
            </div>
            <div style='background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:16px 20px;margin-bottom:16px;'>
                <div style='font-size:11px;font-weight:700;color:#1e40af;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px;'>💬 Message</div>
                <div style='font-size:14px;color:#1e3a5f;line-height:1.6;'>{$escapedMessage}</div>
            </div>
            <p style='font-size:13px;color:#6b7280;margin:0;'>Log in to NUPost Admin Panel to view and reply to this comment.</p>
        </td></tr>
        </table></td></tr></table></body></html>";
    }
}