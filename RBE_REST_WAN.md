[All Extras](README.md) / Enable and use REST with lnd - WAN

# Introduction #
These instructions demonstrate how to use the lnd REST interface from a host outside the local LAN.

Difficulty: Moderate

# Requirements #
You will need access to a host on the Internet (*WAN Host*) that can run [cURL](https://en.wikipedia.org/wiki/CURL). In these instructions, cURL will be run from a PHP script running on web server. But you could do the same from a command line.

# Prerequisite #
You must have successfully completed [Enable and use REST with lnd - LAN](RBE_REST.md).

# Modify local firewall and port forwarding #

 * Add a new Port Forward on your router allowing internet traffic to port 8080 through to your RaspiBolt. 
    * See [Rasberry Pi](https://github.com/Stadicus/guides/blob/master/raspibolt/raspibolt_20_pi.md)

* Determine IP address

Compete this table.

|`__________Host__________`|`___________`Value____________   |
|--|:-------------------------------|
|RaspiBolt External Static IP|                |
|RaspiBolt External FQDN (optional)||

* login as admin to your RaspiBolt

* Allow REST in the RaspiBolt firewall

```
admin ~  ฿  sudo su
root@RaspiBolt:/home/admin# ufw allow to any port 8080 comment 'allow REST from WAN'
root@RaspiBolt:/home/admin# exit
```

# Update tls Cert/Key pair to Add lnd Access for your WAN Host #

* login as admin to your RaspiBolt

* Edit and save the lnd.conf file with the changes shown.

If you have:
   * A static IP address: Add tlsextraip=my.ip.address (i.e. your RaspiBolt External IP address).
   * A static [FQDN](https://en.wikipedia.org/wiki/Fully_qualified_domain_name): Add tlsextradomain=my.fqdn (i.e. your RaspiBolt FQDN).
```
admin ~  ฿  sudo nano /home/bitcoin/.lnd/lnd.conf

[Application Options]
# use tlsextraip ONLY if you have a static public IP address
tlsextraip=my.ip.address
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
You should see my.ip.address or my.fqdn in the result

```
X509v3 Subject Alternative Name:
    DNS:RaspiBolt, DNS:localhost, DNS:my.fqdn, 
    IP Address:127.0.0.1, IP Address:0:0:0:0:0:0:0:1, IP Address:192.168.0.141, 
    IP Address:FE80:0:0:0:2481:BA24:A7E5:3DA1
```
* Copy the TLS cert to user admin
```
admin ~  ฿  sudo cp /home/bitcoin/.lnd/tls.cert /home/admin/.lnd
admin ~  ฿  sudo chown -R admin:admin /home/admin/.lnd
```

# Create Macroon file on Host #
Different Macaroons permit the WAN Host to perform different tasks. See the end of [this file](https://github.com/Stadicus/guides/blob/master/raspibolt/raspibolt_66_remote_lncli.md) for full details. In this exercise, we will use the *invoice.macaroon*.

* Get ASCII version of the macaroon file
```
admin ~  ฿  ls -la /home/bitcoin/.lnd/invoice.macaroon
admin ~  ฿  xxd -ps -u -c 1000  /home/bitcoin/.lnd/invoice.macaroon

0201036C6.....7377C49EE
```

* Login to the Command Prompt of your WAN Host
* RaspiBolt: Copy to your clipboard the output from xxd in the step above
* WAN Host: Paste it into the command below
```
MyWanHost $: echo '0201036C6.....7377C49EE' > invoice_macaroon.base64
```
* Convert invoice_macaroon.base64 into invoice.macaroon
```
MyWanHost $: xxd -r -p  invoice_macaroon.base64 invoice.macaroon
MyWanHost $: ls -la invoice.macaroon
```
* Confirm size in bytes of *invoice.macaroon* is same on RaspiBolt and WAN Host
   
# Test - using cli on WAN Host #

Replace CHANGE_ME with either my.ip.address OR my.fqdn - must be same as tlsextraip or tlsextradomain in lnd.conf

```
MyWanHost $: curl --insecure  --header "Grpc-Metadata-macaroon: $(cat invoice_macaroon.base64)"   https://CHANGE_ME:8080/v1/invoices -d '{"memo":"test","value":"100000"}'

{"r_hash":"nNOovBr33sTBWxH8qjUhHQvkxZBFEAYCXUgV8s2Z684=",
  "payment_request":"lntb1m1p...wn"
}
```

# Test - using PHP on WAN Host #
Create [lnd.php](resources/lnd.php) on your WAN Host. Change the two CHANGE_ME values near the top of the file.

To see this in action, open `http://your.web.server/lnd.php`

