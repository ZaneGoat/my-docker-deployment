<?php
// admin/login.php
require '../db.php';
require '../includes/functions.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    // Simple hardcoded admin password for now per the plan
    if ($password === 'admin123') {
        $_SESSION['admin_logged_in'] = true;
        header("Location: index.php");
        exit;
    } else {
        $error = "Mot de passe incorrect.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin Login - AyaResto</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="section" style="min-height: 100vh; display: flex; align-items: center;">
        <div class="glass-panel" style="margin: 0 auto; padding: 3rem; max-width: 400px;">
            <h2>Admin Access</h2>
            <?php if ($error): ?>
                <p style="color: #e74c3c;"><?= $error ?></p>
            <?php endif; ?>
            <form method="POST" style="display: flex; flex-direction: column; gap: 1rem;">
                <input type="password" name="password" placeholder="Mot de passe" required style="padding: 0.8rem; background: rgba(0,0,0,0.3); border: 1px solid var(--primary); color: white; border-radius: 5px;">
                <button type="submit" class="btn" style="width: 100%;">Connexion</button>
            </form>
        </div>
    </div>
</body>
</html>
