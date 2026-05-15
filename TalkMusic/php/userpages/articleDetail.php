<?php
require_once('../include/menuchoice.php');

//controllo se id (del articolo) manca e se è un numero
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: mainPage.php');
    exit;
}

$idUtenteLoggato = $_SESSION['userId'] ?? null;

$idArticolo = (int)$_GET['id']; 

try {
    // prendi tutti i dati dalla tabella articoli e uniscili con i dati dell'utente (nome cognome email) dove l'articolo è acnora disponibile e salvalo come vendId)
    $sql = "SELECT a.*, u.nome, u.cognome, u.email, u.idUtente as vendId
            FROM Articolo a
            JOIN Utente u ON a.fkUtenteId = u.idUtente
            WHERE a.idArticolo = :id AND a.disponibilita = TRUE";
            
    $istruzione = DBHandler::getPDO()->prepare($sql);
    $istruzione->bindParam(':id', $idArticolo, PDO::PARAM_INT);
    $istruzione->execute();
    
    $articolo = $istruzione->fetch();


    if (!$articolo) {
        header('Location: mainPage.php');
        exit;
    }


   //l'utente segue già il venditore?
  //controllo la riga(uso 1 per guardare la riga in generale) dove idFollower = me e idSeguito = lui esiste nella tabella Segue

    $sqlSeguito = "SELECT 1 FROM Segue WHERE idFollower = :me AND idSeguito = :lui";

    $istruzioneS = DBHandler::getPDO()->prepare($sqlSeguito);

    $istruzioneS->execute([':me' => $idUtenteLoggato, ':lui' => $articolo['vendId']]);

    // Trasformazione risultato in un valore Vero/Falso 

    $giaSegui = (bool)$istruzioneS->fetch(); 



    // Numero di follower del venditore

    $sqlFollower = "SELECT COUNT(*) as tot FROM Segue WHERE idSeguito = :lui";

    $istruzioneF = DBHandler::getPDO()->prepare($sqlFollower);

    $istruzioneF->execute([':lui' => $articolo['vendId']]);

    $numFollower = $istruzioneF->fetch()['tot'];




    //Articoli consigliati
    $sqlArticoli = "SELECT idArticolo, titolo, prezzo, immagine
               FROM Articolo
               WHERE fkUtenteId = :idVenditore 
               AND idArticolo != :idArticoloCorrente 
               AND disponibilita = TRUE
               ORDER BY dataPost DESC";

    $istruzioneA = DBHandler::getPDO()->prepare($sqlArticoli);

    $istruzioneA->execute([
        ':idVenditore'       => $articolo['vendId'], 
        ':idArticoloCorrente' => $idArticolo
    ]);

    $altriArticoli = $istruzioneA->fetchAll();

    // Recensioni del prodotto
    $sqlRecensioni = "SELECT r.valutazione, r.commento, r.dataRecensione, u.nome, u.cognome
                      FROM RecensioneArticolo r
                      JOIN Utente u ON r.fkRecensoreId = u.idUtente
                      WHERE r.fkArticoloId = :idArticolo
                      ORDER BY r.dataRecensione DESC";
    $istruzioneR = DBHandler::getPDO()->prepare($sqlRecensioni);
    $istruzioneR->execute([':idArticolo' => $idArticolo]);
    $listaRecensioni = $istruzioneR->fetchAll();

    // L'utente ha già recensito questo articolo?
    $giaRecensito = false;
    if ($idUtenteLoggato) {
        $sqlCheck = "SELECT 1 FROM RecensioneArticolo WHERE fkRecensoreId = :me AND fkArticoloId = :art";
        $istruzioneC = DBHandler::getPDO()->prepare($sqlCheck);
        $istruzioneC->execute([':me' => $idUtenteLoggato, ':art' => $idArticolo]);
        $giaRecensito = (bool)$istruzioneC->fetch();
    }

    } catch (PDOException $e) {
    die("Errore di connessione: " . $e->getMessage());
    }







// Generazione iniziali avatar
$inizialiVenditore = strtoupper(substr($articolo['nome'], 0, 1) . substr($articolo['cognome'], 0, 1));


// Controllo se utente loggato è lo stesso che sta vendendo l'articolo 
$isMioAnnuncio = ($idUtenteLoggato == $articolo['vendId']);


// Preparazione percorso immagine
$imgPath = $articolo['immagine'] ? '../../uploads/articoli/' . htmlspecialchars($articolo['immagine']) : null;
?>




