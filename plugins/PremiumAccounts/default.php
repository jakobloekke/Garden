<?php if (!defined('APPLICATION')) exit();
/**
* # Premium Accounts #
* 
* ### About ###
* Allows users to upgrade their accounts, through PayPal payment. <b>You must go to Setting to enter PayPal details before this will work</b>
* 
* Features transaction logging and auto expire. works with ordinary roles, so the upgrade can be anything you can achieve through roles such as a private category.
* 
* Please check http://cl.lk/225477c for help.
* 
* ### Sponsor ###
* Special thanks to Rimfya (http://rimfya.com.au) and Autism Advantage for making this happen.
*/


/**
* Changelog:
* v0.5b:Fri Feb 10 14:59:56 GMT 2012
* - consitent translation/locale
* - not ready for on registration ugrade yet
* v0.6b:Fri Feb 10 15:30:21 GMT 2012
* - http://cl.lk/225477c 
* v0.7b:Sun Feb 12 09:59:37 GMT 2012
* - Vanilla version limit
* v0.7.1b:Mon Feb 13 11:35:27 GMT 2012
* - SmartAsset instead of Url for image
* v0.7.2b:Fri Feb 24 12:15:26 GMT 2012
* - PDT verify item_number
* v0.7.3b:Fri Feb 24 12:51:39 GMT 2012
* - PDT restrictions
*/



$PluginInfo['PremiumAccounts'] = array(
   'Name' => 'Premium Accounts',
   'Description' => 'Allows users to upgrade their accounts, through PayPal payment. <b>You must go to Setting to enter PayPal details before this will work</b>',
   'SettingsUrl' => '/dashboard/settings/premium',
   'SettingsPermission' => 'Garden.Settings.Manage',
   'RequiredApplications' => array('Dashboard' => '>=2.0.18.2'),
   'Version' => '0.7.3b',
   'Author' => "Paul Thomas",
   'AuthorEmail' => 'dt01pqt_pt@yahoo.com'
);


include_once(PATH_PLUGINS.'/PremiumAccounts/ipnlistener.php');
include_once(PATH_PLUGINS.'/PremiumAccounts/class.premiumlogmodel.php');

class PremiumAccounts extends Gdn_Plugin {

	private $_configs;
	public $Status='';
	public $AdminName = '';
  
