[All Extras](README.md) / [Lights Out](https://github.com/robclark56/RaspiBolt-Extras/blob/master/README.md#the-lights-out-raspibolt) / Dynamic Public IP

# Introduction #
If your [RaspiBolt]() is running on an internet connection that does not have a static IP address and your ISP changes your IP address, then your Lightning Network Node (LNN) has effectively disappeared from the Lightning Network (LN).

There is discussion among the lnd developers about adding a new *lncli* command to update the *--externalip* parameter without restarting lnd - which locks the wallet. Until that is implemented, the solution presented here is a stop-gap solution.

The basic method is:

* Every 10 minutes:
  * Notice that the external IP address has changed
  * Restart lnd

If you also want to automatically unlock the wallet, see [Auto Lightning Wallet Unlock](https://github.com/robclark56/RaspiBolt-Extras/blob/master/RB_extra_01.md).

# Procedure #

* Login to your RaspiBolt as  user *admin*
* Edit the following script, save and exit

`admin ~  ฿  sudo nano /usr/local/bin/getpublicip.sh`

```
#!/bin/bash
# RaspiBolt LND Mainnet: script to get public ip address
# /usr/local/bin/getpublicip.sh

echo 'getpublicip.sh started, writing public IP address every 10 minutes into /run/publicip'
while [ 0 ];do
 source /run/publicip
 CURRENTIP=$(curl ipinfo.io/ip 2> /run/publicip.log )
 echo  PUBLICIP=$CURRENTIP > /run/publicip;
 if [ "$CURRENTIP" != "$PUBLICIP" ];then
  echo Restarting lnd.service New external IP = $CURRENTIP
  sudo /bin/systemctl restart lnd.service
 fi
 sleep 600
done;
```
