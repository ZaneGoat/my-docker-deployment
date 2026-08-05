<?php
session_start();

$conn = new mysqli("127.0.0.1", "root", "");
if (!$conn->connect_error) {
    $conn->query("CREATE DATABASE IF NOT EXISTS my_project");
    $conn->select_db("my_project");
}

if ($conn->connect_error) {
    die("Erreur de connexion");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = $_POST["username"];
    $password = $_POST["password"];

    $sql = "SELECT * FROM users
            WHERE username='$username'
            AND password='$password'";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {

        $_SESSION["username"] = $username;

        header("Location: accueil.php");
        exit();

    } else {

        echo "<p style='color:red;'>Nom d'utilisateur ou mot de passe incorrect.</p>";

    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Login</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<h2>Connexion</h2>

<form method="POST">

Nom d'utilisateur :<br>
<input type="text" name="username" required>

<br><br>

Mot de passe :<br>
<input type="password" name="password" required>

<br><br>

<button type="submit">Se connecter</button>

</form>

</body>
</html>