[All Extras](README.md) / Simultaneous mainnet & testnet

UNDER CONSTRUCTION
# Introduction #
There is no reason that you can not run both mainnet & testnet lnd instances at the same time on one RaspiBolt. These instructions assume you have a working RaspiBolt running in Testnet mode.

# An Diagram to make things clearer #
![Image Ports](images/RaspiBoltDuo02.png)

After completing these instructions here, the 4 instances shown will be operating on the ports shown above. 

# Overview #

1. [Open new firewall port](#open-new-firewall-port)
1. [Shutdown existing services](#shutdown-existing-services)
1. [Create new conf files](#new-conf-files)
1. [Create new services](#new-services)
1. [Open new firewall ports](#open-new-firewall-ports)
1. [Enable and start new services](#enable-and-start-new-services)
1. [Optional] [Update the raspibolt System Overview utility](#update-the-raspibolt-system-overview-utility)

## Open New Firewall Port ##
1. Open new port in RaspiBolt


`admin ~  ฿  sudo su`
```

$ ufw allow 19735  comment 'allow Lightning testnet'
$ ufw status
$ exit
```
```
root@RaspiBolt:/home/admin# ufw status
Status: active

To                         Action      From
--                         ------      ----
22                         ALLOW       192.168.0.0/24             # allow SSH from local LAN
9735                       ALLOW       Anywhere                   # allow Lightning
8333                       ALLOW       Anywhere                   # allow Bitcoin mainnet
18333                      ALLOW       Anywhere                   # allow Bitcoin testnet
19735                      ALLOW       Anywhere                   # allow Lightning (testnet)
9735 (v6)                  ALLOW       Anywhere (v6)              # allow Lightning
8333 (v6)                  ALLOW       Anywhere (v6)              # allow Bitcoin mainnet
18333 (v6)                 ALLOW       Anywhere (v6)              # allow Bitcoin testnet
19735 (v6)                 ALLOW       Anywhere (v6)              # allow Lightning (testnet)
```

2. Open new port in Router

Review the [Raspberry Pi](https://github.com/Stadicus/guides/blob/master/raspibolt/raspibolt_20_pi.md) guide from Stadicus. Go to the *Accessing your router* section and add this port forwarding:

|Application name|External port|Internal port|Internal IP address|Protocol (TCP or UDP)|
|--|--|--|--|--|
|19375|19375|`<Raspibolt IP>`|`<Raspibolt IP>`|BOTH|



## Shutdown existing services ##
```
admin ~  ฿  sudo systemctl stop lnd
admin ~  ฿  sudo systemctl stop bitcoind
admin ~  ฿  sudo systemctl disable lnd
admin ~  ฿  sudo systemctl disable bitcoind
```
## New conf files ##
Create or update these 2 files.
1. /home/bitcoin/.bitcoin/bitcoin.conf
1. /home/bitcoin/.bitcoin/testnet3/bitcoin.conf
1. /home/bitcoin/.bitcoin/lnd????????.conf
1. /home/bitcoin/.bitcoin/testnet3/bitcoin.conf

`admin ~  ฿  sudo nano /home/bitcoin/.bitcoin/bitcoin.conf`
Note: Change PASSWORD_[B] to your PASSWORD_[B]
```

# RaspiBolt LND  bitcoind configuration
# /home/bitcoin/.bitcoin/bitcoin.conf

zmqpubrawblock=tcp://127.0.0.1:29000
zmqpubrawtx=tcp://127.0.0.1:29000

# Bitcoind options
server=1
daemon=1
txindex=1
disablewallet=1

# Connection settings
rpcuser=raspibolt
rpcpassword=PASSWORD_[B]

# Raspberry Pi optimizations
dbcache=100
maxorphantx=10
maxmempool=50
maxconnections=40
maxuploadtarget=5000
```

`admin ~  ฿  sudo nano /home/bitcoin/.bitcoin/testnet3/bitcoin.conf`
Note: Change PASSWORD_[B] to your PASSWORD_[B]
```
# RaspiBolt LND  bitcoind configuration
# /home/bitcoin/.bitcoin/testnet3/bitcoin.conf

testnet=1
zmqpubrawblock=tcp://127.0.0.1:28332
zmqpubrawtx=tcp://127.0.0.1:28332

# Bitcoind options
server=1
daemon=1
txindex=1
disablewallet=1

# Connection settings
rpcuser=raspibolt
rpcpassword=PASSWORD_[B]

# Raspberry Pi optimizations
dbcache=100
maxorphantx=10
maxmempool=50
maxconnections=40
maxuploadtarget=5000
```

`admin ~  ฿  sudo cat /home/bitcoin/.lnd/lnd.conf`
Edit this line as needed: *alias=YOUR_NAME [LND]*
```
# RaspiBolt LND Mainnet: lnd configuration
# /home/bitcoin/.lnd/lnd.conf

[Application Options]
debuglevel=info
debughtlc=true
maxpendingchannels=5
alias=YOUR_NAME [LND]
color=#68F442

[Bitcoin]
bitcoin.active=1

# enable either testnet or mainnet
#bitcoin.testnet=1
bitcoin.mainnet=1

bitcoin.node=bitcoind

[Bitcoind]
bitcoind.zmqpath=tcp://127.0.0.1:29000

[autopilot]
autopilot.active=1
autopilot.maxchannels=5
autopilot.allocation=0.6
```

`admin ~  ฿  sudo cat /home/bitcoin/.lnd/testnet3/lnd.conf`
Edit this line as needed: *alias=YOUR_NAME [LND]*
```
# RaspiBolt LND Testnet: lnd configuration
# /home/bitcoin/.lnd/testnet3/lnd.conf

[Application Options]
debuglevel=info
debughtlc=true
maxpendingchannels=5
alias=YOUR_NAME [LND]
color=#68F442
rpclisten=localhost:11009
restlisten=localhost:8081
listen=0.0.0.0:19735

[Bitcoin]
bitcoin.active=1

# enable either testnet or mainnet
bitcoin.testnet=1
#bitcoin.mainnet=1

bitcoin.node=bitcoind

[Bitcoind]
bitcoind.zmqpath=tcp://127.0.0.1:28332

[autopilot]
autopilot.active=1
autopilot.maxchannels=5
autopilot.allocation=0.6
```

## New services ##
Create or update these 4 files.
1. bitcoind.service
1. bitcoind_testnet.service
1. lnd.service
1. lnd_testnet.service


`admin ~  ฿  sudo nano /etc/systemd/system/bitcoind.service`

```
# RaspiBolt LND Mainnet: systemd unit for bitcoind
# /etc/systemd/system/bitcoind.service

[Unit]
Description=Bitcoin daemon
Wants=getpublicip.service
After=getpublicip.service

# for use with sendmail alert (coming soon)
#OnFailure=systemd-sendmail@%n

[Service]
User=bitcoin
Group=bitcoin
Type=forking
PIDFile=/home/bitcoin/.bitcoin/bitcoind.pid
ExecStart=/usr/local/bin/bitcoind
KillMode=process
Restart=always
TimeoutSec=120
RestartSec=30

[Install]
WantedBy=multi-user.target
```

`admin ~  ฿ sudo nano /etc/systemd/system/bitcoind_testnet.service`

```
# RaspiBolt LND Testnet: systemd unit for bitcoind
# /etc/systemd/system/bitcoind_testnet.service

[Unit]
Description=Bitcoin Testnet daemon
Wants=getpublicip.service
After=getpublicip.service

# for use with sendmail alert (coming soon)
#OnFailure=systemd-sendmail@%n

[Service]
User=bitcoin
Group=bitcoin
Type=forking
PIDFile=/home/bitcoin/.bitcoin/testnet3/bitcoind.pid
ExecStart=/usr/local/bin/bitcoind -testnet
KillMode=process
Restart=always
TimeoutSec=120
RestartSec=30

[Install]
WantedBy=multi-user.target
```
`admin ~  ฿  sudo nano /etc/systemd/system/lnd.service`
```
xxxx
```

`admin ~  ฿  sudo nano /etc/systemd/system/lnd_testnet.service`
```
# RaspiBolt LND Testnet: systemd unit for lnd
# /etc/systemd/system/lnd_testnet.service

[Unit]
Description=LND Lightning Daemon
Wants=bitcoind.service
After=bitcoind.service

# for use with sendmail alert
#OnFailure=systemd-sendmail@%n

[Service]
# get var PUBIP from file
EnvironmentFile=/run/publicip

ExecStart=/usr/local/bin/lnd --datadir=/home/bitcoin/.lnd/testnet3 --externalip=${PUBLICIP}:19735
User=bitcoin
Group=bitcoin
LimitNOFILE=128000
Type=simple
KillMode=process
TimeoutSec=180
Restart=always
RestartSec=60

[Install]
WantedBy=multi-user.target

```

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


