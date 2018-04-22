[All Extras](README.md) / Enable and use REST with lnd

# Introduction #
There are two interfaces allowing interaction with an lnd instance:

1. rpc: Used by lncli.
2. REST: Uses a web-style access (`http://my.raspibolt/so/something?parameter=value`).

Difficulty: Easy

# Why would you need REST instead of rpc/lncli? #

* Access to your RaspiBolt is needed from a host that does not allow executables like lncli to run. E.g. - an online webstore on a shared host needing to create a Payment Request to give to customers.
* Easy to use from programming languages such as PHP.

# LND Default Denies All REST Access #
As I write this (lnd V 0.4.1 beta) there are only two modes for REST access: *None* or *World*. I consider this a security bug. See also [lnd issues 694](https://github.com/lightningnetwork/lnd/issues/684)

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

# Procedure #

## Create new Certificate/Key file pair to Allow 0.0.0.0 Access ##

* login as admin

* Edit and save the lnd.conf file with the changes shown.
   * Add/Change the restlisten line as shown
   * Add the tlsextraip line

`admin ~  ฿  sudo nano /home/bitcoin/.lnd/lnd.conf`

```
[Application Options]
restlisten=0.0.0.0:8080
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

``` 
X509v3 Subject Alternative Name:
    DNS:RaspiBolt, DNS:localhost, IP Address:127.0.0.1, 
    IP Address:0:0:0:0:0:0:0:1, IP Address:192.168.0.141, IP Address:FE80:0:0:0:2481:BA24:A7E5:3DA1, 
    IP Address:0.0.0.0
```

## Copy the TLS cert to user admin ##
```
admin ~  ฿  sudo cp /home/bitcoin/.lnd/tls.cert /home/admin/.lnd
admin ~  ฿  sudo chown -R admin:admin /home/admin/.lnd
```

##  Unlock wallet ##
`admin ~  ฿  lncli unlock`

## Test ##
```
admin ~  ฿ sudo curl --insecure  --header "Grpc-Metadata-macaroon: $(xxd -ps -u -c 1000  /home/admin/.lnd/admin.macaroon)"   https://127.0.0.1:8080/v1/getinfo

{"identity_pubkey":"022e...1ecdb56c5941020d4",....,"best_header_timestamp":"1524352102"}
```

## Create a Payment Request ##
(Equivalent to *lncli addinvoice --memo test --amt 100000*)
```
admin ~  ฿ sudo curl --insecure  --header "Grpc-Metadata-macaroon: $(xxd -ps -u -c 1000  /home/admin/.lnd/admin.macaroon)"   https://127.0.0.1:8080/v1/invoices -d '{"memo":"test","value":"100000"}'
{"r_hash":"VIel04YP3YcFmBy82VMCJiUgdRLw7eyi6uk9Ee/jDBQ=",
 "payment_request":"lntb.....rfez"
 }
```
Now decode it (Equivalent to *lncli decodepayreq xxxx*)
```
admin ~  ฿  sudo curl --insecure  --header "Grpc-Metadata-macaroon: $(xxd -ps -u -c 1000  /home/admin/.lnd/admin.macaroon)"   https://127.0.0.1:8080/v1/payreq/lntb.....rfez

{"destination":"022ecebcf3c95f39934b30d7d56c42d2fa1b110054f6672301ecdb56c5941020d4",
 "payment_hash":"5487a5d3860fdd8705981cbcd953022625207512f0edeca2eae93d11efe30c14",
 "num_satoshis":"100000",
 "timestamp":"1524356901",
 "expiry":"3600",
 "description":"test",
 "cltv_expiry":"144"
}
```

# List of REST Commands #
See [REST API](https://github.com/ndeet/php-ln-lnd-rest/tree/master/docs/Api)




