<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\PostRequest;
use App\Models\AuditLog;
use App\Models\KayeToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NupostFeatureTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 1. Test Image Optimization Service directly
     */
    public function test_image_optimization()
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'test_img') . '.jpg';
        
        $img = imagecreatetruecolor(1500, 1000);
        $white = imagecolorallocate($img, 255, 255, 255);
        imagefill($img, 0, 0, $white);
        imagejpeg($img, $tempPath);
        imagedestroy($img);

        $this->assertFileExists($tempPath);
        
        $res = \App\Services\ImageOptimizer::optimize($tempPath, 1200, 1200, 75);
        
        $this->assertTrue($res);
        $this->assertFileExists($tempPath);

        $info = getimagesize($tempPath);
        $this->assertLessThanOrEqual(1200, $info[0]);
        $this->assertLessThanOrEqual(1200, $info[1]);
        
        @unlink($tempPath);
    }

    /**
     * Test web request creation optimizes uploaded images
     */
    public function test_web_request_creation_optimizes_images()
    {
        $user = User::create([
            'name' => 'John Requester',
            'email' => 'john@nupost.com',
            'password' => bcrypt('password'),
            'is_verified' => true,
        ]);
        $user->role = 'requestor';
        $user->save();

        $this->actingAs($user);

        Storage::fake('public');
        $tempFile = UploadedFile::fake()->image('post_media.jpg', 1500, 1500);

        $response = $this->withSession([
            'name' => $user->name,
            'role' => 'requestor',
            'user_id' => $user->id
        ])->post(route('requestor.requests.store'), [
            'title' => 'Test Web Request Title',
            'description' => 'Test Web Request Description',
            'category' => 'Event',
            'priority' => 'Medium',
            'platforms' => ['Facebook'],
            'media' => [$tempFile],
        ]);

        $response->assertRedirect(route('requestor.requests'));

        $this->assertDatabaseHas('post_requests', [
            'title' => 'Test Web Request Title',
            'requester' => 'John Requester',
        ]);

        $request = PostRequest::where('title', 'Test Web Request Title')->first();
        $this->assertNotNull($request->media_file);
        
        $savedPath = public_path('uploads/' . $request->media_file);
        $this->assertFileExists($savedPath);

        $info = getimagesize($savedPath);
        $this->assertLessThanOrEqual(1200, $info[0]);
        $this->assertLessThanOrEqual(1200, $info[1]);

        @unlink($savedPath);
    }

    /**
     * 2a. Test Admin Login Audit Log
     */
    public function test_admin_login_audit()
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@nupost.com',
            'password' => bcrypt('password'),
            'is_verified' => true,
        ]);
        $admin->role = 'admin';
        $admin->save();

        $response = $this->post(route('login.store'), [
            'email' => 'admin@nupost.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action_type' => 'admin_login',
        ]);
    }

    /**
     * 2b. Test Requestor Login Audit Log
     */
    public function test_requestor_login_audit()
    {
        $requestor = User::create([
            'name' => 'Requestor User',
            'email' => 'requestor@nupost.com',
            'password' => bcrypt('password'),
            'is_verified' => true,
        ]);
        $requestor->role = 'requestor';
        $requestor->save();

        $response = $this->post(route('login.store'), [
            'email' => 'requestor@nupost.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('requestor.dashboard'));
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $requestor->id,
            'action_type' => 'user_login',
        ]);
    }

    /**
     * 2c. Test Requestor Logout Audit Log
     */
    public function test_requestor_logout_audit()
    {
        $requestor = User::create([
            'name' => 'Requestor User',
            'email' => 'requestor@nupost.com',
            'password' => bcrypt('password'),
            'is_verified' => true,
        ]);
        $requestor->role = 'requestor';
        $requestor->save();

        $response = $this->withSession([
            'role' => 'requestor',
            'user_id' => $requestor->id,
            'name' => $requestor->name
        ])->post(route('logout'));
        
        $response->assertRedirect(route('landing'));
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $requestor->id,
            'action_type' => 'user_logout',
        ]);
    }

    /**
     * 2d. Test Admin Logout Audit Log
     */
    public function test_admin_logout_audit()
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@nupost.com',
            'password' => bcrypt('password'),
            'is_verified' => true,
        ]);
        $admin->role = 'admin';
        $admin->save();

        $this->withSession([
            'role' => 'admin',
            'admin_email' => $admin->email,
            'admin_name' => $admin->name
        ])->post(route('logout'));

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action_type' => 'admin_logout',
        ]);
    }

    /**
     * 3. Test CSV Files Export
     */
    public function test_csv_report_export()
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@nupost.com',
            'password' => bcrypt('password'),
            'is_verified' => true,
        ]);
        $admin->role = 'admin';
        $admin->save();

        PostRequest::create([
            'title' => 'Sample Request for CSV',
            'requester' => 'John Doe',
            'category' => 'Marketing',
            'priority' => 'High',
            'status' => 'Approved',
            'description' => 'Details of request',
            'platform' => 'Facebook,Instagram',
        ]);

        $this->actingAs($admin);

        // Details CSV Export
        $response = $this->withSession([
            'role' => 'admin',
            'admin_email' => $admin->email,
            'admin_name' => $admin->name
        ])->get(route('admin.reports.export') . '?type=details');
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('Sample Request for CSV', $response->streamedContent());

        // Performance CSV Export
        $response = $this->withSession([
            'role' => 'admin',
            'admin_email' => $admin->email,
            'admin_name' => $admin->name
        ])->get(route('admin.reports.export') . '?type=performance');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('Sample Request for CSV', $response->streamedContent());
    }

    /**
     * 4. Test Ms. Kaye's Access Token Expiration & Re-auth
     */
    public function test_kaye_token_expiration_and_renewal()
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@nupost.com',
            'password' => bcrypt('password'),
            'is_verified' => true,
        ]);
        $admin->role = 'admin';
        $admin->save();

        $this->actingAs($admin);

        // Generate Token
        $response = $this->withSession([
            'role' => 'admin',
            'admin_email' => $admin->email,
            'admin_name' => $admin->name
        ])->post(route('admin.settings.kaye-token.generate'));
        $response->assertRedirect();
        
        $this->assertDatabaseCount('kaye_tokens', 1);
        $token = KayeToken::first();
        $this->assertNotNull($token->token);
        $this->assertTrue($token->expires_at->isAfter(now()->addDays(59)));

        // Access dashboard with valid token
        $response = $this->get(route('kaye.login', $token->token));
        $response->assertRedirect(route('kaye.dashboard'));

        $response = $this->withSession([
            'role' => 'kaye',
            'kaye_token' => $token->token,
            'expires_at' => $token->expires_at,
        ])->get(route('kaye.dashboard'));
        $response->assertStatus(200);

        // Make token expired
        $token->update(['expires_at' => now()->subMinute()]);

        // Check that auth middleware redirects expired session
        $response = $this->withSession([
            'role' => 'kaye',
            'kaye_token' => $token->token,
            'expires_at' => $token->expires_at,
        ])->get(route('kaye.dashboard'));

        $response->assertRedirect(route('kaye.login.expired', ['reason' => 'expired']));
        $this->assertNull(session('role'));

        // Revoke token
        $response = $this->withSession([
            'role' => 'admin',
            'admin_email' => $admin->email,
            'admin_name' => $admin->name
        ])->post(route('admin.settings.kaye-token.revoke'));
        $response->assertRedirect();
        $this->assertDatabaseCount('kaye_tokens', 0);
    }

    /**
     * 5. Test Mobile Request Approval (Admin)
     */
    public function test_mobile_admin_request_approval()
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@nupost.com',
            'password' => bcrypt('password'),
            'is_verified' => true,
        ]);
        $admin->role = 'admin';
        $admin->save();

        $requestor = User::create([
            'name' => 'Requestor User',
            'email' => 'req@nupost.com',
            'password' => bcrypt('password'),
            'is_verified' => true,
        ]);
        $requestor->role = 'requestor';
        $requestor->save();

        $postRequest = PostRequest::create([
            'title' => 'Mobile Request Approval Test',
            'requester' => 'Requestor User',
            'category' => 'Academics',
            'priority' => 'High',
            'status' => 'Pending Review',
            'description' => 'Test details',
            'platform' => 'Facebook',
        ]);

        $response = $this->postJson('/api/update_request_status.php', [
            'user_id' => $admin->id,
            'request_id' => $postRequest->id,
            'status' => 'Approved',
            'note' => 'Looks great, go ahead!'
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('post_requests', [
            'id' => $postRequest->id,
            'status' => 'Approved',
        ]);

        $this->assertDatabaseHas('request_activity', [
            'request_id' => $postRequest->id,
            'action' => 'Status changed from "Pending Review" to "Approved"'
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $requestor->id,
            'type' => 'approved',
        ]);

        $this->assertDatabaseHas('request_comments', [
            'request_id' => $postRequest->id,
            'sender_role' => 'admin',
            'message' => 'Looks great, go ahead!',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action_type' => 'post_request_approved',
        ]);
    }

    /**
     * 6. Test Mobile Request Editing (Requestor)
     */
    public function test_mobile_requestor_request_editing()
    {
        $requestor = User::create([
            'name' => 'Requestor User',
            'email' => 'req@nupost.com',
            'password' => bcrypt('password'),
            'is_verified' => true,
        ]);
        $requestor->role = 'requestor';
        $requestor->save();

        $postRequest = PostRequest::create([
            'title' => 'Original Title',
            'requester' => 'Requestor User',
            'category' => 'Academics',
            'priority' => 'High',
            'status' => 'Pending Review',
            'description' => 'Original details',
            'platform' => 'Facebook',
        ]);

        $response = $this->postJson('/api/update_request.php', [
            'user_id' => $requestor->id,
            'request_id' => $postRequest->id,
            'title' => 'Updated Title',
            'description' => 'Updated details',
            'category' => 'Academics',
            'priority' => 'Medium',
            'preferred_date' => '2026-08-10',
            'platforms' => ['Facebook', 'Instagram'],
            'caption' => 'New Caption',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('post_requests', [
            'id' => $postRequest->id,
            'title' => 'Updated Title',
            'description' => 'Updated details',
            'priority' => 'Medium',
            'platform' => 'Facebook,Instagram',
            'caption' => 'New Caption',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $requestor->id,
            'action_type' => 'post_request_edited',
        ]);

        // Attempt edit on Approved post - should fail
        $postRequest->update(['status' => 'Approved']);

        $response = $this->postJson('/api/update_request.php', [
            'user_id' => $requestor->id,
            'request_id' => $postRequest->id,
            'title' => 'Attempted Edit Approved Post',
            'description' => 'Updated details',
            'category' => 'Academics',
            'priority' => 'Medium',
        ]);

        $response->assertStatus(400);
        $this->assertDatabaseHas('post_requests', [
            'id' => $postRequest->id,
            'title' => 'Updated Title', // Unchanged
        ]);
    }
}
