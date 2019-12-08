<?php
include_once("lib/paypal/dkpProAccountLogEntry.php");
include_once("lib/paypal/paymentHistory.php");
include_once("lib/dkp/dkpGuild.php");
include_once("lib/dkp/dkpSettings.php");
include_once("lib/dkp/dkpUtil.php");

/*=================================================
The news page displays news to the user.
=================================================*/
class pageAccount extends page {

	var $layout = "Columns1";
	var $defaultView = "Unknown";
	/*=================================================
	Default View - shows error message
	=================================================*/
	function area2Unknown(){
		$this->border = 1;
		$this->title = "Huh?";
		return "Whats going on? You shouldn't be here!";
	}

	/*=================================================
	Complete View - shows subscription complete message
	=================================================*/
	function area2Complete()
	{
		global $siteUser;
		$guild = new dkpGuild();
		$guild->loadFromDatabase($siteUser->guild);


		/*$serverstr = str_replace(" ","+",$guild->server);
		$guildstr = str_replace(" ","+",$guild->name);

		global $SiteRoot;
		$baseurl = $SiteRoot."dkp/$serverstr/$guildstr/";*/

		$baseurl =  dkpUtil::GetGuildUrl($guild->id);



		$this->border = 1;
		$this->title = "Subscription Complete!";
		$this->set("baseurl", $baseurl);
		return $this->fetch("subscribe/subscribe.tmpl.php");
	}

	/*=================================================
	Cancel View - shows subscription canceled message
	=================================================*/
	function area2Cancel() {

		global $siteUser;
		$guild = new dkpGuild();
		$guild->loadFromDatabase($siteUser->guild);
		/*$serverstr = str_replace(" ","+",$guild->server);
		$guildstr = str_replace(" ","+",$guild->name);

		global $SiteRoot;
		$baseurl = $SiteRoot."dkp/$serverstr/$guildstr/";*/

		$baseurl =  dkpUtil::GetGuildUrl($guild->id);

		$this->border = 1;
		$this->title = "Subscription Canceled.";
		$this->set("baseurl", $baseurl);
		return $this->fetch("subscribe/cancel.tmpl.php");
	}

