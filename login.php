<?php
require_once 'config/config.php';

// Jika sudah login, redirect ke dashboard
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$force_theme      = 'light';
$page_title       = 'Login';
$page_description = 'Login ke ' . APP_NAME . ' - Platform pembelajaran coding interaktif';
$page_css         = ['components/button.css', 'components/card.css', 'components/form.css', 'components/alert.css', 'components/badge.css', 'components/auth.css'];
$body_class       = getThemeClass();

$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';
if (isset($_SESSION['error_message'])) {
    unset($_SESSION['error_message']);
}

if ($_POST) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error_message = 'Sesi tidak valid (CSRF Token Error). Silakan refresh halaman dan coba lagi.';
    } else {
        $email = sanitizeInput($_POST['email']);
        $password = $_POST['password'];

        if (!empty($email) && !empty($password)) {
            $database = new Database();
            $db = $database->getConnection();

            $user = new User($db);

            if ($user->login($email, $password)) {
                $_SESSION['user_id'] = $user->id;
                $_SESSION['username'] = $user->username;
                $_SESSION['nama_lengkap'] = $user->nama_lengkap;
                $_SESSION['user_role'] = $user->role;
                $_SESSION['email'] = $user->email;

                header('Location: dashboard.php');
                exit();
            } else {
                $error_message = 'Email atau kata sandi salah!';
            }
        } else {
            $error_message = 'Email dan kata sandi harus diisi!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php require_once 'includes/head.php'; ?>
    <meta name="robots" content="noindex, nofollow">

    <!-- SVG Symbol Definitions -->
    <svg style="display: none;" aria-hidden="true">
        <defs>
            <linearGradient id="brandGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stop-color="#6366F1"/>
                <stop offset="100%" stop-color="#10B981"/>
            </linearGradient>
        </defs>
        <symbol id="brandLogo" viewBox="0 0 100 100">
            <path d="M 25 20 L 25 75 Q 25 80 30 80 L 35 80 Q 40 80 40 75 L 40 20 Q 40 15 35 15 L 30 15 Q 25 15 25 20 Z" fill="url(#brandGrad)"/>
            <path d="M 40 20 Q 40 15 45 15 L 60 15 Q 70 15 70 25 L 70 35 Q 70 45 60 45 L 45 45 Q 40 45 40 40 L 40 30 Q 40 25 45 25 L 60 25 Q 65 25 65 30 L 65 35 Q 65 40 60 40 L 45 40 Q 40 40 40 35 Z" fill="url(#brandGrad)"/>
        </symbol>
    </svg>
</head>
<body class="<?php echo $body_class; ?> auth-body">
    <div class="auth-wrapper">
        <!-- Decorative circles -->
        <div class="auth-deco-circle auth-deco-circle--tl"></div>
        <div class="auth-deco-circle auth-deco-circle--br"></div>

        <!-- LEFT: Login Form (white card) -->
        <div class="auth-form-panel">
            <div class="auth-form-brand">
                <a href="index.php" class="auth-form-brand-link">
                    <svg class="auth-form-brand-logo" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <use href="#brandLogo"></use>
                    </svg>
                    <span class="auth-form-brand-name"><?php echo APP_NAME; ?></span>
                </a>
            </div>
            <div class="auth-form-header">
                <div class="auth-form-title">Selamat Datang Kembali</div>
                <p class="auth-form-subtitle">Masuk untuk melanjutkan perjalanan coding Anda</p>
            </div>
            <span class="auth-form-title-underline"></span>

            <?php if ($error_message): ?>
                <div class="alert alert-error" role="alert">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <form method="POST" id="loginForm" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">

                <div class="auth-field stagger">
                    <label for="email" class="auth-field-label">Email atau User ID</label>
                    <div class="auth-field-wrap">
                        <span class="auth-field-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 5L2 7"/></svg>
                        </span>
                        <input type="email" id="email" name="email"
                               class="auth-field-input"
                               placeholder="Masukkan User ID atau Email"
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                               required autocomplete="email">
                    </div>
                </div>

                <div class="auth-field stagger">
                    <label for="password" class="auth-field-label">Kata Sandi</label>
                    <div class="auth-field-wrap">
                        <span class="auth-field-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        </span>
                        <input type="password" id="password" name="password"
                               class="auth-field-input"
                               placeholder="Masukkan kata sandi"
                               required autocomplete="current-password">
                        <button type="button" class="auth-field-toggle" id="togglePassword" aria-label="Show password">
                            <svg class="eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg class="eye-closed" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-10-7-10-7a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 10 7 10 7a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                        </button>
                    </div>
                </div>

                <div class="auth-form-row stagger">
                    <label class="auth-remember">
                        <input type="checkbox" name="remember" checked>
                        <span>Ingat saya</span>
                    </label>
                    <a href="forgot-password.php" class="auth-forgot-link">Lupa Kata Sandi?</a>
                </div>

                <button type="submit" class="auth-btn-primary stagger" id="submitBtn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <span class="btn-label">MASUK</span>
                    <span class="btn-spinner" aria-hidden="true"></span>
                </button>
            </form>

            <div class="auth-divider-sm stagger"><span>atau</span></div>

            <div class="auth-form-footer stagger">
                Belum punya akun? <a href="register.php">Daftar di sini</a>
            </div>

            <div class="auth-demo-box stagger">
                <div class="auth-demo-box-title">&#9733; Akun Demo</div>
                <div>Admin: <code>admin / password</code></div>
                <div>Siswa: <code>student1 / password</code></div>
            </div>
        </div>

        <!-- RIGHT: Welcome Panel (blue gradient) -->
        <div class="auth-welcome-panel">
            <button class="auth-welcome-close" onclick="window.location.href='index.php'" aria-label="Close">
                <svg viewBox="0 0 24 24" fill="none"><path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>

            <!-- Watermark -->
            <div class="auth-welcome-watermark"></div>

            <div class="auth-welcome-content">
                <a href="index.php" class="auth-welcome-brand">
                    <svg class="auth-welcome-brand-logo" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <linearGradient id="logoGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#ffffff" stop-opacity="0.9"/>
                                <stop offset="100%" stop-color="#c7d2fe" stop-opacity="0.7"/>
                            </linearGradient>
                        </defs>
                        <path d="M 25 20 L 25 75 Q 25 80 30 80 L 35 80 Q 40 80 40 75 L 40 20 Q 40 15 35 15 L 30 15 Q 25 15 25 20 Z" fill="url(#logoGrad)"/>
                        <path d="M 40 20 Q 40 15 45 15 L 60 15 Q 70 15 70 25 L 70 35 Q 70 45 60 45 L 45 45 Q 40 45 40 40 L 40 30 Q 40 25 45 25 L 60 25 Q 65 25 65 30 L 65 35 Q 65 40 60 40 L 45 40 Q 40 40 40 35 Z" fill="url(#logoGrad)"/>
                    </svg>
                    <span class="auth-welcome-brand-name"><?php echo APP_NAME; ?></span>
                </a>

                <h1 class="auth-welcome-heading">SELAMAT DATANG KEMBALI!</h1>
                <p class="auth-welcome-text">Masuk dan lanjutkan perjalanan coding Anda bersama komunitas. Belajar coding dengan cara yang menyenangkan!</p>

                <a href="register.php" class="auth-btn-signup">DAFTAR</a>

                <div class="auth-welcome-stats">
                    <div class="auth-welcome-stat">
                        <strong>10K+</strong>
                        <span>Siswa</span>
                    </div>
                    <div class="auth-welcome-stat-sep"></div>
                    <div class="auth-welcome-stat">
                        <strong>50+</strong>
                        <span>Kursus</span>
                    </div>
                    <div class="auth-welcome-stat-sep"></div>
                    <div class="auth-welcome-stat">
                        <strong>4.9</strong>
                        <span>Rating</span>
                    </div>
                </div>

                <div class="auth-welcome-features">
                    <div class="auth-welcome-feature">
                        <div class="auth-welcome-feature-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
                        </div>
                        <span class="auth-welcome-feature-text">Code Editor Interaktif</span>
                    </div>
                    <div class="auth-welcome-feature">
                        <div class="auth-welcome-feature-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/></svg>
                        </div>
                        <span class="auth-welcome-feature-text">Belajar dengan Gamifikasi</span>
                    </div>
                    <div class="auth-welcome-feature">
                        <div class="auth-welcome-feature-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        </div>
                        <span class="auth-welcome-feature-text">Clan & Komunitas</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function() {
            'use strict';
            // Toggle password visibility
            const toggle = document.getElementById('togglePassword');
            const input = document.getElementById('password');
            if (toggle && input) {
                toggle.addEventListener('click', function() {
                    const isPw = input.getAttribute('type') === 'password';
                    input.setAttribute('type', isPw ? 'text' : 'password');
                    toggle.querySelector('.eye-open').style.display = isPw ? 'none' : '';
                    toggle.querySelector('.eye-closed').style.display = isPw ? '' : 'none';
                    toggle.setAttribute('aria-label', isPw ? 'Hide password' : 'Show password');
                });
            }
            // Loading state
            const form = document.getElementById('loginForm');
            const btn = document.getElementById('submitBtn');
            if (form && btn) {
                form.addEventListener('submit', function() {
                    btn.classList.add('btn-loading');
                    btn.querySelector('.btn-label').textContent = 'Memproses...';
                    btn.disabled = true;
                });
            }
            // Stagger fallback — ensure elements are visible even if animation fails
            const stagers = document.querySelectorAll('.stagger');
            if (stagers.length && !window.requestAnimationFrame) {
                stagers.forEach(function(el) {
                    el.style.opacity = '1';
                    el.style.transform = 'none';
                });
            }
            // Input focus effect: highlight sibling icon
            document.querySelectorAll('.auth-field-wrap').forEach(function(wrap) {
                const inp = wrap.querySelector('.auth-field-input');
                if (!inp) return;
                inp.addEventListener('focus', function() {
                    wrap.classList.add('is-focused');
                });
                inp.addEventListener('blur', function() {
                    wrap.classList.remove('is-focused');
                });
            });
        })();
    </script>
</body>
</html>
