<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\OtpCode;
use App\Models\OtpAttempt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OtpFlowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test User Registration generates OTP and redirects to verification page.
     */
    public function test_registration_flow_sends_otp_and_redirects()
    {
        Mail::shouldReceive('send')
            ->once()
            ->with([], [], \Mockery::type(\Closure::class));

        $response = $this->post(route('register.store'), [
            'name' => 'Alice Requestor',
            'email' => 'alice@nu-lipa.edu.ph',
            'password' => 'SecurePass123!',
            'confirm_password' => 'SecurePass123!',
        ]);

        $response->assertRedirect(route('otp.index'));

        // Verify User was created unverified
        $user = User::where('email', 'alice@nu-lipa.edu.ph')->first();
        $this->assertNotNull($user);
        $this->assertFalse($user->is_verified);

        // Verify OTP code was generated in DB
        $otp = OtpCode::where('user_id', $user->id)->first();
        $this->assertNotNull($otp);
        $this->assertEquals('alice@nu-lipa.edu.ph', $otp->email);
        $this->assertFalse($otp->is_used);

        // Verify session data was set
        $response->assertSessionHas('reg_user_id', $user->id);
        $response->assertSessionHas('reg_email', $user->email);
    }

    /**
     * Test verifying Registration with incorrect or correct OTP.
     */
    public function test_registration_otp_verification()
    {
        $user = User::create([
            'name' => 'Bob',
            'email' => 'bob@nu-lipa.edu.ph',
            'password' => Hash::make('Password123!'),
            'is_verified' => false,
        ]);

        $otpCode = OtpCode::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'otp_code' => '123456',
            'expires_at' => now()->addMinutes(10),
            'is_used' => false,
        ]);

        $this->withSession(['reg_user_id' => $user->id, 'reg_email' => $user->email]);

        // 1. Submit invalid code
        $response = $this->post(route('otp.store'), [
            'd1' => '9', 'd2' => '9', 'd3' => '9', 'd4' => '9', 'd5' => '9', 'd6' => '9'
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertFalse($user->fresh()->is_verified);
        $this->assertDatabaseHas('otp_attempts', ['user_id' => $user->id, 'success' => false]);

        // 2. Submit valid code
        $response = $this->post(route('otp.store'), [
            'd1' => '1', 'd2' => '2', 'd3' => '3', 'd4' => '4', 'd5' => '5', 'd6' => '6'
        ]);

        $response->assertRedirect(route('login'));
        $this->assertTrue($user->fresh()->is_verified);
        $this->assertTrue($otpCode->fresh()->is_used);
        $this->assertDatabaseHas('otp_attempts', ['user_id' => $user->id, 'success' => true]);
    }

    /**
     * Test rate limiting on OTP attempts.
     */
    public function test_registration_otp_rate_limiting()
    {
        $user = User::create([
            'name' => 'Charlie',
            'email' => 'charlie@nu-lipa.edu.ph',
            'password' => Hash::make('Password123!'),
            'is_verified' => false,
        ]);

        $this->withSession(['reg_user_id' => $user->id, 'reg_email' => $user->email]);

        // Create 5 failed attempts
        for ($i = 0; $i < 5; $i++) {
            OtpAttempt::create([
                'user_id' => $user->id,
                'success' => false,
                'attempted_at' => now(),
            ]);
        }

        // Try to submit again, should be blocked by rate limit
        $response = $this->post(route('otp.store'), [
            'd1' => '1', 'd2' => '2', 'd3' => '3', 'd4' => '4', 'd5' => '5', 'd6' => '6'
        ]);

        $response->assertSessionHas('error', 'Too many failed attempts. Please wait 15 minutes.');
    }

    /**
     * Test login of unverified user redirects to verify page with a new generated code.
     */
    public function test_unverified_user_login_redirection()
    {
        $user = User::create([
            'name' => 'Dave',
            'email' => 'dave@nu-lipa.edu.ph',
            'password' => Hash::make('Password123!'),
            'is_verified' => false,
        ]);
        $user->role = 'requestor';
        $user->save();

        Mail::shouldReceive('send')
            ->once()
            ->with([], [], \Mockery::type(\Closure::class));

        $response = $this->post(route('login.store'), [
            'email' => 'dave@nu-lipa.edu.ph',
            'password' => 'Password123!',
        ]);

        // Redirection should take them directly to the verification page
        $response->assertRedirect(route('otp.index'));
        $response->assertSessionHas('reg_user_id', $user->id);
        $response->assertSessionHas('reg_email', $user->email);
        $this->assertDatabaseHas('otp_codes', ['user_id' => $user->id]);
    }

    /**
     * Test Forgot Password anti-enumeration: both existent and non-existent emails redirect to verify page.
     */
    public function test_forgot_password_anti_enumeration()
    {
        // 1. Non-existent email (should not send mail, just redirect)
        Mail::shouldReceive('send')->never();

        $response = $this->post(route('password.send'), [
            'email' => 'nonexistent@nu-lipa.edu.ph'
        ]);

        $response->assertRedirect(route('password.verify'));
        $response->assertSessionHas('reset_email', 'nonexistent@nu-lipa.edu.ph');
        $this->assertNull(session('reset_user_id'));

        // Reset expectations for Mockery to test existent email
        \Mockery::close();

        // 2. Existent email (should send mail and redirect)
        Mail::shouldReceive('send')
            ->once()
            ->with([], [], \Mockery::type(\Closure::class));

        $user = User::create([
            'name' => 'Eve',
            'email' => 'eve@nu-lipa.edu.ph',
            'password' => Hash::make('Password123!'),
            'is_verified' => true,
        ]);

        $response = $this->post(route('password.send'), [
            'email' => 'eve@nu-lipa.edu.ph'
        ]);

        $response->assertRedirect(route('password.verify'));
        $response->assertSessionHas('reset_email', 'eve@nu-lipa.edu.ph');
        $response->assertSessionHas('reset_user_id', $user->id);
    }

    /**
     * Test Forgot Password verification, rate limiting, resending, and reset flow.
     */
    public function test_forgot_password_verify_and_reset_flow()
    {
        $user = User::create([
            'name' => 'Frank',
            'email' => 'frank@nu-lipa.edu.ph',
            'password' => Hash::make('OldPassword123!'),
            'is_verified' => true,
        ]);

        // Mock mail sending for initial forgot password request
        Mail::shouldReceive('send')
            ->once()
            ->with([], [], \Mockery::type(\Closure::class));

        // Request reset code
        $this->post(route('password.send'), ['email' => 'frank@nu-lipa.edu.ph']);
        $otpCode = OtpCode::where('user_id', $user->id)->first();
        $this->assertNotNull($otpCode);

        // Verify invalid code rate-limiting
        $this->withSession([
            'reset_email' => $user->email,
            'reset_user_id' => $user->id,
            'reset_name' => $user->name,
        ]);

        // 5 bad attempts
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('password.verify.store'), [
                'd1' => '9', 'd2' => '9', 'd3' => '9', 'd4' => '9', 'd5' => '9', 'd6' => '9'
            ])->assertSessionHas('error');
        }

        // 6th attempt should block immediately via rate limiting
        $response = $this->post(route('password.verify.store'), [
            'd1' => '1', 'd2' => '2', 'd3' => '3', 'd4' => '4', 'd5' => '5', 'd6' => '6'
        ]);
        $response->assertSessionHas('error', 'Too many failed attempts. Please wait 15 minutes.');

        // Clear attempts and reset Mockery expectation for resend
        OtpAttempt::truncate();
        \Mockery::close();

        Mail::shouldReceive('send')
            ->once()
            ->with([], [], \Mockery::type(\Closure::class));

        // 2. Resend code
        $response = $this->get(route('password.resend'));
        $response->assertRedirect(route('password.verify'));
        $response->assertSessionHas('success', 'A new code has been sent to your email.');

        $newOtp = OtpCode::where('user_id', $user->id)->orderByDesc('id')->first();
        $this->assertNotEquals($otpCode->otp_code, $newOtp->otp_code);

        // 3. Verify valid resent code
        $digits = str_split($newOtp->otp_code);
        $response = $this->post(route('password.verify.store'), [
            'd1' => $digits[0],
            'd2' => $digits[1],
            'd3' => $digits[2],
            'd4' => $digits[3],
            'd5' => $digits[4],
            'd6' => $digits[5],
        ]);
        $response->assertRedirect(route('password.reset'));
        $response->assertSessionHas('reset_verified', true);

        // 4. Reset password
        $response = $this->withSession(['reset_verified' => true])->post(route('password.reset.store'), [
            'new_password' => 'NewSecurePass99!',
            'confirm_password' => 'NewSecurePass99!',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertTrue(Hash::check('NewSecurePass99!', $user->fresh()->password));
    }
}
