<?php
session_start();
$_SESSION=[];
setcookie(session_name(),'',time()-3600);
session_destroy();
die;
?>