	/* settings/log UI*/
	public function Base_GetAppSettingsMenuItems_Handler($Sender) {
		$Menu = $Sender->EventArguments['SideMenu'];
		$Menu->AddItem('PremiumAccounts', T('Premium Accounts'),FALSE, array('class' => 'Reputation'));
		$Menu->AddLink('PremiumAccounts', T('Settings'), 'settings/premium', 'Garden.Settings.Manage');
		$Menu->AddLink('PremiumAccounts', T('Transaction Log'), 'settings/premiumlog', 'Garden.Settings.Manage');
		if(!C('Garden.DashboardMenu.Sort')){
			//resort PremiumAccounts menu bellow Users
			$Items = array_keys($Menu->Items);
			$PK = array_search('PremiumAccounts',$Items);
			$PA = array_splice($Items,$PK,1);
			$UK = array_search('Users',$Items);
			array_splice($Items,$UK+1,0,$PA);
			$MItems=array();
			foreach ($Items As $Item)
				$MItems[$Item]=$Menu->Items[$Item];
			$Menu->Items=$MItems;
			$Menu->Sort=$Items;
		}
	}
   
   
	public function SettingsController_Premium_Create($Sender) {
		$Sender->Permission('Garden.Settings.Manage');
   		$Sender->Form = Gdn::Factory('Form');
		
		if($Sender->Form->IsPostBack() != False){
			$Days = $Sender->Form->GetValue('SubscriptionPeriod_Days');
			$Months = $Sender->Form->GetValue('SubscriptionPeriod_Months');
			$Years = $Sender->Form->GetValue('SubscriptionPeriod_Years');
			if(empty($Days)  && empty($Months) && empty($Years)){
				$Sender->Form->SetFormValue('SubscriptionPeriod',0);
			}else{
				$Sender->Form->SetFormValue('SubscriptionPeriod',1);
			}
			//Uppercase account ID
			$Sender->Form->SetValue('PayPalAccount',strtoupper($Sender->Form->GetValue('PayPalAccount')));
			
			$Validation = new Gdn_Validation();
			 
			$Validation->AddRule('PayPalAccount','regex:`^[A-Z0-9]{13}$`');
			$Validation->ApplyRule('PayPalAccount', 'PayPalAccount', 'You must enter a valid PayPal Account ID');
			$Validation->ApplyRule('SubscriptionPeriod','Required','You must enter a period');
			$Validation->ApplyRule('Amount', 'Decimal', 'You must enter a valid amount');
			$FormValues = $Sender->Form->FormValues();
			$Validation->Validate($FormValues);
			$Sender->Form->SetValidationResults($Validation->Results());
			$Settings=array();
			$Settings['Plugins.PremiumAccounts.Enabled']=FALSE;
			$Settings['Plugins.PremiumAccounts.Set']=TRUE;//Nessisary due to some bug to get C('Plugins.PremiumAccounts') array when disabled;
			if(!$Sender->Form->ErrorCount()){
				foreach ($FormValues As $FormIndex => $FormValue)
					if(strpos($FormIndex,'SubscriptionPeriod')===FALSE && !in_array($FormIndex,array('Save','TransientKey','hpt')))
						$Settings['Plugins.PremiumAccounts.'.$FormIndex]=$FormValue;
				$Settings['Plugins.PremiumAccounts.DateAdd']="+{$Years} years {$Months} months {$Days} days";
				$Settings['Plugins.PremiumAccounts.Enabled']=TRUE;
			}else{
				C('Plugins.PremiumAccounts.Enabled','');
			}
			
			SaveToConfig($Settings);
		}
		$this->_configs=C('Plugins.PremiumAccounts');
		$this->SetPeriod();
		$RoleModel = new RoleModel();
		$Sender->SetData('Roles',$RoleModel ->GetArray());
		$Sender->Account=$this;
		$Sender->AddSideMenu();
		$Sender->SetData('Title', T('Premium Accounts Settings'));
		$Sender->SetData('Description', T('SettingsDescription'),$this->PluginInfo['Description']);
		$Sender->SetData('Plugin', $this);
		$Sender->Render('Settings', '', 'plugins/PremiumAccounts');
	}
   
	public function SettingsController_PremiumLog_Create($Sender) {
		$Sender->Permission('Garden.Settings.Manage');
		if($Sender->DeliveryMethod() == DELIVERY_METHOD_JSON){
			$Page = GetValue('Page',$_GET);
			$Total = GetValue('rp',$_GET);
			if(!$Page) $Page =1;
			$Offset = ($Page - 1) * $Limit;
			$SortOrder = GetValue('sortorder',$_GET);
			$SortName = GetValue('sortname',$_GET);
			$Search = GetValue('query',$_GET);
			$SearchCol = GetValue('qtype',$_GET);
			$History = GetValue('history',$_GET);
			if($SearchCol=='UserID')
				$SearchCol='Name';
			if($SortName=='UserID')
				$SortName='Name';
			$PremiumLog = new PremiumLog();
			$Log = $PremiumLog->GetLog($Limit,$Offset,$SortName,$SortOrder,$Search,$SearchCol,$History);
			$Rows=array();
			foreach ($Log As $Item)
				$Rows[]=array('cell'=>$Item);
			$Sender->Rows = $Rows;
			$Data=array(
				'page'=>$Page,
				'total'=> $Total,
				'rows'=>$Rows,
				'get'=> Gdn::Structure()->ColumnExists($SearchCol)
				);
			 exit(json_encode($Data));

		}else{
			$Sender->AddSideMenu();
			$Sender->AddDefinition('PayPalUrl','https://www.'.(C('Plugins.PremiumAccounts.AccountType')!=='Live'?'sandbox.':'').'paypal.com/cgi-bin/webscr');
			$Sender->SetData('Title', T('Premium Accounts Log'));
			$Sender->SetData('Description', T('Master log of transactions'));
			$Sender->AddCssFile('flexigrid.css','plugins/PremiumAccounts/library/flexigrid/css');
			$Sender->AddJsFile('flexigrid.js','plugins/PremiumAccounts/library/flexigrid/js');
			$Sender->Render('SettingsLog', '', 'plugins/PremiumAccounts');
		}
	}
   
