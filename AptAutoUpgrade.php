<?php

use Pebble\SMTP;
use Pebble\Service\ConfigService;
use Pebble\Service\LogService;
use Pebble\ExceptionTrace;
use Pebble\Special;
use Diversen\Cli\Utils as CliUtils;

use function Safe\touch;
use function Safe\unlink;

class AptAutoUpgrade
{

    private $config;
    private $smtp;
    private $send_to;
    private $cli_utils;
    protected $lock_file = './restart.lock';

    public function __construct()
    {
        $this->config = (new ConfigService())->getConfig();
        $this->smtp = new SMTP($this->config->getSection('SMTP'));
        $this->send_to = $this->config->get('SMTP.DefaultTo');
        $this->cli_utils = new CliUtils();
        $this->log = (new LogService())->getLog();

        date_default_timezone_set($this->config->get('App.timezone'));
    }

    public function get_hostname()
    {
        $res = $this->cli_utils->execSilent('hostname');
        if ($res) {
            throw new Exception('Could not get server hostname');
        }

        return $this->cli_utils->getStdout();
    }

    function parse_apt_check(string $apt_output)
    {
        $output_ary = explode(";", $apt_output); // 3;0
        if ($output_ary[0] === '0' && $output_ary[1] === '0') {
            return false;
        }
        return true;
    }

    function has_updates()
    {

        $command = "/usr/lib/update-notifier/apt-check";
        $res = $this->cli_utils->execSilent($command);

        if ($res) {
            throw new Exception($this->cli_utils->getStderr());
        }

        // Without any error the apt-check message is available in stderr
        // It has the form "3;3" "security update;non security updates" 
        $apt_output = $this->cli_utils->getStderr();
        if ($this->parse_apt_check($apt_output)) {
            return true;
        }

        return false;
    }

    function upgrade()
    {

        $command = "apt-get upgrade -y";
        $res = $this->cli_utils->execSilent($command);

        if ($res) {
            throw new Exception($this->cli_utils->getStderr());
        }
    }

    function needs_restart()
    {
        if (file_exists('/var/run/reboot-required')) {
            return true;
        }
    }

    function should_auto_restart()
    {

        if ($this->needs_restart() && $this->config->get('App.restart')) {
            return true;
        }
    }

    function send_mail(string $subject, string $message)
    {
        // Fail silently on mail errros
        try {
            $message .= "Mail sent: " . $this->get_datetime();
            $this->smtp->send($this->send_to, $subject, $message, nl2br($message));
        } catch (Exception $e) {
            $this->log->error($e->getMessage());
        }
    }

    function restart()
    {
        $res = $this->cli_utils->execSilent('/sbin/shutdown -r +1');

        if ($res) {
            throw new Exception($this->cli_utils->getStderr());
        }
    }

    public function get_datetime()
    {
        return date('Y-m-d H:i:s');
    }

    public function run()
    {

        try {

            $server_name = $this->get_hostname();

            // Check if server has been restarted
            if (file_exists($this->lock_file)) {

                $subject = "Server ($server_name) restarted with success";
                $message = "Server ($server_name) was restarted. \n\n";
                unlink($this->lock_file);
                $this->log->notice('Removed lock file (restart success): ' . $this->lock_file);
                $this->send_mail($subject, $message);
            }

            if ($this->has_updates()) {

                $this->log->notice('Server should be upgraded. Will now try to upgrade');
                $this->upgrade();
                $this->log->notice('Server upgraded');

                $subject = "Server ($server_name) upgraded with success";
                $message = "Server ($server_name) was upgraded.\n\n";

                if ($this->needs_restart()) {
                    $message .= "The server needs to be restarted\n\n";
                    if ($this->should_auto_restart()) {
                        touch($this->lock_file);
                        $message .= "Server will try to restart automatically\n\n";
                    } else {
                        $message .= "You will need to restart the server manually\n\n";
                    }
                }

                $this->send_mail($subject, $message);

                if ($this->should_auto_restart()) {
                    touch($this->lock_file);
                    $this->restart();
                    $this->log->notice('Server restarting in one minut');
                }
            }

            exit(0);
        } catch (Exception $e) {

            $this->log->error($e->getMessage(), ['exception' => ExceptionTrace::get($e)]);

            $subject = "Server ($server_name) upgrade failed";
            $message = "There was an error while trying to upgrade the server:\n\n";
            $message .= Special::encodeStr(ExceptionTrace::get($e)) . "\n\n";
            $this->send_mail($subject, $message);

            exit(1);
        }
    }
}
