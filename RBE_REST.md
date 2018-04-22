[All Extras](README.md) / Enable and use REST with lnd

UNDER CONSTRUCTION !!!!


# Introduction #
There are two interfaces allowing interaction with an lnd instance:

1. rpc: Used by lncli.
2. REST: Uses a web-style access (`http://my.raspibolt/do/something?parameter=value`).

Difficulty: Easy

# Why would you need REST instead of rpc/lncli? #

* Access to your RaspiBolt is needed from a host that does not allow executables like lncli to run. E.g. - an online webstore on a shared host needing to create a Payment Request to give to customers.
* Easy to use from programming languages such as PHP.

# WARNING #
As of lnd V 0.4.1 beta, REST behaviour id affected buy the value of the *rpclisten* value in lnd.conf. For this reason, *rpclisten* will be set in these instructions.

# Procedure #

## Explictly set rpc and REST access ##

* login as admin

* Determine LAN IP address of your RaspiBolt if you don't already know it
```
admin ~  ฿  hostname -I
admin ~  ฿  ifconfig
```

* Edit and save the lnd.conf file with the changes shown.
   * Add/Change the rpclisten & restlisten lines as shown

`admin ~  ฿  sudo nano /home/bitcoin/.lnd/lnd.conf`

```
[Application Options]
rpclisten=your.LAN.ip.address:10009
restlisten=your.LAN.ip.address:8080
```
## Restart lnd  and Unlock Wallet ##
```
admin ~  ฿  sudo systemctl restart lnd
admin ~  ฿  lncli unlock
```

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

Substitute the full payment request string from above into the command below.
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




