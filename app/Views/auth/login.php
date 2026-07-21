<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMS Lab - Login</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background: radial-gradient(circle at 10% 20%, rgba(16, 185, 129, 0.15) 0%, transparent 40%),
                        radial-gradient(circle at 90% 80%, rgba(59, 130, 246, 0.12) 0%, transparent 40%),
                        linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #f8fafc;
            padding: 1.5rem;
            -webkit-font-smoothing: antialiased;
        }
        .login-card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            padding: 2.75rem 2.5rem;
            border-radius: 20px;
            width: 100%;
            max-width: 440px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            text-align: center;
            transition: transform 0.3s ease;
        }
        .logo-icon {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            width: 56px;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            font-size: 1.1rem;
            font-weight: 800;
            color: white;
            margin: 0 auto 1.25rem;
            box-shadow: 0 10px 25px -5px rgba(16, 185, 129, 0.5);
        }
        h1 {
            font-size: 1.75rem;
            font-weight: 800;
            color: white;
            letter-spacing: -0.02em;
            margin-bottom: 0.4rem;
        }
        p.tagline {
            color: #94a3b8;
            font-size: 0.9rem;
            margin-bottom: 2.25rem;
        }
        .form-group {
            text-align: left;
            margin-bottom: 1.35rem;
        }
        label {
            display: block;
            font-size: 0.78rem;
            font-weight: 700;
            color: #cbd5e1;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }
        input {
            width: 100%;
            padding: 0.82rem 1.1rem;
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 10px;
            color: white;
            font-size: 0.95rem;
            font-family: inherit;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            outline: none;
        }
        input:focus {
            background: rgba(15, 23, 42, 0.85);
            border-color: #10b981;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.2);
        }
        .btn-submit {
            width: 100%;
            padding: 0.85rem 1.25rem;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
            color: white;
            border-radius: 10px;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            margin-top: 1rem;
            box-shadow: 0 10px 20px -5px rgba(16, 185, 129, 0.4);
        }
        .btn-submit:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            box-shadow: 0 14px 25px -5px rgba(16, 185, 129, 0.5);
            transform: translateY(-1px);
        }
        .btn-submit:active {
            transform: scale(0.98);
        }
        .alert {
            padding: 0.85rem 1.1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            font-size: 0.88rem;
            text-align: left;
            font-weight: 500;
        }
        .alert-danger {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
        }
        .alert-success {
            background: rgba(34, 197, 94, 0.15);
            border: 1px solid rgba(34, 197, 94, 0.3);
            color: #86efac;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="logo-icon">SMS</div>
        <h1>SMS Lab System</h1>
        <p class="tagline">Sign in to access your validation parameters</p>
        
        <?php if(session()->getFlashdata('error')): ?>
            <div class="alert alert-danger">
                <?= session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>

        <?php if(session()->getFlashdata('success')): ?>
            <div class="alert alert-success">
                <?= session()->getFlashdata('success') ?>
            </div>
        <?php endif; ?>

        <form action="<?= base_url('login') ?>" method="POST">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="<?= old('email') ?>" required placeholder="Enter email address">
            </div>
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="Enter password">
            </div>
            <button type="submit" class="btn-submit">Sign In</button>
        </form>
    </div>
</body>
</html>