	/* settings UI*/
   
	/* utility */
	public function Get($Index){
		return in_array($Index,$this->_configs)?$this->_configs[$Index]:null;
	}
   
	public function Set($Index,$Value){
		return $this->_configs[$Index]=$Value;
	}
   
	public function SetPeriod(){
		if(preg_match("`\+\s*(\d+)\s+years\s+(\d+)\s+months\s+(\d+)\s+days`i",C('Plugins.PremiumAccounts.DateAdd'),$Date)){
			$this->Set('Years',$Date[1]);
			$this->Set('Months',$Date[2]);
			$this->Set('Days',$Date[3]);
		}
	}
	/* utility */
   
   /* role management */
	public function RoleCheck(){
		if(!Gdn::Session()->isvalid())
			return FALSE;
		$UserModel = new UserModel();
		$RoleData = $UserModel->GetRoles(Gdn::Session()->UserID)->Result();
		$Roles=array();
		foreach ($RoleData As $Role)
			$Roles[]=$Role['Name'];
		if(in_array($this->Get('Role'),$Roles))
			return TRUE;
		else
			return FALSE;
	}
	
	//some magic
	   
	private function RoleSet($UserID){
		if(!ctype_digit($UserID))
				return;
		$UserID = intval($UserID);
		$UserModel = new UserModel();
		$UserRoles = $UserModel->GetRoles($UserID)->Result();
		foreach ($UserRoles  As $Role)
			$Roles[]=$Role['RoleID'];
		$RoleModel = new RoleModel();
		$AllRoles =$RoleModel ->GetArray();
		$RoleID=array_search(C('Plugins.PremiumAccounts.Role'),$AllRoles );
		if($RoleID===FALSE)
			return;
		$Roles[]=$RoleID;
		$Expire = Gdn_Format::ToDateTime(strtotime(C('Plugins.PremiumAccounts.DateAdd')));
		$UserModel->SaveRoles($UserID,$Roles,FALSE);
		Gdn::SQL()->Put('User', array('PremiumExpire' => $Expire,'PremiumRole'=>$RoleID), array('UserID' => $UserID));
	}
	
	private function RoleExpire($UserID){
		if(!ctype_digit($UserID))
				return;
		$UserID = intval($UserID);
		$UserModel = new UserModel();
		$UserRoles = $UserModel->GetRoles($UserID)->Result();
		foreach ($UserRoles  As $Role)
			$Roles[]=$Role['RoleID'];
		$RoleModel = new RoleModel();
		$AllRoles =$RoleModel->GetArray();
		$RoleID=$UserModel->GetID($UserID)->PremiumRole;
		$RemoveI = array_search($RoleID,$Roles);
		if($RemoveI!==FALSE)
			unset($Roles[$RemoveI]);
		$RoleID=array_search(C('Plugins.PremiumAccounts.Role'),$AllRoles);
		$RemoveI = array_search($RoleID,$Roles);
		if($RemoveI!==FALSE)
			unset($Roles[$RemoveI]);
		$UserModel->SaveRoles($UserID,$Roles,FALSE);
		$PremiumLog = new PremiumLog();
		$Log=$PremiumLog->GetLatest($UserID);
		$TransactionID=$PremiumLog->GetLatest();
		if(!$Log->TransactionID || $Log->Status!='payment_complete')
			return;
		$PremiumLog->Log($UserID,$Log->TransactionID,'account_expired','expired term');
		Gdn::SQL()->Put('User', array('PremiumExpire' => null,'PremiumRole'=>null), array('UserID' => $UserID));
	}
	
