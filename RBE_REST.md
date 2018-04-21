[All Extras](README.md) / Enable and use REST with lnd

# Introduction #
There are two interfaces allowing interaction with an lnd instance:

1. rpc: Used by lncli and the best documented.
2. REST: Uses a web-style access (`http://my.raspibolt/so/something?parameter=value`).

Difficulty: Easy

# Why would you need REST instead of rpc/lncli? #

* Access to your RaspiBolt is needed from a host that does not allow executables like lncli to run. E.g. - an online webstore on a shared host needing to create a Payment Request to give to customers.
* Easy to use from programming languages such as PHP.

# LND Default Denies All REST Access #
As I write this (lnd V 0.4.1 beta) there are only two modes for REST access.

|REST Access From Only ...|Default|Possible?|
|--|--|--|
|localhost|Denied|No|
|LAN - specific host|Denied|No|
|LAN - all hosts|Denied|No|
|WAN - specific host|Denied|No|
|WAN - all hosts|Denied|No|
|Everything|Denied|Yes|

Compare that with rpc access.

|rpc/lncli Access From Only ...|Default|Possible?|
|--|--|--|
|localhost|Yes|Yes|
|LAN - specific host|No|Yes|
|LAN - all hosts|No|Yes|
|WAN - specific host|No|Yes|
|WAN - all hosts|No|Yes|
|Everything|No|Yes|

## Bug? ##
I consider it a bug that REST access is either *None* or *World* as a security bug. See [lnd issues 694](https://github.com/lightningnetwork/lnd/issues/684)

# Procedure #

## Create new Certificate/Key file pair to Allow 0.0.0.0 Access ##

* login as admin

* Edit and save the lnd.conf file with the changes shown.
   * Add/Change the restlisten line as shown
   * Add the tlsextraip line

`admin ~  ฿  sudo nano /home/bitcoin/.lnd/lnd.conf`

```
[Application Options]
restlisten=0.0.0.0:8081
tlsextraip=0.0.0.0

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
You should see 0.0.0.0 in the result

X509v3 Subject Alternative Name:
    DNS:RaspiBolt, DNS:localhost, DNS:my.fqdn, 
    IP Address:127.0.0.1, IP Address:0:0:0:0:0:0:0:1, IP Address:192.168.0.141, 
    IP Address:FE80:0:0:0:2481:BA24:A7E5:3DA1

* Copy the TLS cert to user admin
admin ~  ฿  sudo cp /home/bitcoin/.lnd/tls.cert /home/admin/.lnd
admin ~  ฿  sudo chown -R admin:admin /home/admin/.lnd

