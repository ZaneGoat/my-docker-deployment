<?php

$conn=new mysqli("127.0.0.1","root","","my_project");

$details=$conn->query("
SELECT d.*,p.NomPrd
FROM detaille_factur d
INNER JOIN produit p
ON d.NPrd=p.NPrd
ORDER BY n_fct DESC
");

while($d=$details->fetch_assoc()){

$total=$d["prix"]*$d["qteF"];

echo "<tr>

<td>".$d["n_fct"]."</td>

<td>".$d["NomPrd"]."</td>

<td>".number_format($d["prix"],2)." DH</td>

<td>".$d["qteF"]."</td>

<td>".number_format($total,2)." DH</td>

<td>

<a class='btn-modifier'
href='modifier_detaille_facture.php?n_fct=".$d["n_fct"]."&NPrd=".$d["NPrd"]."'>
Modifier
</a>

<a class='btn-delete'
href='delete_detaille_facture.php?n_fct=".$d["n_fct"]."&NPrd=".$d["NPrd"]."'>
Supprimer
</a>

</td>

</tr>";

}
?>