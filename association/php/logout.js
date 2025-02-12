function logout() {
    // Envoyer une requête à logout.php pour détruire la session
    fetch('logout.php', {
        method: 'GET', // Utilisation de la méthode GET (on peut aussi utiliser POST selon la configuration du serveur)
    })
    .then(response => {
        // Une fois la déconnexion réussie, rediriger l'utilisateur vers la page de connexion
        window.location.href = "login.php"; // Redirection vers login.php (ou toute autre page souhaitée)
    })
    .catch(error => {
        // En cas d'erreur lors de la requête, afficher un message d'erreur dans la console
        console.error('La déconnexion a échoué :', error);
    });
}
