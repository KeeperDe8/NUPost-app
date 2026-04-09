<?php

namespace App\Http\Controllers\Requestor;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PostRequest;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    private function getUnreadCount(): int
    {
        return Notification::where('user_id', session('user_id'))->where('is_read', false)->count();
    }

    public function index()
    {
        $user_id   = session('user_id');
        $user_name = session('name');
        $user      = User::find($user_id);
        $requester = $user_name;

        $total    = PostRequest::where('requester', $requester)->count();
        $pending  = PostRequest::where('requester', $requester)->where('status', 'Pending Review')->count();
        $approved = PostRequest::where('requester', $requester)->where('status', 'Approved')->count();
        $posted   = PostRequest::where('requester', $requester)->where('status', 'Posted')->count();

        $unread_count  = $this->getUnreadCount();
        $member_since  = $user->created_at ? $user->created_at->format('F Y') : 'N/A';

        return view('requestor.profile', compact(
            'user', 'total', 'pending', 'approved', 'posted', 'unread_count', 'member_since'
        ));
    }

    public function edit()
    {
        $user         = User::find(session('user_id'));
        $unread_count = $this->getUnreadCount();
        return view('requestor.edit_profile', compact('user', 'unread_count'));
    }

    public function update(Request $request)
    {
        $user_id = session('user_id');
        $user    = User::find($user_id);

        $name         = trim($request->input('name', ''));
        $email        = trim($request->input('email', ''));
        $phone        = trim($request->input('phone', ''));
        $organization = trim($request->input('organization', ''));
        $department   = trim($request->input('department', ''));
        $bio          = trim($request->input('bio', ''));

        if (!$name || !$email) {
            return back()->with('error', 'Full name and email are required.');
        }

        $data = compact('name', 'email', 'phone', 'organization', 'department', 'bio');

        // Handle photo upload
        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            $file      = $request->file('photo');
            $allowed   = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($file->getMimeType(), $allowed)) {
                return back()->with('error', 'Invalid file type.');
            }
            if ($file->getSize() > 5 * 1024 * 1024) {
                return back()->with('error', 'Photo must be under 5MB.');
            }
            // Delete old photo
            if ($user->profile_photo && file_exists(public_path('uploads/' . $user->profile_photo))) {
                unlink(public_path('uploads/' . $user->profile_photo));
            }
            $filename = 'avatar_' . $user_id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads'), $filename);
            $data['profile_photo'] = $filename;
        }

        $user->update($data);
        session(['name' => $name]);

        return back()->with('success', 'Profile updated successfully!');
    }

    public function settings()
    {
        $user         = User::find(session('user_id'));
        $unread_count = $this->getUnreadCount();
        return view('requestor.account_settings', compact('user', 'unread_count'));
    }

    public function updatePassword(Request $request)
    {
        $user       = User::find(session('user_id'));
        $current_pw = trim($request->input('current_password', ''));
        $new_pw     = trim($request->input('new_password', ''));
        $confirm_pw = trim($request->input('confirm_password', ''));

        if (!$current_pw || !$new_pw || !$confirm_pw)
            return back()->with('error', 'Please fill in all password fields.');

        if (!Hash::check($current_pw, $user->password) && $user->password !== $current_pw)
            return back()->with('error', 'Current password is incorrect.');

        if ($new_pw !== $confirm_pw)
            return back()->with('error', 'New passwords do not match.');

        if (strlen($new_pw) < 8)
            return back()->with('error', 'Password must be at least 8 characters.');

        if (!preg_match('/[A-Z]/', $new_pw))
            return back()->with('error', 'Password must contain at least one uppercase letter.');

        if (!preg_match('/[0-9]/', $new_pw))
            return back()->with('error', 'Password must contain at least one number.');

        if (!preg_match('/[\W_]/', $new_pw))
            return back()->with('error', 'Password must contain at least one special character.');

        $user->update(['password' => Hash::make($new_pw)]);
        return back()->with('success', 'Password updated successfully!');
    }

    public function saveSettings(Request $request)
    {
        $user = User::find(session('user_id'));
        $user->update([
            'email_notif'    => $request->has('email_notif')    ? 1 : 0,
            'status_updates' => $request->has('status_updates') ? 1 : 0,
            'public_profile' => $request->has('public_profile') ? 1 : 0,
        ]);
        return back()->with('success', 'Settings saved successfully!');
    }
}