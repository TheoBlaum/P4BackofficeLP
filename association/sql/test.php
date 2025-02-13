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

        // Vérifier si le déchet existe déjà pour cette collecte
        $stmt_check = $pdo->prepare("SELECT id, quantite_kg FROM dechets_collectes WHERE id_collecte = ? AND type_dechet = ?");
        $stmt_check->execute([$id, $type_dechet]);
        $dechet_existant = $stmt_check->fetch();

        if ($dechet_existant) {
            // Ajouter la nouvelle quantité à l'existante
            $nouvelle_quantite = $dechet_existant['quantite_kg'] + $quantite_kg;
            $stmt_update = $pdo->prepare("UPDATE dechets_collectes SET quantite_kg = ? WHERE id = ?");
            $stmt_update->execute([$nouvelle_quantite, $dechet_existant['id']]);
        } else {
            // Insérer un nouveau type de déchet
            $stmt_insert = $pdo->prepare("INSERT INTO dechets_collectes (type_dechet, quantite_kg, id_collecte) VALUES (?, ?, ?)");
            $stmt_insert->execute([$type_dechet, $quantite_kg, $id]);
        } 
    }

    // Rediriger vers la même page pour voir immédiatement les modifications
    header("Location:collecte_edit.php?id=$id");
    exit;
}
?>