	//force expire when role is removed via dashboard/user 
	public function UserModel_AfterSave_Handler($Sender,$Args){
		$UserID = $Args['FormPostValues']['UserID'];
		$RoleIDs = $Args['FormPostValues']['RoleID'];//misleading name
		$UserModel = new UserModel();
		$RoleID = $UserModel->GetID($UserID)->PremiumRole;
		if(!$RoleID)
			return;
		$RoleModel = new RoleModel();
		$AllRoles =$RoleModel->GetArray();
		$PremiumRole=array_search(C('Plugins.PremiumAccounts.Role'),$AllRoles);
		$Exist = array_search($PremiumRole?array($RoleID,$PremiumRole):$RoleID,$RoleIDs);
		if($Exist===FALSE){
			$PremiumLog = new PremiumLog();
			$Log=$PremiumLog->GetLatest($UserID);
			if(!$Log->TransactionID || $Log->Status!='payment_complete')
				return;
			$PremiumLog->Log($UserID,$Log->TransactionID,'account_expired','force expired');
			Gdn::SQL()->Put('User', array('PremiumExpire' => null,'PremiumRole'=>null), array('UserID' => $UserID));
		}
	}
		/* role management */

		/* payment processing*/
	private function CheckIPN(){
		$Listener = new IpnListener();
		$Listener->use_sandbox=$this->Get('AccountType')!=='Live'?true:false;
		 try {
			$Listener->requirePostMethod();
			$Verified = $Listener->processIpn();
		} catch (Exception $e) {
			LogMessage(__FILE__,__LINE__,'PremiumAccounts','CheckIPN', 'payment_exception:'.$e->getMessage());
			exit;
		}	
		if(!$Verified){
		   LogMessage(__FILE__,__LINE__,'PremiumAccounts','CheckIPN', 'payment_not_verified:'.$Listener->getTextReport());
		}else{
		   $this->PoccessPayement($_POST);
		}
	}
   
	private function PoccessPayement($Payment){

		$this->Status='Fail';
		if(empty($Payment))
			return;
		if(@empty($Payment['txn_id'])){
			LogMessage(__FILE__,__LINE__,'PremiumAccounts','PoccessPayement', 'payment_no_txn_id:user_id->'.@$Payment['item_number']);
			return;
		}



		if(@empty($Payment['item_number'])){
			LogMessage(__FILE__,__LINE__,'PremiumAccounts','PoccessPayement', 'payment_no_user_id:txn_id->'.$Payment['txn_id']);
			return;
		}
		$UserID=$Payment['item_number'];
		$TransactionID=$Payment['txn_id'];
		$PremiumLog = new PremiumLog();
		$Log=$PremiumLog->GetLatest($UserID,$TransactionID);
		if($Log && $Log->Status=='payment_complete'){
			$this->Status='Upgraded';
			return;
		}
		if($Payment['payment_status']=='Pending'  && (!$Log || !$Log->payment_status=='Pending')){
			$this->Status='Complete';
			$PremiumLog->Log($UserID,$TransactionID,'payment_pending',  'reason:'.$Payment['pending_reason']);
			return;
		}else if($Payment['payment_status']!='Completed'){
			$PremiumLog->Log($UserID,$TransactionID,'payment_incomplete',  'payment_status:'.$Payment['payment_status'].',reason:'.$Payment['reason_code']);
			return;
		}
		$Required=array(
				'txn_type'=>'web_accept',
				'item_name'=>'PremiumAccount',
				'receiver_id'=>$this->Get('PayPalAccount'),
				'mc_currency'=>$this->Get('Currency'),
				'mc_gross'=>sprintf("%01.2f",$this->Get('Amount'))
				  );
				  
		$Mismatch =array();
		$PaymentComp= array();
		foreach ($Required As $RequireI => $RequireV)
			if(trim($Payment[$RequireI])!=$RequireV)
				$Mismatch[$RequireI]=$RequireI.'->'.trim($Payment[$RequireI]).' doesn\'t match '.$RequireV;

		foreach($Payment as $PaymentI =>$PaymentV)
				$PaymentComp[$PaymentI]=$PaymentI.'->'.trim($PaymentV);
				

		if(empty($Mismatch)){//Completed
			$this->Status='Upgraded';
			$PremiumLog->Log($UserID,$TransactionID,'payment_complete', 'payment_complete:'. implode('|',$PaymentComp));	
			$this->RoleSet($UserID);//this is your pass to premium account 
			$this->Status='Upgraded';//happy days!
		}else{
			$PremiumLog->Log($UserID,$TransactionID,'payment_invalid', 'payment_mismatch:'.implode('|',$Mismatch));
		}		
	}
	/* payment processing*/
   
