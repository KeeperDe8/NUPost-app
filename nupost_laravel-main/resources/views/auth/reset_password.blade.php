<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>NUPost – Reset Password</title>
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
.logo { width: 160px; margin-bottom: 20px; }
.logo img { width: 100%; }
.icon-wrap { width: 60px; height: 60px; background: #ecfdf5; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 14px; }
.icon-wrap svg { color: #10b981; }
.title { font-size: 20px; font-weight: 700; margin-bottom: 6px; text-align: center; }
.subtitle { font-size: 13px; color: #6b7280; text-align: center; line-height: 1.6; margin-bottom: 24px; }
.form { width: 100%; }
.field { margin-bottom: 14px; }
.field label { display: block; font-size: 12px; font-weight: 500; color: #6b7280; margin-bottom: 6px; }
.field input { width: 100%; height: 44px; border: 1px solid #e5e7eb; border-radius: 8px; padding: 0 12px; font-size: 13px; font-family: var(--font); outline: none; transition: border-color .15s; }
.field input:focus { border-color: var(--color-primary); }
.field input::placeholder { color: #d1d5db; }
.password-hint { font-size: 11px; color: #9ca3af; margin-top: 4px; }
.btn { width: 100%; height: 44px; background: var(--color-primary); color: white; border: none; border-radius: 8px; font-size: 14px; font-weight: 700; cursor: pointer; font-family: var(--font); margin-top: 8px; }
.btn:hover { opacity: .9; }
.alert { width: 100%; padding: 11px 14px; border-radius: 8px; font-size: 13px; margin-bottom: 16px; text-align: center; }
.alert--error { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }
/* Password strength */
.strength-bar { height: 4px; background: #e5e7eb; border-radius: 4px; margin-top: 6px; overflow: hidden; }
.strength-fill { height: 100%; border-radius: 4px; transition: width .3s, background .3s; width: 0; }
.strength-text { font-size: 11px; margin-top: 4px; font-weight: 500; }
@media(max-width: 500px) { .card { width: 92%; padding: 28px 20px; } }
</style>
</head>
<body>
<div class="page">
    <div class="page__bg"><img src="/assets/nubg1.png" alt="NU Lipa"></div>
    <div class="card">
        <div class="logo"><img src="/assets/nupostlogo.png" alt="NUPost" onerror="this.style.display='none';"></div>
        <div class="icon-wrap">
            <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        </div>
        <div class="title">Set New Password</div>
        <div class="subtitle">Create a strong password for your account.</div>

        @if(session('error'))
            <div class="alert alert--error">{{ session('error') }}</div>
        @endif

        <form class="form" method="POST" action="{{ route('password.reset.store') }}">
            @csrf
            <div class="field">
                <label>NEW PASSWORD</label>
                <input type="password" name="new_password" id="new_password"
                       placeholder="Min. 8 chars, uppercase, number, symbol"
                       autocomplete="new-password" oninput="checkStrength(this.value)">
                <div class="strength-bar"><div class="strength-fill" id="strength-fill"></div></div>
                <div class="strength-text" id="strength-text"></div>
                <div class="password-hint">Must contain: uppercase, number, special character</div>
            </div>
            <div class="field">
                <label>CONFIRM NEW PASSWORD</label>
                <input type="password" name="confirm_password" placeholder="Re-enter new password" autocomplete="new-password">
            </div>
            <button type="submit" class="btn">Reset Password</button>
        </form>
    </div>
</div>
<script>
function checkStrength(val) {
    let score = 0;
    if (val.length >= 8)          score++;
    if (/[A-Z]/.test(val))        score++;
    if (/[0-9]/.test(val))        score++;
    if (/[\W_]/.test(val))        score++;

    const fill  = document.getElementById('strength-fill');
    const text  = document.getElementById('strength-text');
    const pcts  = ['0%', '25%', '50%', '75%', '100%'];
    const colors = ['', '#ef4444', '#f59e0b', '#3b82f6', '#10b981'];
    const labels = ['', 'Weak', 'Fair', 'Good', 'Strong'];

    fill.style.width      = pcts[score];
    fill.style.background = colors[score];
    text.textContent      = score > 0 ? labels[score] : '';
    text.style.color      = colors[score];
}
</script>
</body>
</html>