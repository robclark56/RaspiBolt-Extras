[All Extras](README.md) / [Lights Out](https://github.com/robclark56/RaspiBolt-Extras/blob/master/README.md#the-lights-out-raspibolt) / Auto Backup of channels.backup file


UNDER CONSTRUCTION

---
# INTRODUCTION #

Difficulty: Medium

Since lnd V0.6, Static Channel Backups (SCB) is supported. In a nutshell, every time a channel changes, lnd writes a new copy of the `channels.backup` file. For more details, see [v0.6-beta Release Notes](https://github.com/lightningnetwork/lnd/releases/tag/v0.6-beta)

This guide explains one way to automatically upload the `channels.backup` file on changes, to using a webserver on a different host. 

# RISK #
Minimal. The `channels.backup` file is encrypted so that it is safe to transmit over the Internet and to store on (e.g.) a cloud server. 


# LND Version #
These instructions were written with lnd at version V0.6.0-beta

# PREPARATION #
Follow the instructions here first: [Auto Lightning Wallet Unlock](https://github.com/robclark56/RaspiBolt-Extras/blob/master/RB_extra_unlock_PK.md)

You only need to establish `utilities.php` on a webserver you control. You do not HAVE to go all the way and get auto unlock to work if you don't want to.

# INSTRUCTIONS #

## Create the backup Folder on Your Webserver  ##
The default location where the `channel.backup` files is stored is  `<current directory>/ChannelBackups`. You can edit `utilities.php` if you want to change that,

On your webserver:
```
   cd <the folder where utilities.php is saved>
   mkdir ChannelBackups
   chmod 0755 ChannelBackups
```

## Test 1 ##
On your Raspibolt, login as admin and execute the command below. (Change the CHANGE.ME text)
```
admin ~  ฿ sudo curl -F 'file=@/home/bitcoin/.lnd/data/chain/bitcoin/mainnet/channel.backup'  https://CHANGE.ME/raspibolt/utilities.php

File ChannelBackups/channel.backup_20190429_01:49:09 saved

```
You should see the message `File ChannelBackups/channel.backup_xxxxxxxx_xx:xx:xx saved`. Also login to your webserver and see if the file was indeed saved. If not, then something is wrong and must be corrected before proceeding.

## Automate Uploads ##
The next step is to create a service on the Raspibolt that:
* Notices when the `channel.backup` file has changed
* Uploads the new `channels.backup` file to your webserver.

The method used is based on this from Alex Bosworth: [alexbosworth/inotify-channel-backup.md](https://gist.github.com/alexbosworth/2c5e185aedbdac45a03655b709e255a3). Alex's method uses a simp 'cp' (Copy) command which is limited if you are trying to create an off-site backup. Of course there are probably many other ways of sending the backup file off-site; I am simply documenting the methoid I used as I was already using the off-site webserver to automate my wallet unlocks.

On your Raspibolt: login as admin. Install inotify-tools. Then create, edit, and save copy-channel-backup-on-change.sh

Edit the `CHANGE.ME` to match your webserver.

```
admin ~  ฿  sudo apt install inotify-tools
admin ~  ฿  cd /home/admin/.lnd
admin ~/.lnd ฿  touch copy-channel-backup-on-change.sh
admin ~/.lnd ฿  chmod +x copy-channel-backup-on-change.sh
admin ~/.lnd ฿  nano copy-channel-backup-on-change.sh


#!/bin/bash
while true; do
    inotifywait /home/bitcoin/.lnd/data/chain/bitcoin/mainnet/channel.backup
    curl  -F 'file=@/home/bitcoin/.lnd/data/chain/bitcoin/mainnet/channel.backup' \
          https://CHANGE.ME/raspibolt/utilities.php
done

```
Create, edit, and save backup-channels.service
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
admin ~/.lnd ฿ sudo journalctl -fu backup-channels

...
Apr 29 14:49:10 RaspiBolt backup-channels[12873]: Setting up watches.
Apr 29 14:49:10 RaspiBolt backup-channels[12873]: Watches established.

```
Type `[Ctrl-C]` to get back to the prompt.

If you don't see the output above, something is wrong and must be corrected.
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

