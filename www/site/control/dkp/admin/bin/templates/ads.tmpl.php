<?=$tabs?>
<?=$sidebar?>

<div class="adminContents">
<br />

<?php if( $settings->GetProaccount() && !$settings->GetNewProaccount()) { ?>

WebDKP has reduced its rate since you first subscribed! It now only costs <b>$1.50</b>
per month to disable advertisements. Thats more than <b>60% off</b>! Want to take advantage
of the new low rate? Just click the update button below. You can also cancel your
subscription entirely using the cancel button.
<br />
<br />
<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="image" src="<?=$siteRoot?>images/dkp/update.gif" border="0" name="submit" alt="Make payments with PayPal - it's fast, free and secure!" style="border:0px">
<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
<input type="hidden" name="cmd" value="_xclick-subscriptions">
<input type="hidden" name="business" value="subscribe@webdkp.com">
<input type="hidden" name="item_name" value="WebDKP Subscription">
<input type="hidden" name="item_number" value="1">
<input type="hidden" name="no_shipping" value="1">
<input type="hidden" name="return" value="http://www.webdkp.com/account.php?view=complete">
<input type="hidden" name="cancel_return" value="http://www.webdkp.com/account.php?view=cancel">
<input type="hidden" name="no_note" value="1">
<input type="hidden" name="currency_code" value="USD">
<input type="hidden" name="lc" value="US">
<input type="hidden" name="bn" value="PP-SubscriptionsBF">
<input type="hidden" name="a3" value="1.50">
<input type="hidden" name="p3" value="1">
<input type="hidden" name="t3" value="M">
<input type="hidden" name="src" value="1">
<input type="hidden" name="sra" value="1">
<input type="hidden" name="modify" value="2">
<input type="hidden" name="custom" value="<?=$guild->id?>">
</form>
<br />
<A HREF="https://www.paypal.com/cgi-bin/webscr?cmd=_subscr-find&alias=subscribe%40webdkp%2ecom">
<IMG SRC="<?=$siteRoot?>images/dkp/cancel.gif" BORDER="0"></A>

<?php } else if( $settings->GetProaccount() && $settings->GetProstatus()=="Active Until End of Term") { ?>
Your subscription has been canceled. Ads will continue to be hidden
on your guild's DKP until the end of term of your final payment. You can resubscribe
at any time for only <b>$1.50</b> per month by using the subscription button below.
<br />
<br />
<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_subscribe_LG.gif" border="0" name="submit" alt="Make payments with PayPal - it's fast, free and secure!" style="border:0px">
<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
<input type="hidden" name="cmd" value="_xclick-subscriptions">
<input type="hidden" name="business" value="subscribe@webdkp.com">
<input type="hidden" name="item_name" value="WebDKP Subscription">
<input type="hidden" name="item_number" value="1">
<input type="hidden" name="no_shipping" value="1">
<input type="hidden" name="return" value="http://www.webdkp.com/account.php?view=complete">
<input type="hidden" name="cancel_return" value="http://www.webdkp.com/account.php?view=cancel">
<input type="hidden" name="no_note" value="1">
<input type="hidden" name="currency_code" value="USD">
<input type="hidden" name="lc" value="US">
<input type="hidden" name="bn" value="PP-SubscriptionsBF">
<input type="hidden" name="a3" value="1.50">
<input type="hidden" name="p3" value="1">
<input type="hidden" name="t3" value="M">
<input type="hidden" name="src" value="1">
<input type="hidden" name="sra" value="1">
<input type="hidden" name="custom" value="<?=$guild->id?>">
</form>

<br />
<img style="margin-left:-14px" src="https://www.paypal.com/en_US/i/bnr/horizontal_solution_PPeCheck.gif" border="0" alt="Solution Graphics">


<?php } else if($settings->GetProaccount()) { ?>
Thank you for supporting WebDKP!
Ad's are currently disabled for your guild.
Your account is only charged
<b>$1.50</b> per month.
<br />
<br />
You can cancel your subscription
at any time by clicking on the cancel button below.
<br />
<br />
<A HREF="https://www.paypal.com/cgi-bin/webscr?cmd=_subscr-find&alias=subscribe%40webdkp%2ecom">
<IMG SRC="<?=$siteRoot?>images/dkp/cancel.gif" BORDER="0"></A>



<?php } else {?>
Like WebDKP? Please show us by subscribing! Not only will your contribution support
WebDKP, it will also disable the advertisments on your guild's page for only
<b>$1.50</b> per month. Subscribing is quick, easy, and secure through PayPal. You
can cancel your subscription at any time from this page or from PayPal.com.
Thanks for your support!
<br />
<br />
<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_subscribe_LG.gif" border="0" name="submit" alt="Make payments with PayPal - it's fast, free and secure!" style="border:0px">
<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
<input type="hidden" name="cmd" value="_xclick-subscriptions">
<input type="hidden" name="business" value="subscribe@webdkp.com">
<input type="hidden" name="item_name" value="WebDKP Subscription">
<input type="hidden" name="item_number" value="1">
<input type="hidden" name="no_shipping" value="1">
<input type="hidden" name="return" value="http://www.webdkp.com/account.php?view=complete">
<input type="hidden" name="cancel_return" value="http://www.webdkp.com/account.php?view=cancel">
<input type="hidden" name="no_note" value="1">
<input type="hidden" name="currency_code" value="USD">
<input type="hidden" name="lc" value="US">
<input type="hidden" name="bn" value="PP-SubscriptionsBF">
<input type="hidden" name="a3" value="1.50">
<input type="hidden" name="p3" value="1">
<input type="hidden" name="t3" value="M">
<input type="hidden" name="src" value="1">
<input type="hidden" name="sra" value="1">
<input type="hidden" name="custom" value="<?=$guild->id?>">
</form>

<br />
<img style="margin-left:-14px" src="https://www.paypal.com/en_US/i/bnr/horizontal_solution_PPeCheck.gif" border="0" alt="Solution Graphics">
<?php } ?>





</div>
