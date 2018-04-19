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

## lncli on Host ##
Follow these instructions: [Auto Wallet Unlock](RB_extra_01.md), but make these changes

1. Security: As you will not be storing any passwords on your Host that allow a hacker to *spend* funds in your wallet, you can ignore the security warnings. 
1. Host: If you already have a host computer, ignore the instructions on creating and using Google Cloud Platform (GCP). Instead follow the instructions as they apply to your login account on your Host. 
1. Do not:
   * install expect
   * create the expect script (lnd_unlock.exp)
   * create the cron file (lnd_unlock)
1. Do:
   * Copy the *invoice.macaroon* file to your Host. You can copy the readonly.macaroon if you want. Do *not* copy the admin.macaroon file to your Host 
1. Enable HTTP access. 
   * Goto the GCP > Compute Engine > VM Instances page. 
   * Click on the instance name to open the *VM instance details* page. 
   * Click *Edit* (top of page). 
   * Click to enable *Firewalls > Allow HTTP traffic*, and *Save*.
1. Install Web Server (if needed). 
   * If your Host already has a Web server, ignore this step. 
   * If you are using a GCP host:
     1. install a web server following [these instructions](https://cloud.google.com/compute/docs/tutorials/basic-webserver-apache)
     1. add PHP: `$ sudo apt install php7.0`

1. Check lncli is working on your VM
   * ` $./run_lncli getinfo`
1. Create run_lncli_invoice
   * `$ cp -p run_lncli run_lncli_invoice`
1. Edit and save run_lncli_invoice to change one line
   * `     --macaroonpath=$home_dir/.lnd/invoice.macaroon  \`
  
1. Check run_lncli_invoice
   * ```
     $ ./run_lncli_invoice addinvoice
     {
        "r_hash": "33210b1e3b......2e8",
        "pay_req": "lntb1pddsda......yvp"
     }
     ```

## Create Web Pages ##
In this section the term WEB_ROOT refers to the directory on your web server holding teh default index.html file. On a GCP instance, WEB_ROOT is '/var/www/html'

1. Login to your Host, and change to WEB_ROOT
   * e.g. `cd  /var/www/html`
1. Create files on WEB_ROOT
   * ```
     $ sudo wget https://raw.githubusercontent.com/robclark56/RaspiBolt-Extras/master/resources/ln_pr.css
     $ sudo wget 
     ```
1. Create or edit the files below. The command to use is:  `$  nano <filename>`

<details><summary>Click to see ln_pr.css</summary><p>

```
.button{background-color:#00C658;-moz-border-radius:4px;-webkit-border-radius:4px;border-radius:4px;display:inline-block;cursor:pointer;color:#ffffff;font-size:15px;padding:6px 15px;text-decoration:none;width:168px}.button:active{position:relative;top:1px}body{margin:0 0 0 0;padding:0 0 0 0}#gear-widget{width:auto;font-size:85%;font-family:"Lucida Grande", "Lucida Sans Unicode", Helvetica, Arial, Verdana, sans-serif}#gear-widget .footer{border-radius:0 0 5px 5px;margin-top:-5px;height:20px;padding:0.3em;color:#ccc;text-align:center;font-size:80%;text-align:right}#gear-widget .footer a,#gear-widget .footer a:visited,#gear-widget .footer a:active{color:black;text-decoration:none}#gear-widget .footer div{margin-top:5px;margin-right:5px}#gear-widget .productInfo,#gear-widget .paymentInfo{border-radius:5px;padding:1em}#gear-widget .shadow{-webkit-box-shadow:1px 1px 1px 0px rgba(50,50,50,0.35);-moz-box-shadow:1px 1px 1px 0px rgba(50,50,50,0.35);box-shadow:1px 1px 1px 0px rgba(50,50,50,0.35)}#gear-widget #quantity{width:200px}#gear-widget input#variable_price{margin-left:-10px}#gear-widget #quantityPlaceholder{color:#B4B4B4;position:absolute;margin-left:-70px;margin-top:10px}#gear-widget #pricePlaceholder{margin-left:-40px;color:#B4B4B4}#gear-widget .productInfo{text-align:center}#gear-widget .productInfo .title,#gear-widget .productInfo .price{margin-bottom:0.2em}#gear-widget .productInfo .title{font-size:x-large;font-weight:bold}#gear-widget .productInfo .productSelector{margin-bottom:0.3em}#gear-widget .productInfo .productSelector select{height:35px;display:block;border:solid 1px #E4E9EA;min-width:200px;max-width:320px;margin-left:auto;margin-right:auto;border-radius:5px;background-color:white}#gear-widget .productInfo .productSelector select:focus{outline:none;border:1px solid #C4E5CA}#gear-widget .productInfo input{border-radius:5px;border:solid 1px #E4E9EA;padding:0.3em;width:200px;height:35px;font-size:100%}#gear-widget .productInfo input:focus{outline:none;border-radius:5px;border:1px solid #C4E5CA}#gear-widget .productInfo .variableAmount{margin:0.5em 0}#gear-widget .productInfo .variableAmount p{margin-bottom:0px}#gear-widget .productInfo .variableAmount .title{margin-bottom:0.5em}#gear-widget .productInfo .field,#gear-widget .productInfo .productQuantity{margin-bottom:0.5em}#gear-widget .paymentInfo{display:none;font-size:85%}#gear-widget .paymentInfo textarea{font-weight:bold;border:solid 1px #aaa;background-color:#fff;padding:0.2em;border-radius:3px;width:100px}#gear-widget .paymentInfo textarea.transactionId{width:100%;height:auto}#gear-widget .paymentInfo .title{font-weight:bold}#gear-widget .paymentInfo .statement{margin-bottom:10px}#gear-widget .paymentInfo.new,#gear-widget .paymentInfo.partiallyPaid{display:none}#gear-widget .paymentInfo.new table,#gear-widget .paymentInfo.partiallyPaid table{width:100%;border-spacing:10px;border-collapse:separate;text-align:center}#gear-widget .paymentInfo.new td,#gear-widget .paymentInfo.partiallyPaid td{vertical-align:top;padding:0px;margin:0px}#gear-widget .paymentInfo.new td.info p,#gear-widget .paymentInfo.partiallyPaid td.info p{margin:0 0 1em 0;font-size:85%}#gear-widget .paymentInfo.new td.timeLeft,#gear-widget .paymentInfo.partiallyPaid td.timeLeft{text-align:center;margin-bottom:0.4em;color:#B4B4B4;font-size:70%}#gear-widget .paymentInfo.new td.cancel,#gear-widget .paymentInfo.partiallyPaid td.cancel{text-align:center;margin:0 0 0 0;color:#B4B4B4;font-size:90%}#gear-widget .paymentInfo.new .qrcode,#gear-widget .paymentInfo.partiallyPaid .qrcode{width:100px;height:100px}#gear-widget .paymentInfo.new .qrcode-placeholder,#gear-widget .paymentInfo.partiallyPaid .qrcode-placeholder{background-color:white;width:100px;height:100px;margin:auto;border:solid 5px white;border-radius:5px}#gear-widget .paymentInfo.new .depositAddressString,#gear-widget .paymentInfo.partiallyPaid .depositAddressString{width:100%}#gear-widget .paymentInfo.new .tapOrClickCaption,#gear-widget .paymentInfo.partiallyPaid .tapOrClickCaption{text-align:center;font-size:120%;font-weight:bold;margin-bottom:0.5em}#gear-widget .paymentInfo.new .convertation-result,#gear-widget .paymentInfo.partiallyPaid .convertation-result{color:#B4B4B4}#gear-widget .paymentInfo.partiallyPaid .tapOrClickCaption{margin-top:0}#gear-widget .paymentInfo.paid{display:none;text-align:center}#gear-widget .paymentInfo.paid .title{font-size:160%}#gear-widget .paymentInfo.paid img{width:80px;height:80px;margin:10px}#gear-widget .paymentInfo.paid .statement{font-size:120%}#gear-widget .paymentInfo.paid .ad_banner{width:90%;height:40px;background-color:#D0D8D8;border-radius:5px;margin:auto;margin-top:10px}#gear-widget .paymentInfo.underpaid,#gear-widget .paymentInfo.connectionProblem,#gear-widget .paymentInfo.serverError,#gear-widget .paymentInfo.userError,#gear-widget .paymentInfo.overpaid,#gear-widget .paymentInfo.expired,#gear-widget .paymentInfo.processing,#gear-widget .paymentInfo.partiallyPaid{display:none;text-align:center}#gear-widget .paymentInfo.underpaid .title,#gear-widget .paymentInfo.connectionProblem .title,#gear-widget .paymentInfo.serverError .title,#gear-widget .paymentInfo.userError .title,#gear-widget .paymentInfo.overpaid .title,#gear-widget .paymentInfo.expired .title,#gear-widget .paymentInfo.processing .title,#gear-widget .paymentInfo.partiallyPaid .title{color:#FF0000;font-size:150%;margin-bottom:10px}#gear-widget .paymentInfo.processing .progressBar{margin-top:5px}#gear-widget .paymentInfo.underpaid.red .title,#gear-widget .paymentInfo.connectionProblem.red .title,#gear-widget .paymentInfo.serverError.red .title,#gear-widget .paymentInfo.userError.red .title,#gear-widget .paymentInfo.overpaid.red .title,#gear-widget .paymentInfo.expired.red .title,#gear-widget .paymentInfo.processing.red .title,#gear-widget .paymentInfo.partiallyPaid.red .title{color:#4C5353}#gear-widget .paymentInfo.overpaid .title .info,#gear-widget .paymentInfo.underpaid .title .info,#gear-widget .paymentInfo.partiallyPaid .title .info{font-weight:normal;font-size:67%}#gear-widget .paymentInfo.overpaid p,#gear-widget .paymentInfo.underpaid p,#gear-widget .paymentInfo.partiallyPaid p{margin-top:22px}#gear-widget .paymentInfo.overpaid p .transactionId,#gear-widget .paymentInfo.underpaid p .transactionId,#gear-widget .paymentInfo.partiallyPaid p .transactionId{word-wrap:break-word}#gear-widget .paymentInfo.overpaid .transactionsHolder,#gear-widget .paymentInfo.underpaid .transactionsHolder,#gear-widget .paymentInfo.partiallyPaid .transactionsHolder{margin-top:22px}#gear-widget .paymentInfo.overpaid .transactionsHolder ul,#gear-widget .paymentInfo.underpaid .transactionsHolder ul,#gear-widget .paymentInfo.partiallyPaid .transactionsHolder ul{list-style:none;padding:0;margin-top:1px}#gear-widget .black{background-color:#1e211f;color:white}#gear-widget .black table{color:white}#gear-widget .black a,#gear-widget .black a:visited,#gear-widget .black a:active{color:#919894}#gear-widget .black td.timeLeft{color:#919894 !important}#gear-widget .gray{background-color:#f3f5f5}#gear-widget .red{background-color:red;color:white}#gear-widget .red table{color:white}#gear-widget .red a,#gear-widget .red a:visited,#gear-widget .red a:active{color:#FFC4C4}#gear-widget .red .button{background-color:#4C5353;border:1px solid #4C5353}#gear-widget .red td.timeLeft{color:#FFC4C4 !important}#gear-widget .paid.red img{filter:url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg'><filter id='grayscale'><feColorMatrix type='matrix' values='0.3333 0.3333 0.3333 0 0 0.3333 0.3333 0.3333 0 0 0.3333 0.3333 0.3333 0 0 0 0 0 1 0'/></filter></svg>#grayscale");filter:gray;-webkit-filter:grayscale(100%)}#gear-widget .footer.red{background-color:rgba(255,0,0,0.5);color:#FFC4C4}#gear-widget .footer.red a,#gear-widget .footer.red a:visited,#gear-widget .footer.red a:active{color:#FFFFFF;text-decoration:none}#gear-widget .footer.gray{background-color:rgba(243,245,245,0.5);color:#8F9C9D}#gear-widget .footer.black{background-color:rgba(30,33,31,0.5);color:#919894}#gear-widget .footer.black a,#gear-widget .footer.black a:visited,#gear-widget .footer.black a:active{color:#919894;text-decoration:none}
```
</p></details>
<details><summary>Click to see index.html</summary><p>

```bash
xxxx
```
</p></details>

