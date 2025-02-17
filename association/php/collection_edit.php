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
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-black">

<div class="flex h-screen">
        <div class="bg-gradient-to-tr relative from-neutral-950 to-neutral-900 text-white w-72 p-6 rounded-2xl m-3">
            <h2
                class="text-2xl font-bold mb-14 bg-gradient-to-r from-green-400 to-emerald-600 bg-clip-text text-transparent">
                Dashboard</h2>
            <ul class="space-y-11">
                <li><a href="collection_list.php"
                        class="flex items-center py-2 px-3 hover:text-white transition-colors duration-500"><i
                            class="fas fa-tachometer-alt mr-3"></i> Tableau de bord</a></li>
                <li><a href="collection_add.php" class="flex items-center py-2 px-3 hover:text-white rounded-lg"><i
                            class="fas fa-plus-circle mr-3"></i> Ajouter une collecte</a></li>
                <li><a href="volunteer_list.php" class="flex items-center py-2 px-3 hover:text-white rounded-lg"><i
                            class="fa-solid fa-list mr-3"></i> Liste des bénévoles</a></li>
                <li><a href="user_add.php" class="flex items-center py-2 px-3 hover:text-white rounded-lg"><i
                            class="fas fa-user-plus mr-3"></i> Ajouter un bénévole</a></li>
                <li><a href="my_account.php" class="flex items-center py-2 px-3 hover:text-white rounded-lg"><i
                            class="fas fa-cogs mr-3"></i> Mon compte</a></li>
            </ul>
            <div class="mt-6">
                <button onclick="logout()"
                    class="w-2/3 mt-10 bg-neutral-800 text-white hover:bg-neutral-700 hover:text-red-500 py-3 rounded-full shadow-md transition-colors duration-500  ">
                    Déconnexion
                </button>
            </div>
            <div class="absolute bottom-10 left-10">
                <svg width="200" height="26" viewBox="0 0 1292 178" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0 0V144.88H107C87.2284 144.88 11.1038 154.551 0 0Z" fill="#1BB761" />
                    <path
                        d="M116.818 145V75.1818H127.545V145H116.818ZM122.273 63.5455C120.182 63.5455 118.379 62.8333 116.864 61.4091C115.379 59.9848 114.636 58.2727 114.636 56.2727C114.636 54.2727 115.379 52.5606 116.864 51.1364C118.379 49.7121 120.182 49 122.273 49C124.364 49 126.152 49.7121 127.636 51.1364C129.152 52.5606 129.909 54.2727 129.909 56.2727C129.909 58.2727 129.152 59.9848 127.636 61.4091C126.152 62.8333 124.364 63.5455 122.273 63.5455ZM204.437 75.1818V84.2727H168.255V75.1818H204.437ZM178.8 58.4545H189.528V125C189.528 128.03 189.967 130.303 190.846 131.818C191.755 133.303 192.907 134.303 194.3 134.818C195.725 135.303 197.225 135.545 198.8 135.545C199.982 135.545 200.952 135.485 201.71 135.364C202.467 135.212 203.073 135.091 203.528 135L205.71 144.636C204.982 144.909 203.967 145.182 202.664 145.455C201.361 145.758 199.71 145.909 197.71 145.909C194.679 145.909 191.71 145.258 188.8 143.955C185.922 142.652 183.528 140.667 181.619 138C179.74 135.333 178.8 131.97 178.8 127.909V58.4545ZM277.817 75.1818V84.2727H241.635V75.1818H277.817ZM252.18 58.4545H262.908V125C262.908 128.03 263.347 130.303 264.226 131.818C265.135 133.303 266.287 134.303 267.68 134.818C269.105 135.303 270.605 135.545 272.18 135.545C273.362 135.545 274.332 135.485 275.09 135.364C275.847 135.212 276.453 135.091 276.908 135L279.09 144.636C278.362 144.909 277.347 145.182 276.044 145.455C274.741 145.758 273.09 145.909 271.09 145.909C268.059 145.909 265.09 145.258 262.18 143.955C259.302 142.652 256.908 140.667 254.999 138C253.12 135.333 252.18 131.97 252.18 127.909V58.4545ZM348.447 146.455C342.144 146.455 336.613 144.955 331.856 141.955C327.129 138.955 323.432 134.758 320.765 129.364C318.129 123.97 316.81 117.667 316.81 110.455C316.81 103.182 318.129 96.8333 320.765 91.4091C323.432 85.9848 327.129 81.7727 331.856 78.7727C336.613 75.7727 342.144 74.2727 348.447 74.2727C354.75 74.2727 360.265 75.7727 364.992 78.7727C369.75 81.7727 373.447 85.9848 376.083 91.4091C378.75 96.8333 380.083 103.182 380.083 110.455C380.083 117.667 378.75 123.97 376.083 129.364C373.447 134.758 369.75 138.955 364.992 141.955C360.265 144.955 354.75 146.455 348.447 146.455ZM348.447 136.818C353.235 136.818 357.174 135.591 360.265 133.136C363.356 130.682 365.644 127.455 367.129 123.455C368.613 119.455 369.356 115.121 369.356 110.455C369.356 105.788 368.613 101.439 367.129 97.4091C365.644 93.3788 363.356 90.1212 360.265 87.6364C357.174 85.1515 353.235 83.9091 348.447 83.9091C343.659 83.9091 339.72 85.1515 336.629 87.6364C333.538 90.1212 331.25 93.3788 329.765 97.4091C328.28 101.439 327.538 105.788 327.538 110.455C327.538 115.121 328.28 119.455 329.765 123.455C331.25 127.455 333.538 130.682 336.629 133.136C339.72 135.591 343.659 136.818 348.447 136.818ZM423.338 145V75.1818H433.702V85.7273H434.429C435.702 82.2727 438.005 79.4697 441.338 77.3182C444.672 75.1667 448.429 74.0909 452.611 74.0909C453.399 74.0909 454.384 74.1061 455.565 74.1364C456.747 74.1667 457.641 74.2121 458.247 74.2727V85.1818C457.884 85.0909 457.05 84.9545 455.747 84.7727C454.475 84.5606 453.126 84.4545 451.702 84.4545C448.308 84.4545 445.278 85.1667 442.611 86.5909C439.975 87.9848 437.884 89.9242 436.338 92.4091C434.823 94.8636 434.065 97.6667 434.065 100.818V145H423.338ZM518.389 146.636C513.964 146.636 509.949 145.803 506.343 144.136C502.737 142.439 499.873 140 497.752 136.818C495.631 133.606 494.57 129.727 494.57 125.182C494.57 121.182 495.358 117.939 496.934 115.455C498.51 112.939 500.616 110.97 503.252 109.545C505.889 108.121 508.798 107.061 511.98 106.364C515.192 105.636 518.419 105.061 521.661 104.636C525.904 104.091 529.343 103.682 531.98 103.409C534.646 103.106 536.586 102.606 537.798 101.909C539.04 101.212 539.661 100 539.661 98.2727V97.9091C539.661 93.4242 538.434 89.9394 535.98 87.4545C533.555 84.9697 529.873 83.7273 524.934 83.7273C519.813 83.7273 515.798 84.8485 512.889 87.0909C509.98 89.3333 507.934 91.7273 506.752 94.2727L496.57 90.6364C498.389 86.3939 500.813 83.0909 503.843 80.7273C506.904 78.3333 510.237 76.6667 513.843 75.7273C517.48 74.7576 521.055 74.2727 524.57 74.2727C526.813 74.2727 529.389 74.5455 532.298 75.0909C535.237 75.6061 538.07 76.6818 540.798 78.3182C543.555 79.9545 545.843 82.4242 547.661 85.7273C549.48 89.0303 550.389 93.4545 550.389 99V145H539.661V135.545H539.116C538.389 137.061 537.177 138.682 535.48 140.409C533.783 142.136 531.525 143.606 528.707 144.818C525.889 146.03 522.449 146.636 518.389 146.636ZM520.025 137C524.267 137 527.843 136.167 530.752 134.5C533.692 132.833 535.904 130.682 537.389 128.045C538.904 125.409 539.661 122.636 539.661 119.727V109.909C539.207 110.455 538.207 110.955 536.661 111.409C535.146 111.833 533.389 112.212 531.389 112.545C529.419 112.848 527.495 113.121 525.616 113.364C523.767 113.576 522.267 113.758 521.116 113.909C518.328 114.273 515.722 114.864 513.298 115.682C510.904 116.47 508.964 117.667 507.48 119.273C506.025 120.848 505.298 123 505.298 125.727C505.298 129.455 506.677 132.273 509.434 134.182C512.222 136.061 515.752 137 520.025 137ZM607.575 51.9091V145H596.848V51.9091H607.575ZM718.438 145V51.9091H749.892C757.195 51.9091 763.165 53.2273 767.801 55.8636C772.468 58.4697 775.923 62 778.165 66.4545C780.407 70.9091 781.529 75.8788 781.529 81.3636C781.529 86.8485 780.407 91.8333 778.165 96.3182C775.953 100.803 772.529 104.379 767.892 107.045C763.256 109.682 757.316 111 750.074 111H727.529V101H749.71C754.71 101 758.726 100.136 761.756 98.4091C764.786 96.6818 766.983 94.3485 768.347 91.4091C769.741 88.4394 770.438 85.0909 770.438 81.3636C770.438 77.6364 769.741 74.303 768.347 71.3636C766.983 68.4242 764.771 66.1212 761.71 64.4545C758.65 62.7576 754.589 61.9091 749.529 61.9091H729.71V145H718.438ZM825.113 145V75.1818H835.477V85.7273H836.204C837.477 82.2727 839.78 79.4697 843.113 77.3182C846.446 75.1667 850.204 74.0909 854.386 74.0909C855.174 74.0909 856.159 74.1061 857.34 74.1364C858.522 74.1667 859.416 74.2121 860.022 74.2727V85.1818C859.659 85.0909 858.825 84.9545 857.522 84.7727C856.25 84.5606 854.901 84.4545 853.477 84.4545C850.083 84.4545 847.053 85.1667 844.386 86.5909C841.75 87.9848 839.659 89.9242 838.113 92.4091C836.598 94.8636 835.84 97.6667 835.84 100.818V145H825.113ZM1025.38 171.182V75.1818H1035.74V86.2727H1037.01C1037.8 85.0606 1038.89 83.5151 1040.29 81.6364C1041.71 79.7273 1043.74 78.0303 1046.38 76.5455C1049.04 75.0303 1052.65 74.2727 1057.2 74.2727C1063.08 74.2727 1068.26 75.7424 1072.74 78.6818C1077.23 81.6212 1080.73 85.7879 1083.24 91.1818C1085.76 96.5758 1087.01 102.939 1087.01 110.273C1087.01 117.667 1085.76 124.076 1083.24 129.5C1080.73 134.894 1077.24 139.076 1072.79 142.045C1068.33 144.985 1063.2 146.455 1057.38 146.455C1052.89 146.455 1049.3 145.712 1046.61 144.227C1043.91 142.712 1041.83 141 1040.38 139.091C1038.92 137.152 1037.8 135.545 1037.01 134.273H1036.11V171.182H1025.38ZM1035.92 110.091C1035.92 115.364 1036.7 120.015 1038.24 124.045C1039.79 128.045 1042.04 131.182 1045.01 133.455C1047.98 135.697 1051.62 136.818 1055.92 136.818C1060.41 136.818 1064.15 135.636 1067.15 133.273C1070.18 130.879 1072.45 127.667 1073.97 123.636C1075.51 119.576 1076.29 115.061 1076.29 110.091C1076.29 105.182 1075.53 100.758 1074.01 96.8182C1072.53 92.8485 1070.27 89.7121 1067.24 87.4091C1064.24 85.0758 1060.47 83.9091 1055.92 83.9091C1051.56 83.9091 1047.89 85.0151 1044.92 87.2273C1041.95 89.4091 1039.71 92.4697 1038.2 96.4091C1036.68 100.318 1035.92 104.879 1035.92 110.091ZM1130.26 145V75.1818H1140.62V85.7273H1141.35C1142.62 82.2727 1144.92 79.4697 1148.26 77.3182C1151.59 75.1667 1155.35 74.0909 1159.53 74.0909C1160.32 74.0909 1161.3 74.1061 1162.49 74.1364C1163.67 74.1667 1164.56 74.2121 1165.17 74.2727V85.1818C1164.8 85.0909 1163.97 84.9545 1162.67 84.7727C1161.39 84.5606 1160.05 84.4545 1158.62 84.4545C1155.23 84.4545 1152.2 85.1667 1149.53 86.5909C1146.89 87.9848 1144.8 89.9242 1143.26 92.4091C1141.74 94.8636 1140.99 97.6667 1140.99 100.818V145H1130.26ZM1231.91 146.455C1225.18 146.455 1219.38 144.97 1214.5 142C1209.65 139 1205.91 134.818 1203.27 129.455C1200.67 124.061 1199.37 117.788 1199.37 110.636C1199.37 103.485 1200.67 97.1818 1203.27 91.7273C1205.91 86.2424 1209.58 81.9697 1214.27 78.9091C1219 75.8182 1224.52 74.2727 1230.82 74.2727C1234.46 74.2727 1238.05 74.8788 1241.59 76.0909C1245.14 77.303 1248.37 79.2727 1251.27 82C1254.18 84.697 1256.5 88.2727 1258.23 92.7273C1259.96 97.1818 1260.82 102.667 1260.82 109.182V113.727H1207V104.455H1249.91C1249.91 100.515 1249.12 97 1247.55 93.9091C1246 90.8182 1243.79 88.3788 1240.91 86.5909C1238.06 84.803 1234.7 83.9091 1230.82 83.9091C1226.55 83.9091 1222.85 84.9697 1219.73 87.0909C1216.64 89.1818 1214.26 91.9091 1212.59 95.2727C1210.93 98.6364 1210.09 102.242 1210.09 106.091V112.273C1210.09 117.545 1211 122.015 1212.82 125.682C1214.67 129.318 1217.23 132.091 1220.5 134C1223.77 135.879 1227.58 136.818 1231.91 136.818C1234.73 136.818 1237.27 136.424 1239.55 135.636C1241.85 134.818 1243.84 133.606 1245.5 132C1247.17 130.364 1248.46 128.333 1249.37 125.909L1259.73 128.818C1258.64 132.333 1256.8 135.424 1254.23 138.091C1251.65 140.727 1248.47 142.788 1244.68 144.273C1240.9 145.727 1236.64 146.455 1231.91 146.455Z"
                        fill="#A5A5A5" />
                    <circle cx="938.5" cy="109.5" r="31.5" stroke="#1BB761" stroke-width="8" />
                </svg>
            </div>
        </div>

    <div class="flex-1 p-8 overflow-y-auto bg-gradient-to-tr from-neutral-950 to-neutral-800 w-72 p-6 rounded-2xl m-3 h-fit px-12">
        <h1 class="text-4xl font-bold text-stone-500 mb-6">Modifier une collecte</h1>

        <div class="bg-neutral-900/30 backdrop-blur-lg border max-w-2xl mb-8 border-white/20 text-white p-6 rounded-lg shadow-xl">
            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-2 text-green-500">Date :</label>
                    <input type="date" name="date" value="<?= htmlspecialchars($collecte['date_collecte']) ?>" required class="w-full p-2 bg-neutral-900/30 backdrop-blur-lg border border-white/20 text-white p-6 rounded-lg shadow-xl max-w-xl mx-auto">
                </div>
                <style>
                        input[type="date"]::-webkit-calendar-picker-indicator {
                            filter: invert(1);
                            opacity: 0.6;
                        }
                    </style>
                <div>
                    <label class="block text-sm font-medium mb-2 text-green-500">Lieu :</label>
                    <input type="text" name="lieu" value="<?= htmlspecialchars($collecte['lieu']) ?>" required class="w-full p-2 bg-neutral-900/30 backdrop-blur-lg border border-white/20 text-white p-6 rounded-lg shadow-xl max-w-xl mx-auto">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2 text-green-500">Bénévole :</label>
                    <select name="benevole" required class="w-full p-2 bg-neutral-900/30 backdrop-blur-lg border border-white/20 text-white p-6 rounded-lg shadow-xl max-w-xl mx-auto">
                        <option value="" disabled>Sélectionnez un bénévole</option>
                        <?php foreach ($benevoles as $benevole): ?>
                            <option value="<?= $benevole['id'] ?>" <?= $benevole['id'] == $collecte['id_benevole'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($benevole['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2  text-green-500">Type de déchet :</label>
                    <select name="type_dechet" required class="w-full p-2 bg-neutral-900/30 backdrop-blur-lg border border-white/20 text-white p-6 rounded-lg shadow-xl max-w-xl mx-auto">
                        <option value="">Sélectionnez le type de déchet</option>
                        <option value="Plastique">Plastique</option>
                        <option value="Verre">Verre</option>
                        <option value="Papier">Papier</option>
                        <option value="Métal">Métal</option>
                        <option value="Organiques">Organiques</option>
                    </select>
                    <label for="quantite_kg" class="block text-sm font-medium mb-2 text-green-500">Quantité (kg) :</label>
                    <input type="number" name="quantite_kg" step="1" required class="w-full p-2 bg-neutral-900/30 backdrop-blur-lg border border-white/20 text-white p-6 rounded-lg shadow-xl max-w-xl mx-auto">
                </div>

                <div class="flex items-center">
                    <input type="radio" id="remplacer" name="action" value="remplacer" checked>
                    <label for="remplacer" class="ml-2 text-red-400">Remplacer</label>

                    <input type="radio" id="ajouter" name="action" value="ajouter" class="ml-4">
                    <label for="ajouter" class="ml-2">Ajouter</label>
                </div>

                <div class="flex justify-end space-x-4">
                    <a href="collection_list.php" class="bg-neutral-800/30 backdrop-blur-lg border border-white/20 hover:bg-neutral-600/30 text-white  hover:text-red-500  px-4 py-2 rounded-lg shadow-lg focus:outline-none focus:ring-2 focus:ring-red-500 transition duration-200">Annuler</a>
                    <button type="submit" class="bg-neutral-800/30 backdrop-blur-lg border border-white/20 hover:bg-neutral-600/30 text-white px-4 py-2 rounded-lg shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200">Modifier</button>
                </div>
            </form>
        </div>

        <!-- Tableau des déchets déjà enregistrés -->
        <div class="bg-neutral-900/30 backdrop-blur-lg mb-8 border border-white/20 text-white p-6 rounded-lg shadow-xl">
            <h2 class="text-2xl font-bold mb-4 text-white">Déchets enregistrés</h2>
            <table class="overflow-hidden bg-neutral-900/30 backdrop-blur-lg border border-white/20 border-collapse text-white w-full  mb-8 rounded-lg shadow-xl">
                <thead class="bg-gradient-to-tr from-green-500 to-emerald-600 text-neutral-950">
                    <tr >
                        <th class="px-4 py-2">Type de déchet</th>
                        <th class="px-4 py-2">Quantité (kg)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dechets_collectes as $dechet): ?>
                        <tr>
                            <td class="hover:bg-neutral-800 transition duration-500 ease-in-out px-4 py-2"><?= htmlspecialchars($dechet['type_dechet']) ?></td>
                            <td class="hover:bg-neutral-800 transition duration-500 ease-in-out px-4 py-2"><?= htmlspecialchars($dechet['quantite_kg']) ?></td>
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
