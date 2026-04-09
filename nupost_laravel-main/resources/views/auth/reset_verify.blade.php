<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>NUPost – Verify Reset Code</title>
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
.card { position: relative; z-index: 1; background: white; width: 460px; border-radius: 12px; box-shadow: 0 10px 15px rgba(0,0,0,0.1); padding: 40px; display: flex; flex-direction: column; align-items: center; }
.logo { width: 140px; margin-bottom: 20px; }
.logo img { width: 100%; }
.icon-wrap { width: 60px; height: 60px; background: #fff0f0; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 14px; }
.icon-wrap svg { color: #ef4444; }
.title { font-size: 20px; font-weight: 700; margin-bottom: 6px; text-align: center; }
.subtitle { font-size: 13px; color: #6b7280; text-align: center; line-height: 1.6; margin-bottom: 20px; }
.timer-wrap { width: 100%; margin-bottom: 20px; }
.timer-label { display: flex; align-items: center; justify-content: space-between; font-size: 12px; color: #6b7280; margin-bottom: 6px; }
.timer-label span { font-weight: 600; color: var(--color-primary); }
.timer-bar-bg { height: 4px; background: #e5e7eb; border-radius: 4px; overflow: hidden; }
.timer-bar { height: 4px; background: #ef4444; border-radius: 4px; width: 100%; transition: width 1s linear; }
.otp-boxes { display: flex; gap: 10px; justify-content: center; margin-bottom: 16px; }
.otp-box { width: 52px; height: 60px; border: 2px solid #e5e7eb; border-radius: 10px; font-size: 26px; font-weight: 700; color: #ef4444; text-align: center; outline: none; font-family: monospace; transition: border-color .15s, box-shadow .15s; background: #fafafa; }
.otp-box:focus { border-color: #ef4444; background: white; box-shadow: 0 0 0 3px rgba(239,68,68,0.1); }
.otp-box.filled { border-color: #ef4444; background: #fff5f5; }
.btn { width: 100%; height: 44px; background: #ef4444; color: white; border: none; border-radius: 8px; font-size: 14px; font-weight: 700; cursor: pointer; font-family: var(--font); margin-bottom: 14px; }
.btn:hover { opacity: .9; }
.btn:disabled { opacity: .5; cursor: not-allowed; }
.alert { width: 100%; padding: 11px 14px; border-radius: 8px; font-size: 13px; margin-bottom: 16px; text-align: center; }
.alert--error   { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }
.alert--success { background: #dcfce7; color: #16a34a; border: 1px solid #bbf7d0; }
.resend-row { font-size: 12.5px; color: #6b7280; margin-bottom: 8px; }
.resend-row a { color: var(--color-primary); font-weight: 600; text-decoration: none; }
.resend-row a:hover { text-decoration: underline; }
.back-link { font-size: 12px; color: #6b7280; text-decoration: none; display: flex; align-items: center; gap: 4px; }
.back-link:hover { color: #111827; }
@media(max-width: 500px) { .card { width: 92%; padding: 28px 20px; } .otp-box { width: 42px; height: 52px; font-size: 22px; } }
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
        <div class="title">Enter Reset Code</div>
        <div class="subtitle">
            We sent a 6-digit code to<br>
            <strong>{{ session('reset_masked', 'your email') }}</strong>
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

        <form method="POST" action="{{ route('password.verify.store') }}" id="otp-form" style="width:100%;">
            @csrf
            <div class="otp-boxes">
                @for($i = 1; $i <= 6; $i++)
                <input class="otp-box" type="text" name="d{{ $i }}" id="d{{ $i }}"
                       maxlength="1" inputmode="numeric" pattern="[0-9]" autocomplete="off">
                @endfor
            </div>
            <button type="submit" class="btn" id="verify-btn" disabled>Verify Code</button>
        </form>

        <div class="resend-row">
            Didn't receive it? <a href="{{ route('password.forgot') }}">Request new code</a>
        </div>
        <a href="{{ route('login') }}" class="back-link">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
            Back to Login
        </a>
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
    timerBar.style.width = ((remaining / TOTAL) * 100) + '%';
    if (remaining <= 0) { clearInterval(countdown); timerText.textContent = 'Expired'; document.getElementById('verify-btn').disabled = true; }
}, 1000);
</script>
</body>
</html>