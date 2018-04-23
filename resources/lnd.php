<?php
/*
*  Example PHP file to get a Payment Request from an lnd instance on a different host

   See: https://github.com/robclark56/RaspiBolt-Extras/blob/master/RBE_REST_WAN.md
   
   Feel free to copy and use
   
   See this link to learn the full LND REST API: 
      https://github.com/ndeet/php-ln-lnd-rest/tree/master/docs/Api
      
   Optional GET Parameters:
    - memo=Text    (eg: memo=Thanks+for+the+Donation)
    - amt=Sataoshi (eg: amt=100000)
    - image_only   (eg: image_only=1). memo and/or amt must also be set.

*/

function getPaymentRequest($memo='',$satoshi=0){
 $lnd_ip         ='CHANGE_ME';
 $lnd_port       ='8080';
 $macaroon_base64='CHANGE_ME';
 
 $data = json_encode(array("memo"  => $memo,
                           "value" => "$satoshi"
                         )     
                    );            
                    
 $ch = curl_init("https://$lnd_ip:$lnd_port/v1/invoices");
 curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
 curl_setopt($ch, CURLOPT_POST, 1);
 curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
 curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
 curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    "Grpc-Metadata-macaroon: $macaroon_base64"
    ));
 $response = curl_exec($ch);
 curl_close($ch);

 $PR = json_decode($response);
 return $PR->payment_request;
}

if($_GET['memo'] || isset($_GET['amt'])){
 //Only make one PR
 $memo = $_GET['memo']?$_GET['memo']:'Example Payment Request';
 $amt = $_GET['amt']?$_GET['amt']:'0';
 $pr = getPaymentRequest($memo,$amt);
} else {
  //Make two default PRs
  $donation = getPaymentRequest('Donation');
  $fixed    = getPaymentRequest('Fixed Payment',100000);
}

if($pr && $_GET['image_only']){
 header('Cache-Control: no-store, no-cache, must-revalidate');
 header('Cache-Control: post-check=0, pre-check=0', FALSE);
 header('Pragma: no-cache');
 header("Location: http://qrickit.com/api/qr.php?qrsize=200&d=$pr");
 exit;
}


?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Example LND Payment Request</title>
</head>

<body>
<?php
if($pr){
?>
<br>
<img src="http://qrickit.com/api/qr.php?qrsize=200&d=<?php echo $pr;?>">
<br>
<button class="btn" data-clipboard-text="<?php echo $pr;?>">Copy to clipboard</button>

<?php
} else {
?>

<h3>Payment Request - Donation</h3>
<?php echo $donation;?>
<br>
<img src="http://qrickit.com/api/qr.php?qrsize=200&d=<?php echo $donation;?>">
<br>
<button class="btn" data-clipboard-text="<?php echo $donation;?>">Copy to clipboard</button>
<hr>
<h3>Payment Request - Fixed Amount</h3>
<?php echo $fixed;?>
<br>
<img src="http://qrickit.com/api/qr.php?qrsize=200&d=<?php echo $fixed;?>">
<br>
<button class="btn" data-clipboard-text="<?php echo $fixed;?>">Copy to clipboard</button>  
<?php
}
?>
<script src='https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/1.5.12/clipboard.min.js'></script>
<script >var clip = new Clipboard('.btn');</script>

</body>
</html>
