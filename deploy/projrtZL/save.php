<?php
session_start();

if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}
?>
<?php
$conn = new mysqli("127.0.0.1", "root", "", "my_project");

if ($conn->connect_error) {
    die("Erreur de connexion");
}

$nom = $_POST["nom"];
$prenom = $_POST["prenom"];
$telephone = $_POST["telephone"];
$adresse = $_POST["adresse"];

$sql = "INSERT INTO clients (nom, prenom, telephone, adresse)
VALUES ('$nom','$prenom','$telephone','$adresse')";

if ($conn->query($sql)) {
    echo "<script>
        alert('Client ajouté avec succès !');
        window.location='client.html';
    </script>";
} else {
    echo "Erreur : " . $conn->error;
}

$conn->close();
?>