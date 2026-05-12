<?php
require_once('../include/menuchoice.php');

$idUtenteLoggato = $_SESSION['userId'];





try {
    // Dati Personali 
    $sqlUtente = "SELECT nome, cognome, email, dataRegistrazione FROM Utente WHERE idUtente = :userId";
    $istruzioneUtente = DBHandler::getPDO()->prepare($sqlUtente);
    $istruzioneUtente->execute([':userId' => $idUtenteLoggato]);
    $datiUtente = $istruzioneUtente->fetch();

    if (!$datiUtente) {
        // Utente non trovato nel DB: sessione non valida, forza logout
        session_destroy();
        header('Location: userLoginPage.php');
        exit;
    }




    //  Conteggio dei tuoi articoli in vendita 
    $sqlConteggioArticoli = "SELECT COUNT(*) as totale FROM ArticoloInVendita WHERE fkUtenteId = :userId";
    $istruzioneConteggio = DBHandler::getPDO()->prepare($sqlConteggioArticoli);
    $istruzioneConteggio->execute([':userId' => $idUtenteLoggato]);
    $statisticheArticoli = $istruzioneConteggio->fetch();





    // Numero follower 
    $sqlSeguaci = "SELECT COUNT(*) as totale FROM Segue WHERE idSeguito = :userId";
    $istruzioneSeguaci = DBHandler::getPDO()->prepare($sqlSeguaci);
    $istruzioneSeguaci->execute([':userId' => $idUtenteLoggato]);
    $numeroFollower = $istruzioneSeguaci->fetch();

    // Numero utenti seguiti
    $sqlSeguiti = "SELECT COUNT(*) as totale FROM Segue WHERE idFollower = :userId";
    $istruzioneSeguiti = DBHandler::getPDO()->prepare($sqlSeguiti);
    $istruzioneSeguiti->execute([':userId' => $idUtenteLoggato]);
    $numeroSeguiti = $istruzioneSeguiti->fetch();




    //  La lista articoli in vendita 
    $sqlMieiArticoli = "SELECT idArticolo, titolo, prezzo, categoria, stato, immagine
                        FROM ArticoloInVendita 
                        WHERE fkUtenteId = :userId ORDER BY dataPost DESC";

    $istruzioneMieiArticoli = DBHandler::getPDO()->prepare($sqlMieiArticoli);
    $istruzioneMieiArticoli->execute([':userId' => $idUtenteLoggato]);



    $mieiArticoli = $istruzioneMieiArticoli->fetchAll();









} catch (PDOException $e) {
    die("Errore nel recupero dati del profilo: " . $e->getMessage());
}
?>






<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../css/profilePage.css">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <title>Il tuo Profilo - TalkMusic</title>
</head>
<body>

    <nav class="navbar">
        <a class="nav-logo" href="welcomePage.php">TALKMUSIC</a>
        <div class="nav-actions" style="margin-left:auto; display:flex; align-items:center; gap:16px;">
            <?php if (isset($_SESSION['userId'])): ?>
                <a href="profilePage.php" class="nav-link" style="color:#aaa;text-decoration:none;font-size:14px;">Profilo</a>
                <a href="../include/logout.php" class="nav-link" style="color:#aaa;text-decoration:none;font-size:14px;">Esci</a>
            <?php else: ?>
                <a href="userLoginPage.php" class="nav-link" style="color:#aaa;text-decoration:none;font-size:14px;">Accedi</a>
                <a href="userSigninPage.php" class="btn-sell" style="background:#00bcd4;color:#111;padding:7px 16px;border-radius:20px;text-decoration:none;font-size:13px;font-weight:bold;">Registrati</a>
            <?php endif; ?>
        </div>
    </nav>

    <main class="container">

        <!-- card principale del profilo -->

        <div class="profile-card">
            <div class="profile-header">
                <div class="profile-avatar">
                    
                    <?php echo strtoupper(substr($datiUtente['nome'], 0, 1) . substr($datiUtente['cognome'], 0, 1)); ?>
                </div>

                   
                <h1 class="profile-name">
                    <?php echo htmlspecialchars($datiUtente['nome'] . ' ' . $datiUtente['cognome']); ?>
                </h1>

            
                <p class="profile-email">
                    <?php echo htmlspecialchars($datiUtente['email']); ?>
                </p>

            </div>

            <!-- Statistiche  -->
            <div class="profile-stats">
                <div class="stat">
                    <div class="stat-number"><?php echo $statisticheArticoli['totale']; ?></div>
                    <p class="stat-label">Articoli pubblicati</p>
                </div>

                <div class="stat">
                    <div class="stat-number"><?php echo $numeroFollower['totale']; ?></div>
                    <p class="stat-label">Follower</p>
                </div>

                <div class="stat">
                    <a href="followingPage.php" style="text-decoration:none;color:inherit;">
                        <div class="stat-number"><?php echo $numeroSeguiti['totale']; ?></div>
                        <p class="stat-label">Seguiti</p>
                    </a>
                </div>


            </div>


        </div>

<!--i tuoi annunci -->
<div class="section-block">
    <h2 class="section-title">Articoli pubblicati</h2>

    <?php if (empty($mieiArticoli)): ?>
        <div class="empty-box">Non hai ancora pubblicato nessun articolo.</div>
    <?php else: ?>
        <?php foreach ($mieiArticoli as $annuncio): ?>
            <div class="article-item">
                <!-- Immagine a sinistra -->
                <div class="article-item-img">
                    <?php if (!empty($annuncio['immagine'])): ?>
                        <img src="../../Immagini/<?php echo htmlspecialchars($annuncio['immagine']); ?>" alt="<?php echo htmlspecialchars($annuncio['titolo']); ?>">
                    <?php else: ?>
                        <div class="article-item-noimg">🎵</div>
                    <?php endif; ?>
                </div>

                <!-- Info a destra -->
                <div class="article-item-info">
                    <strong><?php echo htmlspecialchars($annuncio['titolo']); ?></strong>
                    <span class="article-item-price">€ <?php echo number_format($annuncio['prezzo'], 2, ',', '.'); ?></span>
                    <span class="article-item-cat"><?php echo htmlspecialchars($annuncio['categoria']); ?></span>
                </div>

                <!-- Bottone elimina -->
                <a href="deleteArticle.php?id=<?php echo $annuncio['idArticolo']; ?>"
                   onclick="return confirm('Sei sicuro di voler eliminare definitivamente questo annuncio?')"
                   class="btn-delete">
                    Elimina
                </a>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>



    </main>
</body>
</html>