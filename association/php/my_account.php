<?php
require 'config.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

// Vérifiez le rôle de l'utilisateur connecté
$userId = $_SESSION['user_id'];
$query = $pdo->prepare("SELECT role FROM benevoles WHERE id = ?");
$query->execute([$userId]);
$user = $query->fetch(PDO::FETCH_ASSOC);
$userRole = $user ? $user['role'] : null;

// Récupérer les infos actuelles de l'utilisateur
$stmt = $pdo->prepare("SELECT email, mot_de_passe FROM benevoles WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Vérifier le mot de passe actuel
    if (!password_verify($current_password, $user['mot_de_passe'])) {
        $message = "Mot de passe actuel incorrect.";
    } else {
        // Mise à jour de l'email
        $stmt = $pdo->prepare("UPDATE benevoles SET email = ? WHERE id = ?");
        $stmt->execute([$email, $user_id]);

        // Mise à jour du mot de passe si un nouveau est fourni
        if (!empty($new_password)) {
            if ($new_password === $confirm_password) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE benevoles SET mot_de_passe = ? WHERE id = ?");
                $stmt->execute([$hashed_password, $user_id]);
                $message = "Mise à jour réussie !";
            } else {
                $message = "Les nouveaux mots de passe ne correspondent pas.";
            }
        } else {
            $message = "Email mis à jour avec succès.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon compte</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-black">
    <div class="flex h-screen">

        <!-- Barre de navigation -->
        <div class="bg-gradient-to-tr relative from-neutral-950 to-neutral-900 text-white w-72 p-6 rounded-2xl m-3">
            <h2
                class="text-2xl font-bold mb-14 bg-gradient-to-r from-green-400 to-emerald-600 bg-clip-text text-transparent">
                Dashboard</h2>
            <ul class="space-y-11">
                <li><a href="collection_list.php"
                        class="flex items-center py-2 px-3 hover:text-white transition-colors duration-500 "><i
                            class="fas fa-tachometer-alt mr-3"></i> Tableau de bord</a></li>
                <?php if ($userRole === 'admin'): ?>             
                <li><a href="collection_add.php" class="flex items-center py-2 px-3 hover:text-white rounded-lg"><i
                            class="fas fa-plus-circle mr-3"></i> Ajouter une collecte</a></li>
                <?php endif; ?> <li><a href="chatting.php" class="flex items-center py-2 px-3 hover:text-white rounded-lg"><i
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
            <div class="absolute bottom-9 left-1/2 transform -translate-x-1/2">
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

        <!-- Contenu principal -->
        <div
            class="flex-1 p-8 overflow-y-auto bg-gradient-to-tr from-neutral-950 to-neutral-800 w-72 p-6 rounded-2xl m-3 h-fit px-12">
            <!-- Titre -->
            <h1 class="text-4xl font-bold text-white mb-6">Paramètres</h1>

            <!-- Affichage du message -->
            <?php if (!empty($message)): ?>
                <div class="text-center mb-4 <?= strpos($message, 'succès') !== false ? 'text-green-600' : 'text-red-600' ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <div class="flex gap-8">
                <form method="post"
                    class="flex-1 space-y-6 bg-neutral-900/30 backdrop-blur-lg border border-white/20 text-white p-6 rounded-lg shadow-xl mb-8">
                    <!-- Champ Email -->
                    <div>
                        <label for="email" class="block text-sm mb-2 font-medium text-green-500">Email :</label>
                        <input type="email" name="email" id="email" placeholder="<?= htmlspecialchars($user['email']) ?>" required
                            class="w-full p-2 bg-neutral-900/30 backdrop-blur-lg border border-white/20 text-white p-6 rounded-lg shadow-xl max-w-xl mx-auto">
                    </div>

                    <!-- Champ Mot de passe actuel -->
                    <div>
                        <label for="current_password" class="block text-sm mb-2 font-medium text-green-500">Mot de passe
                            actuel :</label>
                        <input type="password" name="current_password" id="current_password" required
                            class="w-full p-2 bg-neutral-900/30 backdrop-blur-lg border border-white/20 text-white p-6 rounded-lg shadow-xl max-w-xl mx-auto">
                    </div>

                    <!-- Champ Nouveau Mot de passe -->
                    <div>
                        <label for="new_password" class="block text-sm mb-2 font-medium text-green-500">Nouveau mot de passe
                            :</label>
                        <input type="password" name="new_password" id="new_password"
                            class="w-full p-2 bg-neutral-900/30 backdrop-blur-lg border border-white/20 text-white p-6 rounded-lg shadow-xl max-w-xl mx-auto">
                    </div>

                    <!-- Champ Confirmer le nouveau Mot de passe -->
                    <div>
                        <label for="confirm_password" class="block text-sm mb-2  font-medium text-green-500">Confirmer le
                            mot de
                            passe :</label>
                        <input type="password" name="confirm_password" id="confirm_password"
                            class="w-full p-2 bg-neutral-900/30 backdrop-blur-lg border border-white/20 text-white p-6 rounded-lg shadow-xl max-w-xl mx-auto">
                    </div>
                    <style>
                            input::placeholder {
                                color: rgba(255, 255, 255);
                            }
                        </style>

                    <!-- Boutons -->
                    <div class="flex justify-between items-center">
                        <a href="collection_list.php" class="text-sm text-neutral-400 hover:underline">Retour à la liste des
                            collectes</a>
                        <button type="submit"
                            class="bg-neutral-800/30 backdrop-blur-lg border border-white/20 hover:bg-neutral-600/30 text-white px-4 py-2 rounded-lg shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200">
                            Mettre à jour
                        </button>
                    </div>
                </form>

                <!-- SVG déplacé ici -->
                <div class="w-72 flex items-center">
                    <svg width="291" height="196" viewBox="0 0 291 196" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M46.8762 92.9629C49.0109 88.0423 55.9891 88.0423 58.1238 92.9629V92.9629C59.6946 96.5835 64.2275 97.7981 67.3982 95.4479V95.4479C71.7072 92.254 77.7505 95.7431 77.139 101.072V101.072C76.689 104.993 80.0073 108.311 83.9282 107.861V107.861C89.257 107.25 92.746 113.293 89.5521 117.602V117.602C87.2019 120.772 88.4165 125.305 92.0371 126.876V126.876C96.9577 129.011 96.9577 135.989 92.0371 138.124V138.124C88.4165 139.695 87.2019 144.228 89.5521 147.398V147.398C92.746 151.707 89.257 157.75 83.9282 157.139V157.139C80.0073 156.689 76.689 160.007 77.139 163.928V163.928C77.7505 169.257 71.7072 172.746 67.3982 169.552V169.552C64.2275 167.202 59.6946 168.417 58.1238 172.037V172.037C55.9891 176.958 49.0109 176.958 46.8762 172.037V172.037C45.3054 168.417 40.7725 167.202 37.6018 169.552V169.552C33.2928 172.746 27.2495 169.257 27.861 163.928V163.928C28.311 160.007 24.9927 156.689 21.0718 157.139V157.139C15.7431 157.75 12.254 151.707 15.4479 147.398V147.398C17.7981 144.228 16.5835 139.695 12.9629 138.124V138.124C8.0423 135.989 8.0423 129.011 12.9629 126.876V126.876C16.5835 125.305 17.7981 120.772 15.4479 117.602V117.602C12.254 113.293 15.743 107.25 21.0718 107.861V107.861C24.9927 108.311 28.311 104.993 27.861 101.072V101.072C27.2495 95.743 33.2928 92.254 37.6018 95.4479V95.4479C40.7725 97.7981 45.3054 96.5835 46.8762 92.9629V92.9629Z"
                            fill="#1BB761" />
                        <path
                            d="M131.883 31.866C133.578 24.6555 143.062 22.9448 147.169 29.1086V29.1086C150.192 33.644 156.65 34.1834 160.382 30.2122V30.2122C165.455 24.8152 174.524 28.0754 174.999 35.4671V35.4671C175.349 40.906 180.672 44.6022 185.89 43.0295V43.0295C192.982 40.892 199.205 48.2496 195.921 54.8887V54.8887C193.504 59.7737 196.266 65.6363 201.572 66.8834V66.8834C208.782 68.5783 210.493 78.0617 204.329 82.1692V82.1692C199.794 85.1915 199.255 91.6496 203.226 95.3824V95.3824C208.623 100.455 205.363 109.524 197.971 109.999V109.999C192.532 110.349 188.836 115.672 190.408 120.89V120.89C192.546 127.982 185.188 134.205 178.549 130.921V130.921C173.664 128.504 167.802 131.266 166.555 136.572V136.572C164.86 143.782 155.376 145.493 151.269 139.329V139.329C148.246 134.794 141.788 134.255 138.056 138.226V138.226C132.983 143.623 123.914 140.363 123.439 132.971V132.971C123.089 127.532 117.766 123.836 112.548 125.408V125.408C105.456 127.546 99.2326 120.188 102.517 113.549V113.549C104.934 108.664 102.172 102.802 96.8661 101.555V101.555C89.6556 99.8597 87.9448 90.3762 94.1086 86.2687V86.2687C98.644 83.2464 99.1834 76.7883 95.2122 73.0555V73.0555C89.8152 67.9825 93.0754 58.9142 100.467 58.4389V58.4389C105.906 58.0892 109.602 52.7661 108.029 47.5478V47.5478C105.892 40.4559 113.25 34.2326 119.889 37.5169V37.5169C124.774 39.9335 130.636 37.1716 131.883 31.866V31.866Z"
                            fill="#1BB761" />
                        <path
                            d="M232.876 103.963C235.011 99.0423 241.989 99.0423 244.124 103.963V103.963C245.695 107.583 250.228 108.798 253.398 106.448V106.448C257.707 103.254 263.75 106.743 263.139 112.072V112.072C262.689 115.993 266.007 119.311 269.928 118.861V118.861C275.257 118.25 278.746 124.293 275.552 128.602V128.602C273.202 131.772 274.417 136.305 278.037 137.876V137.876C282.958 140.011 282.958 146.989 278.037 149.124V149.124C274.417 150.695 273.202 155.228 275.552 158.398V158.398C278.746 162.707 275.257 168.75 269.928 168.139V168.139C266.007 167.689 262.689 171.007 263.139 174.928V174.928C263.75 180.257 257.707 183.746 253.398 180.552V180.552C250.228 178.202 245.695 179.417 244.124 183.037V183.037C241.989 187.958 235.011 187.958 232.876 183.037V183.037C231.305 179.417 226.772 178.202 223.602 180.552V180.552C219.293 183.746 213.25 180.257 213.861 174.928V174.928C214.311 171.007 210.993 167.689 207.072 168.139V168.139C201.743 168.75 198.254 162.707 201.448 158.398V158.398C203.798 155.228 202.583 150.695 198.963 149.124V149.124C194.042 146.989 194.042 140.011 198.963 137.876V137.876C202.583 136.305 203.798 131.772 201.448 128.602V128.602C198.254 124.293 201.743 118.25 207.072 118.861V118.861C210.993 119.311 214.311 115.993 213.861 112.072V112.072C213.25 106.743 219.293 103.254 223.602 106.448V106.448C226.772 108.798 231.305 107.583 232.876 103.963V103.963Z"
                            fill="white" />
                    </svg>
                </div>
            </div>
        </div>
    </div>
    <script src="logout.js"></script>
</body>

</html>

