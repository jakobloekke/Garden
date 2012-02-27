<?php if (!defined('APPLICATION')) exit(); ?>
<div class="PremiumMessage">
<h1><?php echo sprintf(T('UpgradeToPremium',"Upgrade to %s Account"),$this->Account->Get('Role')); ?></h1>
<?php switch ($this->Status) {
case 'Upgraded':
	echo T('UpgradeSuccess','<p>Thank you for your payment, your account has been upgraded. Enjoy.</p>'); 
	break;
case 'Complete':
	echo T('UpgradeComplete','<p>Thank you for your payment, your account will be upgraded as soon as we have been notified by PayPal.</p>'); 
	break;
case 'Fail':
	echo sprintf(T('UpgradeFailed','<p>There was some problem with your upgrade transaction, please contact the <a href="%s">admin</a>.</p>'),Url('/user/profile/'.$this->Data['AdminName'],true));
	break;
case 'Cancel':
	echo T('UpgradeCancel','<p>Your upgrade transaction was canceled.</p>'); 
	break;
case 'New':
default:
?>
	     <?php echo sprintf(T('UpgradeMessage','<p>If you would like to upgrade your account with a simple PayPal subscribiton, you could be enjoying the benefits of a %s Account in no time.</p>'),$this->Account->Get('Role')); ?>
		<br />
	    <? echo
	    T('UpgradePrefix','Upgrade for '). 
	    join(', ',array_filter(array(
	    ($this->Account->Get('Years')?$this->Account->Get('Years'). ' '.T('Year(s)'):''),
	    ($this->Account->Get('Months')?$this->Account->Get('Months'). ' '.T('Month(s)'):''),
	    ($this->Account->Get('Days')?$this->Account->Get('Days'). ' '.T('Day(s)'):'')
	    ))).
	    T('UpgradeSuffix',':'); 
	    ?>
	    <br />
	    <br />
	<?php
	if(Gdn::Session()->IsValid()){
	?>
	<form name="_xclick"  action="<?php echo $this->Data['PayPalUrl']; ?>" method="post">
	<ul>
	    <li>
	    <input type="hidden" name="cmd" value="_xclick" />
	    <input type="hidden" name="business" value="<?php echo $this->Account->Get('PayPalAccount') ?>">
	    <input type="hidden" name="currency_code" value="<?php echo $this->Account->Get('Currency') ?>">
	    <input type="hidden" name="amount" value="<?php echo $this->Account->Get('Amount') ?>">
	    <input type="hidden" name="item_name" value="PremiumAccount">
	    <input type="hidden" name="item_number" value="<?php echo Gdn::Session()->UserID ?>">
	    <input type="hidden" name="no_shipping" value="1">
	    <input type="hidden" name="notify_url" value="<?php echo $this->Data['NotifyUrl']; ?>">
	    <input type="hidden" name="return" value="<?php echo $this->Data['ReturnUrl']; ?>">
	    <input type="hidden" name="cancel_return" value="<?php echo $this->Data['CancelUrl']; ?>">
	    <input type="hidden" name="cbt" value="<?php echo T('Return to Account') ?>">
	    <input type="hidden" name="rm" value="2">
	    <input type="image" src="<?php echo SmartAsset('/plugins/PremiumAccounts/design/buynow.gif',true); ?>" class="Button" style="background-color:#fff;padding:5px;" border="0" name="submit" alt="PayPal -- The safer, easier way to pay online.">
	    </li>
	</ul>
	</form>
<?php
       }else{
		echo T('UpgradeLogin','<p>Please login to upgrade.</p>');
       }
}
?>
</div>

