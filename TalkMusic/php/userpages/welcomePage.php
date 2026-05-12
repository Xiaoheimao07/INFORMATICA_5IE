<?php
require_once('../include/menuchoice.php');
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../css/welcomePage.css">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <title>Benvenuto - TalkMusic</title>
</head>
<body>

    <nav class="navbar">
        <a class="nav-logo" href="welcomePage.php">TALKMUSIC</a>
        <div class="nav-search">
            <input type="text" placeholder="Cerca chitarre, bassi, tastiere...">
        </div>
        <div class="nav-actions">
            <?php if (isset($_SESSION['userId'])): ?>
                <a href="profilePage.php" class="nav-link">Il mio profilo</a>
                <a href="../include/logout.php" class="btn-sell">Esci</a>
            <?php else: ?>
                <a href="userLoginPage.php" class="nav-link">Accedi</a>
                <a href="userSigninPage.php" class="btn-sell">Registrati</a>
            <?php endif; ?>
        </div>
    </nav>

    <main class="main-content">

        <div class="hero">
            <h1>BENVENUTO SU TALKMUSIC</h1>
            <p>Il portale italiano delle recensioni di strumenti musicali.<br>Scopri, confronta e recensisci migliaia di strumenti.<br>Scritto da musicisti, per musicisti.</p>
        </div>

        <div class="cat-grid">

            <a href="mainPage.php?categoria=chitarre" class="cat-card">
                <div class="cat-img-wrap">
                    <img src="../../Immagini/chittaraEletrica.webp" alt="Chitarra Elettrica">
                </div>
                <span class="cat-name">Chitarra Elettrica</span>
            </a>

            <a href="mainPage.php?categoria=chitarre" class="cat-card">
                <div class="cat-img-wrap">
                    <img src="../../Immagini/chittaraAcustica.webp" alt="Chitarra Acustica">
                </div>
                <span class="cat-name">Chitarra Acustica</span>
            </a>

            <a href="mainPage.php?categoria=chitarre" class="cat-card">
                <div class="cat-img-wrap">
                    <img src="../../Immagini/chittaraClassica.webp" alt="Chitarra Classica">
                </div>
                <span class="cat-name">Chitarra Classica</span>
            </a>

            <a href="mainPage.php?categoria=batterie" class="cat-card">
                <div class="cat-img-wrap">
                    <img src="../../Immagini/batteriaAcustica.webp" alt="Batteria Acustica">
                </div>
                <span class="cat-name">Batteria Acustica</span>
            </a>

            <a href="mainPage.php?categoria=batterie" class="cat-card">
                <div class="cat-img-wrap">
                    <img src="../../Immagini/batteriaEletronica.webp" alt="Batteria Elettronica">
                </div>
                <span class="cat-name">Batteria Elettronica</span>
            </a>

            <a href="mainPage.php?categoria=tastiere" class="cat-card">
                <div class="cat-img-wrap">
                    <img src="../../Immagini/pianoForte.webp" alt="Pianoforte">
                </div>
                <span class="cat-name">Pianoforte</span>
            </a>

            <a href="mainPage.php?categoria=tastiere" class="cat-card">
                <div class="cat-img-wrap">
                    <img src="../../Immagini/tastiera.webp" alt="Tastiera">
                </div>
                <span class="cat-name">Tastiera</span>
            </a>

            <a href="mainPage.php?categoria=bassi" class="cat-card">
                <div class="cat-img-wrap">
                    <img src="../../Immagini/bassoAcustico.webp" alt="Basso Acustico">
                </div>
                <span class="cat-name">Basso Acustico</span>
            </a>

            <a href="mainPage.php?categoria=bassi" class="cat-card">
                <div class="cat-img-wrap">
                    <img src="../../Immagini/BassoEletrico.webp" alt="Basso Elettrico">
                </div>
                <span class="cat-name">Basso Elettrico</span>
            </a>

            <a href="mainPage.php?categoria=archi" class="cat-card">
                <div class="cat-img-wrap">
                    <img src="../../Immagini/violino.webp" alt="Violino">
                </div>
                <span class="cat-name">Violino</span>
            </a>

            <a href="mainPage.php?categoria=archi" class="cat-card">
                <div class="cat-img-wrap">
                    <img src="../../Immagini/violoncello.webp" alt="Violoncello">
                </div>
                <span class="cat-name">Violoncello</span>
            </a>

        </div>
    </main>

</body>
</html>
