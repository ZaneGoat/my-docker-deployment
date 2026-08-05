<?php

$conn = new mysqli("127.0.0.1","root","","my_project");

if($conn->connect_error){
    die("Erreur de connexion");
}

if(!isset($_GET["n_fct"])){
    exit();
}

$n_fct = intval($_GET["n_fct"]);

// Calcul Total HT
$result = $conn->query("
SELECT IFNULL(SUM(prix*qteF),0) AS total
FROM detaille_factur
WHERE n_fct='$n_fct'
");

$data = $result->fetch_assoc();

$totalHT = $data["total"];

// TVA
$result2 = $conn->query("
SELECT tva
FROM facture
WHERE n_fct='$n_fct'
");

$f = $result2->fetch_assoc();

$tva = $f["tva"];

$ttc = $totalHT + ($totalHT * $tva / 100);

// Mise à jour de la facture
$conn->query("
UPDATE facture
SET total_HT='$totalHT',
    TTC='$ttc'
WHERE n_fct='$n_fct'
");

// Retour JSON
echo json_encode([
    "totalHT"=>$totalHT,
    "ttc"=>$ttc
]);

?>