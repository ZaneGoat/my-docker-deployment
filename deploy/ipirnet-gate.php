<?php
session_start();

$secret_code = 'ZaneX2003??';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['code'])) {
    if ($_POST['code'] === $secret_code) {
        $_SESSION['ipirnet_unlocked'] = true;
        header('Location: /ipirnet/');
        exit;
    } else {
        $error = 'Wrong code, genius. Try again.';
    }
}

if (isset($_SESSION['ipirnet_unlocked']) && $_SESSION['ipirnet_unlocked'] === true) {
    header('Location: /ipirnet/');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IPIRNET V7 — Locked</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .lock-box {
            background: rgba(255,255,255,0.05);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 24px;
            padding: 3rem;
            width: 100%;
            max-width: 420px;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
        }
        .lock-icon { font-size: 4rem; margin-bottom: 1rem; }
        h1 { font-size: 1.8rem; margin-bottom: 0.5rem; }
        .sub { color: #94a3b8; margin-bottom: 0.5rem; font-size: 0.8rem; letter-spacing: 3px; text-transform: uppercase; }
        p { color: #94a3b8; margin-bottom: 2rem; font-size: 0.95rem; }
        form { display: flex; flex-direction: column; gap: 1rem; }
        input[type="password"] {
            width: 100%;
            padding: 1rem 1.2rem;
            background: rgba(0,0,0,0.3);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px;
            color: #fff;
            font-size: 1.1rem;
            text-align: center;
            outline: none;
            transition: border-color 0.3s;
            letter-spacing: 0.3em;
        }
        input[type="password"]:focus {
            border-color: #fbbf24;
            box-shadow: 0 0 0 3px rgba(251,191,36,0.15);
        }
        button {
            padding: 1rem;
            background: linear-gradient(to right, #f59e0b, #fbbf24);
            border: none;
            border-radius: 12px;
            color: #000;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        button:hover {
            box-shadow: 0 0 20px rgba(251,191,36,0.4);
            transform: translateY(-2px);
        }
        .error {
            color: #f87171;
            font-size: 0.9rem;
            margin-top: -0.5rem;
        }
    </style>
</head>
<body>
    <div class="lock-box">
        <div class="lock-icon">🔒</div>
        <div class="sub">IPIRNET V7</div>
        <h1>Access Code Required</h1>
        <p>Enter the code to access the flagship project.</p>
        <form method="POST">
            <input type="password" name="code" placeholder="Enter code" autofocus>
            <button type="submit">Unlock</button>
            <?php if (isset($error)): ?>
                <div class="error"><?= $error ?></div>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>