	/*=================================================
	IPN View - Handles IPN messages from paypal. This will
	automattically updates a guild pro setting when they
	subscribe or cancel their subscription.
	=================================================*/
	function area2IPN() {
		global $sql;

		// read the post from PayPal system and add 'cmd'
		$req = 'cmd=_notify-validate';

		foreach ($_POST as $key => $value) {
			$value = urlencode(stripslashes($value));
			$req .= "&$key=$value";
		}

		// post back to PayPal system to validate
		$header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
		$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
		$fp = fsockopen ('www.paypal.com', 80, $errno, $errstr, 30);

		// assign posted variables to local variables
		$item_name = $_POST['item_name'];
		$item_number = $_POST['item_number'];
		$payment_status = $_POST['payment_status'];
		$payment_amount = $_POST['mc_gross'];
		$payment_currency = $_POST['mc_currency'];
		$payment_amount2 = $_POST['mc_amount3'];
		$txn_id = $_POST['txn_id'];
		$txn_type = $_POST['txn_type'];
		$receiver_email = $_POST['receiver_email'];
		$payer_email = $_POST['payer_email'];
		$fee = $_POST['mc_fee'];
		$period = $_POST['period3'];
		$income = $payment_amount - $fee;

		$guildid = $_POST["custom"];
	  	$guild = new dkpGuild();
	  	$guild->loadFromDatabase($guildid);

		if (!$fp) {
			// HTTP ERROR
		} else {
			fputs ($fp, $header . $req);
			while (!feof($fp)) {
				$res = fgets ($fp, 1024);
				if (strcmp ($res, "VERIFIED") == 0) {
					// check the payment_status is Completed
					// check that txn_id has not been previously processed
					// check that receiver_email is your Primary PayPal email
					// check that payment_amount/payment_currency are correct
					// process payment

					// get the value stored in the "custom" field
				  	// (username) in a local variable

					if($period == "1 M") {
						$periodString = "Monthly";
					}
					else if($period == "6 M"){
						$periodString = "Semi-Annual";
					}
					else if($period == "1 Y"){
						$periodString = "Annual";
					}


					//$this->addLogEntry("Test",$guildid,"period: $period type: $txn_type. payment status: $payment_status", $txn_id);

					if($txn_type == "subscr_signup"){
						//startup their pro account
						//$this->addLogEntry("Subscribe",$guildid,"Subscription started. $payment_status", $txn_id);
						$this->addPaymentHistory(0, 0, 0, "$periodString Subscription Started", $guildid, $txn_id, "Subscribe");
						$guild->setProStatus("Active");
						$settings = $guild->loadSettings();
						$settings->SetNewProaccount(1);

					}
					else if($txn_type == "subscr_cancel"){
						//disable their pro account
						//$this->addLogEntry("Canceled",$guildid,"Subscription canceled. $payment_status", $txn_id);
						$this->addPaymentHistory(0, 0, 0, "$periodString Subscription Canceled", $guildid, $txn_id, "Cancel");
						$guild->setProStatus("Active Until End of Term");

					}
					else if($txn_type == "subscr_failed"){
						//disable pro account until next paymenet goes through
						//$this->addLogEntry("Failed",$guildid,"Subscription payment failed. Account Suspended. $payment_status", $txn_id);
						$this->addPaymentHistory(0, 0, 0, "Subscription Payment Failed", $guildid, $txn_id, "Failed Payment");
						$guild->setProStatus("Suspended");

					}
					else if($txn_type == "subscr_modify") {
						$settings = $guild->loadSettings();
						$settings->SetNewProaccount(1);
					}
					else if($txn_type == "subscr_payment" && $payment_status=="Completed"){
						//double check that their pro account is enabled
						//if($payment_amount == $expectedAmount) {
							//$this->addLogEntry("Payment",$guildid,"Subscription payment recieved. $payment_status", $txn_id);
							$this->addPaymentHistory($payment_amount, $fee, $income, "$periodString Subscription Payment", $guildid, $txn_id, "Payment");
							$guild->setProStatus("Active");
						//}
						//else {
							//They attempted to pay to little. Are they spoofing? Make a note of it and suspend their pro account rights
							//$this->addPaymentHistory($payment_amount, $fee, $income, "Incorrect Subscription Payment", $guildid, $txn_id, "Payment");
							//$guild->setProStatus("Suspended");
							//send email notification to self here?
						//}
					}
					else if($txn_type == "subscr_eot"){
						//disable their pro account
						//$this->addLogEntry("Ended",$guildid,"Subscription reached end of term. $payment_status", $txn_id);
						$this->addPaymentHistory(0, 0, 0, "Subscription Reached End of Term", $guildid, $txn_id, "End of Term");
						$guild->setProStatus("Disabled");
					}
					else if($payment_status == "Refunded") {
						$this->addPaymentHistory( ($payment_amount), ($fee), ($income), "Payment Refunded", $guildid, $txn_id, "Refund");
					} //(-3.99, -.42, -3.57
					else {
						$this->addLogEntry("Other",$guildid,"Some other IPN call was made: $txn_type. $payment_status", $txn_id);
					}

				}
				else if (strcmp ($res, "INVALID") == 0) {
					$this->addLogEntry("Invalid",$guildid,"An invalid or spoofed IPN call was made.");
				}
			}
			fclose ($fp);
		}
		//die();

		return "";
	}

	/*=================================================
	addLogEntry()
	Records a log entry into the ipn log database. Right now
	this is mainly just used to log odd events other than payments.
	Parameters are:
	$type - The type of info being recorded: "Invalid", "Subscribe", "Canceled" "Failed" "Payment" "Ended"
	$guild - The id of the guild that the log is related to (if available)
	$message - A longer / more discription log entry
	=================================================*/
	function addLogEntry($type,$guild,$message, $txn){
		$logEntry = new dkpProAccountLogEntry();
		$logEntry->type = $type;
		$logEntry->guild = $guild;
		$logEntry->message = $message;
		$logEntry->txn = $txn;
		$logEntry->saveNew();
	}

	/*=================================================
	addPaymentHistory()
	Records a payement event in the payment history log.
	Parameters are:
	$amount 	-	how much was paid
	$fee		- 	how much paypal charged
	$income		- 	how much income was made (amount - fee)
	$reason		-	The reason for teh payment
	$guild		-	The id of the guild who paid
	$txn 		-	The paypal transaction id
	=================================================*/
	function addPaymentHistory($amount, $fee, $income, $reason, $guild, $txn, $type){
		$payment = new paymentHistory();
		$payment->amount = $amount;
		$payment->fee = $fee;
		$payment->income = $income;
		$payment->reason = $reason;
		$payment->guild = $guild;
		$payment->transactionNumber = $txn;
		$payment->type = $type;
		$payment->saveAttempt();
	}
}

?>