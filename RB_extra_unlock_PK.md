[All Extras](README.md) / [Lights Out](https://github.com/robclark56/RaspiBolt-Extras/blob/master/README.md#the-lights-out-raspibolt) / Auto Wallet Unlock using Encyrypted Wallet Password

---
# INTRODUCTION #

Difficulty: Medium

If your lnd wallet is locked, the lnd server is effectively offline and can not participate in the Lightning Network.

This guide explains how to automatically unlock the [RaspiBolt](https://github.com/Stadicus/guides/blob/master/raspibolt/README.md) Lighting (lnd) wallet using a webserver at a different location. The objective is to have a 'Lights Off' RaspiBolt that recovers automatically all the way to an unlocked wallet in the event that it has rebooted and is unattended - e.g. a power failure.

# DISCLAIMER #
If you store your Wallet Password anywhere you risk loosing 100% of your wallet funds to bad actors. By implementing any of this guide, you accept 100% of any risk.

# LND Version #
These instructions were written with lnd at version V0.4.2
# REQUIREMENTS #
* Your RaspiBolt
* A webserver at a different location that you control, with
  * [PHP](https://en.wikipedia.org/wiki/PHP)
  * [HTTPS](https://en.wikipedia.org/wiki/HTTPS). Note: The webserver does NOT need a valid SSL certificate.
* Your RaspiBolt must be behind a firewall with either:
  * A static public IP, or
  * A static public [Fully Qualified Domain Name](https://en.wikipedia.org/wiki/Fully_qualified_domain_name) (FQDN). This can be provided using a [Dynamic DNS Service](https://en.wikipedia.org/wiki/Dynamic_DNS).
  
# SECURITY #
As the RaspiBolt is probably more secure than your webserver, all password encryption and decryption is done on the RaspiBolt.

In these instructions, 
 * Your wallet password is not be stored anywhere in plain text; it is stored encrypted (using a Public Key) on the webserver.
 * The Private Key (decryption key) is stored on the RaspiBolt.
 
|Hacker access after RaspiBolt reboot| Hacker Can ...|Hacker Can Not ...|
|------|---|-------|
|RaspiBolt Physical Access||Login, See Wallet Password, Open Wallet, Spend BTC |
|Remote webserver Login|Stop wallet automatically unlocking|See Wallet Password, Spend BTC, Login to RaspiBolt|

This does open a new attack vector so adds risk. But to spend your coins, a hacker would still need
* LAN access to your RaspiBolt, 
* the admin SSH certificate, and 
* to have not moved the RaspiBolt to a network with a different public interface.

# DESIGN #
* A cron job runs hourly on the RaspiBolt.
* If it finds the wallet is locked, the RaspiBolt ...
  * retrieves the encrypted wallet password from the webserver
  * decrypts the wallet password using the Private Key
  * unlocks the wallet with the wallet password
* The webserver only responds to requests from the IP address (or FQDN) you nominate.
  
# INSTRUCTIONS #

## Install _Bonus guide: System overview_ ##
If not already done, follow [these instructions](https://github.com/Stadicus/guides/blob/master/raspibolt/raspibolt_61_system-overview.md)

![OvervewImage](https://github.com/Stadicus/guides/raw/master/raspibolt/images/60_status_overview.png)
## Prepare your lnd ##
* Edit your lnd.conf to enable the REST interface. 

`admin ~  ฿ nano /home/bitcoin/.lnd/lnd.conf`

```
[Application Options]
restlisten=localhost:8080
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
In the steps below, you will encrypt your password, and then check you can correctly decode it. This is done in such a way that your wallet password is not saved in the terminal history file.

Change `MyUnlockWalletPassword` to your `LND wallet password [C]`.

```bash
admin ~/temp_unlock  ฿  echo -n 'Enter LND wallet password [C]: ';read -s password; echo -n $password | openssl rsautl -encrypt -inkey public.pem -pubin |base64 > wallet_password.enc;echo
Enter LND wallet password [C]: MyUnlockWalletPassword

admin ~/temp_unlock  ฿  cat wallet_password.enc | base64 -d | openssl rsautl -decrypt -inkey private.pem;echo
MyUnlockWalletPassword

admin ~/temp_unlock  ฿ cat wallet_password.enc
ERn6gAhdCOW9Zc6Y7v/ZvbxVKcorVcoF3OWt+QSuUdVhwLecrDGDk5Z2W8BtYDafXDo4lTujKKCB
[...lines deleted...]
wjNRhxvTnLiGp4xs+F5ocjuQdfO7bbIrmWZ9jw==

```
## Copy Encrypted Wallet Password to Webserver ##
On your webserver, edit the __CHANGE ME__ section in the PHP file to:

* include either the IP ADDRESS or FQDN of your RaspiBolt.
* enter the output of `cat wallet_password.enc`, as the ENCRYPTED_PASSWORD. __CAREFUL:__ Do not accidentally add extra spaces between the `''` characters.

## Test the PHP file ##
From the admin login on the RaspiBolt, execute this command (CHANGE_ME should be the FQDN of your webserver. e.g. my.domain.com)

```bash
admin ~/temp_unlock  ฿  curl --data "action=getEncryptedPassword" https://CHANGE_ME/raspibolt/utilities.php;echo
ERn6gAhdCOW9Zc6Y7v/ZvbxVKcorVcoF3OWt+QSuUdVhwLecrDGDk5Z2W8BtYDafXDo4lTujKKCB
[...lines deleted...]
wjNRhxvTnLiGp4xs+F5ocjuQdfO7bbIrmWZ9jw=
```

If you do not see your Encrypted Password, try temporarily commenting out the line in the PHP file below `// DIAGNOSTICS:` and trying again.

## Move the Private Key to the .lnd directory ##
Also remove write permission.
```
admin ~/temp_unlock  ฿  sudo mv private.pem /home/bitcoin/.lnd/
admin ~/temp_unlock  ฿  sudo chmod 0400 /home/bitcoin/.lnd/private.pem
```

## Delete the temp_unlock Directory ##
```
admin ~/temp_unlock  ฿  cd ..
admin ~  ฿  rm -r temp_unlock
```

## Create a Cron Job ##
* Create and save hourly cron job.  

Note: The cron job will run approximately every 60 mins, but not usually at 'the top of the hour'.

```
admin ~  ฿  sudo touch /etc/cron.hourly/lnd_unlock
admin ~  ฿  sudo chmod +x /etc/cron.hourly/lnd_unlock
admin ~  ฿  sudo nano /etc/cron.hourly/lnd_unlock
```
Change the CHANGE_ME section
```
#!/bin/bash
###### CHANGE_ME ############
url='my.domain.com/raspibolt/utilities.php'
###### END CHANGE_ME ########
restlisten=8080
locked=$(/usr/local/bin/raspibolt 2> /dev/null | grep 'Wallet Locked' > /dev/null;echo $?)
if [ "$locked" == "1" ]; then
 exit;
fi

response=$(curl -s --data "action=getEncryptedPassword" https://${url})
pw=$(echo  $response| sed 's/ /\n/g' | base64 -d | openssl rsautl -decrypt -inkey /home/bitcoin/.lnd/private.pem)
curl --insecure \
     --header "Content-Type: application/json" \
     --header "Grpc-Metadata-macaroon: $(xxd -ps -u -c 1000  /home/admin/.lnd/admin.macaroon)"  \
     --data "{\"wallet_password\":\"$(echo -n ${pw}|base64)\"}"   \
     https://localhost:${restlisten}/v1/unlockwallet

 
```

# Test #
In this section you will lock your lnd wallet, and then unlock it with the cron file.

Note: It is not clear why but lnd responds with `{"error":"context canceled","code":1}` when the wallet is successfully unlocked.
```
admin ~  ฿  sudo systemctl restart lnd
admin ~  ฿  raspibolt
.....  Wallet Locked  ....
admin ~  ฿  /etc/cron.hourly/lnd_unlock;echo
{"error":"context canceled","code":1}
admin ~  ฿  sleep 30;raspibolt
```
If you do not see _Wallet Locked_: Congratulations - everything is working fine!

---

|![Busy Programmer](images/RaspiBoltBusy.jpg)|Like these Guides? [Donate](RBE_donation.md) some satoshis.|
|--|--|
