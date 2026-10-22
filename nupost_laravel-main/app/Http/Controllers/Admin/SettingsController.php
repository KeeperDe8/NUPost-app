<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Setting;
use App\Models\KayeToken;
use App\Models\User;
use App\Models\Notification;

class SettingsController extends Controller
{
    public function index()
    {
        $kayeToken = KayeToken::orderByDesc('id')->first();

        // Retrieve AI Keys: Database first, fallback to .env/config
        $geminiApiKey = Setting::get('gemini_api_key', config('services.gemini.key') ?: env('GEMINI_API_KEY'));
        $groqApiKey   = Setting::get('groq_api_key', config('services.groq.key') ?: env('GROQ_API_KEY'));

        // Determine storage source for transparent status indication
        $hasDbGemini = false;
        $hasDbGroq   = false;
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                $hasDbGemini = Setting::where('key', 'gemini_api_key')->whereNotNull('value')->where('value', '!=', '')->exists();
                $hasDbGroq   = Setting::where('key', 'groq_api_key')->whereNotNull('value')->where('value', '!=', '')->exists();
            }
        } catch (\Throwable $e) {}

        $geminiSource = $hasDbGemini ? 'database' : (env('GEMINI_API_KEY') ? 'env' : 'none');
        $groqSource   = $hasDbGroq   ? 'database' : (env('GROQ_API_KEY')   ? 'env' : 'none');

        return view('admin.settings', compact('kayeToken', 'geminiApiKey', 'groqApiKey', 'geminiSource', 'groqSource'));
    }

    public function updateAiKeys(Request $request)
    {
        $request->validate([
            'gemini_api_key' => 'nullable|string',
            'groq_api_key'   => 'nullable|string',
        ]);

        $geminiKey = trim((string) $request->input('gemini_api_key', ''));
        $groqKey   = trim((string) $request->input('groq_api_key', ''));

        $oldGemini = (string) Setting::get('gemini_api_key', '');
        $oldGroq   = (string) Setting::get('groq_api_key', '');

        $adminName = session('admin_name') ?: (session('admin_email') ?: 'Administrator');
        $changedKeys = [];

        if ($geminiKey !== '' && $geminiKey !== $oldGemini) {
            Setting::set('gemini_api_key', $geminiKey);
            $changedKeys[] = 'Google Gemini (Caption Generation)';
        }
        if ($groqKey !== '' && $groqKey !== $oldGroq) {
            Setting::set('groq_api_key', $groqKey);
            $changedKeys[] = 'Groq (Bulldog AI Assistant)';
        }

        if (!empty($changedKeys)) {
            $keyNames = implode(' and ', $changedKeys);
            $title = 'AI API Key Updated';
            $message = "The API key for {$keyNames} was updated by {$adminName}.";

            // Automatically notify admins
            $admins = User::where('role', 'admin')->get();
            if ($admins->isNotEmpty()) {
                foreach ($admins as $adm) {
                    Notification::create([
                        'user_id' => $adm->id,
                        'title'   => $title,
                        'message' => $message,
                        'type'    => 'api_key',
                        'is_read' => false,
                    ]);
                }
            } else {
                Notification::create([
                    'user_id' => 0,
                    'title'   => $title,
                    'message' => $message,
                    'type'    => 'api_key',
                    'is_read' => false,
                ]);
            }

            \App\Models\AuditLog::record('api_key_updated');
        }

        return back()->with('success', 'AI API configuration saved successfully to database!');
    }

    public function testGeminiKey(Request $request)
    {
        $key = trim((string) $request->input('gemini_api_key'));
        if (!$key) {
            $key = Setting::get('gemini_api_key', config('services.gemini.key') ?: env('GEMINI_API_KEY'));
        }

        if (!$key) {
            return response()->json([
                'success' => false,
                'message' => 'No Gemini API key provided to test.'
            ], 400);
        }

        try {
            $response = Http::withoutVerifying()
                ->timeout(12)
                ->get("https://generativelanguage.googleapis.com/v1beta/models?key={$key}");

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Connection successful! Gemini API Key is active and authorized.'
                ]);
            }

            $err = $response->json()['error']['message'] ?? 'Invalid or unauthorized Gemini API key.';
            return response()->json([
                'success' => false,
                'message' => 'Google Gemini Error: ' . $err
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function testGroqKey(Request $request)
    {
        $key = trim((string) $request->input('groq_api_key'));
        if (!$key) {
            $key = Setting::get('groq_api_key', config('services.groq.key') ?: env('GROQ_API_KEY'));
        }

        if (!$key) {
            return response()->json([
                'success' => false,
                'message' => 'No Groq API key provided to test.'
            ], 400);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $key,
                'Content-Type'  => 'application/json',
            ])
            ->timeout(12)
            ->withoutVerifying()
            ->get('https://api.groq.com/openai/v1/models');

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Connection successful! Groq API Key is active.'
                ]);
            }

            $err = $response->json()['error']['message'] ?? 'Invalid Groq API key.';
            return response()->json([
                'success' => false,
                'message' => 'Groq API Error: ' . $err
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function generateKayeToken()
    {
        KayeToken::query()->delete();

        $tokenStr = bin2hex(random_bytes(32));
        $expiresAt = now()->addDays(60);

        KayeToken::create([
            'token' => $tokenStr,
            'expires_at' => $expiresAt,
        ]);

        return back()->with('success', 'New access token generated successfully.');
    }

    public function revokeKayeToken()
    {
        KayeToken::query()->delete();
        return back()->with('success', 'Access token revoked successfully.');
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $email = session('admin_email');
        if ($email) {
            $user = User::where('email', $email)->first();
            if ($user) {
                $user->name = $request->name;
                $user->save();
                session(['admin_name' => $user->name]);
            }
        }

        return back()->with('success', 'Profile updated successfully.');
    }
}