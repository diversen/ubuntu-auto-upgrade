# auto-update-ubuntu

This script will auto-upgrade ubuntu and send email notifications using SMTP. 

## Install

    git clone git@github.com:diversen/ubuntu-auto-upgrade.git
    cd ubuntu-auto-upgrade
    composer install

## Config

Edit config files.

    cp -R config/* config-locale

Edit SMTP settings in `config-locale/SMTP.php`. 
`DefaultTo` setting in SMTP is the email of the person who should receive mails on upgrade. 

You can also edit `config-locale/App.php`. `restart` determines if server will restart are upgrade. 
This will only happen if needed. You can also set `timezone`. 

## Cron

