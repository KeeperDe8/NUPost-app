<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>NUPost – Register</title>
<link rel="icon" type="image/png" href="{{ asset('assets/clearlogo.png') }}">
<link rel="apple-touch-icon" href="{{ asset('assets/clearlogo.png') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@100;300;400;700&family=Arimo:wght@400&display=swap" rel="stylesheet">
<style>
:root {
    --color-primary: #002366;
    --color-white: #ffffff;
    --color-input-border: rgba(0,0,0,0.2);
    --font-inter: 'Inter', sans-serif;
    --radius-card: 8px;
    --radius-input: 5px;
    --radius-btn: 5px;
    --shadow-card: 0px 10px 15px rgba(0,0,0,0.1), 0px 4px 6px rgba(0,0,0,0.1);
    --card-width: 448px;
}
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html, body { height: 100%; font-family: var(--font-inter); }
.register { position: relative; width: 100%; min-height: 100vh; display: flex; align-items: center; justify-content: center; overflow: hidden; }
.register__bg { position: absolute; inset: 0; z-index: 0; }
.register__bg img { width: 100%; height: 100%; object-fit: cover; }
.register__card {
    position: relative; z-index: 1; background: white;
    width: var(--card-width); border-radius: var(--radius-card);
    box-shadow: var(--shadow-card); padding: 32px;
    display: flex; flex-direction: column; align-items: center;
}
.register__logo { width: 200px; margin-bottom: 24px; }
.register__logo img { width: 100%; }
.register__form { width: 100%; display: flex; flex-direction: column; gap: 20px; }
.register__field { display: flex; flex-direction: column; gap: 8px; }
.register__label { font-size: 12px; font-weight: 300; }
.register__input {
    width: 100%; height: 44px; border: 1px solid var(--color-input-border);
    border-radius: var(--radius-input); padding: 0 12px; font-size: 12px; outline: none; transition: border-color .15s;
}
.register__input:focus { border-color: var(--color-primary); }
.register__button {
    width: 100%; height: 40px; background: var(--color-primary);
    color: white; border: none; border-radius: var(--radius-btn);
    font-weight: 700; font-size: 12px; cursor: pointer; transition: opacity .15s;
}
.register__button:hover { opacity: .9; }
.message { font-size: 12px; text-align: center; padding: 8px 12px; border-radius: 6px; }
.error { color: #dc2626; background: #fee2e2; border: 1px solid #fecaca; }
.login-link { margin-top: 15px; font-size: 12px; text-align: center; }
.login-link a { color: var(--color-primary); text-decoration: none; font-weight: 600; }
.login-link a:hover { text-decoration: underline; }
.verify-notice {
    display: flex; align-items: flex-start; gap: 10px;
    background: #eff6ff; border: 1px solid #bfdbfe;
    border-radius: 8px; padding: 12px 14px; font-size: 12px; color: #1e40af; line-height: 1.5;
    width: 100%; margin-bottom: 20px;
}
.verify-notice svg { flex-shrink: 0; margin-top: 1px; }
@media(max-width: 768px) { .register__card { width: 92%; padding: 28px 24px; } }

/* Terms & Conditions Row & Modal */
.terms-container { display: flex; flex-direction: column; gap: 8px; width: 100%; }
.terms-row { display: flex; align-items: flex-start; gap: 8px; margin-top: -2px; }
.terms-checkbox-wrap { display: flex; align-items: flex-start; gap: 8px; cursor: pointer; user-select: none; }
.terms-checkbox-wrap input[type="checkbox"] { display: none; }
.terms-custom-box {
    width: 18px; height: 18px; border: 1.5px solid var(--color-input-border);
    border-radius: 4px; display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; background: white; transition: all .15s; margin-top: 1px;
}
.terms-checkbox-wrap input[type="checkbox"]:checked + .terms-custom-box {
    background: var(--color-primary); border-color: var(--color-primary);
}
.terms-custom-box svg { display: none; }
.terms-checkbox-wrap input[type="checkbox"]:checked + .terms-custom-box svg { display: block; }
.terms-custom-box.terms-error-highlight {
    border-color: #dc2626 !important;
    background-color: #fef2f2;
    box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.2);
    animation: shakeBox .35s ease;
}
@keyframes shakeBox {
    0%, 100% { transform: translateX(0); }
    20%, 60% { transform: translateX(-4px); }
    40%, 80% { transform: translateX(4px); }
}
.terms-error-alert {
    display: flex; align-items: center; gap: 6px;
    font-size: 11.5px; color: #dc2626; background: #fee2e2;
    border: 1px solid #fecaca; border-radius: 6px; padding: 7px 10px;
    animation: fadeInError .2s ease;
}
.terms-error-alert svg { flex-shrink: 0; }
@keyframes fadeInError {
    from { opacity: 0; transform: translateY(-4px); }
    to { opacity: 1; transform: translateY(0); }
}
.terms-text { font-size: 11.5px; color: #4b5563; line-height: 1.45; }
.terms-link { color: var(--color-primary); font-weight: 600; text-decoration: none; cursor: pointer; }
.terms-link:hover { text-decoration: underline; }

/* Modal */
.terms-modal {
    position: fixed; inset: 0; background: rgba(0, 15, 45, 0.65);
    backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px);
    z-index: 1000; display: none; align-items: center; justify-content: center;
    padding: 20px; animation: modalFadeIn .2s ease;
}
.terms-modal.open { display: flex; }
@keyframes modalFadeIn { from { opacity: 0; } to { opacity: 1; } }
.terms-modal__dialog {
    background: white; width: 100%; max-width: 580px; max-height: 85vh;
    border-radius: 14px; box-shadow: 0 20px 45px rgba(0,0,0,0.25);
    display: flex; flex-direction: column; overflow: hidden;
    animation: modalSlideUp .25s cubic-bezier(0.16, 1, 0.3, 1);
}
@keyframes modalSlideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
.terms-modal__header {
    background: #002366; color: white; padding: 18px 24px;
    display: flex; align-items: center; justify-content: space-between;
}
.terms-modal__header h3 { font-size: 16px; font-weight: 700; margin: 0; letter-spacing: -0.2px; }
.terms-modal__header p { font-size: 11.5px; opacity: 0.8; margin-top: 2px; }
.terms-modal__close {
    background: rgba(255,255,255,0.15); border: none; color: white;
    width: 28px; height: 28px; border-radius: 50%; font-size: 14px;
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    transition: background .15s;
}
.terms-modal__close:hover { background: rgba(255,255,255,0.3); }
.terms-modal__body {
    padding: 22px 24px; overflow-y: auto; font-size: 13px;
    color: #374151; line-height: 1.6; display: flex; flex-direction: column; gap: 14px;
}
.terms-modal__body::-webkit-scrollbar { width: 5px; }
.terms-modal__body::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
.terms-modal__body h4 { font-size: 13.5px; font-weight: 700; color: #002366; margin-bottom: 4px; }
.terms-modal__body p { margin-bottom: 0; }
.terms-modal__footer {
    padding: 14px 24px; background: #f8fafc; border-top: 1px solid #e2e8f0;
    display: flex; align-items: center; justify-content: flex-end; gap: 10px;
}
.terms-modal__btn {
    padding: 9px 18px; border-radius: 6px; font-size: 12.5px; font-weight: 600;
    cursor: pointer; border: none; transition: all .15s;
}
.terms-modal__btn--accept { background: #002366; color: white; }
.terms-modal__btn--accept:hover { background: #001844; }
.terms-modal__btn--cancel { background: #e2e8f0; color: #475569; }
.terms-modal__btn--cancel:hover { background: #cbd5e1; }
</style>
</head>
<body>
<main class="register">
    <div class="register__bg">
        <img src="/assets/nubg1.png" alt="NU Lipa Campus">
    </div>
    <section class="register__card">
        <div class="register__logo">
            <img src="/assets/nupostlogo.png" alt="NUPost Logo">
        </div>
        <div class="verify-notice">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                <polyline points="22,6 12,13 2,6"/>
            </svg>
            A 6-digit verification code will be sent to your email after registration.
        </div>
        <form class="register__form" method="POST" action="{{ route('register.store') }}">
            @csrf
            @if(session('error'))
                <div class="message error">{{ session('error') }}</div>
            @endif
            <div style="display:flex;gap:12px;">
                <div class="register__field" style="flex:1;">
                    <label class="register__label">FIRST NAME:</label>
                    <input class="register__input" type="text" name="first_name"
                           placeholder="Juan" required value="{{ old('first_name') }}">
                </div>
                <div class="register__field" style="flex:1;">
                    <label class="register__label">LAST NAME:</label>
                    <input class="register__input" type="text" name="last_name"
                           placeholder="Dela Cruz" required value="{{ old('last_name') }}">
                </div>
            </div>
            <div class="register__field">
                <label class="register__label">EMAIL ADDRESS:</label>
                <input class="register__input" type="email" name="email"
                       placeholder="your.email@nu-lipa.edu.ph" required value="{{ old('email') }}">
            </div>
            <div class="register__field">
                <label class="register__label">PASSWORD:</label>
                <div style="position:relative;">
                    <input class="register__input" type="password" name="password" id="reg-password" required
                           placeholder="Min. 8 chars, uppercase, number, symbol" style="padding-right: 40px;">
                    <button type="button" onclick="togglePassword('reg-password', this)" style="position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; color:#6b7280; display:flex; align-items:center; padding:0;">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:16px;height:16px;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
            </div>
            <div class="register__field">
                <label class="register__label">CONFIRM PASSWORD:</label>
                <div style="position:relative;">
                    <input class="register__input" type="password" name="confirm_password" id="reg-confirm" required placeholder="Re-enter password" style="padding-right: 40px;">
                    <button type="button" onclick="togglePassword('reg-confirm', this)" style="position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; color:#6b7280; display:flex; align-items:center; padding:0;">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:16px;height:16px;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
            </div>
            {{-- TERMS AND CONDITIONS --}}
            <div class="terms-container">
                <div class="terms-row">
                    <label class="terms-checkbox-wrap">
                        <input type="checkbox" name="terms" id="terms-checkbox" value="1" {{ old('terms') ? 'checked' : '' }}>
                        <span class="terms-custom-box">
                            <svg width="10" height="10" fill="none" stroke="white" stroke-width="2.5" viewBox="0 0 24 24">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                        </span>
                        <span class="terms-text">
                            I agree to the <a href="javascript:void(0)" class="terms-link" onclick="openTermsModal(event)">Terms and Conditions</a> and University Privacy Policy
                        </span>
                    </label>
                </div>
                <div id="terms-error-message" class="terms-error-alert" style="display: none;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <span>Please agree to the Terms and Conditions before proceeding.</span>
                </div>
            </div>

            <button class="register__button" type="submit" id="reg-submit-btn">CREATE ACCOUNT</button>
        </form>
        <div class="login-link">
            Already have an account? <a href="{{ route('login') }}">Login here</a>
        </div>
    </section>
</main>

{{-- TERMS & CONDITIONS MODAL --}}
<div class="terms-modal" id="terms-modal" onclick="closeTermsOnBackdrop(event)">
    <div class="terms-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="terms-modal-title">
        <div class="terms-modal__header">
            <div>
                <h3 id="terms-modal-title">Terms & Conditions</h3>
                <p>NUPost – NU Lipa Social Media Post Request Management System</p>
            </div>
            <button type="button" class="terms-modal__close" onclick="closeTermsModal()" aria-label="Close modal">✕</button>
        </div>
        <div class="terms-modal__body">
            <div>
                <h4>1. Acceptance of Terms</h4>
                <p>By creating an account on NUPost, you acknowledge and agree to comply with these Terms and Conditions as well as the official social media policies and student/employee handbook of National University – Lipa.</p>
            </div>
            <div>
                <h4>2. Eligibility & Account Responsibilities</h4>
                <p>NUPost is intended solely for authorized NU Lipa students, recognized student organizations, faculty, staff, and campus departments. You are responsible for maintaining the confidentiality of your login credentials and for all activities conducted under your account.</p>
            </div>
            <div>
                <h4>3. Post Submission & Content Guidelines</h4>
                <p>All submitted post requests, graphic assets, videos, and captions must adhere to university standards of integrity, respect, and accuracy. Content that is offensive, defamatory, unauthorized, copyrighted without permission, or politically partisan is strictly prohibited.</p>
            </div>
            <div>
                <h4>4. Review, Approval & Publication</h4>
                <p>Post submission does not guarantee immediate publication. All requests are subject to administrative review by the NU Lipa Marketing & Communications team. Administrators reserve the right to review, request modifications, schedule, or decline posts at their discretion.</p>
            </div>
            <div>
                <h4>5. Data Privacy & Account Security</h4>
                <p>Your personal information is handled in accordance with the Data Privacy Act of 2012 (Republic Act No. 10173). NUPost uses your official email address strictly for authentication, notifications, and post workflow communication.</p>
            </div>
        </div>
        <div class="terms-modal__footer">
            <button type="button" class="terms-modal__btn terms-modal__btn--cancel" onclick="closeTermsModal()">Decline</button>
            <button type="button" class="terms-modal__btn terms-modal__btn--accept" onclick="acceptTermsModal()">I Understand & Agree</button>
        </div>
    </div>
</div>

<script>
function togglePassword(id, btn) {
    const input = document.getElementById(id);
    const svg = btn.querySelector('svg');
    if (input.type === 'password') {
        input.type = 'text';
        svg.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>';
    } else {
        input.type = 'password';
        svg.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
    }
}

function openTermsModal(e) {
    if (e) e.preventDefault();
    document.getElementById('terms-modal').classList.add('open');
}

function closeTermsModal() {
    document.getElementById('terms-modal').classList.remove('open');
}

function closeTermsOnBackdrop(e) {
    if (e.target === document.getElementById('terms-modal')) {
        closeTermsModal();
    }
}

function acceptTermsModal() {
    const cb = document.getElementById('terms-checkbox');
    if (cb) {
        cb.checked = true;
        clearTermsError();
    }
    closeTermsModal();
}

const regForm = document.querySelector('.register__form');
const termsCheckbox = document.getElementById('terms-checkbox');
const termsBox = document.querySelector('.terms-custom-box');
const termsError = document.getElementById('terms-error-message');

function showTermsError() {
    if (termsError) termsError.style.display = 'flex';
    if (termsBox) {
        termsBox.classList.remove('terms-error-highlight');
        void termsBox.offsetWidth;
        termsBox.classList.add('terms-error-highlight');
    }
}

function clearTermsError() {
    if (termsError) termsError.style.display = 'none';
    if (termsBox) termsBox.classList.remove('terms-error-highlight');
}

if (termsCheckbox) {
    termsCheckbox.addEventListener('change', function() {
        if (this.checked) {
            clearTermsError();
        }
    });
}

if (regForm) {
    regForm.addEventListener('submit', function(e) {
        if (termsCheckbox && !termsCheckbox.checked) {
            e.preventDefault();
            showTermsError();
            termsCheckbox.closest('.terms-container')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            return false;
        }
    });
}

@if(session('error') && (str_contains(session('error'), 'Terms') || str_contains(session('error'), 'terms')))
document.addEventListener('DOMContentLoaded', function() {
    showTermsError();
});
@endif
</script>
</body>
</html>