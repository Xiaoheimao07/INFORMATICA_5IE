<?php
session_start();

$_SESSION = array();

session_destroy();

header('Location: /INFORMATICA_5IE/TalkMusic/php/userpages/welcomePage.php');

exit; 
?>