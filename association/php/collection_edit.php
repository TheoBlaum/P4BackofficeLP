<?php
require 'config.php';

// Vérifier si un ID de collecte est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: collection_list.php");
    exit;
}

$id = $_GET['id'];

// Récupérer les informations de la collecte
$stmt = $pdo->prepare("SELECT * FROM collectes WHERE id = ?");
$stmt->execute([$id]);
$collecte = $stmt->fetch();

if (!$collecte) {
    header("Location: collection_list.php");
    exit;
}

// Récupérer la liste des bénévoles
$stmt_benevoles = $pdo->prepare("SELECT id, nom FROM benevoles ORDER BY nom");
$stmt_benevoles->execute();
$benevoles = $stmt_benevoles->fetchAll();

// Récupérer la liste des déchets associés à la collecte
$stmt_dechets = $pdo->prepare("SELECT id, type_dechet, quantite_kg FROM dechets_collectes WHERE id_collecte = ?");
$stmt_dechets->execute([$id]);
$dechets_collectes = $stmt_dechets->fetchAll();

// Mettre à jour la collecte et les déchets
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $date = $_POST["date"];
    $lieu = $_POST["lieu"];
    $benevole_id = $_POST["benevole"];

    // Mettre à jour la collecte
    $stmt = $pdo->prepare("UPDATE collectes SET date_collecte = ?, lieu = ?, id_benevole = ? WHERE id = ?");
    $stmt->execute([$date, $lieu, $benevole_id, $id]);

    // Vérifier si un type de déchet a été soumis
    if (!empty($_POST["type_dechet"]) && !empty($_POST["quantite_kg"])) {
        $type_dechet = $_POST["type_dechet"];
        $quantite_kg = $_POST["quantite_kg"];
        $action = $_POST["action"]; // Action ajoutée : "ajouter" ou "remplacer"

        // Vérifier si le déchet existe déjà pour cette collecte
        $stmt_check = $pdo->prepare("SELECT id, quantite_kg FROM dechets_collectes WHERE id_collecte = ? AND type_dechet = ?");
        $stmt_check->execute([$id, $type_dechet]);
        $dechet_existant = $stmt_check->fetch();

        if ($dechet_existant) {
            // Si le déchet existe déjà et l'action est "ajouter", additionner la quantité
            if ($action === 'ajouter') {
                $quantite_kg += $dechet_existant['quantite_kg']; // Ajouter à l'existant
                $stmt_update = $pdo->prepare("UPDATE dechets_collectes SET quantite_kg = ? WHERE id = ?");
                $stmt_update->execute([$quantite_kg, $dechet_existant['id']]);
            }
            // Si l'action est "remplacer", remplacer la quantité existante
            else {
                $stmt_update = $pdo->prepare("UPDATE dechets_collectes SET quantite_kg = ? WHERE id = ?");
                $stmt_update->execute([$quantite_kg, $dechet_existant['id']]);
            }
        } else {
            // Si le déchet n'existe pas, insérer un nouveau type de déchet
            $stmt_insert = $pdo->prepare("INSERT INTO dechets_collectes (type_dechet, quantite_kg, id_collecte) VALUES (?, ?, ?)");
            $stmt_insert->execute([$type_dechet, $quantite_kg, $id]);
        }
    }

    // Rediriger vers la même page pour voir immédiatement les modifications
    header("Location: collection_edit.php?id=$id");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier une collecte</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-900">

<div class="flex h-screen">
    <div class="bg-cyan-200 text-white w-64 p-6">
        <h2 class="text-2xl font-bold mb-6">Dashboard</h2>
        <li><a href="collection_list.php" class="flex items-center py-2 px-3 hover:bg-blue-800 rounded-lg"><i class="fas fa-tachometer-alt mr-3"></i> Tableau de bord</a></li>
            <li><a href="collection_add.php" class="flex items-center py-2 px-3 hover:bg-blue-800 rounded-lg"><i class="fas fa-plus-circle mr-3"></i> Ajouter une collecte</a></li>
            <li><a href="volunteer_list.php" class="flex items-center py-2 px-3 hover:bg-blue-800 rounded-lg"><i class="fa-solid fa-list mr-3"></i> Liste des bénévoles</a></li>
            <li><a href="user_add.php" class="flex items-center py-2 px-3 hover:bg-blue-800 rounded-lg"><i class="fas fa-user-plus mr-3"></i> Ajouter un bénévole</a></li>
            <li><a href="my_account.php" class="flex items-center py-2 px-3 hover:bg-blue-800 rounded-lg"><i class="fas fa-cogs mr-3"></i> Mon compte</a></li>
        <div class="mt-6">
            <button onclick="logout()" class="w-full bg-red-600 hover:bg-red-700 text-white py-2 rounded-lg">
                Déconnexion
            </button>
        </div>
    </div>

    <div class="flex-1 p-8 overflow-y-auto">
        <h1 class="text-4xl font-bold text-blue-900 mb-6">Modifier une collecte</h1>

        <div class="bg-white p-6 rounded-lg shadow-lg">
            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Date :</label>
                    <input type="date" name="date" value="<?= htmlspecialchars($collecte['date_collecte']) ?>" required class="w-full p-2 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Lieu :</label>
                    <input type="text" name="lieu" value="<?= htmlspecialchars($collecte['lieu']) ?>" required class="w-full p-2 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Bénévole :</label>
                    <select name="benevole" required class="w-full p-2 border border-gray-300 rounded-lg">
                        <option value="" disabled>Sélectionnez un bénévole</option>
                        <?php foreach ($benevoles as $benevole): ?>
                            <option value="<?= $benevole['id'] ?>" <?= $benevole['id'] == $collecte['id_benevole'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($benevole['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Type de déchet :</label>
                    <select name="type_dechet" required>
                        <option value="">Sélectionnez le type de déchet</option>
                        <option value="Plastique">Plastique</option>
                        <option value="Verre">Verre</option>
                        <option value="Papier">Papier</option>
                        <option value="Métal">Métal</option>
                        <option value="Organiques">Organiques</option>
                    </select>
                    <label for="quantite_kg">Quantité (kg) :</label>
                    <input type="number" name="quantite_kg" step="1" required>
                </div>

                <div class="flex items-center">
                    <input type="radio" id="remplacer" name="action" value="remplacer" checked>
                    <label for="remplacer" class="ml-2">Remplacer</label>

                    <input type="radio" id="ajouter" name="action" value="ajouter" class="ml-4">
                    <label for="ajouter" class="ml-2">Ajouter</label>
                </div>

                <div class="flex justify-end space-x-4">
                    <a href="collection_list.php" class="bg-gray-500 text-white px-4 py-2 rounded-lg">Retour</a>
                    <button type="submit" class="bg-cyan-200 text-white px-4 py-2 rounded-lg">Modifier</button>
                </div>
            </form>
        </div>

        <!-- Tableau des déchets déjà enregistrés -->
        <div class="mt-6 bg-white p-6 rounded-lg shadow-lg">
            <h2 class="text-2xl font-bold mb-4">Déchets enregistrés</h2>
            <table class="w-full border-collapse border border-gray-300">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="border border-gray-300 px-4 py-2">Type de déchet</th>
                        <th class="border border-gray-300 px-4 py-2">Quantité (kg)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dechets_collectes as $dechet): ?>
                        <tr>
                            <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($dechet['type_dechet']) ?></td>
                            <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($dechet['quantite_kg']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<script src="logout.js"></script>
</body>
</html>
