<?php
// admin/ventes.php - Point of Sale (POS) Interface
require '../db.php';
require '../includes/functions.php';
check_admin_login();

// Handle POS submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cart_data = json_decode($_POST['cart_data'], true);
    $mode_paiement = sanitize($_POST['mode_paiement']);
    $montant_paye = floatval($_POST['montant_paye']);
    
    if (!empty($cart_data)) {
        try {
            $pdo->beginTransaction();
            
            // Create a generic "Client" for in-store sales if needed, or use a default
            // We'll create a "Client Passager" (Guest)
            $stmt_client = $pdo->prepare("INSERT INTO Client (nom, prenom, tel, email) VALUES ('Passager', 'Client', '', '')");
            $stmt_client->execute();
            $client_id = $pdo->lastInsertId();

            $no_ticket = strtoupper(uniqid('POS-'));
            $stmt = $pdo->prepare("INSERT INTO Commande (statut, No_ticket, mode_paiement, id_client) VALUES ('Livrée', ?, ?, ?)");
            $stmt->execute([$no_ticket, $mode_paiement, $client_id]);
            $commande_id = $pdo->lastInsertId();

            $stmt_detail = $pdo->prepare("INSERT INTO Detail_Commande (id_commande, id_produit, quantite) VALUES (?, ?, ?)");
            
            foreach ($cart_data as $item) {
                $stmt_detail->execute([$commande_id, $item['id'], $item['qty']]);
            }

            $pdo->commit();
            
            // Redirect to receipt
            header("Location: receipt.php?id=" . $commande_id . "&paye=" . $montant_paye);
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Erreur lors de la vente : " . $e->getMessage();
        }
    }
}

// Fetch products for POS
$stmt_prods = $pdo->query("SELECT id_produit, libelle, prix FROM Produit ORDER BY libelle ASC");
$produits = $stmt_prods->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Point de Vente (POS) - AyaAdmin</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .pos-container { display: flex; gap: 2rem; margin-top: 1rem; }
        .pos-products { flex: 2; display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 1rem; align-content: start; }
        .pos-cart { flex: 1; background: var(--surface); padding: 1.5rem; border-radius: 10px; border: 1px solid var(--surface-border); height: fit-content; }
        .pos-item { background: rgba(255,255,255,0.05); padding: 1rem; border-radius: 8px; cursor: pointer; text-align: center; border: 1px solid var(--surface-border); transition: 0.2s; }
        .pos-item:hover { border-color: var(--primary); background: rgba(212, 175, 55, 0.1); }
        .cart-row { display: flex; justify-content: space-between; margin-bottom: 0.5rem; padding-bottom: 0.5rem; border-bottom: 1px solid rgba(255,255,255,0.1); }
    </style>
