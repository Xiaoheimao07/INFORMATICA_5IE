<?php

if (!isset($_SESSION['userId'])) {
    
    header('Location: /TALKMUSIC/php/userpages/userLoginpage.php');

    exit; 
}
?>