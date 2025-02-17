<?php
// session_start(); // Démarrer la session
require 'config.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];

    // Vérifier si l'utilisateur existe dans la table `benevoles`
    $stmt = $pdo->prepare("SELECT * FROM benevoles WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Vérification du mot de passe (si hashé en BDD)
    if ($user && password_verify($password, $user['mot_de_passe'])) {
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["nom"] = $user["nom"];
        $_SESSION["role"] = $user["role"];

        header("Location: collection_list.php");
        exit;
    } else {
        $error = "Identifiants incorrects";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black">

<div class="flex justify-center items-center min-h-screen">
    <div class="bg-gradient-to-tr relative from-neutral-950 to-neutral-900 text-white w-72 p-6 rounded-2xl m-3 sm:w-96">
        <h1 class="text-3xl font-bold text-white mb-6 text-center">Connexion</h1>

        <?php if (!empty($error)) : ?>
            <div class="text-red-600 text-center mb-4">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <div>
                <label for="email" class="block text-sm font-medium mb-2 text-white">Email :</label>
                <input type="email" name="email" id="email" required class="w-full p-2 bg-neutral-900/30 backdrop-blur-lg border border-white/20 text-white p-4 rounded-lg shadow-xl max-w-xl mx-auto">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium mb-2 text-white">Mot de passe :</label>
                <input type="password" name="password" id="password" required class="w-full p-2 bg-neutral-900/30 backdrop-blur-lg border border-white/20 text-white p-4 rounded-lg shadow-xl max-w-xl mx-auto mb-4">
            </div>

            <div class="flex justify-between items-center">
                <a href="#" class="text-sm text-white hover:underline">Mot de passe oublié ?</a>
                <button type="submit" class="bg-green-500 hover:bg-white text-stone-900 font-bold px-6 py-2 rounded-full shadow-md transition duration-200">
                    Se connecter
                </button>
            </div>
        </form>
    </div>
</div>

</body>
</html>
