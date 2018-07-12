# RaspiBolt-Extras

These Extras expand on the excellent [RaspiBolt](https://github.com/Stadicus/guides/blob/master/raspibolt/README.md) Lightning Network (LND) project by [Stadicus](https://github.com/Stadicus/)

![RaspiBolt](https://github.com/Stadicus/guides/raw/master/raspibolt/images/00_raspibolt_banner_440.png)

---

# The 'Lights Out' RaspiBolt

|<img src="images/lightsoff.gif" alt="Lights Off" height="120" width="240">|The objective of this pair of Extras is to allow a RaspiBolt to recover *without human intervention* from a power failure and/or a change in public IP.|
|---|:--|

1. [Auto Lightning Wallet Unlock](RB_extra_unlock_PK.md)
1. [Dynamic Public IP Address](RB_extra_02.md)

---

# RaspiBoltDuo
## Running LND mainnet & testnet Simultaneously on one RaspiBolt

|<img src="images/RaspiBoltDuo.png" alt="Simultaneous mainnet & testnet" height="120" width="150">|The objective of this Extra is to have two instances of bitcoind and two of lnd running on the same RaspiBolt.|
|---|:--|

3. [Simultaneous mainnet & testnet](RB_extra_03.md)

---

# Using REST Access #
## Including generating and displaying Payment Request QR Codes ##
|<img src="images/RBE_REST.jpg" alt="REST" width="120" height="120">|The objective of this Extra is to enable and demonstrate using the REST interface instead of rpc/lncli.|
|---|:--|

4. [Enable and Use REST with lnd - LAN](RBE_REST.md). 
5. [Enable and Use REST with lnd - WAN](RBE_REST_WAN.md). 
   * Using lncli, or 
   * Using REST & PHP. Includes generating QR codes. Requires web server.

---

# Receive LN Payments
## Donations (sender decides) & Fixed Amounts (you decide)
|![QR Demo](images/RBE-QR_demo.png)|The objective of this Extra is to demonstrate generating Payment Requests including QR codes.|
|---|:--|
6. [One-Time Payment Requests (a.k.a Invoices)](RB_extra_04.md) using lncli
7. [Live Payment Requests (a.k.a Invoices)](RB_extra_05.md) using lncli, or REST & PHP (Requires web server).

---

|![Busy Programmer](images/RaspiBoltBusy.jpg)|Like these Guides? [Donate](RBE_donation.md) some satoshis.|
|--|--|

