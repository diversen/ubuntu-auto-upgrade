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
`DefaultTo` setting in SMTP is the email address of the person who will receive emails. 

You can also edit `config-locale/App.php`. `restart` determines if server will restart after upgrade. 
This will only happen if needed. You can also set `timezone`. 

## Cron

Set the script up as a cron script. Let et run every 5 minutes of so.
You will need to run the script as root.

    */5 * * * * cd /path/to/auto-update-ubuntu && php cron.php