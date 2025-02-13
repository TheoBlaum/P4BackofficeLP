<?php
require 'config.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int) $_GET['id'];

    try {
        $pdo = new PDO("mysql:host=localhost;dbname=gestion_collectes", "root", "", [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        // Supprimer les déchets collectés liés aux collectes du bénévole
        $stmt = $pdo->prepare("DELETE FROM dechets_collectes WHERE id_collecte IN (SELECT id FROM collectes WHERE id_benevole = :id)");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        // Supprimer d'abord les collectes associées au bénévole
        $stmt = $pdo->prepare("DELETE FROM collectes WHERE id_benevole = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        // Ensuite, supprimer le bénévole
        $stmt = $pdo->prepare("DELETE FROM benevoles WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            header("Location: volunteer_list.php?success=1");
            exit();
        } else {
            echo "Erreur lors de la suppression.";
        }
    } catch (PDOException $e) {
        die("Erreur: " . $e->getMessage());
    }
} else {
    echo "ID invalide.";
}

?>
