# Ubuntu Auto Upgrade

This script will auto-upgrade ubuntu and send email notifications using SMTP. 
It it will probably also work with other Debian variants of linux.

Tested on 20.04 LTS

## Install

    git clone git@github.com:diversen/ubuntu-auto-upgrade.git
    cd ubuntu-auto-upgrade
    composer install

## Config

Create config files:

    cp -R config/* config-locale/

Edit SMTP settings in `config-locale/SMTP.php`. 
`DefaultTo` setting in SMTP is the email address of the person who will receive emails. 

You can also edit `config-locale/App.php`. `restart` determines if the server should restart if needed. 
You may also set `timezone`. 

## Cron

Set the script up as a cron script. Let it run every 10 minutes of so.
You will need to let the script run as root. Edit crontab as `sudo`, e.g.: 

    sudo crontab -e

Add the crontab line (change to your own source path):

    */10 * * * * cd /home/dennis/ubuntu-auto-upgrade && php cron.php

## Logs

Logs are written to `logs/main.log`. This log file will be created if it does not exist.  

# License

MIT © [Dennis Iversen](https://github.com/diversen)