</head>
<body>
    <header style="position: static; background: rgba(0,0,0,0.8); border-bottom: 1px solid var(--surface-border);">
        <a href="index.php" class="logo">AyaAdmin</a>
        <nav>
            <ul>
                <li><a href="ventes.php" style="color: var(--primary);">Ventes (POS)</a></li>
                <li><a href="categories.php">Catégories</a></li>
                <li><a href="produits.php">Produits</a></li>
                <li><a href="commandes.php">Commandes</a></li>
                <li><a href="logout.php" style="color: #e74c3c; font-weight: bold;">Déconnexion</a></li>
            </ul>
        </nav>
    </header>

    <div class="section" style="padding-top: 2rem;">
        <h1 style="color: var(--primary);">Caisse / Ventes</h1>
        <?php if (isset($error)) echo "<p style='color:#e74c3c;'>$error</p>"; ?>

        <div class="pos-container">
            <!-- Left: Product Grid -->
            <div class="pos-products">
                <?php foreach ($produits as $p): ?>
                    <div class="pos-item" onclick="addToCart(<?= $p['id_produit'] ?>, '<?= addslashes($p['libelle']) ?>', <?= $p['prix'] ?>)">
                        <div style="font-weight: bold; margin-bottom: 0.5rem;"><?= htmlspecialchars($p['libelle']) ?></div>
                        <div style="color: var(--primary);"><?= number_format($p['prix'], 2) ?> DH</div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Right: Cart & Calculation -->
            <div class="pos-cart">
                <h2 style="margin-bottom: 1rem; border-bottom: 1px solid var(--primary); padding-bottom: 0.5rem;">Ticket en cours</h2>
                <div id="cart-items" style="min-height: 150px; max-height: 300px; overflow-y: auto; margin-bottom: 1rem;">
                    <!-- JS fills this -->
                </div>
                
                <div style="font-size: 1.2rem; font-weight: bold; text-align: right; margin-bottom: 1rem;">
                    Total: <span id="cart-total" style="color: var(--primary);">0.00</span> DH
                </div>

                <div style="margin-bottom: 1rem;">
                    <label>Montant Payé (DH):</label>
                    <input type="number" id="montant_paye" class="input-field" style="width: 100%; margin-top: 0.5rem;" oninput="calculateExchange()" value="0">
                </div>
                
                <div style="font-size: 1.2rem; font-weight: bold; text-align: right; margin-bottom: 1.5rem; color: #2ecc71;">
                    À Rendre: <span id="rendu">0.00</span> DH
                </div>

                <form method="POST" id="pos-form">
                    <input type="hidden" name="cart_data" id="cart_data">
                    <input type="hidden" name="montant_paye" id="hidden_montant_paye">
                    <select name="mode_paiement" class="input-field" style="width: 100%; margin-bottom: 1rem;" required>
                        <option value="Espèces">Espèces</option>
                        <option value="Carte Bancaire">Carte Bancaire</option>
                    </select>
                    <button type="button" class="btn" style="width: 100%; background: #2ecc71; color: #fff; border:none;" onclick="submitPOS()">Valider & Imprimer</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        let cart = {};

        function addToCart(id, libelle, prix) {
            if (cart[id]) {
                cart[id].qty++;
            } else {
                cart[id] = { id: id, libelle: libelle, prix: prix, qty: 1 };
            }
            renderCart();
        }

        function removeFromCart(id) {
            delete cart[id];
            renderCart();
        }

        function renderCart() {
            const container = document.getElementById('cart-items');
            container.innerHTML = '';
            let total = 0;

            for (let id in cart) {
                let item = cart[id];
                let subtotal = item.prix * item.qty;
                total += subtotal;

                container.innerHTML += `
                    <div class="cart-row">
                        <div style="flex:2;">${item.libelle}</div>
                        <div style="flex:1; text-align:center;">x${item.qty}</div>
                        <div style="flex:1; text-align:right;">${subtotal.toFixed(2)}</div>
                        <div style="margin-left: 10px; cursor: pointer; color: #e74c3c;" onclick="removeFromCart(${id})">✕</div>
                    </div>
                `;
            }
            
            document.getElementById('cart-total').innerText = total.toFixed(2);
            calculateExchange();
        }

        function calculateExchange() {
            let total = parseFloat(document.getElementById('cart-total').innerText);
            let paye = parseFloat(document.getElementById('montant_paye').value) || 0;
            let rendu = paye - total;
            document.getElementById('rendu').innerText = (rendu > 0 ? rendu : 0).toFixed(2);
            document.getElementById('hidden_montant_paye').value = paye;
        }

        function submitPOS() {
            if (Object.keys(cart).length === 0) {
                alert("Le panier est vide !");
                return;
            }
            let cartArray = Object.values(cart);
            document.getElementById('cart_data').value = JSON.stringify(cartArray);
            document.getElementById('pos-form').submit();
        }
    </script>
    <a href="index.php" class="fab no-print" title="Retour au Dashboard">🏠</a>
</body>
</html>
