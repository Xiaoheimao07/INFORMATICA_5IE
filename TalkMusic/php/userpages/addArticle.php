<?php
require_once('../include/menuchoice.php');

$userId = $_SESSION['userId'];

$messaggio = ''; 
$tipoMessaggio = ''; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $titolo      = trim($_POST['titolo']);
    $descrizione = trim($_POST['descrizione']);
    $stato       = 'ottimo'; // valore default, non più nel form
    $categoria   = $_POST['categoria'];
    $prezzo      = 0; // valore default, non più nel form 

    //controllo campi obbligatori

    if (empty($titolo) || empty($categoria)) {
        $messaggio = "Per favore, compila tutti i campi obbligatori (*).";
        $tipoMessaggio = "error";
    } 
    else {

        // gestione immagine:

        $nomeImmagine = null; 

        if (!empty($_FILES['immagine']['name']) && $_FILES['immagine']['error'] === UPLOAD_ERR_OK) {

            $estensione = strtolower(pathinfo($_FILES['immagine']['name'], PATHINFO_EXTENSION));

            $nomeImmagine = uniqid('art_') . "." . $estensione;

            // Percorso assoluto dalla root del server
            $cartellaUpload = $_SERVER['DOCUMENT_ROOT'] . '/INFORMATICA_5IE/TalkMusic/uploads/articoli/';

            // Crea la cartella se non esiste
            if (!is_dir($cartellaUpload)) {
                mkdir($cartellaUpload, 0755, true);
            }

            $percorso = $cartellaUpload . $nomeImmagine;

            if (!move_uploaded_file($_FILES['immagine']['tmp_name'], $percorso)) {
                $nomeImmagine = null;
            }
        }

        // salvataggio nel database

        try {
            $sql = "INSERT INTO ArticoloInVendita 
                    (fkUtenteId, titolo, descrizione, prezzo, stato, categoria, immagine, disponibilita)
                    VALUES (:userId, :titolo, :descrizione, :prezzo, :stato, :categoria, :immagine, TRUE)";

            $istruzione = DBHandler::getPDO()->prepare($sql);
            
            $istruzione->execute([
                ':userId'      => $userId,
                ':titolo'      => $titolo,
                ':descrizione' => $descrizione,
                ':prezzo'      => $prezzo,
                ':stato'       => $stato,
                ':categoria'   => $categoria,
                ':immagine'    => $nomeImmagine
            ]);

            $messaggio = "Articolo pubblicato con successo!";
            $tipoMessaggio = "success";
            
            // Redirect dopo 2 secondi
            header("refresh:2; url=mainPage.php");

        } catch (PDOException $e) {
            $messaggio = "Errore nel database: " . $e->getMessage();
            $tipoMessaggio = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../css/addArticle.css">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <title>Vendi un articolo - TalkMusic</title>
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

    <main class="add-article-container">
        <div class="form-wrapper">
            <h1>Vendi il tuo articolo</h1>
            <p class="form-subtitle">Completa il form per mettere in vendita il tuo articolo</p>

            <!--  messaggio (errore o successo) -->
            <?php if ($messaggio): ?>
                <div class="alert alert-<?php echo $tipoMessaggio; ?>">
                    <?php echo htmlspecialchars($messaggio); ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="form-articolo" enctype="multipart/form-data">

                <div class="form-group">
                    <label for="titolo">Titolo articolo *</label>
                    <input type="text" id="titolo" name="titolo" placeholder="es: Fender Stratocaster MIM" required>
                </div>

                <div class="form-group">
                    <label for="categoria">Categoria *</label>
                    <select id="categoria" name="categoria" required>
                        <option value="">Seleziona categoria</option>
                        <option value="chitarre">Chitarre</option>
                        <option value="bassi">Bassi</option>
                        <option value="batterie">Batterie</option>
                        <option value="tastiere">Tastiere</option>
                        <option value="archi">Archi</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="descrizione">Descrizione</label>
                    <textarea id="descrizione" name="descrizione"
                              placeholder="Descrivi lo stato dell'articolo, eventuali difetti, custodia inclusa..."
                              rows="6"></textarea>
                </div>

                <!-- caricamento foto-->
                <div class="form-group">
                    <label>Foto articolo</label>
                    <div class="upload-area" id="uploadArea">
                        <input type="file" name="immagine" id="immagineInput"
                               accept="image/jpeg,image/png,image/webp"
                               onchange="previewImage(event)">
                        <div class="upload-icon">📷</div>
                        <p>Clicca per caricare una foto (JPG, PNG, WEBP — max 15MB)</p>
                    </div>

                    <!-- l'anteprima della foto -->
                    <img id="preview-img" src="" alt="Anteprima">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-submit">Pubblica articolo</button>
                    <a href="mainPage.php" class="btn-cancel">Annulla</a>
                </div>
            </form>
        </div>
    </main>



   
    <script>

        // Script per mostrare l'anteprima dell'immagine prima dell'invio 
        function previewImage(event) {

            // Prendiamo il file che l'utente ha appena selezionato

            const file = event.target.files[0];

            if (!file) return; // Se ha annullato, ci fermiamo
            
            // FileReader è uno strumento di Javascript per leggere i file nel browser

            const reader = new FileReader();
            
            // Quando il file finisce di caricare...

            reader.onload = e => {

                // Prendiamo il tag <img> nascosto

                const img = document.getElementById('preview-img');

                // Gli diamo come sorgente (src) il file appena letto

                img.src = e.target.result;
                img.style.display = 'block'; // Lo rendiamo visibile
                
                // Nascondiamo l'icona della macchina fotografica e aggiorniamo il testo col nome del file

                document.querySelector('.upload-area .upload-icon').style.display = 'none';
                document.querySelector('.upload-area p').textContent = file.name;
            };
            
            // Diciamo a FileReader di iniziare a leggere il file come se fosse un URL immagine
            reader.readAsDataURL(file);
        }

    </script>

</body>
</html>