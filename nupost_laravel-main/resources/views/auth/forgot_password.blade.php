<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>NUPost – Forgot Password</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
<style>
:root { --color-primary: #002366; --font: 'Inter', sans-serif; }
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html, body { height: 100%; font-family: var(--font); }
.page { position: relative; min-height: 100vh; display: flex; align-items: center; justify-content: center; overflow: hidden; }
.page__bg { position: absolute; inset: 0; z-index: 0; }
.page__bg img { width: 100%; height: 100%; object-fit: cover; }
.card { position: relative; z-index: 1; background: white; width: 440px; border-radius: 12px; box-shadow: 0 10px 15px rgba(0,0,0,0.1); padding: 36px; display: flex; flex-direction: column; align-items: center; }
.logo { width: 180px; margin-bottom: 24px; }
.logo img { width: 100%; }
.icon-wrap { width: 60px; height: 60px; background: #eff6ff; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 16px; }
.icon-wrap svg { color: var(--color-primary); }
.title { font-size: 20px; font-weight: 700; margin-bottom: 6px; text-align: center; }
.subtitle { font-size: 13px; color: #6b7280; text-align: center; line-height: 1.6; margin-bottom: 24px; }
.form { width: 100%; }
.field { margin-bottom: 16px; }
.field label { display: block; font-size: 12px; font-weight: 500; color: #6b7280; margin-bottom: 6px; }
.field input { width: 100%; height: 44px; border: 1px solid #e5e7eb; border-radius: 8px; padding: 0 12px; font-size: 13px; font-family: var(--font); outline: none; transition: border-color .15s; }
.field input:focus { border-color: var(--color-primary); }
.field input::placeholder { color: #d1d5db; }
.btn { width: 100%; height: 44px; background: var(--color-primary); color: white; border: none; border-radius: 8px; font-size: 14px; font-weight: 700; cursor: pointer; font-family: var(--font); }
.btn:hover { opacity: .9; }
.alert { width: 100%; padding: 11px 14px; border-radius: 8px; font-size: 13px; margin-bottom: 16px; text-align: center; }
.alert--error   { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }
.alert--success { background: #dcfce7; color: #16a34a; border: 1px solid #bbf7d0; }
.back-link { margin-top: 16px; font-size: 12.5px; color: #6b7280; text-decoration: none; display: flex; align-items: center; gap: 4px; }
.back-link:hover { color: #111827; }
@media(max-width: 500px) { .card { width: 92%; padding: 28px 20px; } }
</style>
</head>
<body>
<div class="page">
    <div class="page__bg"><img src="/assets/nubg1.png" alt="NU Lipa"></div>
    <div class="card">
        <div class="logo"><img src="/assets/nupostlogo.png" alt="NUPost" onerror="this.style.display='none';"></div>
        <div class="icon-wrap">
            <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        </div>
        <div class="title">Forgot Password?</div>
        <div class="subtitle">Enter your registered email and we'll send you a 6-digit reset code.</div>

        @if(session('error'))
            <div class="alert alert--error">{{ session('error') }}</div>
        @endif
        @if(session('success'))
            <div class="alert alert--success">{{ session('success') }}</div>
        @endif

        <form class="form" method="POST" action="{{ route('password.send') }}">
            @csrf
            <div class="field">
                <label>EMAIL ADDRESS</label>
                <input type="email" name="email" placeholder="your.email@nu-lipa.edu.ph" required value="{{ old('email') }}">
            </div>
            <button type="submit" class="btn">Send Reset Code</button>
        </form>
        <a href="{{ route('login') }}" class="back-link">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
            Back to Login
        </a>
    </div>
</div>
</body>
</html>