<?
session_start();
session_unset();
session_destory();
header("Location: login.php");
exit();
