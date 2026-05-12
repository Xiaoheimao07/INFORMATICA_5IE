<?php
require_once('../include/menuchoice.php');
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../css/userSigninPage.css">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <title>Registrazione - TalkMusic</title>
</head>
<body>

    <nav class="navbar">
        <a class="nav-logo" href="welcomePage.php">TALKMUSIC</a>
        <div class="nav-actions">
            
        </div>
    </nav>

    <div class="form-wrapper">
        <div class="form-card">

            <h2>Crea il tuo Account</h2>

            <?php
            if (isset($_GET['error']) && $_GET['error'] == 'email_esistente') {
                echo '<p class="msg-error">Questa email è già registrata. Prova ad accedere.</p>';
            }
            ?>

            <form action="signIn.php" id="userSigninForm" method="POST">

                <div class="input-row">
                    <div class="input-group">
                        <label for="nome">Nome</label>
                        <input type="text" id="nome" name="nome" placeholder="Nome" required>
                    </div>
                    <div class="input-group">
                        <label for="cognome">Cognome</label>
                        <input type="text" id="cognome" name="cognome" placeholder="Cognome" required>
                    </div>
                </div>

                <div class="input-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="mail" placeholder="email@esempio.it" required>
                </div>

                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Scegli una password" required>
                </div>

                <button type="submit" class="btn">Conferma e Registrati</button>

                <div class="footer-link">
                    <p>Hai già un account? <a href="userLoginPage.php">Accedi qui</a></p>
                </div>
            </form>

        </div>
    </div>

</body>
</html>
