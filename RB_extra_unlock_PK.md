#  DRAFT - NOT READY #

[All Extras](README.md) / [Lights Out](https://github.com/robclark56/RaspiBolt-Extras/blob/master/README.md#the-lights-out-raspibolt) / Auto Wallet Unlock using Encyrypted Wallet Password

# DISCLAIMER #
If you store your Wallet Password anywhere you risk loosing 100% of your wallet funds to bad actors. By implementing any of this guide, you accept 100% of any risk.

---
# INTRODUCTION #

Difficulty: Medium

If your lnd wallet is unlocked, the lnd server is effectively offline and can not participate in the Lightning Network.

This guide explains how to automatically unlock the [RaspiBolt](https://github.com/Stadicus/guides/blob/master/raspibolt/README.md) Lighting (lnd) wallet using a computer at a different location. The objective is to have a 'Lights Off' RaspiBolt that recovers automatically all the way to an unlocked wallet in the event that it has rebooted and is unattended - e.g. a power failure.

# REQUIREMENTS #
* Your RaspiBolt
* A webserver at a different location that you control, with
  * [PHP](https://en.wikipedia.org/wiki/PHP)
  * [HTTPS](https://en.wikipedia.org/wiki/HTTPS). Note: The webserver does NOT need a valid SSL certificate.
* Your RaspiBolt must be behind a firewall with either:
  * A static public IP, or
  * A static public [Fully Qualified Domain Name=FQDN](https://en.wikipedia.org/wiki/Fully_qualified_domain_name). This can be provided using a [Dynamic DNS Service](https://en.wikipedia.org/wiki/Dynamic_DNS).
  
# SECURITY #
In these instructions, 
 * Your wallet password is not be stored anywhere in plain text; it is stored encrypted (using a Private Key) on the webserver.
 * The Public Key (decryption key) is stored on the webserver.
 * Your wallet password is never transmitted over the Internet; either in Plain Text or Encrypted.
 
|Hacker access after RaspiBolt reboot| Hacker Can ...|Hacker Can Not ...|
|------|---|-------|
|RaspiBolt Physical Access||Login, See Wallet Password, Open Wallet, Spend BTC |
|Remote webserver Login|Open Wallet|See Wallet Password, Spend BTC, Login to OS|

This does open a new attack vector so adds risk. But to spend your coins, a hacker would still need
* LAN access to your RaspiBolt, 
* the admin SSH certificate, and 
* to have not moved the RaspiBolt to a different network.

# DESIGN #
* A cron job runs hourly on the RaspiBolt.
* If it finds the wallet is locked, the RaspiBolt ...
  * retrieves the Public Key from the webserver
  * decrypts the wallet password using the Public Key
  * unlocks the wallet with the wallet password
* The webserver only responds to requests from the IP address (or FQDN) you nominate.
  
# INSTRUCTIONS #

## Prepare your lnd ##
* Edit your lnd.conf to enable the REST interface. 

`admin ~  ฿ nano /home/bitcoin/.lnd/lnd.conf`

```
[Application Options]
restlisten=localhost:8080
```
* Restart your lnd server
```bash
admin ~  ฿  sudo systemctl restart lnd
```
## Prepare your Webserver ## 
Login to your webserver and add this PHP file so it can be accessed via a URL like: `https://my.domain.com/raspibolt/utilities.php`

```php
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

switch($_POST['action']){
    case 'getEncryptedPassword':
        echo ENCRYPTED_PASSWORD;
        exit;
}
?>
```

## Create a Temporary Directory and a Private/Public Key Pair ##
Login as admin to your RaspiBolt and execute these commands
```bash
admin ~  ฿  mkdir temp_unlock
admin ~  ฿  cd temp_unlock
admin ~/temp_unlock  ฿  openssl genrsa -out private.pem 2048
admin ~/temp_unlock  ฿  openssl rsa -in private.pem -outform PEM -pubout -out public.pem
admin ~/temp_unlock  ฿  ls -la
total 16
drwxr-xr-x  2 admin admin 4096 Jun 19 14:33 .
drwxr-xr-x 11 admin admin 4096 Jun 19 14:29 ..
-rw-------  1 admin admin 1679 Jun 19 14:32 private.pem
-rw-r--r--  1 admin admin  451 Jun 19 14:33 public.pem
```
## Encrypt your Wallet Password ##
In the steps below, you will encrypt your password, and then check you can correctly decode it.

Change `MyUnlockWalletPassword` to the password you enter with the `lncli unlock` command.

```bash
admin ~/temp_unlock  ฿ echo -n 'MyUnlockWalletPassword' | openssl rsautl -encrypt -inkey public.pem -pubin |base64 > wallet_password.enc

admin ~/temp_unlock  ฿ cat wallet_password.enc | base64 -d | openssl rsautl -decrypt -inkey private.pem 
MyUnlockWalletPassword

admin ~/temp_unlock  ฿ cat wallet_password.enc
ERn6gAhdCOW9Zc6Y7v/ZvbxVKcorVcoF3OWt+QSuUdVhwLecrDGDk5Z2W8BtYDafXDo4lTujKKCB
[...lines deleted...]
wjNRhxvTnLiGp4xs+F5ocjuQdfO7bbIrmWZ9jw==

```
## Copy Encrypted Wallet Password to Webserver ##
On your webserver, edit the __CHANGE ME__ section in the PHP file to:

* include either the IP ADDRESS or FQDN of your RaspiBolt.
* enter the output of `cat wallet_password.enc`, as the ENCRYPTED_PASSWORD

## Test the PHP file ##
From the admin login on the RaspiBolt, exeute this command (CHANGE_ME should be the FQDN of your webserver. e.g. raspibolt.my.domain.com)

```bash
admin ~  ฿  curl --data "action=getEncryptedPassword" https://CHANGE_ME/raspibolt/utilities.php

xxxxxxxxxx
```

If you do not see your Encrypted Password, try commenting out the line below `// DIAGNOSTICS:` in the PHP file and trying again.

## Create a Cron Job ##
* Create and save hourly cron job.  

Note: The cron job will run approximately every 60 mins, but not usually at 'the top of the hour'.

```
$ sudo touch /etc/cron.hourly/lnd_unlock
$ sudo chmod +x /etc/cron.hourly/lnd_unlock
$ sudo nano /etc/cron.hourly/lnd_unlock
```
Change *GCP_Username*
```
#!/bin/bash
# RaspiBol t LND: Script to unlock wallet
# /etc/cron.hourly/lnd_unlock

#
# Change next 1 line
#
home_dir="/home/GCP_Username"

################################
lncli="$home_dir/run_lncli"
$lncli getinfo  2>&1 | grep "identity_pubkey" >/dev/null
wallet_unlocked=$?
if [ "$wallet_unlocked" -eq 1 ] ; then
 echo "Wallet Locked"
 /usr/bin/expect $home_dir/.lnd/lnd_unlock.exp  2>&1 > /dev/null
else
 echo "Wallet UnLocked"
fi
```

# Test #
On GCP:

```
$ ./run_lncli getinfo
[lncli] Wallet is encrypted. Please unlock using 'lncli unlock', or set password using 'lncli create' if this is the first time starting lnd.
```
If you see the above, it confirms the communication from your GCP instance to your RaspiBolt is working.

Repeat the above command every 10 mins or so for at max. 1 hour, until you see something like:
```
$ ./run_lncli getinfo
{
    "identity_pubkey": "xxxxx",
    "alias": "xxxxx",
    "num_pending_channels": 0,
    "num_active_channels": 1,
    "num_peers": 2,
    "block_height": 1291957,
    "block_hash": "00000000000000ca546331fbe0d83df81b8f4bf2b24f081cce359920faaa8dc1",
    "synced_to_chain": true,
    "testnet": true,
    "chains": [
        "bitcoin"
    ],
    "uris": [
        "xxx@x.x.x.x:9735"
    ],
    "best_header_timestamp": "1523192388"
}
```

---

|![Busy Programmer](images/RaspiBoltBusy.jpg)|Like these Guides? [Donate](RBE_donation.md) some satoshis.|
|--|--|
