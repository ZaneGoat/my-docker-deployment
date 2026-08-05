<?php
session_start();

if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("127.0.0.1","root","","my_project");

if($conn->connect_error){
    die("Erreur de connexion");
}

if($_SERVER["REQUEST_METHOD"]=="POST"){

    $nom=$_POST["nom"];
    $prenom=$_POST["prenom"];
    $telephone=$_POST["telephone"];
    $adresse=$_POST["adresse"];
    $email=$_POST["email"];

    $sql="INSERT INTO fournisseur(nom_frs,prnom_frs,tele_frs,adrs_frs,email_frs)
          VALUES('$nom','$prenom','$telephone','$adresse','$email')";

    $conn->query($sql);

    header("Location: fournisseur.php");
    exit();
}

if(isset($_GET["recherche"]) && $_GET["recherche"]!=""){

    $r=$_GET["recherche"];

    $result=$conn->query("SELECT * FROM fournisseur
    WHERE nom_frs LIKE '%$r%'
    OR prnom_frs LIKE '%$r%'
    OR tele_frs LIKE '%$r%'");

}else{

    $result=$conn->query("SELECT * FROM fournisseur");

}
?>

<!DOCTYPE html>
<html>
<head>
<?php
session_start();

if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("127.0.0.1","root","","my_project");

if($conn->connect_error){
    die("Erreur de connexion");
}

if($_SERVER["REQUEST_METHOD"]=="POST"){

    $nom=$_POST["nom"];
    $prenom=$_POST["prenom"];
    $telephone=$_POST["telephone"];
    $adresse=$_POST["adresse"];
    $email=$_POST["email"];

    $sql="INSERT INTO fournisseur(nom_frs,prnom_frs,tele_frs,adrs_frs,email_frs)
          VALUES('$nom','$prenom','$telephone','$adresse','$email')";

    $conn->query($sql);

    header("Location: fournisseur.php");
    exit();
}

if(isset($_GET["recherche"]) && $_GET["recherche"]!=""){

    $r=$_GET["recherche"];

    $result=$conn->query("SELECT * FROM fournisseur
    WHERE nom_frs LIKE '%$r%'
    OR prnom_frs LIKE '%$r%'
    OR tele_frs LIKE '%$r%'");

}else{

    $result=$conn->query("SELECT * FROM fournisseur");

}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Fournisseurs</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<div class="dashboard-layout">

    <!-- SIDEBAR FORMS -->
    <div class="sidebar">
        <h2>Ajouter un fournisseur</h2>
        <form method="POST">
            Nom<br>
            <input type="text" name="nom" required><br><br>
            Prénom<br>
            <input type="text" name="prenom" required><br><br>
            Téléphone<br>
            <input type="text" name="telephone" required><br><br>
            Adresse<br>
            <input type="text" name="adresse" required><br><br>
            Email<br>
            <input type="email" name="email" required><br><br>
            <button type="submit">Ajouter</button>
        </form>
        <br>
        <a href="accueil.php">
            <button style="width: 100%; background: rgba(255, 255, 255, 0.1);">Retour à l'accueil</button>
        </a>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <div class="header-actions" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <form method="GET" style="margin: 0;">
                <input type="text" name="recherche" placeholder="Recherche" style="margin-bottom: 0;">
                <button type="submit">Rechercher</button>
                <?php
                if(isset($_GET["recherche"]) && $_GET["recherche"]!=""){
                ?>
                <a href="fournisseur.php">
                <button type="button">Retour</button>
                </a>
                <?php } ?>
            </form>
            <button type="button" class="btn btn-print" onclick="window.print()" style="margin-left: 10px;">Imprimer la liste</button>
        </div>

        <table border="1" cellpadding="10">
            <tr>
                <th>N°</th>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Téléphone</th>
                <th>Adresse</th>
                <th>Email</th>
                <th>Action</th>
            </tr>
            <?php
            while($row=$result->fetch_assoc()){
                echo "<tr>
                <td>".$row["N_frs"]."</td>
                <td>".$row["nom_frs"]."</td>
                <td>".$row["prnom_frs"]."</td>
                <td>".$row["tele_frs"]."</td>
                <td>".$row["adrs_frs"]."</td>
                <td>".$row["email_frs"]."</td>
                <td>
                <a class='btn' href='modifier_fournisseur.php?id=".$row["N_frs"]."'>Modifier</a>
                <a class='btn delete' href='delete_fournisseur.php?id=".$row["N_frs"]."' onclick=\"return confirm('Supprimer ce fournisseur ?')\">Supprimer</a>
                </td>
                </tr>";
            }
            ?>
        </table>
    </div>

</div>

<?php include 'fab.php'; ?>
</body>
</html>