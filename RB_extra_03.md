[All Extras](README.md) / Simultaneous mainnet & testnet

UNDER CONSTRUCTION

# Introduction #
There is no reason that you can not run both mainnet & testnet lnd instances at the same time on one RaspiBolt. These instrictions assume you have a working RaspiBolt running in Testnet mode.

# Summary of Changes Needed #
This table shows the state we need to get to so the two lnd instances do not clash. 

Changes highlighted in <b>bold</b>.

|Module|Chain|Item|Original|After Change|
|-----:|-----|----|--------|------------|
|bitcoind|mainnet|Port|8333|8333|
|||RPC Port|xx|xx|
|||conf file|xx|xx|
|||service file|xx|xx|
|||data files|xx|xx|
||testnet|Port|18333|18333|
|||RPC Port|xx|xx|
|||conf file|xx|xx|
|||service file|xx|xx|
|||data files|xx|xx|
|lnd|mainnet|Port|9375|9375|
|||RPC Port|10009|10009|
|||conf file|xx|xx|
|||service file|xx|xx|
|||data files|xx|xx|
|||wallet file|xx|xx|
||testnet|Port|9375|19375|
|||RPC Port|10009|<b>11009</b>|
|||conf file|xx|xx|
|||service file|xx|xx|
|||data files|xx|xx|
|||wallet file|xx|xx|


