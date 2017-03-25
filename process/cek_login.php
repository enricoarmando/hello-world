<?php
session_start();

$is_login = isset($_SESSION['userid']) and $_SESSION['userid']<>'' ? true : false;

echo json_encode(array('is_login' => $is_login));

?>