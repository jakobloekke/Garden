<?php if (!defined('APPLICATION')) exit(); ?>
<style>
  fieldset{
	border:1px solid #CCCCCC;
	width:350px;
	padding:5px;
	margin-bottom:6px;
  }
  
  label{
	display:block;
  }
  label.Inline{
	display:inline;
  }
  
  .PremiumButton{
	margin-top:5px!important;
  }
  
  .Message {
	width:500px;
  }
</style>
<h1><?php echo $this->Data['Title'] ?></h1>
<div class="Info">
   <?php echo $this->Data['Description'] ?>
</div>
<div>
<?
      echo $this->Form->Open();
      echo $this->Form->Errors();
?>
<div class="Configuration">
   <div class="ConfigurationForm">
      <ul>
         <li>
            <?php   
	    echo $this->Form->Label('Status');
	    echo  $this->Account->Get('Enabled') ? T('Enabled') :T('Disabled') ; 
	    ?>
         </li>
         <li>
	    <fieldset>
	    <legend><?php echo T('Account Type') ?></legend>
            <?php
	      echo $this->Form->DropDown('AccountType', array('Sandbox'=>'Sandbox','Live'=>'Live'),array('value'=>$this->Account->Get('AccountType')?$this->Account->Get('AccountType'):'Sandbox'));
            ?>
	    </fieldset>
         </li>
         <li>
            <?php
               echo $this->Form->Label('PayPal Account ID', 'PayPalAccount');
               echo $this->Form->TextBox('PayPalAccount', array('value'=>$this->Account->Get('PayPalAccount')));
	       echo '<div class="Message">'.T('PayPal ID Message', '
			This is <u>not</u> your Paypal email, but another ID that will help protect your account and prevent spam. For information on where to find Paypal Business / Merchant ID  
			<a href="http://cl.lk/225477c">click here</a>.').'</div>';
            ?>
         </li>
         <li>
	    <fieldset>
	    <legend><?php echo T('Subscription Period') ?></legend>
            <?php
               echo $this->Form->DropDown('SubscriptionPeriod_Days', range(0,31),array('Default'=>$this->Account->Get('Days')));
	        echo $this->Form->Label('Day(s)', 'SubscriptionPeriod_Days',array('class'=>'Inline'));
	       echo $this->Form->DropDown('SubscriptionPeriod_Months', range(0,12),array('Default'=>$this->Account->Get('Months')));
	        echo $this->Form->Label('Month(s)', 'SubscriptionPeriod_Months',array('class'=>'Inline'));
	       echo $this->Form->DropDown('SubscriptionPeriod_Years', range(0,10),array('Default'=>$this->Account->Get('Years')));
	        echo $this->Form->Label('Year(s)', 'SubscriptionPeriod_Years',array('class'=>'Inline'));
            ?>
	    </fieldset>
         </li>
         <li>
            <?php
	       $CurrencyList=array('AUD','BRL','CAD','CZK','DKK','EUR','HKD','HUF','ILS','JPY','MYR','MXN','NOK','NZD','PHP','PLN','GBP','SGD','SEK','CHF','TWD','THB','TRY','USD');
               echo $this->Form->Label('Currency', 'Currency');
               echo $this->Form->DropDown('Currency',
								array_combine($CurrencyList,$CurrencyList),
								array('Default'=>$this->Account->Get('Currency')?$this->Account->Get('Currency'):'USD'));
            ?>
         </li>
         <li>
            <?php
               echo $this->Form->Label('Amount', 'Amount');
               echo $this->Form->TextBox('Amount', array('value'=>$this->Account->Get('Amount')));
            ?>
         </li>
         <li>
            <?php
               echo $this->Form->Label('Premium Role', 'Role');
               echo $this->Form->DropDown('Role',array_combine($this->Data['Roles'],$this->Data['Roles']),array('Default'=>$this->Account->Get('Role')?$this->Account->Get('Role'): 'Member'));
            ?>
         </li>
         <li>
            <?php echo $this->Form->Button('Save', array('class' => 'SmallButton PremiumButton SliceSubmit')); ?>
         </li>
      </ul>
      
   </div>
</div>
      <?php
      echo $this->Form->Close();
   ?>
</div>
