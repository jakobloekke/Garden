<?php if (!defined('APPLICATION')) exit();
/**
*
* # KarmaBank #
*
* ### About ###
* Using simple forum/plugin meta based rules such as comment count, users can earn karma, which is added to their balance, and can even be traded for goods and privileges.
*
* ### Sponsor ###
* Special thanks to Bigfan (http://manyuses.org) for making this happen.
*
*/

/**
 * 
 * Change log:
 * v0.8:Sat Feb 11 11:52:44 GMT 2012
 * - priamry key fix
 * v0.9.1b:Sat Feb 11 14:33:21 GMT 2012
 * - easier locale of transaction type
 * v0.9.2b:Sat Feb 11 20:43:01 GMT 2012
 * - RuleID validation
 * v0.9.3b:Sat Feb 11 20:43:01 GMT 2012
 * - locale of transaction type correction
 * v0.9.4b:Sat Feb 11 21:44:30 GMT 2012
 * - more primary key madeness
 * v0.9.5b:Sun Feb 12 09:59:37 GMT 2012
 * - Vanilla version limit
 * v0.9.5.1b:Thu Feb 16 09:47:11 GMT 2012
 * - Option to display Balance on Comment Meta (thanks to "VS")
 */

$PluginInfo['KarmaBank'] = array(
    'Name' => 'Karma Bank',
    'Description' => 'Using simple forum/plugin meta based rules such as comment count, users can earn karma, which is added to their balance, and can even be traded for goods and privileges. <b>You must add a starting balance, and add at least 1 rule to activate</b>.',
    'SettingsUrl' => '/dashboard/settings/karmabank',
    'SettingsPermission' => 'Garden.Settings.Manage',
    'RegisterPermissions' =>array('Plugins.KarmaBank.RewardTax'),
    'RequiredApplications' => array('Dashboard' => '>=2.0.18.1'),
    'Version' => '0.9.5.1b',
    'Author' => "Paul Thomas",
    'AuthorEmail' => 'dt01pqt_pt@yahoo.com'
);

include_once(PATH_PLUGINS.'/KarmaBank/class.karmabankmodel.php');
include_once(PATH_PLUGINS.'/KarmaBank/class.karmarulesmodel.php');

class KarmaBank extends Gdn_Plugin {

    public $Meta;
    public $Operations;
    static public $KarmaChecked = FALSE;

    /*
     *  The meta spec
     */

    public function MetaMap(){
        $this->Meta=array(
            'CountVisits'=>'Counts every session vist',
            'CountComments'=>'Counts every time a member adds a comment',
            'CountDiscussions'=>'Counts every time a member adds a discussion or question (regardless of type)',
            'QnACountAccept'=>'(Requires Q&A plugin) Counts every time a member accepts an answer to their question',
            'QnACountAcceptance'=>'(Requires Q&A plugin) Counts every time a member has their answer to a question accepted  (excluding their own)'
        );

        $this->Operations=array(
            'Equals'=>'When Meta == Target then add Amount. Not retrospective',
            'Every'=>'When Meta % Target == 0 then add Amount. Not retrospective based on absolute Meta value'
        );
        
        $this->OperationsMap=array(
			'Equals'=>'KarmaBank::OperationEquals',
			'Every'=>'KarmaBank::OperationEvery'
		);
        //custom mappings
        $this->FireEvent('KarmaBankMetaMap');

    }
    
    public static function OperationEquals($MetaValue,$Target,$Condition=null){
		return $MetaValue == $Target;
	}
	
	public static function OperationEvery($MetaValue,$Target,$Condition=null){
		return $MetaValue % $Target == 0;
	}


