<?php
session_start();
session_destroy();
setcookie('c_u', '', time() - (8 * 3600), "/");
setcookie('c_p', '', time() - (8 * 3600), "/");
?>