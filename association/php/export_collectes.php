<?php
require 'config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Check user role
$userId = $_SESSION['user_id'];
$query = $pdo->prepare("SELECT role FROM benevoles WHERE id = ?");
$query->execute([$userId]);
$user = $query->fetch(PDO::FETCH_ASSOC);
$userRole = $user ? $user['role'] : null;

// Only allow admin to export CSV
if ($userRole !== 'admin') {
    die("Vous n'avez pas l'autorisation d'accéder à cette page.");
}

header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename=collectes.csv');

// Output the CSV headers
$output = fopen('php://output', 'w');
fputcsv($output, ['ID', 'Date de Collecte', 'Lieu', 'Nom du Bénévole', 'Type de Déchet', 'Quantité (kg)']);

// Fetch the collection data
$stmt = $pdo->query("
    SELECT c.id, c.date_collecte, c.lieu, b.nom, d.type_dechet, d.quantite_kg
    FROM collectes c
    LEFT JOIN benevoles b ON c.id_benevole = b.id
    LEFT JOIN dechets_collectes d ON c.id = d.id_collecte
    ORDER BY c.date_collecte DESC
");

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($output, [
        $row['id'],
        $row['date_collecte'],
        $row['lieu'],
        $row['nom'],
        $row['type_dechet'],
        $row['quantite_kg']
    ]);
}

fclose($output);
exit;
?>
