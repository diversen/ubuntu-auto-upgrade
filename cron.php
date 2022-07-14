<?php

include_once "vendor/autoload.php";
include_once "AptAutoUpgrade.php";

$apt_auto_upgrade = new AptAutoUpgrade();
$apt_auto_upgrade->run();
