#  DRAFT - NOT READY #

[All Extras](README.md) / [Lights Out](https://github.com/robclark56/RaspiBolt-Extras/blob/master/README.md#the-lights-out-raspibolt) / Auto Wallet Unlock (REST)

# DISCLAIMER #
If you store your Wallet Password anywhere you risk loosing 100% of your wallet funds to bad actors. By implementing any of this guide, you accept 100% of any risk.

---
# INTRODUCTION #

Difficulty: Medium

This guide explains how to automatically unlock the [RaspiBolt](https://github.com/Stadicus/guides/blob/master/raspibolt/README.md) Lighting (lnd) wallet using a computer at a different location. The objective is to have a 'Lights Off' RaspiBolt that recovers automatically all the way to an unlocked wallet in the event that it has rebooted and is unattended - e.g. a power failure.

If the wallet remains unlocked, the lnd server is effectively offline and can not participate in the Lightning Network.

# REQUIREMENTS #
* Your RaspiBolt
* A webserver at a different location that you control, with
  * [PHP](https://en.wikipedia.org/wiki/PHP)
  * [HTTPS](https://en.wikipedia.org/wiki/HTTPS). Note: The webserver does NOT need a valid SSL certificate.
  * ???? static IP????
* Your RaspiBolt must be behind a firewall with either:
  * A static public IP, or
  * A static public [Fully Qualified Domain Name=FQDN](https://en.wikipedia.org/wiki/Fully_qualified_domain_name). This can be provided using a [Dynamic DNS Service](https://en.wikipedia.org/wiki/Dynamic_DNS).
  
# SECURITY #
In these instructions, 
 * Your wallet password will not be stored anywhere in plain text; it is stored encrypted (using a Private Key) on the RaspiBolt.
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
  
# PREPARATION #

## Modify local firewall and port forwarding ##
* Add a new Port Forward for port 10009 on your router. See [ Rasberry Pi ](https://github.com/Stadicus/guides/blob/master/raspibolt/raspibolt_20_pi.md)
* login as admin to your RaspiBolt
* Allow rpc from your VM in RaspiBolt firewall

Subsitute GCP_External_IP with your GCP External IP

```
admin ~  ฿  sudo su
root@RaspiBolt:/home/admin# ufw allow from GCP_External_IP to any port 10009 comment 'allow lnd rpc from GCP VM'
root@RaspiBolt:/home/admin# exit
```

## Create new Certificate/Key file pair ##
* login as admin to your RaspiBolt

* Edit and save the lnd.conf file with the changes shown. 

  * Change the *rpclisten* line as shown
  * If you have:
    * A static IP address: Add *tlsextraip=my.vm.ip.address* (i.e. your External IP address).
    * A static FQDN: Add *tlsextradomain=my.fqdn* (i.e. your FQDN).
```
admin ~  ฿  sudo nano /home/bitcoin/.lnd/lnd.conf
```

```
[Application Options]
#rpclisten=localhost:10009
rpclisten=0.0.0.0:10009
# use tlsextraip ONLY if you have a static public IP address
tlsextraip=my.vm.ip.address
# use tlsextradomain ONLY if you have a static public FQDN
tlsextradomain=my.fqdn
```
* Hide the existing tls.key and tls.cert files so that lnd regenerates them
```
admin ~  ฿  sudo mv /home/bitcoin/.lnd/tls.cert  /home/bitcoin/.lnd/tls.cert.backup
admin ~  ฿  sudo mv /home/bitcoin/.lnd/tls.key   /home/bitcoin/.lnd/tls.key.backup
``` 

* Restart lnd to generate new tls files
```
admin ~  ฿  sudo systemctl restart lnd
admin ~  ฿  openssl x509 -in /home/bitcoin/.lnd/tls.cert -text -noout
```

You should see *my.vm.ip.address* or *my.fqdn* in the result
```
X509v3 Subject Alternative Name:
    DNS:RaspiBolt, DNS:localhost, DNS:my.fqdn, 
    IP Address:127.0.0.1, IP Address:0:0:0:0:0:0:0:1, IP Address:192.168.0.141, 
    IP Address:FE80:0:0:0:2481:BA24:A7E5:3DA1
```
*  Copy the TLS cert to user admin 
```
admin ~  ฿  sudo cp /home/bitcoin/.lnd/tls.cert /home/admin/.lnd
admin ~  ฿  sudo chown -R admin:admin /home/admin/.lnd
```

## Copy files from RaspiBolt to GCP ##

In this section you will 'copy & paste' long text strings between login windows.

|On RaspiBolt|On GCP|
|--|--|
||`$ cd ~/.lnd`|
|`sudo xxd -ps -u -c 1000 /home/bitcoin/.lnd/tls.cert`||
|copy *long_text_string*||
||`echo 'long_text_string' `&#124;` xxd -r -p - tls.cert`|
|`sudo xxd -ps -u -c 1000 /home/bitcoin/.lnd/readonly.macaroon`||
|copy *long_text_string*||
||`echo 'long_text_string' `&#124;` xxd -r -p - readonly.macaroon`|
|`sudo ls -la /home/bitcoin/.lnd/tls.cert`|`ls -la tls.cert`|
|`sudo ls -la /home/bitcoin/.lnd/readonly.macaroon`|`ls -la readonly.macaroon`|
|Check file sizes match|Check file sizes match| 
 

## Setup the VM ##
* Login to your GCP VM
  * Visit: https://console.cloud.google.com
  * Compute Engine > VM Instances > Connect > Open in browser window

* Check you have these 3 files

```
$ ls -l . .lnd
.:
total 15852
-rwxr-xr-x 1 GCP_Username GCP_Username 16231264 Apr  8 03:56 lncli

.lnd:
total 8
-rw-r--r-- 1 GCP_Username GCP_Username 183 Apr  8 07:19 readonly.macaroon
-rw-r--r-- 1 GCP_Username GCP_Username 741 Apr  8 07:15 tls.cert
```

## Check Wallet hourly, and Unlock if needed ##
* Login to your GCP VM
* Install expect

`$ sudo apt-get install expect`
* Create and save an lncli wrapper
```
$ sudo touch run_lncli
$ sudo chmod +x run_lncli
$ sudo nano run_lncli
```
Add the code below, after changing *GCP_Username*, and *my.fqdn*
```
#!/bin/bash
# Wrapper for lncli executable
# <home_dir>/run_lncli

#
# Change next 2 lines`
#
home_dir="/home/GCP_Username"
RaspiBoltExternal="my.fqdn"

############################
$home_dir/lncli --rpcserver=$RaspiBoltExternal:10009 \
                --macaroonpath=$home_dir/.lnd/readonly.macaroon  \
                --lnddir=$home_dir/.lnd \
                $1
```
* Create and save Expect script

Change *MyLndWalletPassword* and *GCP_Username*.

```
$ sudo nano .lnd/lnd_unlock.exp

#!/usr/bin/expect
#
# File invoked by /etc/cron.hourly to unlock the lnd wallet
# <home_dir>/.lnd/lnd_unlock.exp

#
# Change next 2 lines
#
set walletPW "MyLndWalletPassword"
set home_dir "/home/GCP_Username"

##################################
set lncli "$home_dir/run_lncli"
set timeout 40
spawn $lncli unlock
log_user 0
expect "Input wallet password: "
send "$walletPW\r"
log_user 1
expect "lnd successfully unlocked!"
```

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
# RaspiBolt LND: Script to unlock wallet
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
