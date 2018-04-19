[All Extras](README.md) / [Receive LN Payments](README.md#receive-ln-payments) / Live Payment Requests

# Introduction #
In [One-time Payment Requests](RB_extra_04.mb) it was demonstated how to make one-time Payment Requests (PRs). This guide expands on that to have a Web site generate PRs on the fly.

Difficulty: Hard

# Requirements #
1. A working [RaspiBolt](https://github.com/Stadicus/guides/blob/master/raspibolt/README.md)
1. A computer ("Host") under your control, that has  a web server with ability to run PHP scripts. In this guide, we will go through the steps on a Google Cloud Platform (CGP) virtual machine (VM), but you should perform similar steps on your Host.

# Procedure #
The overall steps are:
1. Install *lncli* on the Host computer, and configure it to connect to lnd running on your RaspiBolt
1. Create web pages (HTTP) on the Host that retrieve a PR from the RaspiBolt, and then display on a web page.

## lnci on Host ##
Follow these instructions: [Auto Wallet Unlock](RB_extra_01.md), but make these changes

1. Security: As you will not be storing any passwords on your Host that allow a hacker to *spend* funds in your wallet, you can ignore the secuity warnings. 
1. Host: If you already have a host computer, ignore the instructions on creating and using Google Cloud Platform (GCP). Instead follow the instructions as they apply to your login account on your Host. 
1. Do not:
   * install expect
   * create the expect script (lnd_unlock.exp)
   * create the cron file (lnd_unlock)
1. Install Web Server (if needed). If your Host already has a Web server, ignore this step. If you are using a GCP host, install a web server following [these instructions](https://cloud.google.com/compute/docs/tutorials/basic-webserver-apache).
1. Copy the *invoice.macaroon* file to your Host. You can copy the readonly.macaroon if you want. Do *not* copy the admin.macaroon file to your Host  
  

## Create Web Pages ##
xxxxx