	/* upgrade UI*/
	public function GetStatus(){
		if(Gdn::Session()->IsValid()){
			$PremiumLog = new PremiumLog();
			$Log=$PremiumLog->GetLatest(Gdn::Session()->UserID);
			$Inform='';
			if($Log){
				
				switch($Log->Status){
					case 'payment_complete':
						$Inform=T('PayementComplete','PremiumAccount:Congrats! Your payment is complete');
						$this->Status='Upgraded';
						break;
					case 'payment_pending':
						$Inform=T('PayementPending','PremiumAccount:Your payment is pending...');
						$this->Status='Complete';
						break;
					case 'payment_incomplete':
						$Inform=T('PayementIncomplete','PremiumAccount:For some reason you payment wasn\'t completed, please contact <a href="%s">admin</a>');
						$this->Status='Fail';
						break;
					case 'payment_invalid':
						$Inform=T('PayementInvalid','PremiumAccount:For some reason your payment was invalid, please contact <a href="%s">admin</a>');
						$this->Status='Fail';
						break;
					case 'account_expired':
						$Inform=T('AccountExpired','PremiumAccount:Your premium account has expired');
						break;
				}
			}
			if($Inform && !$Log->Inform){
				$this->AdminName = Gdn::SQL()->Select('Name')->From('User')->Where('Admin',1)->Get()->FirstRow()->Name;
				Gdn::Controller()->InformMessage('<a href="'.Url('/user/upgrade/dismiss/'.strtotime($Log->Date).'/'.$Log->TransactionID).'" class="Dismiss Close"><span>&times;</span></a><span class="InformSprite Key"></span>'.sprintf($Inform,Url('/user/profile/'.$this->AdminName,true)), 'HasSprite');
			}
		}
	}
   
	public function DismissInform($TimeStamp,$TransactionID){
		if(Gdn::Session()->IsValid()){
			$PremiumLog = new PremiumLog();
			$PremiumLog->DismissInform(Gdn::Session()->UserID,Gdn_Format::ToDateTime($TimeStamp),$TransactionID);
		}
	}

