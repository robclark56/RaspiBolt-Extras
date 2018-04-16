[All Extras](README.md) / Simultaneous mainnet & testnet

UNDER CONSTRUCTION
# Introduction #
There is no reason that you can not run both mainnet & testnet lnd instances at the same time on one RaspiBolt. These instructions assume you have a working RaspiBolt running in Testnet mode.

# An Diagram to make things clearer #
![Image Ports](images/RaspiBoltDuo02.png)

After completing these instructions here, the 4 instances shown will be operating on the ports shown above. 

# Overview #

1. [Shutdown existing services](#shutdown-existing-services)
1. [Create new conf files](#create-new-conf-files)
1. [Create new services](#create-new-services)
1. [Enable and start new services](#enable-and-start-new-services)
1. [Optional] [Update the raspibolt System Overview utility](#update-the-raspibolt-system-overview-utility)

## Shutdown existing services ##
```
admin ~  ฿  sudo systemctl stop lnd
admin ~  ฿  sudo systemctl stop bitcoind
admin ~  ฿  sudo systemctl disable lnd
admin ~  ฿  sudo systemctl disable bitcoind
```
## Create new conf files ##
## Create new services ##
## Enable and start new services ##
```
admin ~  ฿  sudo systemctl enable bitcoind
admin ~  ฿  sudo systemctl enable bitcoind_testnet
admin ~  ฿  sudo systemctl enable lnd
admin ~  ฿  sudo systemctl enable lnd_testnet
admin ~  ฿  sudo systemctl start bitcoind
admin ~  ฿  sudo systemctl start bitcoind_testnet
admin ~  ฿  sudo systemctl start lnd
admin ~  ฿  sudo systemctl start lnd_testnet
```
## Update the raspibolt System Overview utility ##


# Summary of Changes Needed #
This table shows the state we need to get to so the two lnd instances do not clash. 

|Module|Chain|Item|Original<br>After|Change?|
|-----:|-----|----|:-------|------------|
|bitcoind|mainnet|Public Port|8333<br>8333||
|||RPC Port|8332<br>8332||
|||conf file|/home/bitcoin/.bitcoin/bitcoin.conf<br>/home/bitcoin/.bitcoin/bitcoin.conf||
|||service file|/etc/systemd/system/bitcoind.service<br>/etc/systemd/system/bitcoind.service||
|||data/log files|xx<br>|xx|
||testnet|Public Port|18333<br>18333||
|||RPC Port|18332<br>18332||
|||conf file|/home/bitcoin/.bitcoin/bitcoin.conf<br>/home/bitcoin/.bitcoin/bitcoin_testnet.conf|Yes|
|||service file|/etc/systemd/system/bitcoind.service<br>/etc/systemd/system/bitcoind_testnet.service|Yes|
|||data/log files|xx<br>|xx|
|lnd|mainnet|Public Port|9735<br>9735||
|||RPC Port|10009<br>10009||
|||conf file|/home/bitcoin/.lnd/lnd.conf<br>|xx|
|||service file|/etc/systemd/system/lnd.service<br>/etc/systemd/system/lnd.service||
|||data/wallet files|???<br> |xx|
|||log files|/home/bitcoin/.lnd<br>???|xx|
|||Security files|/home/bitcoin/.lnd<br>???|xx|
||testnet|Public Port|9735<br>19735|Yes|
|||RPC Port|10009<br>11009|Yes|
|||conf file|/home/bitcoin/.lnd/lnd.conf<br>/home/bitcoin/.lnd/lnd_testnet.conf|Yes|
|||service file|/etc/systemd/system/lnd.service<br>/etc/systemd/system/lnd_testnet.service|Yes|
|||data/wallet files|/home/bitcoin/.lnd/data/chain/bitcoin/testnet<br>/home/bitcoin/.lnd/data/chain/bitcoin/testnet||
|||log files|/home/bitcoin/.lnd/logs/bitcoin/testnet<br>/home/bitcoin/.lnd/logs/bitcoin/testnet||
|||Security files|/home/bitcoin/.lnd<br>????|xx|


`Use Password_[A]`

admin ~  ฿  sudo adduser bitcoin_testnet
admin ~  ฿  cd /mnt/hdd 
admin /mnt/hdd  ฿  sudo su bitcoin
bitcoin@RaspiBolt:/mnt/hdd $ mkdir bitcoin_testnet
bitcoin@RaspiBolt:/mnt/hdd $ mkdir lnd_testnet
bitcoin@RaspiBolt:/mnt/hdd $ exit
admin /mnt/hdd  ฿  sudo chown -R bitcoin_testnet:bitcoin_testnet /mnt/hdd/bitcoin_testnet
admin /mnt/hdd  ฿  sudo chown -R bitcoin_testnet:bitcoin_testnet /mnt/hdd/lnd_testnet


