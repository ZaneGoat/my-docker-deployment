<?php

$conn = new mysqli("127.0.0.1","root","","my_project");

if($conn->connect_error){
    die("Erreur de connexion");
}

$n_fct = $_POST["n_fct"];
$NPrd  = $_POST["NPrd"];
$qteF  = $_POST["qteF"];
$prix  = $_POST["prix"];

$sql = "INSERT INTO detaille_factur
(n_fct,NPrd,qteF,prix)
VALUES
('$n_fct','$NPrd','$qteF','$prix')";

if($conn->query($sql)){

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

    // Mise à jour
    $conn->query("
    UPDATE facture
    SET total_HT='$totalHT',
        TTC='$ttc'
    WHERE n_fct='$n_fct'
    ");

    echo json_encode([
        "success"=>true,
        "totalHT"=>$totalHT,
        "ttc"=>$ttc
    ]);

}else{

    echo json_encode([
        "success"=>false
    ]);

}

?>