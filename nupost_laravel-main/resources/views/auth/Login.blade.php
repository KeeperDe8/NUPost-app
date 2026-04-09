<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>NUPost – Login</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@100;300;400;600;700&family=Arimo:wght@400&display=swap" rel="stylesheet">
<style>
:root {
    --color-primary: #002366;
    --color-primary-light: #003a8c;
    --color-white: #ffffff;
    --color-input-border: rgba(0,0,0,0.2);
    --font-inter: 'Inter', sans-serif;
    --font-arimo: 'Arimo', sans-serif;
    --radius-card: 8px;
    --radius-input: 5px;
    --radius-btn: 5px;
    --shadow-card: 0px 10px 15px rgba(0,0,0,0.1), 0px 4px 6px rgba(0,0,0,0.1);
    --card-width: 448px;
}
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html, body { height: 100%; font-family: var(--font-inter); }
.login { position: relative; width: 100%; min-height: 100vh; display: flex; align-items: center; justify-content: center; overflow: hidden; }
.login__bg { position: absolute; inset: 0; z-index: 0; }
.login__bg img { width: 100%; height: 100%; object-fit: cover; }
.login__card {
    position: relative; z-index: 1; background: white;
    width: var(--card-width); border-radius: var(--radius-card);
    box-shadow: var(--shadow-card); padding: 32px;
    display: flex; flex-direction: column; align-items: center;
}
.login__logo { width: 200px; margin-bottom: 24px; }
.login__logo img { width: 100%; }
.login__form { width: 100%; display: flex; flex-direction: column; gap: 20px; }
.login__field { display: flex; flex-direction: column; gap: 8px; }
.login__label { font-size: 12px; font-weight: 300; }
.login__input {
    width: 100%; height: 44px; border: 1px solid var(--color-input-border);
    border-radius: var(--radius-input); padding: 0 12px; font-size: 12px; outline: none; transition: border-color .15s;
}
.login__input:focus { border-color: var(--color-primary); }
.login__input--password { font-family: var(--font-arimo); letter-spacing: 2px; }
.remember-row { display: flex; align-items: center; gap: 8px; }
.remember-row input[type="checkbox"] { display: none; }
.remember-box {
    width: 18px; height: 18px; border: 1.5px solid var(--color-input-border);
    border-radius: 4px; cursor: pointer; display: flex; align-items: center;
    justify-content: center; flex-shrink: 0; transition: all .15s; background: white;
}
.remember-box.checked { background: var(--color-primary); border-color: var(--color-primary); }
.remember-box svg { display: none; }
.remember-box.checked svg { display: block; }
.remember-label { font-size: 12px; color: #374151; cursor: pointer; user-select: none; }
.login__button {
    width: 100%; height: 39px; background: var(--color-primary);
    color: white; border: none; border-radius: var(--radius-btn);
    font-weight: 700; font-size: 12px; cursor: pointer; transition: opacity .15s;
}
.login__button:hover { opacity: .9; }
.login__error {
    color: #dc2626; font-size: 12px; text-align: center;
    background: #fee2e2; border: 1px solid #fecaca; border-radius: 6px; padding: 8px 12px;
}
.login__success {
    color: #16a34a; font-size: 12px; text-align: center;
    background: #dcfce7; border: 1px solid #bbf7d0; border-radius: 6px; padding: 8px 12px;
}
.signup-link { margin-top: 15px; font-size: 12px; text-align: center; }
.signup-link a { color: var(--color-primary); font-weight: 600; text-decoration: none; }
.signup-link a:hover { text-decoration: underline; }
.secure-badge { display: flex; align-items: center; gap: 6px; font-size: 11px; color: #6b7280; margin-top: 16px; }
.secure-badge svg { color: #10b981; }
@media(max-width: 768px) { .login__card { width: 92%; padding: 28px 24px; } }
</style>
</head>
<body>
<main class="login">
    <div class="login__bg">
        <img src="/assets/nubg1.png" alt="NU Lipa Campus">
    </div>
    <section class="login__card">
        <div class="login__logo">
            <img src="/assets/nupostlogo.png" alt="NUPost Logo">
        </div>
        <form class="login__form" method="POST" action="{{ route('login.store') }}">
            @csrf
            @if(session('error'))
                <div class="login__error">{{ session('error') }}</div>
            @endif
            @if(session('success') || $success ?? false)
                <div class="login__success">{{ session('success') ?? $success }}</div>
            @endif
            <div class="login__field">
                <label class="login__label">EMAIL ADDRESS:</label>
                <input class="login__input" type="email" name="email"
                       placeholder="your.email@nu-lipa.edu.ph" required
                       value="{{ old('email') }}">
            </div>
            <div class="login__field">
                <label class="login__label">PASSWORD:</label>
                <input class="login__input login__input--password" type="password" name="password" placeholder="••••••••" required>
            </div>
            <div class="remember-row">
                <div class="remember-box" id="remember-box" onclick="toggleRemember()">
                    <svg width="11" height="11" fill="none" stroke="white" stroke-width="2.5" viewBox="0 0 24 24">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                </div>
                <input type="checkbox" name="remember_me" id="remember-input">
                <span class="remember-label" onclick="toggleRemember()">Remember this device for 7 days</span>
            </div>
            <button class="login__button" type="submit">LOGIN</button>
        </form>
        <div class="signup-link">
            Don't have an account? <a href="{{ route('register') }}">Sign Up</a>
        </div>
        <div style="margin-top:8px;font-size:12px;text-align:center;">
    <a href="{{ route('password.forgot') }}" style="color:#6b7280;text-decoration:none;">Forgot Password?</a>
</div>
        <div class="secure-badge">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
            </svg>
            Email Verified Accounts Only
        </div>
    </section>
</main>
<script>
function toggleRemember() {
    const box   = document.getElementById('remember-box');
    const input = document.getElementById('remember-input');
    box.classList.toggle('checked');
    input.checked = box.classList.contains('checked');
}
</script>
</body>
</html>