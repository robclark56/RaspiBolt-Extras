[All Extras](README.md) / Simultaneous mainnet & testnet

UNDER CONSTRUCTION
# Introduction #
There is no reason that you can not run both mainnet & testnet lnd instances at the same time on one RaspiBolt. These instrictions assume you have a working RaspiBolt running in Testnet mode.

# Summary of Changes Needed #
This table shows the state we need to get to so the two lnd instances do not clash. 

|Module|Chain|Item|Original<br>After|Change?|
|-----:|-----|----|--------|------------|
|bitcoind|mainnet|Port|8333|8333|
|||RPC Port|xx|xx|
|||conf file|/home/bitcoin/.bitcoin/bitcoin.conf|xx|
|||service file|/etc/systemd/system/bitcoind.service|xx|
|||data/log files|xx|xx|
||testnet|Port|18333<br>18333|<br>|
|||RPC Port|xx|xx|
|||conf file|/home/bitcoin/.bitcoin/bitcoin.conf|xx|
|||service file|/etc/systemd/system/bitcoind.service|xx|
|||data/log files|xx|xx|
|lnd|mainnet|Port|9375<br>9375||
|||RPC Port|10009<br>10009||
|||conf file|/home/bitcoin/.lnd/lnd.conf|xx|
|||service file|/etc/systemd/system/lnd.service|xx|
|||data/wallet files|??? |xx|
|||log files|/home/bitcoin/.lnd|xx|
|||Security files|/home/bitcoin/.lnd|xx|
||testnet|Port|9375|19375|
|||RPC Port|10009<br>11009|Yes|
|||conf file|/home/bitcoin/.lnd/lnd.conf|xx|
|||service file|/etc/systemd/system/lnd.service|xx|
|||data/wallet files|/home/bitcoin/.lnd/data/chain/bitcoin/testnet |xx|
|||log files|/home/bitcoin/.lnd/logs/bitcoin/testnet |xx|
|||Security files|/home/bitcoin/.lnd|xx|