    /*
    *   Settings Menu Hack (force under Users reputation/privilege related stuff)
    */
    public function Base_GetAppSettingsMenuItems_Handler($Sender) {
        $Menu = &$Sender->EventArguments['SideMenu'];
        $Menu->AddItem('KarmaBank', T('Karma Bank'),FALSE, array('class' => 'Reputation'));
        $Menu->AddLink('KarmaBank', T('Settings/Rules'), 'settings/karmabank', 'Garden.Settings.Manage');
        if(!C('Garden.DashboardMenu.Sort')){
            //resort KarmaBank menu bellow Users
            $Items = array_keys($Menu->Items);
            $PK = array_search('KarmaBank',$Items);
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

    /*
    *   Dashboard interface for setting up KarmaBank
    */
    public function SettingsController_KarmaBank_Create($Sender) {
        $Sender->Permission('Garden.Settings.Manage');
        $KarmaRules=new KarmaRulesModel();
        $Sender->Form = Gdn::Factory('Form');
        if($Sender->Form->IsPostBack() != False){
            $FormValues = $Sender->Form->FormValues();
            //$FormValues['RuleID']=1;
            if($Sender->Form->GetValue('Task')=='AddRule'){ 
                $KarmaRules->DefineSchema();
                $Validation = &$KarmaRules->Validation;
                $KarmaRules->Save($FormValues);
			}else if($Sender->Form->GetValue('Task')=='DisplayOptions'){
				$Validation = new Gdn_Validation();
				SaveToConfig('Plugins.KarmaBank.CommentShowBalance',$FormValues['CommentShowBalance']);
				
            }else if($Sender->Form->GetValue('Task')=='AddStartingBalance'){
                $Validation = new Gdn_Validation();
                $Validation->ApplyRule('StartingBalance', 'Decimal','Starting Balance invalid amount');
                $Validation->Validate($FormValues);
                if (count($Validation->Results()) == 0) {
                    SaveToConfig('Plugins.KarmaBank.StartingBalance', sprintf("%01.2f",GetValue('StartingBalance',$FormValues)));
                }
            }else{
                $Validation = new Gdn_Validation();
                $Validation->ApplyRule('Task','Required');
                $Validation->Validate($FormValues);
            }
            if (count($Validation->Results()) == 0) {
                Redirect('/settings/karmabank');
            }
            $Sender->Form->SetValidationResults($Validation->Results());

        }else if(array_key_exists(0,$Sender->RequestArgs) && strtolower($Sender->RequestArgs[0])=='remove' && ctype_digit($Sender->RequestArgs[1])){
            $KarmaRules->RemoveRule($Sender->RequestArgs[1]);
            Redirect('/settings/karmabank');
        }

        $Rules = $KarmaRules->GetRules();

        if($Rules && is_numeric(C('Plugins.KarmaBank.StartingBalance')) && C('Plugins.KarmaBank.StartingBalance')>0){
			SaveToConfig('Plugins.KarmaBank.Enabled',TRUE);
        }else{
			SaveToConfig('Plugins.KarmaBank.Enabled',FALSE);
		}


        $Sender->AddSideMenu();
        $Sender->SetData('Title', T('Karma Bank Settings'));
        $Sender->SetData('Description', T($this->PluginInfo['Description']));
        $Sender->SetData('Meta',$this->Meta);
        $Sender->SetData('Operations',$this->Operations);

        $Sender->SetData('Rules',$Rules );
        $Sender->SetData('Enabled',$this->IsEnabled());
        $Sender->Form->SetValue('CommentShowBalance',C('Plugins.KarmaBank.CommentShowBalance'));
        
        $Sender->Render('Settings', '', 'plugins/KarmaBank');
    }
    /*
    *   Users are given a starting balance
    */
    public function StartingBalance($UserID=null){
        if(!$UserID){
            $UserID = Gdn::Session()->UserID;
        }else{
            $User = Gdn::UserModel()->GetID($UserID);

            if(!$User)
                return;
        }

        $KarmaBank = new KarmaBankModel($UserID);
        $Balance = $KarmaBank->GetBalance();
        if(!empty($Balance) || @$GLOBALS['php_errormsg'])
            return;
        $StartingBalance = C('Plugins.KarmaBank.StartingBalance');
        if(!$KarmaBank->CheckForCollissions('Starting Balance',floatval($StartingBalance))){
			$KarmaBank->Transaction('Starting Balance',floatval($StartingBalance));
		}
    }



    /*
    *   Adds a tab to user profiles linking Karma Bank
    *   Shows current Balance as count
    */
    public function ProfileController_AddProfileTabs_Handler($Sender){
		if(!$this->IsEnabled())
			return;
        $KarmaBank = new KarmaBankModel($Sender->User->UserID);
        $Balance = $KarmaBank->GetBalance();
        $Sender->AddProfileTab('KarmaBank','profile/karmabank/'.$Sender->User->UserID.'/'.rawurlencode($Sender->User->Name),
                        'KarmaBank',T('Karma Bank').(is_numeric($Balance->Balance) ? '<span class="Count">'.sprintf("%01.2f",$Balance->Balance).'</span>':''));
    }

    /*
    *   Shows the user balance/transactions
    *   Pageable history
    *   Tax/reward system
    */
    public function ProfileController_KarmaBank_Create($Sender,$Args){
		if(!$this->IsEnabled())
			return;
        $Sender->Permission('Garden.Profiles.View');
        if(!ctype_digit($Args[0]))
            throw NotFoundException('User');
        $Sender->ThisUser=Gdn::UserModel()->GetID($Args[0]);
        list($Offset, $Limit) = OffsetLimit(array_key_exists(2,$Args)?$Args[2]:0,C('Plugins.KarmaBank.PageLimit',5));
        $Sender->Offset=$Offset;
        $KarmaBank = new KarmaBankModel($Sender->ThisUser->UserID);
        $Balance = $KarmaBank->GetBalance();
        if($Sender->Form->IsPostBack() != False && (Gdn::Session()->CheckPermission('Plugins.KarmaBank.RewardTax') || Gdn::Session()->User->Admin)){
            $FormValues = $Sender->Form->FormValues();
            $Validation = new Gdn_Validation();
            $Validation->ApplyRule('RewardTax', 'Decimal','Reward/Tax invalid amount');
            $Validation->Validate($FormValues);
            if (count($Validation->Results()) == 0) {
                $Value=GetValue('RewardTax',$FormValues);
                $Type=($Value==abs($Value))?Gdn::Session()->User->Name.' Rewards':Gdn::Session()->User->Name.' Taxes';
				$KarmaBank->Transaction($Type,$Value,$Value);
                Redirect($Sender->Request->RequestURI());
            }
            $Sender->Form->SetValidationResults($Validation->Results());
        }else if($Sender->Form->IsPostBack() != False){
            throw PermissionException();
        }
        $PagerFactory = new Gdn_PagerFactory();
        $Sender->Pager = $PagerFactory->GetPager('Pager', $Sender);
        $Sender->Pager->MoreCode = 'Older';
        $Sender->Pager->LessCode = 'Newer';
        $Sender->Pager->ClientID = 'Pager';
        $Sender->Pager->Configure(
        $Sender->Offset,
        $Limit,
        $Balance->TransCount,
         'profile/karmabank/'.$Sender->ThisUser->UserID.'/'.rawurlencode($Sender->ThisUser->Name).'/{Page}'
        );
        $Sender->SetData('Transactions',$KarmaBank->GetTransactionList($Limit,$Sender->Offset));
        $Sender->SetData('Balance',$Balance?$Balance->Balance:0);
        $Sender->AddCssFile('karma.css','plugins/KarmaBank/');
        $Sender->GetUserInfo();
        $Sender->SetTabView('Back');
        $Sender->Render('KarmaBank', '', 'plugins/KarmaBank');
    }
    
    /*
    *   Show balance with comment user meta
    */
    
   public function DiscussionController_BeforeDiscussionRender_Handler(&$Sender) {
		if(!$this->IsEnabled() || !C('Plugins.KarmaBank.CommentShowBalance'))
		return;
		$this->CacheBalances($Sender);
   }
   
   public function PostController_BeforeCommentRender_Handler(&$Sender) {
		if(!$this->IsEnabled() || !C('Plugins.KarmaBank.CommentShowBalance'))
		return;
		$this->CacheBalances($Sender);
   }
    
	protected function CacheBalances(&$Sender) {
		$Discussion = $Sender->Data('Discussion');
		$Comments = $Sender->Data('CommentData');
		$UserIDList = array();

		if ($Discussion)
		 $UserIDList[$Discussion->InsertUserID] = 1;
		 
		if ($Comments && $Comments->NumRows()) {
		 $Comments->DataSeek(-1);
		 while ($Comment = $Comments->NextRow())
			$UserIDList[$Comment->InsertUserID] = 1;
		}

		$UserBalances = array();
		if (sizeof($UserIDList)) {
			$KarmaBankModel = new KarmaBankModel(0);
			$Balances = $KarmaBankModel->GetBalances(array_keys($UserIDList));
			foreach($Balances As $UserBalance)
				$UserBalances[$UserBalance->UserID] = $UserBalance->Balance;
			
		}
		$Sender->SetData('KarmaBalances', $UserBalances);
		
	}
    
	public function DiscussionController_CommentInfo_Handler(&$Sender) {
		if(!$this->IsEnabled() || !C('Plugins.KarmaBank.CommentShowBalance'))
		return;
		$this->ShowBalance($Sender);

	}

	public function PostController_CommentInfo_Handler(&$Sender) {
		if(!$this->IsEnabled() || !C('Plugins.KarmaBank.CommentShowBalance'))
		return;
		$this->ShowBalance($Sender);
	}

	protected function ShowBalance(&$Sender) {
		$Balance = ArrayValue($Sender->EventArguments['Author']->UserID, $Sender->Data('KarmaBalances'));
		echo '<span>'.sprintf(T("%01.2f Karma"),$Balance).'</span>';
	}

    /*
    *   CheckKarma is where the magic happens
    *   Sniffing out user meta, applying those rules
    */

    public function CheckKarma($UserID=null){
        if(self::$KarmaChecked)
            return;
        if(!$UserID){
            $User = Gdn::Session()->User;
        }else{
            $User = Gdn::UserModel()->GetID($UserID);
            if(!$User)
                return;
        }
        $UserID=$User->UserID;
        $KarmaRules = new KarmaRulesModel();
        $Rules = $KarmaRules->GetRules();
        $TallySet = $KarmaRules->GetTally($UserID);
        $KarmaBank = new KarmaBankModel($UserID);
        foreach($Rules As $Rule){
            $Condition= $Rule->Condition;
            if(!property_exists($User,$Condition))
                continue;



            $Tally = null;

            foreach($TallySet As $TallyRow){
                if($TallyRow->RuleID==$Rule->RuleID)
                    $Tally = $TallyRow;
            }

            $TallyValue =$Tally && $Tally->Value? $Tally->Value : 0;
            $Value = $User->$Condition;

            $RuleID=$Rule->RuleID;
            $Target=$Rule->Target;
            $Type = $Condition.' '.$Rule->Operation.' '.$Target;
            $Transaction=FALSE;
            if($TallyValue!=$Value){

                $Transaction = call_user_func($this->OperationsMap[$Rule->Operation],$Value,$Target,$Condition);
/*
                switch($Rule->Operation){

                    case 'Equals':
                        if($Value == $Target){
                            $Transaction=TRUE;
                        }

                        break;
                    case 'Every':
                        if($Value % $Target == 0){
                            $Transaction=TRUE;
                        }
                        break;
                }
*/
                if(!$KarmaBank->CheckForCollissions($Type,$Rule->Amount,$Value)){//try to prevent collissions (uses file cache psuedo-lock)
                    if($Transaction && $TallyValue){
                        $KarmaBank->Transaction($Type,$Rule->Amount,$Value);
                    }
                $KarmaRules->SetTally($UserID,$RuleID,$Value);
                }

            }


        }
        self::$KarmaChecked=TRUE;
    }

    /*
    *   This is used as a per request cron
    *   A top level pseudo-event check, and meta update for plugins
    *   and where the Karma is checked
    */

    public function Base_BeforeControllerMethod_Handler($Sender) {
		if(!$this->IsEnabled())
			return;
        if(!Gdn::Session()->isValid()) return;
        /* QnA Accepted /  Acceptance Counts */

        if(C('EnabledPlugins.QnA')
            && strtolower($Sender->Controller())=='discussion'
            && strtolower($Sender->ControllerMethod())=='qna'){

            $Comment = Gdn::SQL()->GetWhere('Comment', array('CommentID' => GetValue('commentid',$_GET)))->FirstRow(DATASET_TYPE_ARRAY);
            if (!$Comment)
                throw NotFoundException('Comment');

            $Discussion = Gdn::SQL()->GetWhere('Discussion', array('DiscussionID' => $Comment['DiscussionID']))->FirstRow(DATASET_TYPE_ARRAY);

              // Check for permission (let QnA handle exceptions)
            if ((Gdn::Session()->UserID == GetValue('InsertUserID', $Discussion) /*|| Gdn::Session()->CheckPermission('Garden.Moderation.Manage')*/)){
                if (Gdn::Session()->ValidateTransientKey(GetValue('tkey',$_GET))){

                    $Args=$Sender->ControllerArguments();
                    if($Args[0]=='accept'){

						if ($Discussion['QnA'] != 'Accepted' && (!$Discussion['QnA'] || in_array($Discussion['QnA'], array('Unanswered', 'Answered', 'Rejected')))){
							$User = Gdn::UserModel()->GetID($Discussion['InsertUserID']);
							Gdn::SQL()->Update('User',array('QnACountAccept'=>$User->QnACountAccept+1))->Where(array('UserID'=>$User->UserID))->Put();
							if(Gdn::Session()->UserID==$User->UserID)
								Gdn::Session()->User->QnACountAccept+=1;
						}

						//You don't get points for accepting your own comments
						if($Discussion['InsertUserID']!=$Comment['InsertUserID']){
							if($Comment['QnA'] != 'Accepted'){
								$User = Gdn::UserModel()->GetID($Comment['InsertUserID']);
								Gdn::SQL()->Update('User',array('QnACountAcceptance'=>$User->QnACountAcceptance+1))->Where(array('UserID'=>$User->UserID))->Put();
								if(Gdn::Session()->UserID==$User->UserID)
									Gdn::Session()->User->QnACountAcceptance+=1;
							}
						}

                    }
                }
            }
        }
          /* QnA Accepted /  Acceptance Counts */

          /* check/update Starting Balance */
        if(strtolower($Sender->Controller())=='profile' /*&& ctype_digit($Sender->ControllerMethod())*/){
            $Args=$Sender->ControllerArguments();
            if(ctype_digit($Args[0])){
                $this->StartingBalance($Args[0]);
            }
        }else{
            $this->StartingBalance();
        }
        /* check/update Starting Balance */
        $this->CheckKarma();
    }

    public function Setup() {
        $this->Structure();
    }
    /*
    *   Earlier per request cron, hot version update of structure, and map meta spec
    */
    public function Base_BeforeDispatch_Handler($Sender){
        if(C('Plugins.KarmaBank.Version')!=$this->PluginInfo['Version'])
            $this->Structure();

        /* load meta details */
        $this->MetaMap();
    }

    public function Structure() {
		
        Gdn::Structure()
            ->Table('KarmaBankBalance')
            ->Column('UserID', 'int(11)',FALSE, 'primary')
            ->Column('Balance','decimal(5,2)')
            ->Column('TransCount','int(11)')
            ->Set();

        Gdn::Structure()
            ->Table('KarmaBankTrans')
            ->PrimaryKey('TransID')
            ->Column('UserID', 'int(11)')
            ->Column('Type','varchar(500)')
            ->Column('Date','datetime')
            ->Column('Amount','decimal(5,2)')
            ->Set();
            
        //$Index = Gdn::Structure()->Table('KarmaRules')->ColumnExists('RuleID');
			  
        Gdn::Structure()
            ->Table('KarmaRules')
            ->Column('RuleID','int(11)',FALSE,'key')
            ->Column('Condition','varchar(100)')
            ->Column('Operation','varchar(100)')
            ->Column('Target','int(11)')
            ->Column('Amount','decimal(5,2)')
            ->Column('Remove','int(4)',0)
            ->Set();
            
        //if(!$Index)
			Gdn::Structure()
				->Table('KarmaRules')
				->PrimaryKey('RuleID')
				->Set();
			
		//$Index = Gdn::Structure()->Table('KarmaRulesTally')->ColumnExists('TallyID');
			  
        Gdn::Structure()
            ->Table('KarmaRulesTally')
			->Column('TallyID','int(11)',FALSE,'key')
            ->Column('RuleID','int(11)')
            ->Column('UserID', 'int(11)')
            ->Column('Value','int(11)')
            ->Set();
		
		//if(!$Index)
			Gdn::Structure()
				->Table('KarmaRulesTally')
				->PrimaryKey('TallyID')
				->Set();
			
		Gdn::Structure()
			->Table('User')
			->Column('QnACountAccept','int(11)',0)
			->Column('QnACountAcceptance','int(11)',0)
			->Set();

        //Save Version for hot update

        SaveToConfig('Plugins.KarmaBank.Version', $this->PluginInfo['Version']);
   }
}
