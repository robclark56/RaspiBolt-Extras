[All Extras](README.md) / Receive LN Payments

UNDER CONSTRUCTION

---

# Introduction #
In this guide, you will create and display Payment Requests (PR)so that you can recieve Lightning payments.

All PRs have an *expiry* value, and that means it is not possible to create a PR with an infinite validity. The instructions below demonstrate the principals - but for a useful system, the PRs should be generated 'live' and then displayed to the customer.

In much of the Lightning Network documentation the terms *Invoice* and *Payment Request* are synonynous. [Dictionary.com](http://www.dictionary.com/browse/invoice) makes it clear that an Invoice should be provided when the good or service paid for is *delivered*. For that reason, we will use only the term *Payment Request* in this guide.

There are two types of payment requests:

1. Receiver Decides Amount. It this case, the payment request has the amount to pay hard-coded in the request. It can not be changed. 
1. Sended Decides Amount: It this case the amount to pay is set to zero. Most Lightning wallets interpret this to means the sender can enter their own amount to send. Ideal for donations.

In the steps below, *lncli* is used to generate the PR. If your lnd is currently running on testnet, then it will be a testnet PR. If your lnd is currently running on mainnet, then it will be a mainnet PR.

# Procedure #

* Login to your [RaspiBolt](https://github.com/Stadicus/guides/blob/master/raspibolt/README.md) as user *admin*
* Create a Donation PR
`admin ~  ฿  lncli addinvoice --memo 'Donation Payment request' --expiry 3600`
```bash
{
  "r_hash": "8bada7d5a4b8397524076396da0f13dcb400e6f304e5b4d3b9c60c3a2dbbd7c7",
  "pay_req": "lntb1pdd0e48pp53wk604dyhquh2fq8vwtd5rcnmj6qpehnqnjmf5aeccxr5tdm6lrsdp8g3hkuct5d9hkugzsv9uk6etwwss8yet3w4jhxaqcqzys8p04easl3lk5dld2yauq03akn5h5mehhjcm76jtw9w800nj8r5khv2qht7nlwa5jqhvzxcnf5shyxzjj5plz53n24f0fmjxldhlmzlsqv3mw9p"
}
```
* Check the PR
```bash
admin ~  ฿  lncli decodepayreq "lntb1pdd0e48pp53wk604dyhquh2fq8vwtd5rcnmj6qpehnqnjmf5aeccxr5tdm6lrsdp8g3hkuct5d9hkugzsv9uk6etwwss8yet3w4jhxaqcqzys8p04easl3lk5dld2yauq03akn5h5mehhjcm76jtw9w800nj8r5khv2qht7nlwa5jqhvzxcnf5shyxzjj5plz53n24f0fmjxldhlmzlsqv3mw9p"
{
    "destination": "022ecebcf3c95f39934b30d7d56c42d2fa1b110054f6672301ecdb56c5941020d4",
    "payment_hash": "8bada7d5a4b8397524076396da0f13dcb400e6f304e5b4d3b9c60c3a2dbbd7c7",
    "num_satoshis": "0",
    "timestamp": "1524098727",
    "expiry": "3600",
    "description": "Donation Payment request",
    "description_hash": "",
    "fallback_addr": "",
    "cltv_expiry": "144"
}
```
* Generate the QR Code*
Visit [www.qr-code-generator.com](https://www.qr-code-generator.com/)

Click the *Text* option at the top and paste your PR excluding the "" characters in the *Message* box and then click *Create QR Code*