<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../css/articleDetail.css">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <title><?php echo htmlspecialchars($articolo['titolo']); ?> - TalkMusic</title>
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

   <main class="detail-container">

    <!-- COLONNA SINISTRA: IMMAGINE -->
    <div class="image-col">
        
        <?php if ($imgPath): ?>
            <!-- Mostro l'immagine così com'è -->
            <img src="<?php echo $imgPath; ?>" class="main-image">
        <?php else: ?>
            <!-- Rettangolo grigio se manca la foto -->
            <div class="placeholder-img">Nessuna foto</div>
        <?php endif; ?>


    </div>

    <!-- COLONNA DESTRA: INFO E BOTTONI -->
    <div class="info-col">
        
        <!-- Titolo -->
        <div class="categoria-label"><?php echo $articolo['categoria']; ?></div>
        <h1 class="titolo"><?php echo $articolo['titolo']; ?></h1>
        
        <!-- Prezzo  -->
        <div class="prezzo">€ <?php echo $articolo['prezzo']; ?></div>

        <!-- Descrizione  -->
        <?php if (!empty($articolo['descrizione'])): ?>
            <div class="descrizione">
                <h3>Descrizione</h3>
                <p><?php echo nl2br($articolo['descrizione']); ?></p>
            </div>
        <?php endif; ?>

        <!-- SCHEDA VENDITORE -->
        <div class="seller-card">
            <div class="seller-avatar"><?php echo $inizialiVenditore; ?></div>
            <div class="seller-name">
                <?php echo $articolo['nome'] . ' ' . $articolo['cognome']; ?>
            </div>


            <!-- Azioni se l'utente è un altro -->
            <?php if (!$isMioAnnuncio): ?>
             
                <div class="seller-actions">
                    
                    <button class="btn-follow <?php echo $giaSegui ? 'following' : ''; ?>" id="followBtn" onclick="toggleFollow(<?php echo $articolo['vendId']; ?>)">
                        <?php echo $giaSegui ? 'Seguito' : '+ Segui'; ?>
                    </button>
                    
                </div>
            <?php else: ?>
                <div class="my-listing-badge">Questo è il tuo annuncio</div>
            <?php endif; ?>
        </div>

    </div>
</main>

<!-- RECENSIONI PRODOTTO -->
<div class="reviews-section">

    <!-- Form per lasciare recensione -->
    <div class="review-form-block">
        <h2 class="section-title">Lascia una recensione</h2>

        <?php if (isset($_GET['review_success'])): ?>
            <div class="alert-success">Recensione inviata con successo!</div>
        <?php endif; ?>

        <?php if (!$idUtenteLoggato): ?>
            <div class="empty-box">Effettua il <a href="userLoginPage.php" style="color:#00bcd4;font-weight:bold;">login</a> per lasciare una recensione.</div>
        <?php elseif ($isMioAnnuncio): ?>
            <div class="empty-box">Non puoi recensire il tuo stesso articolo.</div>
        <?php elseif ($giaRecensito): ?>
            <div class="empty-box">Hai già lasciato una recensione per questo articolo.</div>
        <?php else: ?>
            <form action="addReview.php" method="POST" class="review-form">
                <input type="hidden" name="fkArticoloId" value="<?php echo $idArticolo; ?>">
                <div>
                    <p style="font-size:14px;color:#222;margin-bottom:8px;">Valutazione *</p>
                    <div class="rating-stars">
                        <input type="radio" name="valutazione" id="s5" value="5" required><label for="s5">★</label>
                        <input type="radio" name="valutazione" id="s4" value="4"><label for="s4">★</label>
                        <input type="radio" name="valutazione" id="s3" value="3"><label for="s3">★</label>
                        <input type="radio" name="valutazione" id="s2" value="2"><label for="s2">★</label>
                        <input type="radio" name="valutazione" id="s1" value="1"><label for="s1">★</label>
                    </div>
                </div>
                <textarea name="commento" placeholder="Scrivi la tua recensione sul prodotto..."></textarea>
                <button type="submit">Invia recensione</button>
            </form>
        <?php endif; ?>
    </div>

    <!-- Lista recensioni ricevute -->
    <div class="review-list-block">
        <h2 class="section-title">Recensioni del prodotto</h2>

        <?php if (empty($listaRecensioni)): ?>
            <div class="empty-box">Nessuna recensione ancora per questo prodotto.</div>
        <?php else: ?>
            <?php foreach ($listaRecensioni as $rec): ?>
                <div class="review-item">
                    <div class="review-header">
                        <strong><?php echo htmlspecialchars($rec['nome'] . ' ' . $rec['cognome']); ?></strong>
                        <span class="review-date"><?php echo date('d/m/Y', strtotime($rec['dataRecensione'])); ?></span>
                    </div>
                    <div class="review-stars">
                        <?php echo str_repeat('★', $rec['valutazione']) . str_repeat('☆', 5 - $rec['valutazione']); ?>
                    </div>
                    <?php if (!empty($rec['commento'])): ?>
                        <div class="review-comment"><?php echo htmlspecialchars($rec['commento']); ?></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>





    <!-- JAVASCRIPT: Logica per i pulsanti -->
    <script>

        // --- FUNZIONE PER SEGUIRE/SMETTERE DI SEGUIRE ---
        function toggleFollow(idVenditore) {
            const btn = document.getElementById('followBtn');
            const staGiaSeguendo = btn.classList.contains('following');

            // Mandiamo una richiesta "invisibile" a followUser.php
            fetch('followUser.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'idSeguito=' + idVenditore + '&action=' + (staGiaSeguendo ? 'unfollow' : 'follow')
            })
            .then(risposta => risposta.json())
            .then(dati => {
                if (dati.success) {
                    // Cambiamo colore e testo al pulsante in modo dinamico
                    btn.classList.toggle('following');
                    btn.textContent = staGiaSeguendo ? '+ Segui' : 'Stai seguendo';
                }
            })
            .catch(() => alert('Errore di rete'));
        }



    </script>

</body>
</html>