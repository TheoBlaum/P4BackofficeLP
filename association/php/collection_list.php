<?php
require 'config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Vérifiez le rôle de l'utilisateur connecté
$userId = $_SESSION['user_id'];
$query = $pdo->prepare("SELECT role FROM benevoles WHERE id = ?");
$query->execute([$userId]);
$user = $query->fetch(PDO::FETCH_ASSOC);
$userRole = $user ? $user['role'] : null;

try {
    $stmt = $pdo->query("
        SELECT c.id, c.date_collecte, c.lieu, b.nom
        FROM collectes c
        LEFT JOIN benevoles b ON c.id_benevole = b.id
        ORDER BY c.date_collecte DESC
    ");

    $collectes = $stmt->fetchAll();

    // Récupérer l'admin
    $query = $pdo->prepare("SELECT nom FROM benevoles WHERE role = 'admin' LIMIT 1");
    $query->execute();
    $admin = $query->fetch(PDO::FETCH_ASSOC);
    $adminNom = $admin ? htmlspecialchars($admin['nom']) : 'Aucun administrateur trouvé';

    // Récupérer les quantités totales de chaque type de déchet par collecte
    $stmt2 = $pdo->query("
        SELECT id_collecte, type_dechet, SUM(quantite_kg) AS total_kg
        FROM dechets_collectes
        GROUP BY id_collecte, type_dechet
    ");

    // Récupérer les quantités totales de chaque type de déchet par collecte
    $dechets_collectes = [];
    while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
        $dechets_collectes[$row['id_collecte']][$row['type_dechet']][] = $row;
    }

    $stmt3 = $pdo->query("
    SELECT  SUM(quantite_kg) AS total_kg
    FROM dechets_collectes
");
    $total2 = $stmt3->fetch(PDO::FETCH_ASSOC)['total_kg'];



} catch (PDOException $e) {
    echo "Erreur de base de données : " . $e->getMessage();
    exit;
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=0.80">
    <title>Liste des Collectes</title>

    <head>
        <link
            href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&family=Lora:wght@400;700&family=Montserrat:wght@300;400;700&family=Open+Sans:wght@300;400;700&family=Poppins:wght@300;400;700&family=Playfair+Display:wght@400;700&family=Raleway:wght@300;400;700&family=Nunito:wght@300;400;700&family=Merriweather:wght@300;400;700&family=Oswald:wght@300;400;700&display=swap"
            rel="stylesheet">
    </head>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="bg-black">
    <div class="flex">
        <!-- Barre de navigation -->
        <div class="fixed top-0 h-screen p-3">
            <div class="bg-gradient-to-tr h-full from-neutral-950 to-neutral-900 text-white w-72 p-6 rounded-2xl">
                <h2
                    class="text-2xl font-bold mb-14 bg-gradient-to-r from-green-400 to-emerald-600 bg-clip-text text-transparent">
                    Dashboard
                </h2>

                <ul class="space-y-11">

                    <li><a href="collection_list.php"
                            class="flex items-center py-2 px-3 hover:text-white transition-colors duration-500"><i
                                class="fas fa-tachometer-alt mr-3"></i> Tableau de bord</a></li>

                    <?php if ($userRole === 'admin'): ?>
                        <li><a href="collection_add.php" class="flex items-center py-2 px-3 hover:text-white rounded-lg"><i
                                    class="fas fa-plus-circle mr-3"></i> Ajouter une collecte</a></li>
                    <?php endif; ?>

                    <li><a href="chatting.php" class="flex items-center py-2 px-3 hover:text-white rounded-lg"><i
                                class="fa-solid fa-message mr-3"></i> Messagerie</a></li>

                    <li><a href="volunteer_list.php" class="flex items-center py-2 px-3 hover:text-white rounded-lg"><i
                                class="fa-solid fa-list mr-3"></i> Liste des bénévoles</a></li>

                    <?php if ($userRole === 'admin'): ?>
                        <li><a href="user_add.php" class="flex items-center py-2 px-3 hover:text-white rounded-lg"><i
                                    class="fas fa-user-plus mr-3"></i> Ajouter un bénévole</a></li>
                  
                        <li><a href="message_add.php" class="flex items-center py-2 px-3 hover:text-white rounded-lg"><i
                                    class="fa-solid fa-pen-to-square mr-3"></i> Ajouter un message</a></li>
                    <?php endif; ?>
                      
                    <?php if ($userRole === 'admin'): ?>
                         <li><a href="budget.php" class="flex items-center py-2 px-3 hover:text-white rounded-lg"><i
                                  class="fas fa-coins mr-3"></i> Budget</a></li>
                    <?php endif; ?>


                    <li><a href="my_account.php" class="flex items-center py-2 px-3 hover:text-white rounded-lg"><i
                                class="fas fa-cogs mr-3"></i> Mon compte</a></li>

                </ul>

                <div class="mt-6">
                    <button onclick="logout()"
                        class="w-2/3 mt-10 bg-neutral-800 text-white hover:bg-neutral-700 hover:text-red-500 py-3 rounded-full shadow-md transition-colors duration-500  ">
                        Déconnexion
                    </button>
                </div>
                <div class="absolute bottom-12 left-1/2 transform -translate-x-1/2">
                    <svg width="200" height="52" viewBox="0 0 1276 323" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M45.5556 0C45.5556 0 -56.9444 144.88 45.5556 144.88C148.056 144.88 154.556 144.88 154.556 144.88C173.799 129.027 181.5 123 183.442 121.5C176.5 125 61.7059 195.025 45.5556 0Z"
                            fill="#1BB761" />
                        <path
                            d="M214.936 77.8H225.496V145H214.936V77.8ZM281.021 87.4H269.981V77.8H302.621V87.4H291.581V145H281.021V87.4ZM356.287 87.4H345.247V77.8H377.887V87.4H366.847V145H356.287V87.4ZM437.249 145.96C432.065 145.96 428.097 144.488 425.345 141.544C422.593 138.6 421.217 134.44 421.217 129.064V93.736C421.217 88.36 422.593 84.2 425.345 81.256C428.097 78.312 432.065 76.84 437.249 76.84C442.433 76.84 446.401 78.312 449.153 81.256C451.905 84.2 453.281 88.36 453.281 93.736V129.064C453.281 134.44 451.905 138.6 449.153 141.544C446.401 144.488 442.433 145.96 437.249 145.96ZM437.249 136.36C440.897 136.36 442.721 134.152 442.721 129.736V93.064C442.721 88.648 440.897 86.44 437.249 86.44C433.601 86.44 431.777 88.648 431.777 93.064V129.736C431.777 134.152 433.601 136.36 437.249 136.36ZM499.782 77.8H515.43C520.87 77.8 524.838 79.08 527.334 81.64C529.83 84.136 531.078 88.008 531.078 93.256V97.384C531.078 104.36 528.774 108.776 524.166 110.632V110.824C526.726 111.592 528.518 113.16 529.542 115.528C530.63 117.896 531.174 121.064 531.174 125.032V136.84C531.174 138.76 531.238 140.328 531.366 141.544C531.494 142.696 531.814 143.848 532.326 145H521.574C521.19 143.912 520.934 142.888 520.806 141.928C520.678 140.968 520.614 139.24 520.614 136.744V124.456C520.614 121.384 520.102 119.24 519.078 118.024C518.118 116.808 516.422 116.2 513.99 116.2H510.342V145H499.782V77.8ZM514.182 106.6C516.294 106.6 517.862 106.056 518.886 104.968C519.974 103.88 520.518 102.056 520.518 99.496V94.312C520.518 91.88 520.07 90.12 519.174 89.032C518.342 87.944 516.998 87.4 515.142 87.4H510.342V106.6H514.182ZM586.021 77.8H600.325L611.269 145H600.709L598.789 131.656V131.848H586.789L584.869 145H575.077L586.021 77.8ZM597.541 122.728L592.837 89.512H592.645L588.037 122.728H597.541ZM655.752 77.8H666.312V135.4H683.688V145H655.752V77.8ZM782.847 77.8H798.399C803.647 77.8 807.583 79.208 810.207 82.024C812.831 84.84 814.143 88.968 814.143 94.408V101.032C814.143 106.472 812.831 110.6 810.207 113.416C807.583 116.232 803.647 117.64 798.399 117.64H793.407V145H782.847V77.8ZM798.399 108.04C800.127 108.04 801.407 107.56 802.239 106.6C803.135 105.64 803.583 104.008 803.583 101.704V93.736C803.583 91.432 803.135 89.8 802.239 88.84C801.407 87.88 800.127 87.4 798.399 87.4H793.407V108.04H798.399ZM859.238 77.8H874.886C880.326 77.8 884.294 79.08 886.79 81.64C889.286 84.136 890.534 88.008 890.534 93.256V97.384C890.534 104.36 888.23 108.776 883.622 110.632V110.824C886.182 111.592 887.974 113.16 888.998 115.528C890.086 117.896 890.63 121.064 890.63 125.032V136.84C890.63 138.76 890.694 140.328 890.822 141.544C890.95 142.696 891.27 143.848 891.782 145H881.03C880.646 143.912 880.39 142.888 880.262 141.928C880.134 140.968 880.07 139.24 880.07 136.744V124.456C880.07 121.384 879.558 119.24 878.534 118.024C877.574 116.808 875.878 116.2 873.446 116.2H869.798V145H859.238V77.8ZM873.638 106.6C875.75 106.6 877.318 106.056 878.342 104.968C879.43 103.88 879.974 102.056 879.974 99.496V94.312C879.974 91.88 879.526 90.12 878.63 89.032C877.798 87.944 876.454 87.4 874.598 87.4H869.798V106.6H873.638ZM1015.11 77.8H1030.67C1035.91 77.8 1039.85 79.208 1042.47 82.024C1045.1 84.84 1046.41 88.968 1046.41 94.408V101.032C1046.41 106.472 1045.1 110.6 1042.47 113.416C1039.85 116.232 1035.91 117.64 1030.67 117.64H1025.67V145H1015.11V77.8ZM1030.67 108.04C1032.39 108.04 1033.67 107.56 1034.51 106.6C1035.4 105.64 1035.85 104.008 1035.85 101.704V93.736C1035.85 91.432 1035.4 89.8 1034.51 88.84C1033.67 87.88 1032.39 87.4 1030.67 87.4H1025.67V108.04H1030.67ZM1091.51 77.8H1107.15C1112.59 77.8 1116.56 79.08 1119.06 81.64C1121.55 84.136 1122.8 88.008 1122.8 93.256V97.384C1122.8 104.36 1120.5 108.776 1115.89 110.632V110.824C1118.45 111.592 1120.24 113.16 1121.27 115.528C1122.35 117.896 1122.9 121.064 1122.9 125.032V136.84C1122.9 138.76 1122.96 140.328 1123.09 141.544C1123.22 142.696 1123.54 143.848 1124.05 145H1113.3C1112.91 143.912 1112.66 142.888 1112.53 141.928C1112.4 140.968 1112.34 139.24 1112.34 136.744V124.456C1112.34 121.384 1111.83 119.24 1110.8 118.024C1109.84 116.808 1108.15 116.2 1105.71 116.2H1102.07V145H1091.51V77.8ZM1105.91 106.6C1108.02 106.6 1109.59 106.056 1110.61 104.968C1111.7 103.88 1112.24 102.056 1112.24 99.496V94.312C1112.24 91.88 1111.79 90.12 1110.9 89.032C1110.07 87.944 1108.72 87.4 1106.87 87.4H1102.07V106.6H1105.91ZM1169.58 77.8H1198.38V87.4H1180.14V105.16H1194.64V114.76H1180.14V135.4H1198.38V145H1169.58V77.8Z"
                            fill="white" />
                        <path
                            d="M952.581 145.96C947.397 145.96 943.429 144.488 940.677 141.544C937.925 138.6 936.549 134.44 936.549 129.064V93.736C936.549 88.36 937.925 84.2 940.677 81.256C943.429 78.312 947.397 76.84 952.581 76.84C957.765 76.84 961.733 78.312 964.485 81.256C967.237 84.2 968.613 88.36 968.613 93.736V129.064C968.613 134.44 967.237 138.6 964.485 141.544C961.733 144.488 957.765 145.96 952.581 145.96ZM952.581 136.36C956.229 136.36 958.053 134.152 958.053 129.736V93.064C958.053 88.648 956.229 86.44 952.581 86.44C948.933 86.44 947.109 88.648 947.109 93.064V129.736C947.109 134.152 948.933 136.36 952.581 136.36Z"
                            fill="#1BB761" />
                        <path
                            d="M763.274 176.239C763.274 176.239 1385.74 182.965 1227.25 181.253C1068.76 179.541 1387.22 323.489 1227.73 321.766C1068.24 320.042 763.274 176.239 763.274 176.239Z"
                            fill="white" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Contenu principal -->
        <div class="flex-1 ml-[324px]">
            <div class="bg-gradient-to-tr from-neutral-950 to-neutral-800 p-8 min-h-screen rounded-2xl m-3">
                <!-- Titre -->
                <h1 class="text-4xl font-bold text-white mb-6">Liste des Collectes de Déchets</h1>

                <!-- Message de notification -->
                <?php if (isset($_GET['message'])): ?>
                    <div class="bg-green-100 text-green-800 p-4 rounded-md mb-6">
                        <?= htmlspecialchars($_GET['message']) ?>
                    </div>
                <?php endif; ?>

                <!-- Cartes d'informations -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Nombre total de collectes -->
                    <div
                        class="bg-neutral-900/30 backdrop-blur-lg border border-white/20 text-white p-6 rounded-lg shadow-xl">
                        <h3 class="text-xl font-semibold text-white mb-3">Total des Collectes</h3>
                        <p class="text-3xl font-bold text-green-400"><?= count($collectes) ?></p>
                    </div>
                    <!-- Dernière collecte -->
                    <?php if (!empty($collectes)): ?>
                        <div
                            class="bg-neutral-900/30 backdrop-blur-lg border border-white/20 text-white p-6 rounded-lg shadow-xl">
                            <h3 class="text-xl font-semibold text-white mb-3">Dernière Collecte</h3>
                            <p class="text-lg text-gray-400"><?= htmlspecialchars($collectes[0]['lieu']) ?></p>
                            <p class="text-lg text-gray-400"><?= date('d/m/Y', strtotime($collectes[0]['date_collecte'])) ?>
                            </p>
                        </div>
                    <?php endif; ?>
                    <!-- Bénévole Responsable -->
                    <div
                        class="bg-neutral-900/30 backdrop-blur-lg border border-white/20 text-white p-6 rounded-lg shadow-xl">
                        <h3 class="text-xl font-semibold text-white mb-3">Bénévole Admin</h3>
                        <p class="text-lg text-gray-400"><?= $adminNom ?></p>
                    </div>

                    <div
                        class="bg-neutral-900/30 backdrop-blur-lg border border-white/20 text-white p-6 rounded-lg shadow-xl">
                        <h3 class="text-xl font-semibold text-white mb-3">Total Déchets Collectés (kg)</h3>
                        <p class="text-3xl font-bold text-green-400"><?= $total2 ?> kg</p>
                    </div>

                </div>

                <!-- Tableau des collectes -->
                <div
                    class="overflow-hidden bg-neutral-900/30 backdrop-blur-lg border border-white/20 text-white  mb-8 rounded-lg shadow-xl">
                    <table class="w-full table-auto border-collapse">
                        <thead class="bg-gradient-to-tr from-green-500 to-emerald-600 text-neutral-950">
                            <tr>
                                <th class="py-3 px-4 text-left">Bénévole Responsable</th>
                                <th class="py-3 px-4 text-left">Date</th>
                                <th class="py-3 px-4 text-left">Lieu</th>
                                <th class="py-3 px-4 text-left">Plastique</th>
                                <th class="py-3 px-4 text-left">Verre</th>
                                <th class="py-3 px-4 text-left">Papier</th>
                                <th class="py-3 px-4 text-left">Metal</th>
                                <th class="py-3 px-4 text-left">Organiques</th>
                                <th class="py-3 px-4 text-left">Poids Total</th>
                                <?php if ($userRole === 'admin'): ?>
                                    <th class="py-3 px-4 text-left">Actions</th> <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($collectes as $collecte): ?>

                                <?php
                                $id_collecte = $collecte['id'];
                                // Récupération des poids des différents types de déchets pour la collecte
                                $plastique = $dechets_collectes[$id_collecte]['Plastique'][0]['total_kg'] ?? 0;
                                $verre = $dechets_collectes[$id_collecte]['Verre'][0]['total_kg'] ?? 0;
                                $papier = $dechets_collectes[$id_collecte]['Papier'][0]['total_kg'] ?? 0;
                                $metal = $dechets_collectes[$id_collecte]['Métal'][0]['total_kg'] ?? 0;
                                $organiques = $dechets_collectes[$id_collecte]['Organiques'][0]['total_kg'] ?? 0;
                                $total = $plastique + $verre + $papier + $metal + $organiques;
                                ?>
                                <tr class="hover:bg-neutral-800 transition duration-500 ease-in-out">
                                    <td class="py-3 px-4">
                                        <?= htmlspecialchars($collecte['nom']) ?: 'Aucun bénévole' ?>
                                    </td>
                                    <td class="py-3 px-4 text-stone-400">
                                        <?= date('d/m/Y', strtotime($collecte['date_collecte'])) ?>
                                    </td>
                                    <td class="py-3 px-4 text-stone-400"><?= htmlspecialchars($collecte['lieu']) ?></td>
                                    <td class="py-3 px-4 text-stone-400"><?= $plastique ?> kg</td>
                                    <td class="py-3 px-4 text-stone-400"><?= $verre ?> kg</td>
                                    <td class="py-3 px-4 text-stone-400"><?= $papier ?> kg</td>
                                    <td class="py-3 px-4 text-stone-400"><?= $metal ?> kg</td>
                                    <td class="py-3 px-4 text-stone-400"><?= $organiques ?> kg</td>
                                    <td class="py-3 px-4 text-white font-bold"><?= $total ?> kg</td>

                                    <?php if ($userRole === 'admin'): ?>
                                        <td class="py-3 px-4 flex space-x-2"><a
                                                href="collection_edit.php?id=<?= $collecte['id'] ?>"
                                                class="bg-neutral-800/30 backdrop-blur-lg border border-white/20 hover:bg-neutral-600/30 text-white px-4 py-2 rounded-lg shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200">Modifier</a>
                                            <a href="collection_delete.php?id=<?= $collecte['id'] ?>"
                                                class="bg-neutral-800/30 backdrop-blur-lg border border-white/20 hover:bg-neutral-600/30 text-white  hover:text-red-500  px-4 py-2 rounded-lg shadow-lg focus:outline-none focus:ring-2 focus:ring-red-500 transition duration-200"
                                                onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette collecte ?');">Supprimer
                                            </a>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <?php if ($userRole === 'admin'): ?>
                        <div class="p-6 flex justify-end">
                            <a href="export_collectes.php"
                                class="bg-neutral-800 text-white hover:bg-neutral-700 hover:text-green-500 py-2 px-4 rounded transition-colors duration-500">
                                <i class="fas fa-download mr-2"></i>Exporter le tableau en CSV
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Graphique -->
                <div
                    class="overflow-hidden bg-neutral-900/30 backdrop-blur-lg border border-white/20 text-white w-1/2 rounded-lg shadow-xl p-6">
                    <h2 class="text-xl font-semibold mb-4">Répartition des types de déchets collectés</h2>

                    <canvas id="radarChart"></canvas>     <!-- Zone du graphique -->
                    <?php if ($userRole === 'admin'): ?>    <!--  Bouton télécharger uniquement possible pour les admin -->
                        <div class="mt-6 flex justify-end">
                            <button onclick="downloadChart()"
                                class="bg-neutral-800 text-white hover:bg-neutral-700 hover:text-green-500 py-2 px-4 rounded transition-colors duration-500">
                                <i class="fas fa-download mr-2"></i>Exporter le graphique
                            </button>
                        </div>
                    <?php endif; ?>

                    <script>
                        // Récupérer le contexte du canvas
                        const ctx = document.getElementById('radarChart').getContext('2d');

                        // Calculer les totaux par type de déchet
                        const data = {
                            labels: ['Plastique', 'Verre', 'Papier', 'Métal', 'Organiques'],
                            datasets: [{
                                label: 'Quantité totale (kg)',
                                data: [
                                    <?php
                                    // Requête SQL pour obtenir la somme des quantités par type de déchet
                                    $stmt = $pdo->query("SELECT type_dechet, SUM(quantite_kg) as total 
                                                        FROM dechets_collectes 
                                                        GROUP BY type_dechet");
                                    
                                    // Récupération des résultats dans un tableau associatif
                                    $totals = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                    
                                    // Création d'un tableau pour stocker les totaux par type de déchet
                                    $data = array();
                                    
                                    // Remplissage du tableau avec les totaux
                                    foreach ($totals as $total) {
                                        $data[$total['type_dechet']] = $total['total'];
                                    }
                                    
                                    // Affichage des valeurs dans l'ordre correspondant aux labels
                                    // L'opérateur ?? 0 permet de retourner 0 si le type de déchet n'existe pas
                                    echo $data['Plastique'] ?? 0, ", ";  // Total pour le plastique
                                    echo $data['Verre'] ?? 0, ", ";      // Total pour le verre
                                    echo $data['Papier'] ?? 0, ", ";     // Total pour le papier
                                    echo $data['Métal'] ?? 0, ", ";      // Total pour le métal
                                    echo $data['Organiques'] ?? 0;       // Total pour les déchets organiques
                                    ?>
                                ],
                                backgroundColor: 'rgba(27, 183, 97, 0.2)',
                                borderColor: 'rgba(27, 183, 97, 1)',
                                borderWidth: 2,
                                pointBackgroundColor: 'rgba(27, 183, 97, 1)',
                                pointBorderColor: '#fff',
                                pointHoverBackgroundColor: '#fff',
                                pointHoverBorderColor: 'rgba(27, 183, 97, 1)'
                            }]
                        };

                        // Configuration du radar chart (chart.js)
                        const config = {
                            type: 'radar',
                            data: data,
                            options: {
                                responsive: true,
                                scales: {
                                    r: {
                                        angleLines: {
                                            color: 'rgba(255, 255, 255, 0.1)'
                                        },
                                        grid: {
                                            color: 'rgba(255, 255, 255, 0.1)'
                                        },
                                        ticks: {
                                            color: 'rgba(255, 255, 255, 0.8)',
                                            backdropColor: 'transparent'
                                        },
                                        pointLabels: {
                                            color: 'rgba(255, 255, 255, 0.8)'
                                        }
                                    }
                                },
                                plugins: {
                                    legend: {
                                        labels: {
                                            color: 'rgba(255, 255, 255, 0.8)'
                                        }
                                    }
                                }
                            }
                        };

                        // Création du radar chart
                        new Chart(ctx, config);

                        // Fonction pour télécharger le graphique
                        function downloadChart() {
                            const canvas = document.getElementById('radarChart');

                            // Créer un lien temporaire
                            const link = document.createElement('a');
                            link.download = 'repartition-dechets.png';

                            // Convertir le canvas en URL de données
                            link.href = canvas.toDataURL('image/png');

                            // Simuler un clic pour déclencher le téléchargement
                            document.body.appendChild(link);
                            link.click();
                            document.body.removeChild(link);
                        }
                    </script>
                </div>
            </div>
        </div>
    </div>
    <script src="logout.js"></script>
</body>

</html>