	public function UserController_Upgrade_Create($Sender, $Args) {
		if(!$this->IsEnabled())
			return ;	
		$this->_configs=C('Plugins.PremiumAccounts');
		$Sender->Account=$this;
		if($this->RoleCheck()){
			$this->Status='Upgraded';
			
			
		}else switch (strtolower($Sender->RequestArgs[0])){
			case 'notify':
				if(!empty($_POST))
					$this->CheckIPN();
				exit;
				break;
			case 'return':
				if(!empty($_POST) 
				&& GetValue('item_number',$_POST)==Gdn::Session()->UserID 
				&& GetValue('payment_status',$_POST)!='Completed'){
					$this->PoccessPayement($_POST);
					Redirect('/user/upgrade/return');
				}	
				break;
			case 'cancel':
				$this->Status='Cancel';	
				break;
			case 'dismiss':
				$this->DismissInform($Sender->RequestArgs[1],$Sender->RequestArgs[2]);
				break;
		}

		if(strtolower($Sender->RequestArgs[0])=='dismiss'){
			$this->DismissInform($Sender->RequestArgs[1],$Sender->RequestArgs[2]);
		}

		$this->GetStatus();
		$Sender->Status=$this->Status;
		$Sender->SetData('AdminName',$this->AdminName);
		$this->SetPeriod();
		$Sender->SetData('PayPalUrl','https://www.'.($this->Get('AccountType')!=='Live'?'sandbox.':'').'paypal.com/cgi-bin/webscr' );
		$Sender->SetData('NotifyUrl',Url('/user/upgrade/notify',TRUE));
		$Sender->SetData('ReturnUrl',Url('/user/upgrade/return',TRUE));
		$Sender->SetData('CancelUrl',Url('/user/upgrade/cancel',TRUE));
		//As I'm piggybacking a dashboard controller some styling conserns. 
		$Sender->RemoveCssFile('admin.css');
		$Sender->AddCssFile('style.css');
		$Sender->MasterView = 'default';
		$Sender->View= $this->GetView('upgrade.php');
		$Sender->Render();
	}
   
	public function ProfileController_AfterAddSideMenu_Handler($Sender,$Args){
		if(!$this->IsEnabled())
			return;
		$Args['SideMenu']->AddLink('Options',Gdn::Session()->User->PremiumExpire?T('Premium Account'):T('Upgrade Account'),
									'/user/upgrade', FALSE, array('class' => 'UpgradeAccount'));
	}
	
    public function ProfileController_AddProfileTabs_Handler($Sender){
		if(!$this->IsEnabled())
			return;;
        $Sender->AddProfileTab('PremiumAccounts','user/upgrade',
                        'PremiumAccounts', Gdn::Session()->User->PremiumExpire?T('Premium Account'):T('Upgrade Account'));
    }

	public function EntryController_Render_Before($Sender,$Args) {
		if(!$this->IsEnabled())
			return;
/*
		if(strcasecmp($Sender->RequestMethod,'register')==0){
			$RegistrationMethod = Gdn::Config('Garden.Registration.Method');
			if (in_array($RegistrationMethod, array( 'Basic','Captcha'))){
				$this->Set('Role',C('Plugins.PremiumAccounts.Role'));
				$Sender->Account=$this;
				$Sender->View =$this->GetView( 'register'.strtolower($RegistrationMethod).'.php');
			}
		}
*/

	}
    /* upgrade UI*/
    
    /* this is the expire cron */
    public function Base_BeforeControllerMethod_Handler($Sender) {
		if(!Gdn::Session()->isValid()) return;
		$Expire = Gdn::Session()->User->PremiumExpire;
		if(strtotime($Expire) && strtotime($Expire)<=time()){
			$this->RoleExpire(Gdn::Session()->User->UserID);
		}
		
	}
   
    /* setup spec*/
    
	public function Base_BeforeDispatch_Handler($Sender){
		if(C('Plugins.KarmaBank.Version')!=$this->PluginInfo['Version'])
			$this->Structure();
	}
    
	public function Setup() {
		$this->Structure();
	}

	public function Structure() {
		Gdn::Structure()
		 ->Table('PremiumLog')
		 ->Column('UserID', 'int(11)', FALSE, 'key')
		 ->Column('TransactionID','char(19)')
		 ->Column('Status','varchar(100)')
		 ->Column('Inform','int(4)',0)
		 ->Column('Date','datetime',FALSE, 'unique')
		 ->Column('Log','mediumtext')
		 ->Set();
		 
		Gdn::Structure()
		 ->Table('User')
		 ->Column('PremiumExpire','datetime',NULL)
		 ->Column('PremiumRole','int(11)',0)
		 ->Set();
		//Save Version for hot update

		SaveToConfig('Plugins.PremiumAccounts.Version', $this->PluginInfo['Version']);
	}
    /* setup spec*/
}
