<?php
$conn = new mysqli("127.0.0.1","root","","my_project");

if($conn->connect_error){
    die("Erreur de connexion");
}

$id = $_GET["id"];

$facture = $conn->query("
SELECT facture.*, fournisseur.nom_frs
FROM facture
INNER JOIN fournisseur
ON facture.N_frs = fournisseur.N_frs
WHERE facture.n_fct='$id'
");

$f = $facture->fetch_assoc();

$details = $conn->query("
SELECT
detaille_factur.*,
produit.NomPrd
FROM detaille_factur
INNER JOIN produit
ON detaille_factur.NPrd = produit.NPrd
WHERE detaille_factur.n_fct='$id'
");
?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">

<title>Impression Facture</title>

<style>

body{
font-family:Arial;
margin:40px;
}

table{
width:100%;
border-collapse:collapse;
margin-top:20px;
}

table,th,td{
border:1px solid black;
}

th,td{
padding:10px;
text-align:center;
}

h1,h2{
text-align:center;
}

@media print {
    .fab-container, button, a {
        display: none !important;
    }
}

</style>

</head>

<body>

<h1>GESTION COMMERCIALE</h1>

<h2>FACTURE</h2>

<hr>

<p><strong>N° Facture :</strong> <?php echo $f["n_fct"]; ?></p>

<p><strong>Date :</strong> <?php echo $f["date_fct"]; ?></p>

<p><strong>Fournisseur :</strong> <?php echo $f["nom_frs"]; ?></p>

<table>

<tr>

<th>Produit</th>

<th>Prix</th>

<th>Quantité</th>

<th>Total</th>

</tr>

<?php

while($d=$details->fetch_assoc()){

$total=$d["prix"]*$d["qteF"];

?>

<tr>

<td><?php echo $d["NomPrd"]; ?></td>

<td><?php echo $d["prix"]; ?></td>

<td><?php echo $d["qteF"]; ?></td>

<td><?php echo $total; ?></td>

</tr>

<?php } ?>

</table>

<br>

<h3>Total HT : <?php echo $f["total_HT"]; ?> DH</h3>

<h3>TVA : <?php echo $f["tva"]; ?> %</h3>

<h3>TTC : <?php echo $f["TTC"]; ?> DH</h3>

<br><br>

<p style="text-align:right;">
Signature
</p>

<script>
window.print();
</script>

<?php include 'fab.php'; ?>
</body>
</html>