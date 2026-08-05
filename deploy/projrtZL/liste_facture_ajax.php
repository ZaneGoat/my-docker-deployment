<?php

$conn=new mysqli("127.0.0.1","root","","my_project");

$liste=$conn->query("
SELECT facture.*, fournisseur.nom_frs
FROM facture
INNER JOIN fournisseur
ON facture.N_frs=fournisseur.N_frs
ORDER BY n_fct DESC
");

while($row=$liste->fetch_assoc()){

echo "<tr>

<td>".$row["n_fct"]."</td>

<td>".$row["date_fct"]."</td>

<td>".$row["tva"]."%</td>

<td>".number_format($row["total_HT"],2)." DH</td>

<td>".number_format($row["TTC"],2)." DH</td>

<td>".$row["nom_frs"]."</td>

<td>

<a class='btn-modifier'
href='modifier_facture.php?id=".$row["n_fct"]."'>
Modifier
</a>

<a class='btn-delete'
href='delete_facture.php?id=".$row["n_fct"]."'>
Supprimer
</a>

<a class='btn-imprimer'
href='imprimer_facture.php?id=".$row["n_fct"]."'
target='_blank'>
Imprimer
</a>

</td>

</tr>";

}
?>