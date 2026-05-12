<?php
require_once('../include/menuchoice.php');
// Salva la pagina di provenienza (se arriva da una pagina interna, non da login/signin)
if (!isset($_SESSION['redirect_after_login'])) {
    $ref = $_SERVER['HTTP_REFERER'] ?? '';
    if ($ref && strpos($ref, 'userLoginPage') === false && strpos($ref, 'userSigninPage') === false && strpos($ref, 'login.php') === false) {
        $_SESSION['redirect_after_login'] = $ref;
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../css/userLoginPage.css">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <title>Login - TalkMusic</title>
</head>
<body>

    <nav class="navbar">
        <a class="nav-logo" href="welcomePage.php">TALKMUSIC</a>
        <div class="nav-actions">
            
        </div>
    </nav>

    <div class="form-wrapper">
        <div class="form-card">

            <h2>Accedi a TalkMusic</h2>

            <?php
            if (isset($_GET['error'])) {
                echo '<p class="msg-error">Errore: controlla email e password e riprova.</p>';
            }
            if (isset($_GET['reg']) && $_GET['reg'] == 'success') {
                echo '<p class="msg-success">Registrazione completata! Ora puoi accedere.</p>';
            }
            ?>

            <form action="login.php" id="userLoginForm" method="POST">
                <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($_SESSION['redirect_after_login'] ?? 'welcomePage.php'); ?>">

                <div class="input-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="latua@email.it" required>
                </div>

                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="••••••••" required>
                </div>

                <button type="submit" class="btn">Accedi</button>

                <div class="footer-link">
                    <p>Non hai un account? <a href="userSigninPage.php">Registrati</a></p>
                </div>
            </form>

        </div>
    </div>

</body>
</html>
