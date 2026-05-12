<?php
require_once('../include/menuchoice.php');

if (!isset($_SESSION['userId'])) {
    header('Location: userLoginPage.php');
    exit;
}

$idArticolo   = isset($_POST['fkArticoloId']) ? (int)$_POST['fkArticoloId'] : 0;
$valutazione  = isset($_POST['valutazione'])  ? (int)$_POST['valutazione']  : 0;
$commento     = trim($_POST['commento'] ?? '');
$idRecensore  = $_SESSION['userId'];

if ($idArticolo === 0 || $valutazione < 1 || $valutazione > 5) {
    header('Location: mainPage.php');
    exit;
}

try {
    $sql = "INSERT INTO RecensioneArticolo (fkRecensoreId, fkArticoloId, valutazione, commento)
            VALUES (:recensoreId, :articoloId, :valutazione, :commento)";

    $istruzione = DBHandler::getPDO()->prepare($sql);
    $istruzione->execute([
        ':recensoreId' => $idRecensore,
        ':articoloId'  => $idArticolo,
        ':valutazione' => $valutazione,
        ':commento'    => $commento
    ]);

    header('Location: articleDetail.php?id=' . $idArticolo . '&review_success=1');
    exit;

} catch (PDOException $e) {
    die("Errore durante l'inserimento della recensione: " . $e->getMessage());
}
?>
