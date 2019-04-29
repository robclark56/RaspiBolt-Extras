[All Extras](README.md) / [Lights Out](https://github.com/robclark56/RaspiBolt-Extras/blob/master/README.md#the-lights-out-raspibolt) / Auto Backup of channels.backup file


UNDER CONSTRUCTION

---
# INTRODUCTION #

Difficulty: Medium

Since lnd V0.6, Static Channel Backups (SCB) is supported. In a nutshell, every time a channel changes, lnd writes a new copy of the `channels.backup` file. This file is encypted so that it is safe to store on (e.g.) a cloud server. 

This guide explains how to automatically upload `channels.backup` file on changes, to using a webserver on a different host. 

# RISK #
Minimal. The `channels.backup` file is encypted so that it is safe to transmit over the Internet and to store on (e.g.) a cloud server. 


# LND Version #
These instructions were written with lnd at version V0.6.0-beta

# PREPARATION #
Follow the instructions here first: [Auto Lightning Wallet Unlock](https://github.com/robclark56/RaspiBolt-Extras/blob/master/RB_extra_unlock_PK.md)

You only need to establish `utilities.php` on a webserver you control. You do not HAVE to go all the way and get auto unlock to work if you don't want to.

# INSTRUCTIONS #

## Update utilities.php ##
Update the utilities.php file so that it looks like as below. 

Specifically change the code after `/////////// END CHANGE ME /////////////`

```
<?php
/*
  raspibolt/utilities.php
  
  An offsite web server to support a RaspiBolt (https://github.com/Stadicus/guides/blob/master/raspibolt/README.md)
  
  Specifically: https://github.com/robclark56/RaspiBolt-Extras/blob/master/RB_extra_unlock_PK.md

*/

/////////// CHANGE ME /////////////////
//Lock down source security 
// 'xx' must be either:
//     'IP'  : Set 'yyy' to the public static IP address of your RaspiBolt (eg. '100.20.30.40')
//     'FQDN': Set 'yyy' to the public FQDN of your RaspiBolt (eg. 'raspibolt.my.domain.com')
$source = array('xx'=>'yyy');

define(
'ENCRYPTED_PASSWORD',
'CHANGE ME'
);
/////////// END CHANGE ME /////////////


// Only allow if source is the RaspiBolt site
if(isset($source['IP'])){
    if($_SERVER['REMOTE_ADDR'] != $source['IP']) exit;
} elseif (isset($source['FQDN'])) {
    if($_SERVER['REMOTE_ADDR'] != gethostbyname($source['FQDN'])) exit;
} else {
    echo '$source not set';
    exit;
}

//CASE 1 - UPLOADING file
/*
eg  curl  -F 'file=@channel.backup'  https://my.domain.com/raspibolt/utilities.php
*/
if($_FILES){
    $filename = 'ChannelBackups/'.$_FILES['file']['name'].'_'.date('Ymd_H:i:s');
    move_uploaded_file($_FILES['file']['tmp_name'], $filename);
    echo "File $filename saved\n";
    exit;
}

//CASE 2 - Actions
switch($_POST['action']){
    case 'getEncryptedPassword':
        echo ENCRYPTED_PASSWORD;
        exit;
}

?>
```

## Create the backup Folder on Your Webserver  ##
The default location where the `channel.backup` files is stored is  `<current directory>/ChannelBackups`. You can edit `utilities.php` if you want to change that,

On your webserver:
```
   cd <the folder where utilities.php is saved>
   mkdir ChannelBackups
   chmod 0755 ChannelBackups
```

## Test 1 ##
On your Raspibolt:
```
login as admin
   $ sudo curl  -F 'file=@/home/bitcoin/.lnd/data/chain/bitcoin/mainnet/channel.backup'  https://my.domain.com/raspibolt/utilities.php

File ChannelBackups/channel.backup_20190429_01:49:09 saved

```

## Automate Uploads ##
The next step is to create a service on the Raspibolt that:
* Notices when the `channel.backup` file has changed
* Uploads the new `channels.backup` file to your webserver.

The method used is based on this from Alex Bosworth: [alexbosworth/inotify-channel-backup.md](https://gist.github.com/alexbosworth/2c5e185aedbdac45a03655b709e255a3)

On your Raspibolt: login as admin. Then create, edit, and save copy-channel-backup-on-change.sh

Edit the `CHANGE.ME` to match your webserver.

```
admin ~  ฿  cd /home/admin/.lnd
admin ~/.lnd ฿  touch copy-channel-backup-on-change.sh
admin ~/.lnd ฿  chmod +x copy-channel-backup-on-change.sh
admin ~/.lnd ฿  nano copy-channel-backup-on-change.sh


#!/bin/bash
while true; do
    inotifywait /path/to/.lnd/data/chain/bitcoin/mainnet/channel.backup
    curl  -F 'file=@/home/bitcoin/.lnd/data/chain/bitcoin/mainnet/channel.backup'  https://CHANGE.ME/raspibolt/utilities.php
done

```
Create, edit , and save backup-channels.service
```
admin ~/.lnd ฿ sudo nano /etc/systemd/system/backup-channels.service

[Service]
ExecStart=/home/admin/.lnd/copy-channel-backup-on-change.sh
Restart=always
RestartSec=5
StandardOutput=syslog
StandardError=syslog
SyslogIdentifier=backup-channels
#User=ubuntu
#Group=ubuntu

[Install]
WantedBy=multi-user.target

```
Manually start the new service, and check the output
```
admin ~/.lnd ฿ sudo systemctl start backup-channels
admin ~/.lnd ฿ journalctl -fu backup-channels


xxxx
```

If you don't see the output above, something is wrong and must be corrected
```
admin ~/.lnd ฿ sudo systemctl stop backup-channels

```
When all looks good, enable the service to start at boot
```
admin ~/.lnd ฿ sudo systemctl enable backup-channels

```

## Test 2 ##
You will now cause the `channel.backup` to change and see if the copy gets uploaded to your webserver.
```
admin ~/.lnd ฿ touch /home/bitcoin/.lnd/data/chain/bitcoin/mainnet/channel.backup
```

Logon to your webserver and see if you have a new file.
```
e.g.    channel.backup_20190429_01:49:10
```

