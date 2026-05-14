<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — e-Honai Connect</title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/images/logo-papua-pegunungan.png') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --ehonai-navy: #0c2340;
            --ehonai-navy-mid: #153a5c;
            --ehonai-teal: #0d6e6e;
            --ehonai-gold: #b8952f;
            --ehonai-card-radius: 1rem;
        }

        body.login-body {
            font-family: "Source Sans 3", system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            min-height: 100vh;
            margin: 0;
            background: linear-gradient(145deg, var(--ehonai-navy) 0%, var(--ehonai-navy-mid) 42%, #0a4a5c 100%);
            position: relative;
            overflow-x: hidden;
        }

        body.login-body::before {
            content: "";
            position: fixed;
            inset: 0;
            background-image:
                radial-gradient(ellipse 80% 50% at 50% -20%, rgba(255, 255, 255, 0.08), transparent),
                radial-gradient(circle at 100% 100%, rgba(13, 110, 110, 0.25), transparent 45%),
                radial-gradient(circle at 0% 80%, rgba(184, 149, 47, 0.08), transparent 40%);
            pointer-events: none;
        }

        .login-center-wrap {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            position: relative;
            z-index: 1;
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            border: none;
            border-radius: var(--ehonai-card-radius);
            overflow: hidden;
            box-shadow:
                0 1.25rem 3rem rgba(0, 0, 0, 0.35),
                0 0.5rem 1rem rgba(0, 0, 0, 0.12),
                0 0 0 1px rgba(255, 255, 255, 0.06) inset;
            background: #fff;
        }

        .login-card-accent {
            height: 4px;
            background: linear-gradient(90deg, var(--ehonai-teal), var(--ehonai-gold), var(--ehonai-teal));
        }

        .login-logo-img {
            max-height: 7.5rem;
            width: auto;
            max-width: 11rem;
            object-fit: contain;
            display: block;
            margin-left: auto;
            margin-right: auto;
            filter: drop-shadow(0 0.25rem 0.5rem rgba(12, 35, 64, 0.15));
        }

        .login-title {
            font-weight: 700;
            letter-spacing: -0.02em;
            color: var(--ehonai-navy);
            font-size: 1.5rem;
        }

        .login-subtitle {
            font-weight: 500;
            color: #5c6b7a;
            font-size: 0.9rem;
            letter-spacing: 0.02em;
        }

        .login-instansi {
            font-size: 0.75rem;
            color: #8b9aab;
            line-height: 1.35;
        }

        .login-card .form-control {
            border-radius: 0.5rem;
            padding: 0.65rem 0.85rem;
            border-color: #d8dee6;
        }

        .login-card .form-control:focus {
            border-color: var(--ehonai-teal);
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 110, 0.15);
        }

        .login-card .form-label {
            font-weight: 600;
            font-size: 0.875rem;
            color: #3d4f5f;
        }

        .btn-login {
            font-weight: 600;
            letter-spacing: 0.03em;
            padding: 0.65rem 1rem;
            border-radius: 0.5rem;
            color: #fff !important;
            background: linear-gradient(135deg, var(--ehonai-navy-mid), var(--ehonai-teal)) !important;
            border: none !important;
            box-shadow: 0 0.35rem 0.85rem rgba(12, 35, 64, 0.28);
        }

        .btn-login:hover,
        .btn-login:focus {
            color: #fff !important;
            background: linear-gradient(135deg, #122f4d, #0a5c5c) !important;
            box-shadow: 0 0.45rem 1rem rgba(12, 35, 64, 0.35);
        }

        .btn-login:active {
            transform: translateY(1px);
        }

        .login-alert {
            border-radius: 0.5rem;
            border: none;
            font-size: 0.875rem;
        }
    </style>
</head>
<body class="login-body">
<div class="login-center-wrap">
    <div class="card login-card">
        <div class="login-card-accent" aria-hidden="true"></div>
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <img src="<?= base_url('assets/images/logo-papua-pegunungan.png') ?>"
                     alt="Lambang Provinsi Papua Pegunungan"
                     class="login-logo-img mb-3"
                     decoding="async">
                <h1 class="login-title mb-1">e-Honai Connect</h1>
                <p class="login-subtitle mb-2">Sistem Layanan Terpadu IT</p>
                <p class="login-instansi mb-0">Dinas Komunikasi, Informatika, Persandian dan Statistik<br>Pemerintah Provinsi Papua Pegunungan</p>
            </div>

            <?php if (session()->getFlashdata('error')) : ?>
                <div class="alert alert-danger login-alert d-flex align-items-start gap-2 mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill flex-shrink-0 mt-1"></i>
                    <span><?= esc(session()->getFlashdata('error')) ?></span>
                </div>
            <?php endif; ?>

            <?= form_open('/login', ['method' => 'post', 'class' => 'login-form']) ?>
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-secondary"><i class="bi bi-person"></i></span>
                    <input type="text" name="username" id="username" class="form-control border-start-0 ps-0"
                           autocomplete="username" value="<?= esc(old('username', '')) ?>" required maxlength="100"
                           placeholder="Masukkan username">
                </div>
            </div>
            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-secondary"><i class="bi bi-key"></i></span>
                    <input type="password" name="password" id="password" class="form-control border-start-0 ps-0"
                           autocomplete="current-password" required maxlength="255"
                           placeholder="Masukkan password">
                </div>
            </div>
            <button type="submit" class="btn btn-login w-100 text-uppercase small">Login</button>
            <?= form_close() ?>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>
</html>
