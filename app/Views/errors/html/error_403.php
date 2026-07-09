<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>403 - Forbidden</title>
    <style>
        body {
            height: 100vh;
            background: #f6f8fb;
            font-family: 'Segoe UI', 'Roboto', 'Helvetica Neue', sans-serif;
            color: #172033;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }
        .container {
            text-align: center;
            background: white;
            padding: 3rem 2rem;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            max-width: 450px;
            border: 1px solid #e2e8f0;
        }
        h1 {
            font-size: 5rem;
            color: #e74c3c;
            margin: 0 0 1rem;
            font-weight: 800;
            line-height: 1;
        }
        h2 {
            font-size: 1.5rem;
            color: #1e4668;
            margin: 0 0 1rem;
            font-weight: 600;
        }
        p {
            color: #5a6e7c;
            margin-bottom: 2rem;
            font-size: 0.95rem;
            line-height: 1.5;
        }
        .btn {
            display: inline-block;
            background: #153e5c;
            color: white;
            padding: 0.75rem 1.5rem;
            text-decoration: none;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            transition: background 0.2s;
        }
        .btn:hover {
            background: #0b2b3b;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>403</h1>
        <h2>Access Denied</h2>
        <p><?= esc($message ?? 'You do not have permission to access this resource.') ?></p>
        <a href="<?= base_url('dashboard') ?>" class="btn">Return to Dashboard</a>
    </div>
</body>
</html>
