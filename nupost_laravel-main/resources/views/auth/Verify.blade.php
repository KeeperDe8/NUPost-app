<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>NUPost – Verify Your Email</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
<style>
:root {
    --color-primary: #002366;
    --color-primary-light: #003a8c;
    --color-bg: #f5f6fa;
    --color-border: #e5e7eb;
    --color-text: #111827;
    --color-muted: #6b7280;
    --font: 'Inter', sans-serif;
    --shadow-card: 0 10px 15px rgba(0,0,0,0.1), 0 4px 6px rgba(0,0,0,0.08);
}
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html, body { height: 100%; font-family: var(--font); }
.page { position: relative; min-height: 100vh; display: flex; align-items: center; justify-content: center; overflow: hidden; }
.page__bg { position: absolute; inset: 0; z-index: 0; }
.page__bg img { width: 100%; height: 100%; object-fit: cover; }
.card {
    position: relative; z-index: 1; background: white;
    width: 460px; border-radius: 12px; box-shadow: var(--shadow-card);
    padding: 40px 40px 32px; display: flex; flex-direction: column; align-items: center;
}
.logo { width: 140px; margin-bottom: 24px; }
.logo img { width: 100%; }
.shield-wrap { width: 64px; height: 64px; background: #eff6ff; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 16px; }
.shield-wrap svg { color: var(--color-primary); }
.title { font-size: 20px; font-weight: 700; color: var(--color-text); margin-bottom: 6px; text-align: center; }
.subtitle { font-size: 13px; color: var(--color-muted); text-align: center; line-height: 1.6; margin-bottom: 20px; }
.subtitle strong { color: var(--color-text); }
.timer-wrap { width: 100%; margin-bottom: 20px; }
.timer-label { display: flex; align-items: center; justify-content: space-between; font-size: 12px; color: var(--color-muted); margin-bottom: 6px; }
.timer-label span { font-weight: 600; color: var(--color-primary); }
.timer-bar-bg { height: 4px; background: #e5e7eb; border-radius: 4px; overflow: hidden; }
.timer-bar { height: 4px; background: var(--color-primary); border-radius: 4px; width: 100%; transition: width 1s linear; }
.timer-bar.warning { background: #f59e0b; }
.timer-bar.danger  { background: #ef4444; }
.otp-form { width: 100%; }
.otp-boxes { display: flex; gap: 10px; justify-content: center; margin-bottom: 16px; }
.otp-box {
    width: 52px; height: 60px; border: 2px solid var(--color-border); border-radius: 10px;
    font-size: 26px; font-weight: 700; color: var(--color-primary);
    text-align: center; outline: none; font-family: monospace;
    transition: border-color .15s, box-shadow .15s; background: #fafafa;
    caret-color: var(--color-primary);
}
.otp-box:focus { border-color: var(--color-primary); background: white; box-shadow: 0 0 0 3px rgba(0,35,102,0.1); }
.otp-box.filled { border-color: var(--color-primary); background: #f0f4ff; }
.otp-box.error  { border-color: #ef4444; background: #fff5f5; }
.btn-verify {
    width: 100%; height: 44px; background: var(--color-primary); color: white;
    border: none; border-radius: 8px; font-size: 14px; font-weight: 700;
    cursor: pointer; font-family: var(--font); transition: opacity .15s; margin-bottom: 14px;
}
.btn-verify:hover { opacity: .9; }
.btn-verify:disabled { opacity: .5; cursor: not-allowed; }
.alert { width: 100%; padding: 11px 14px; border-radius: 8px; font-size: 13px; margin-bottom: 16px; text-align: center; }
.alert--error   { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }
.alert--success { background: #dcfce7; color: #16a34a; border: 1px solid #bbf7d0; }
.links { display: flex; flex-direction: column; align-items: center; gap: 8px; width: 100%; }
.resend-row { font-size: 12.5px; color: var(--color-muted); }
.resend-row a { color: var(--color-primary); font-weight: 600; text-decoration: none; }
.resend-row a:hover { text-decoration: underline; }
.back-link { font-size: 12px; color: var(--color-muted); text-decoration: none; display: flex; align-items: center; gap: 4px; }
.back-link:hover { color: var(--color-text); }
.secure-badge { display: flex; align-items: center; gap: 6px; font-size: 11px; color: #9ca3af; margin-top: 18px; }
.secure-badge svg { color: #10b981; }
@media(max-width: 500px) { .card { width: 92%; padding: 32px 20px 24px; } .otp-box { width: 42px; height: 52px; font-size: 22px; } }
</style>
</head>
<body>
<div class="page">
    <div class="page__bg"><img src="/assets/nubg1.png" alt="NU Lipa"></div>
    <div class="card">
        <div class="logo"><img src="/assets/nupostlogo.png" alt="NUPost" onerror="this.style.display='none';"></div>
        <div class="shield-wrap">
            <svg width="30" height="30" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                <polyline points="22,6 12,13 2,6"/>
            </svg>
        </div>
        <div class="title">Verify Your Email</div>
        <div class="subtitle">
            @if(session('otp_sent'))
                We sent a 6-digit code to<br>
                <strong>{{ session('masked_email') }}</strong>
            @else
                Please check your email for your verification code.<br>
                If you didn't receive it, use the Resend button below.
            @endif
        </div>
        <div class="timer-wrap">
            <div class="timer-label">
                <span>Code expires in:</span>
                <span id="timer-text">10:00</span>
            </div>
            <div class="timer-bar-bg"><div class="timer-bar" id="timer-bar"></div></div>
        </div>
        @if(session('error'))
            <div class="alert alert--error">{{ session('error') }}</div>
        @endif
        @if(session('success'))
            <div class="alert alert--success">{{ session('success') }}</div>
        @endif
        <form class="otp-form" method="POST" action="{{ route('otp.store') }}" id="otp-form">
            @csrf
            <div class="otp-boxes">
                @for($i = 1; $i <= 6; $i++)
                <input class="otp-box" type="text" name="d{{ $i }}"
                       id="d{{ $i }}" maxlength="1" inputmode="numeric"
                       pattern="[0-9]" autocomplete="off">
                @endfor
            </div>
            <button type="submit" class="btn-verify" id="verify-btn" disabled>Verify Email</button>
        </form>
        <div class="links">
            <div class="resend-row">
                Didn't receive the code? <a href="{{ route('otp.resend') }}">Resend Code</a>
            </div>
            <a href="{{ route('register') }}" class="back-link">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
                Back to Registration
            </a>
        </div>
        <div class="secure-badge">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            Email Verification Required
        </div>
    </div>
</div>
<script>
const boxes = document.querySelectorAll('.otp-box');
boxes.forEach((box, i) => {
    box.addEventListener('input', (e) => {
        const val = e.target.value.replace(/\D/g, '');
        e.target.value = val ? val[val.length - 1] : '';
        if (val && i < boxes.length - 1) boxes[i + 1].focus();
        box.classList.toggle('filled', !!e.target.value);
        checkAllFilled();
    });
    box.addEventListener('keydown', (e) => {
        if (e.key === 'Backspace' && !box.value && i > 0) { boxes[i - 1].focus(); boxes[i - 1].value = ''; boxes[i - 1].classList.remove('filled'); }
        if (e.key === 'ArrowLeft'  && i > 0)                boxes[i - 1].focus();
        if (e.key === 'ArrowRight' && i < boxes.length - 1) boxes[i + 1].focus();
    });
    box.addEventListener('paste', (e) => {
        e.preventDefault();
        const pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '');
        if (pasted.length === 6) { boxes.forEach((b, idx) => { b.value = pasted[idx] || ''; b.classList.toggle('filled', !!b.value); }); boxes[5].focus(); checkAllFilled(); }
    });
});
function checkAllFilled() { document.getElementById('verify-btn').disabled = !Array.from(boxes).every(b => b.value !== ''); }
boxes[0].focus();
const TOTAL = 10 * 60;
let remaining = TOTAL;
const timerText = document.getElementById('timer-text');
const timerBar  = document.getElementById('timer-bar');
const countdown = setInterval(() => {
    remaining--;
    const mins = Math.floor(remaining / 60);
    const secs = remaining % 60;
    timerText.textContent = `${mins}:${secs.toString().padStart(2,'0')}`;
    const pct = (remaining / TOTAL) * 100;
    timerBar.style.width = pct + '%';
    if (pct <= 20)      { timerBar.className = 'timer-bar danger';  timerText.style.color = '#ef4444'; }
    else if (pct <= 40) { timerBar.className = 'timer-bar warning'; timerText.style.color = '#d97706'; }
    if (remaining <= 0) {
        clearInterval(countdown);
        timerText.textContent = 'Expired';
        document.getElementById('verify-btn').disabled = true;
        document.getElementById('verify-btn').textContent = 'Code Expired — Click Resend';
        boxes.forEach(b => { b.disabled = true; b.classList.add('error'); });
    }
}, 1000);
</script>
</body>
</html>