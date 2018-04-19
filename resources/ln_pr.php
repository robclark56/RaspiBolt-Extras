<?php
$ThisFile = basename(__FILE__);
unset ($Action);
$error = false;

if($_POST){
 //echo "POST:".print_r($_POST,1);

 if(isset($_POST['stripeToken'])){
  //Payment
 
   $params['amount']    = $_POST['cents']; 
   $params['source']    = $_POST['stripeToken'];      				
   $params['currency']  = 'aud';
   // $params['capture']       = 'true' ;
   $params['description']   = $_POST['Description'];
   $params['receipt_email'] = $_POST['stripeEmail'];
   
   $Response= json_decode(stripe_sendTransactionToGateway('https://api.stripe.com/v1/charges', $params),true);
 
   if($Response['error']){
     $messageStack->add($ThisFile, $Response['error']['message'], 'error');
   } else {
     $messageStack->add($ThisFile, $Response['outcome']['seller_message'], ($Response['captured']?'success':'error'));
     if($Response['captured']){
      $messageStack->add($ThisFile, 'A receipt has been emailed to '.$_POST['stripeEmail'], 'success');   
      mail(STORE_OWNER_EMAIL_ADDRESS,'AdHoc Credit Card Payment Received',
     $_POST['Email']." has just paid $".($_POST['cents']/100). " (including surcharge) for ".$_POST['Description']."\n\n".
     "Full Details from Stripe:\n".print_r($Response,1)."\n\nFile: ".__FILE__
     );     
      unset ($_POST['Amount']);
      unset ($_POST['Email']);
      unset ($_POST['Description']);     
     }

   } 
  
 
 } else {
  //Before Confirm
  $Action = 'Confirm';
 
  $_Post['Amount'] = (float) $_POST['Amount'];
  if($_Post['Amount'] <= 0){
   $error = true;
   $messageStack->add($ThisFile, 'Amount in AU$ required. Do NOT include dollar sign ($) or commas (,)','error');
 }
 
  if(strlen($_POST['Email']) < 10){
   $error = true;
   $messageStack->add($ThisFile, 'Email address required','error');
  }
 
  if(strlen($_POST['Description']) < 4){
    $error = true;
   $messageStack->add($ThisFile, 'Description required','error');
  }

}
 
 
}//$_POST

?>

<div class="page-header">
  <img src="images/card_acceptance/visa.png"> <img src="images/card_acceptance/mastercard.png">
  <h1>Pay with Lightning</h1>
</div>

<div class="contentContainer">
 <div>
  <h3>Instructions</h3>
  <ol>
   <li>If you want to pay for a specific online order, do not use this page. Please pay from <a target="_blank" href="https://ubwh.com.au/account_history.php">your Accounts page</a> instead.
   <li>Enter your email address in the top box. We need this if we receive a payment that we can&#8217t match to an order.
   <li>Enter a Description in the 2nd box. Example: If you are paying for order UBWH-5555, enter  UBWH-5555
   <li>Enter amount to pay in the 3rd box (AUD). Do NOT include the dollar sign ($) or commas (,).
   <li>Press the green button.
   <li>The next screen you see will show the Total to be charged to your card, including processing surcharges.
   <li>Click the blue button to proceed.
   </ul>
  </ol>   

  <link rel="stylesheet" media="screen" href="ln_pr.css" />
  <form action="" method="POST">
   <table>
    <tr>
     <td>
      <div class='payment' id='gear-widget'>
       <div class='container' style='display: inline'>
        <div class='black productInfo'>
         <div class='variableAmount'>
          <div class='title'>XXXX</div>
          <div class='field' style="color:black"><input type="text" value="<?php echo $_POST['Email']?>" name="Email" id="Email" placeholder="*Email" data-required="1" /></div>
          <div class='field' style="color:black"><input type="text" value="<?php echo $_POST['Description']?>" name="Description" id="Description" placeholder="Description" data-required="1" /></div>
          <div class='field' style="color:black"><input type="text" name="Amount" value="<?php echo $_POST['Amount']?>" id="variable_price" placeholder="0" /><span id='pricePlaceholder'>AUD</span></div>
         </div>
       
         <?php
          if(false && !$error && $Action == 'Confirm'){
           $Total = $_POST['Amount'] + 0.40 + .019 * $_POST['Amount'];
           $Total = number_format($Total,2,'.','');
          ?>
          <div class='field' style="color:black"><input type="text" value="Total (inc Fees) $<?php echo $Total;?>" name="Total"   data-required="0" /></div>
    
          <?php
            }  
          ?>
  
        </div>   
       </div> 
      </div>                                            
     </td>
    </tr>

    <tr>
     <td>
      <div style="background:black; text-align: center;">
      <?php 
       if((!$error && $Action == 'Confirm')){
         $cents_before_surcharge =   $_POST['Amount'] *100;        
         $description = $_POST['Description'];
         $label       = 'Pay with Card $';  
         echo ubwh_display_card_pay_button($cents_before_surcharge,$description,$label); 
       } else {
        echo '<button type="submit" class="btn btn-success"> <span class="glyphicon"></span> Pay with Card</button>';
       } 
      ?>
      </div>
     </td>
    </tr>
   </table>
  </form>
  <br> 
 </div>
</div> 
