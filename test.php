<?php

include_once "vendor/autoload.php";
include_once "AptAutoUpgrade.php";

use function Safe\touch;

class TestAptAutoUpgrade extends AptAutoUpgrade
{

    public function __construct()
    {
        parent::__construct();
    }

    public function test()
    {
        var_dump("Server needs restart ", $this->needs_restart());
        var_dump("Sever has updates ", $this->has_updates());
        var_dump("Sever hostname is ", $this->get_hostname());
        var_dump("Should restart ", $this->should_restart());
        var_dump("date ", $this->get_datetime());
        var_dump("Create lock file", touch($this->lock_file));
    }
}

$apt_auto_upgrade = new TestAptAutoUpgrade();
$apt_auto_upgrade->test();
exit();
