<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>WMS - Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

    <style>
        body {
            background-color: #1e1e2f; /* dark background */
            color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: #2c2c3b; /* slightly lighter than background */
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.5);
            padding: 2rem;
            width: 100%;
            max-width: 400px;
        }
        .login-card .form-control {
            background-color: #3a3a4d;
            border: none;
            color: #fff;
        }
        .login-card .form-control:focus {
            background-color: #44445a;
            color: #fff;
            box-shadow: 0 0 0 0.25rem rgba(13,110,253,.25);
        }
        .login-card .btn-primary {
            background: #0d6efd;
            border: none;
            font-weight: 500;
        }
        .login-card .btn-primary:hover {
            background: #0b5ed7;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="text-center mb-4">
            <i class="fa-solid fa-warehouse fa-2x mb-2"></i>
            <h3>WMS Login</h3>
        </div>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <form method="post" action="<?= site_url('auth/doLogin') ?>">
            <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
            </div>
            <div class="mb-3">
                <label>Password</label>
                <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
            </div>
            <button class="btn btn-primary w-100">
                <i class="fa-solid fa-right-to-bracket"></i> Login
            </button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
