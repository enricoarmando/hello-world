<?php
// toggle this to change the setting
define('DEBUG', true);

// you want all errors to be triggered
error_reporting(E_ERROR | E_WARNING | E_PARSE);

ini_set('display_errors', DEBUG ? 'On' : 'Off');
?>