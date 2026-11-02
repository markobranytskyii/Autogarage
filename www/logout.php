<?php
require 'auth.php';

$_SESSION = [];
session_destroy();

header('Location: login.php?melding=uitgelogd');
exit;
