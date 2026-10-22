<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\PostRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AntiSpamAndSettingsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test successful request creation with submission token.
     */
    public function test_request_creation_succeeds_with_valid_data()
    {
        $user = User::create([
            'name' => 'Alice Requestor',
            'email' => 'alice@nupost.com',
            'password' => bcrypt('password'),
            'is_verified' => true,
            'role' => 'requestor',
        ]);

        $token = (string) Str::uuid();

        $response = $this->withSession([
            'name' => $user->name,
            'role' => 'requestor',
            'user_id' => $user->id,
        ])->post(route('requestor.requests.store'), [
            'submission_token' => $token,
            'title' => 'Fresh Campus Orientation 2026',
            'description' => 'Comprehensive detailed description of the orientation event for students.',
            'category' => 'Standard PubMat',
            'priority' => 'High',
            'platforms' => ['Facebook'],
            'post_date' => date('Y-m-d', strtotime('+3 days')),
        ]);

        $response->assertRedirect(route('requestor.requests'));
        $this->assertDatabaseHas('post_requests', [
            'title' => 'Fresh Campus Orientation 2026',
            'requester' => 'Alice Requestor',
        ]);
    }

    /**
     * Test that re-submitting with the same submission_token does not duplicate request.
     */
    public function test_duplicate_submission_token_does_not_create_duplicate()
    {
        $user = User::create([
            'name' => 'Bob Requestor',
            'email' => 'bob@nupost.com',
            'password' => bcrypt('password'),
            'is_verified' => true,
            'role' => 'requestor',
        ]);

        $token = (string) Str::uuid();

        $payload = [
            'submission_token' => $token,
            'title' => 'Annual Research Colloquium 2026',
            'description' => 'Full event announcement description for university research presentations.',
            'category' => 'Standard PubMat',
            'priority' => 'Medium',
            'platforms' => ['Facebook', 'LinkedIn'],
            'post_date' => date('Y-m-d', strtotime('+5 days')),
        ];

        // First submission
        $res1 = $this->withSession([
            'name' => $user->name,
            'role' => 'requestor',
            'user_id' => $user->id,
        ])->post(route('requestor.requests.store'), $payload);

        $res1->assertRedirect(route('requestor.requests'));

        // Immediate second submission with identical token (simulating double click)
        $res2 = $this->withSession([
            'name' => $user->name,
            'role' => 'requestor',
            'user_id' => $user->id,
        ])->post(route('requestor.requests.store'), $payload);

        $res2->assertRedirect(route('requestor.requests'));

        // Ensure only ONE request was created in the database
        $count = PostRequest::where('title', 'Annual Research Colloquium 2026')->count();
        $this->assertEquals(1, $count);
    }

    /**
     * Test that duplicate title by the same user within 24 hours is rejected.
     */
    public function test_duplicate_title_by_same_user_is_rejected()
    {
        $user = User::create([
            'name' => 'Charlie Requestor',
            'email' => 'charlie@nupost.com',
            'password' => bcrypt('password'),
            'is_verified' => true,
            'role' => 'requestor',
        ]);

        PostRequest::create([
            'title' => 'Leadership Summit 2026',
            'requester' => $user->name,
            'user_id' => $user->id,
            'description' => 'Leadership summit details and student council schedule.',
            'category' => 'Standard PubMat',
            'priority' => 'High',
            'status' => 'Pending Review',
            'platform' => 'Facebook',
            'preferred_date' => date('Y-m-d', strtotime('+4 days')),
        ]);

        // Attempt to submit another request with the same title
        $response = $this->withSession([
            'name' => $user->name,
            'role' => 'requestor',
            'user_id' => $user->id,
        ])->post(route('requestor.requests.store'), [
            'submission_token' => (string) Str::uuid(),
            'title' => 'Leadership Summit 2026',
            'description' => 'A slightly different description about the leadership summit.',
            'category' => 'Standard PubMat',
            'priority' => 'High',
            'platforms' => ['Facebook'],
            'post_date' => date('Y-m-d', strtotime('+4 days')),
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals(1, PostRequest::where('title', 'Leadership Summit 2026')->count());
    }

    /**
     * Test that duplicate content hash within 24 hours is rejected.
     */
    public function test_duplicate_content_hash_is_rejected()
    {
        $user = User::create([
            'name' => 'Daisy Requestor',
            'email' => 'daisy@nupost.com',
            'password' => bcrypt('password'),
            'is_verified' => true,
            'role' => 'requestor',
        ]);

        PostRequest::create([
            'title' => 'Inter-Campus Sports Meet',
            'requester' => 'Other User',
            'description' => 'Exact matching description for the athletic championship games.',
            'category' => 'Standard PubMat',
            'priority' => 'High',
            'status' => 'Pending Review',
            'platform' => 'Facebook',
            'preferred_date' => date('Y-m-d', strtotime('+6 days')),
        ]);

        $response = $this->withSession([
            'name' => $user->name,
            'role' => 'requestor',
            'user_id' => $user->id,
        ])->post(route('requestor.requests.store'), [
            'submission_token' => (string) Str::uuid(),
            'title' => 'Inter-Campus Sports Meet',
            'description' => 'Exact matching description for the athletic championship games.',
            'category' => 'Standard PubMat',
            'priority' => 'High',
            'platforms' => ['Facebook'],
            'post_date' => date('Y-m-d', strtotime('+6 days')),
        ]);

        $response->assertSessionHas('error');
    }

    /**
     * Test admin settings does not show System Information or Access Token sections.
     */
    public function test_admin_settings_does_not_contain_system_info_or_access_token()
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@nupost.com',
            'password' => bcrypt('password'),
            'is_verified' => true,
            'role' => 'admin',
        ]);

        $response = $this->withSession([
            'role' => 'admin',
            'admin_email' => $admin->email,
            'admin_name' => $admin->name,
        ])->get(route('admin.settings'));

        $response->assertStatus(200);

        // Verify "System Information" panel is not in settings
        $response->assertDontSee('System Information');
        $response->assertDontSee('Current environment and configuration');

        // Verify "Access Token" panel / nav is not in settings
        $response->assertDontSee('Access Token');
        $response->assertDontSee('Generate Access Token');
        $response->assertDontSee('kayeLinkInput');

        // Verify kept sections are still there
        $response->assertSee('Admin Account');
        $response->assertSee('Security');
        $response->assertSee('Notification Settings');
        $response->assertSee('AI & API Configuration', false);
    }

    /**
     * Test mobile API rejects duplicate title by the same user within 24h.
     */
    public function test_mobile_api_duplicate_title_is_rejected()
    {
        $user = User::create([
            'name' => 'Eve Mobile User',
            'email' => 'eve@nupost.com',
            'password' => bcrypt('password'),
            'is_verified' => true,
            'role' => 'requestor',
        ]);

        PostRequest::create([
            'title' => 'Mobile Hackathon 2026',
            'requester' => $user->name,
            'user_id' => $user->id,
            'description' => 'Original hackathon competition details.',
            'category' => 'Template-Based PubMat',
            'priority' => 'High',
            'status' => 'Pending Review',
            'platform' => 'Facebook',
        ]);

        $response = $this->postJson('/api/create_request.php', [
            'user_id' => $user->id,
            'title' => 'Mobile Hackathon 2026',
            'description' => 'Different description for the hackathon event.',
            'category' => 'Template-Based PubMat',
            'priority' => 'High',
        ]);

        $response->assertStatus(422);
        $this->assertFalse($response->json('success'));
        $this->assertStringContainsString('Duplicate request detected', $response->json('message'));
    }

    /**
     * Test mobile API rejects duplicate content hash within 24h.
     */
    public function test_mobile_api_duplicate_content_is_rejected()
    {
        $user = User::create([
            'name' => 'Frank Mobile User',
            'email' => 'frank@nupost.com',
            'password' => bcrypt('password'),
            'is_verified' => true,
            'role' => 'requestor',
        ]);

        PostRequest::create([
            'title' => 'Campus Film Festival',
            'requester' => 'Existing Director',
            'description' => 'Exact duplicate description for student film entries.',
            'category' => 'Standard PubMat',
            'priority' => 'Medium',
            'status' => 'Pending Review',
            'platform' => 'Facebook',
        ]);

        $response = $this->postJson('/api/create_request.php', [
            'user_id' => $user->id,
            'title' => 'Campus Film Festival',
            'description' => 'Exact duplicate description for student film entries.',
            'category' => 'Standard PubMat',
            'priority' => 'Medium',
        ]);

        $response->assertStatus(422);
        $this->assertFalse($response->json('success'));
        $this->assertStringContainsString('Duplicate request detected', $response->json('message'));
    }

    /**
     * Test submitting a request with just a year appended (e.g. "College Week Ceremony 2026")
     * when "College Week Ceremony" already exists is detected and rejected as a duplicate.
     */
    public function test_fuzzy_duplicate_with_year_suffix_is_rejected()
    {
        $user = User::create([
            'name' => 'Grace Requestor',
            'email' => 'grace@nupost.com',
            'password' => bcrypt('password'),
            'is_verified' => true,
            'role' => 'requestor',
        ]);

        PostRequest::create([
            'title' => 'College Week Ceremony',
            'requester' => $user->name,
            'user_id' => $user->id,
            'description' => 'College Week Ceremony opening details and stage preparation schedule.',
            'category' => 'Posting with Ready-Made PubMat',
            'priority' => 'Medium',
            'status' => 'Pending Review',
            'platform' => 'Facebook',
            'preferred_date' => date('Y-m-d', strtotime('+3 days')),
        ]);

        // Attempt to submit "College Week Ceremony 2026"
        $response = $this->withSession([
            'name' => $user->name,
            'role' => 'requestor',
            'user_id' => $user->id,
        ])->post(route('requestor.requests.store'), [
            'submission_token' => (string) \Illuminate\Support\Str::uuid(),
            'title' => 'College Week Ceremony 2026',
            'description' => 'College Week Ceremony opening details and stage preparation schedule.',
            'category' => 'Posting with Ready-Made PubMat',
            'priority' => 'Low',
            'platforms' => ['Facebook'],
            'post_date' => date('Y-m-d', strtotime('+3 days')),
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals(1, PostRequest::count());
    }

    /**
     * Test that permuted word order (e.g. "College Ceremony Week" vs "College Week Ceremony") is rejected.
     */
    public function test_permuted_word_order_duplicate_title_is_rejected()
    {
        $user = User::create([
            'name' => 'Henry Requestor',
            'email' => 'henry@nupost.com',
            'password' => bcrypt('password'),
            'is_verified' => true,
            'role' => 'requestor',
        ]);

        PostRequest::create([
            'title' => 'College Week Ceremony',
            'requester' => $user->name,
            'user_id' => $user->id,
            'description' => 'College Week Ceremony details and student program.',
            'category' => 'Posting with Ready-Made PubMat',
            'priority' => 'Medium',
            'status' => 'Pending Review',
            'platform' => 'Facebook',
            'preferred_date' => date('Y-m-d', strtotime('+3 days')),
        ]);

        // Attempt to submit "College Ceremony Week"
        $response = $this->withSession([
            'name' => $user->name,
            'role' => 'requestor',
            'user_id' => $user->id,
        ])->post(route('requestor.requests.store'), [
            'submission_token' => (string) \Illuminate\Support\Str::uuid(),
            'title' => 'College Ceremony Week',
            'description' => 'College Ceremony Week details and student program.',
            'category' => 'Posting with Ready-Made PubMat',
            'priority' => 'Low',
            'platforms' => ['Facebook'],
            'post_date' => date('Y-m-d', strtotime('+3 days')),
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals(1, PostRequest::where('requester', $user->name)->count());
    }

    /**
     * Test notification fetching and marking as read for both admin and requestor.
     */
    public function test_admin_and_requestor_notification_fetch_and_mark_read()
    {
        $admin = User::create([
            'name' => 'Notification Admin',
            'email' => 'admin_notif@nupost.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $requestor = User::create([
            'name' => 'Notification Requestor',
            'email' => 'requestor_notif@nupost.com',
            'password' => bcrypt('password'),
            'is_verified' => true,
            'role' => 'requestor',
        ]);

        $post = PostRequest::create([
            'title' => 'Sample Request for Notification',
            'requester' => $requestor->name,
            'user_id' => $requestor->id,
            'description' => 'Detailed description for testing notifications.',
            'category' => 'Standard PubMat',
            'priority' => 'Medium',
            'status' => 'Pending Review',
            'admin_seen' => false,
            'platform' => 'Facebook',
        ]);

        \App\Models\Notification::create([
            'user_id' => $requestor->id,
            'title' => 'Request Submitted',
            'message' => 'Your request has been submitted.',
            'type' => 'new_request',
            'is_read' => false,
        ]);

        // 1. Admin Fetch Test
        $adminFetch = $this->withSession([
            'role' => 'admin',
            'admin_email' => $admin->email,
            'admin_name' => $admin->name,
        ])->getJson(route('admin.notifications.fetch'));

        $adminFetch->assertStatus(200);
        $this->assertTrue($adminFetch->json('unread_count') >= 1);
        $notifs = $adminFetch->json('notifications');
        $this->assertNotEmpty($notifs);

        // 2. Admin Mark Read Test
        $adminRead = $this->withSession([
            'role' => 'admin',
            'admin_email' => $admin->email,
            'admin_name' => $admin->name,
        ])->postJson(route('admin.notifications.read'), ['all' => true]);

        $adminRead->assertStatus(200);
        $this->assertTrue($adminRead->json('success'));

        // 3. Requestor Fetch Test
        $reqFetch = $this->withSession([
            'role' => 'requestor',
            'user_id' => $requestor->id,
            'name' => $requestor->name,
        ])->getJson(route('requestor.notifications.fetch'));

        $reqFetch->assertStatus(200);
        $this->assertEquals(1, $reqFetch->json('unread_count'));

        // 4. Requestor Mark Read Test
        $reqRead = $this->withSession([
            'role' => 'requestor',
            'user_id' => $requestor->id,
            'name' => $requestor->name,
        ])->postJson(route('requestor.notifications.markread'), ['all' => true]);

        $reqRead->assertStatus(200);
        $this->assertTrue($reqRead->json('success'));
        $this->assertEquals(0, \App\Models\Notification::where('user_id', $requestor->id)->where('is_read', false)->count());
    }

    /**
     * Test changing AI API key generates an automatic notification for admin.
     */
    public function test_api_key_update_creates_automatic_notification_for_admin()
    {
        $admin = User::create([
            'name' => 'API Admin',
            'email' => 'api_admin@nupost.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        // Submit new API keys
        $response = $this->withSession([
            'role' => 'admin',
            'admin_email' => $admin->email,
            'admin_name' => $admin->name,
        ])->post(route('admin.settings.ai-keys'), [
            'gemini_api_key' => 'AIzaSyTestGeminiKey12345',
            'groq_api_key' => 'gsk_TestGroqKey67890',
        ]);

        $response->assertSessionHas('success');

        // Verify notification exists in database
        $notif = \App\Models\Notification::where('type', 'api_key')->first();
        $this->assertNotNull($notif);
        $this->assertEquals('AI API Key Updated', $notif->title);
        $this->assertStringContainsString('Google Gemini', $notif->message);
        $this->assertStringContainsString('Groq', $notif->message);

        // Verify admin notifications fetch includes the new API key notification
        $fetch = $this->withSession([
            'role' => 'admin',
            'admin_email' => $admin->email,
            'admin_name' => $admin->name,
        ])->getJson(route('admin.notifications.fetch'));

        $fetch->assertStatus(200);
        $found = collect($fetch->json('notifications'))->firstWhere('type', 'api_key');
        $this->assertNotNull($found);
        $this->assertEquals('AI API Key Updated', $found['title']);
    }

    /**
     * Test minimal landing page renders successfully with proper links.
     */
    public function test_landing_page_renders_successfully()
    {
        $response = $this->get(route('landing'));
        $response->assertStatus(200);
        $response->assertSee('Social Media Request & Content Portal', false);
        $response->assertSee('Submit a Post Request', false);
        $response->assertSee(route('login'), false);
        $response->assertSee(route('register'), false);
    }

    /**
     * Test submitting a new request creates an automatic notification for admin.
     */
    public function test_submitting_new_request_creates_admin_notification()
    {
        $admin = User::create([
            'name' => 'Notification Admin',
            'email' => 'admin_notif@nupost.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $requestor = User::create([
            'name' => 'Event Officer',
            'email' => 'officer@nupost.com',
            'password' => bcrypt('password'),
            'is_verified' => true,
            'role' => 'requestor',
        ]);

        // Requestor submits new request
        $response = $this->withSession([
            'name' => $requestor->name,
            'role' => 'requestor',
            'user_id' => $requestor->id,
        ])->post(route('requestor.requests.store'), [
            'submission_token' => (string) Str::uuid(),
            'title' => 'Freshers Welcome Assembly 2026',
            'description' => 'Orientation and general welcoming ceremony for all first-year freshmen students.',
            'category' => 'Standard PubMat',
            'priority' => 'Medium',
            'platforms' => ['Facebook', 'Instagram'],
            'post_date' => date('Y-m-d', strtotime('+7 days')),
        ]);

        $response->assertSessionHas('success');

        // Verify admin notification exists in database
        $adminNotif = \App\Models\Notification::where('user_id', $admin->id)
            ->where('type', 'new_request')
            ->first();

        $this->assertNotNull($adminNotif);
        $this->assertStringContainsString('Freshers Welcome Assembly 2026', $adminNotif->title);

        // Verify admin fetch includes the new request notification
        $fetch = $this->withSession([
            'role' => 'admin',
            'admin_id' => $admin->id,
            'admin_email' => $admin->email,
            'admin_name' => $admin->name,
        ])->getJson(route('admin.notifications.fetch'));

        $fetch->assertStatus(200);
        $this->assertTrue($fetch->json('unread_count') >= 1);
        $found = collect($fetch->json('notifications'))->firstWhere('type', 'new_request');
        $this->assertNotNull($found);
        $this->assertStringContainsString('Freshers Welcome Assembly 2026', $found['title']);
    }

    /**
     * Test requestor sending chat comment creates an automatic notification for admin.
     */
    public function test_requestor_sending_chat_creates_admin_notification()
    {
        $admin = User::create([
            'name' => 'Chat Admin',
            'email' => 'chat_admin@nupost.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $requestor = User::create([
            'name' => 'Student Leader',
            'email' => 'student_leader@nupost.com',
            'password' => bcrypt('password'),
            'is_verified' => true,
            'role' => 'requestor',
        ]);

        $post = PostRequest::create([
            'title' => 'Campus Hackathon 2026',
            'requester' => $requestor->name,
            'user_id' => $requestor->id,
            'description' => 'Annual student hackathon and tech exhibit.',
            'category' => 'Standard PubMat',
            'priority' => 'High',
            'status' => 'Pending Review',
            'platform' => 'Facebook',
        ]);

        // Requestor sends a chat message
        $response = $this->withSession([
            'name' => $requestor->name,
            'role' => 'requestor',
            'user_id' => $requestor->id,
        ])->post(route('requestor.requests.chat.send', $post->id), [
            'chat_message' => 'Good day Admin, we uploaded the revised poster version.',
        ]);

        $response->assertRedirect(route('requestor.requests.chat', $post->id));

        // Verify notification was created for admin
        $notif = \App\Models\Notification::where('user_id', $admin->id)
            ->where('type', 'comment')
            ->first();

        $this->assertNotNull($notif);
        $this->assertStringContainsString('Student Leader', $notif->title);
        $this->assertStringContainsString('revised poster version', $notif->message);

        // Verify admin fetch reflects comment notification
        $fetch = $this->withSession([
            'role' => 'admin',
            'admin_id' => $admin->id,
            'admin_email' => $admin->email,
            'admin_name' => $admin->name,
        ])->getJson(route('admin.notifications.fetch'));

        $fetch->assertStatus(200);
        $this->assertTrue($fetch->json('unread_count') >= 1);
        $found = collect($fetch->json('notifications'))->firstWhere('type', 'comment');
        $this->assertNotNull($found);
        $this->assertStringContainsString('Student Leader', $found['title']);

        // Admin marks notification as read
        $markRead = $this->withSession([
            'role' => 'admin',
            'admin_id' => $admin->id,
            'admin_email' => $admin->email,
            'admin_name' => $admin->name,
        ])->postJson(route('admin.notifications.read'), ['id' => $found['id']]);

        $markRead->assertStatus(200);
        $this->assertTrue($markRead->json('success'));

        // Verify unread count decreases
        $fetchAfter = $this->withSession([
            'role' => 'admin',
            'admin_id' => $admin->id,
            'admin_email' => $admin->email,
            'admin_name' => $admin->name,
        ])->getJson(route('admin.notifications.fetch'));

        $foundAfter = collect($fetchAfter->json('notifications'))->firstWhere('id', $found['id']);
        $this->assertTrue((bool)$foundAfter['is_read']);
    }
}


