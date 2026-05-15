<?php
require_once('../include/menuchoice.php');

// Se non c'è un ID valido nella sessione
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    //ritorno alla mainPage
    header('Location: mainPage.php');
    exit;
}

$idUtenteProfilo = (int)$_GET['id'];

$idUtenteLoggato = $_SESSION['userId'] ?? null;

// se utente preme sul suo profilo pubblico lo riporta sul privato
if ($idUtenteLoggato && $idUtenteProfilo === $idUtenteLoggato) {
    header('Location: profilePage.php');
    exit;
}

try {
    // Dati generali dell'utente
    $sqlUtente = "SELECT idUtente, nome, cognome, email, dataRegistrazione FROM Utente WHERE idUtente = :idProfilo";
    $istruzioneUtente = DBHandler::getPDO()->prepare($sqlUtente);
    $istruzioneUtente->execute([':idProfilo' => $idUtenteProfilo]);
    $datiUtente = $istruzioneUtente->fetch();


    if (!$datiUtente) {
        header('Location: mainPage.php');
        exit;
    }

    // Statistiche (Quanti articoli in vendita ha)
    $sqlArticoliCount = "SELECT COUNT(*) as totale FROM Articolo WHERE fkUtenteId = :idProfilo AND disponibilita = TRUE";
    $istruzioneCount = DBHandler::getPDO()->prepare($sqlArticoliCount);
    $istruzioneCount->execute([':idProfilo' => $idUtenteProfilo]);
    $statisticheArticoli = $istruzioneCount->fetch();

    // Statistiche (Quanti follower ha)
    $sqlFollower = "SELECT COUNT(*) as totale FROM Segue WHERE idSeguito = :idProfilo";
    $istruzioneFollower = DBHandler::getPDO()->prepare($sqlFollower);
    $istruzioneFollower->execute([':idProfilo' => $idUtenteProfilo]);
    $numeroFollower = $istruzioneFollower->fetch();

    // Lista dei suoi articoli attualmente in vendita
    $sqlArticoli = "SELECT idArticolo, titolo, prezzo, categoria, stato, immagine
                    FROM Articolo
                    WHERE fkUtenteId = :idProfilo AND disponibilita = TRUE
                    ORDER BY dataPost DESC";
    $istruzioneArticoli = DBHandler::getPDO()->prepare($sqlArticoli);
    $istruzioneArticoli->execute([':idProfilo' => $idUtenteProfilo]);
    $articoliInVendita = $istruzioneArticoli->fetchAll();


    // Variabili per utente loggato
    $giaSegui = false;



    if ($idUtenteLoggato) {
        // Controllo se lo stiamo già seguendo
        $sqlCheckFollow = "SELECT 1 FROM Segue WHERE idFollower = :me AND idSeguito = :lui";
        $istruzioneCheckFollow = DBHandler::getPDO()->prepare($sqlCheckFollow);
        $istruzioneCheckFollow->execute([':me' => $idUtenteLoggato, ':lui' => $idUtenteProfilo]);
        $giaSegui = (bool)$istruzioneCheckFollow->fetch();
    }

} catch (PDOException $e) {
    die("Errore nel recupero dati del profilo: " . $e->getMessage());
}

$inizialiAvatar = strtoupper(substr($datiUtente['nome'], 0, 1) . substr($datiUtente['cognome'], 0, 1));
?>












<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../css/publicProfile.css">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <title>Profilo di <?php echo htmlspecialchars($datiUtente['nome'] . ' ' . $datiUtente['cognome']); ?> - TalkMusic</title>

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

        <!-- Intestazione profilo -->
        <div class="profile-card">
            <div class="profile-header">
                <div class="profile-avatar"><?php echo $inizialiAvatar; ?></div>
                <h1 class="profile-name">
                    <?php echo htmlspecialchars($datiUtente['nome'] . ' ' . $datiUtente['cognome']); ?>
                </h1>
                <p class="profile-email"><?php echo htmlspecialchars($datiUtente['email']); ?></p>
            </div>

            <div class="profile-stats">
                <div class="stat">
                    <div class="stat-number"><?php echo $statisticheArticoli['totale']; ?></div>
                    <p class="stat-label">Articoli in vendita</p>
                </div>
                <div class="stat">
                    <div class="stat-number"><?php echo $numeroFollower['totale']; ?></div>
                    <p class="stat-label">Follower</p>
                </div>
            </div>

            <p class="join-date">Membro dal <?php echo date('d/m/Y', strtotime($datiUtente['dataRegistrazione'])); ?></p>

            <?php if ($idUtenteLoggato): ?>
            <div style="text-align:center; margin-top:20px;">
                <button id="followBtn"
                    onclick="toggleFollow(<?php echo $idUtenteProfilo; ?>)"
                    class="<?php echo $giaSegui ? 'btn-following' : 'btn-follow'; ?>">
                    <?php echo $giaSegui ? 'Stai seguendo' : '+ Segui'; ?>
                </button>
            </div>
            <?php endif; ?>
        </div>


        
<!-- Articoli in vendita dell'utente -->
<div class="section-block">
    <h2 class="section-title">Articoli di <?php echo $datiUtente['nome']; ?></h2>

    <?php if (empty($articoliInVendita)): ?>
        <div class="empty-box">Nessun articolo al momento.</div>
    <?php else: ?>

        <div class="product-grid">
            <?php foreach ($articoliInVendita as $articolo): ?>
                
                <div class="product-card" onclick="location.href='articleDetail.php?id=<?php echo $articolo['idArticolo']; ?>'">
                    
                    <!-- Immagine dell'articolo -->
                    <?php if ($articolo['immagine']): ?>
                        <img src="../../uploads/articoli/<?php echo $articolo['immagine']; ?>" class="card-image">
                    <?php else: ?>
                        <div class="card-image placeholder-img">NESSUNA FOTO</div>
                    <?php endif; ?>

                    <!-- Info dell'articolo -->
                    <div class="card-body">
                        <div class="card-price">€ <?php echo $articolo['prezzo']; ?></div>
                        <div class="card-title"><?php echo $articolo['titolo']; ?></div>
                        <div class="card-category"><?php echo $articolo['categoria']; ?></div>
                        <div class="stato-badge"><?php echo $articolo['stato']; ?></div>
                    </div>

                </div>

            <?php endforeach; ?>
        </div>

    <?php endif; ?>
</div>

    </main>














    <!-- JS per gestire il pulsante segui collegato a followUser.php -->
    <script>
        function toggleFollow(idVenditore) {
            const btn = document.getElementById('followBtn');
            const staGiaSeguendo = btn.classList.contains('following');

            fetch('followUser.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'idSeguito=' + idVenditore + '&action=' + (staGiaSeguendo ? 'unfollow' : 'follow')
            })
            .then(risposta => risposta.json())
            .then(dati => {
                if (dati.success) {
                    btn.classList.toggle('following');
                    btn.textContent = staGiaSeguendo ? '+ Segui' : 'Stai seguendo';
                } else {
                    alert('Errore: ' + dati.error);
                }
            })
            .catch(() => alert('Errore di rete'));
        }
    </script>

</body>
</html>