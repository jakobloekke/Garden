<?php if (!defined('APPLICATION')) exit();

function WriteActivity($Activity, &$Sender, &$Session, $Comment) {
   // If this was a status update or a wall comment, don't bother with activity strings
   $ActivityType = explode(' ', $Activity->ActivityType); // Make sure you strip out any extra css classes munged in here
   $ActivityType = $ActivityType[0];
   $Author = UserBuilder($Activity, 'Activity');
   $PhotoAnchor = UserPhoto($Author, 'Photo');
   $CssClass = 'Item Activity '.$ActivityType;
   if ($PhotoAnchor != '')
      $CssClass .= ' HasPhoto';
   if (in_array($ActivityType, array('WallComment', 'AboutUpdate')))
      $CssClass .= ' Condensed';
      
   $Title = '';
   $Excerpt = $Activity->Story;
   if (!in_array($ActivityType, array('WallComment', 'AboutUpdate'))) {
      $Title = '<div class="Title">'.Format::ActivityHeadline($Activity, $Sender->ProfileUserID).'</div>';
   } else if ($Activity->ActivityType == 'WallComment' && $Activity->RegardingUserID > 0 && (!property_exists($Sender, 'ProfileUserID') || $Sender->ProfileUserID != $Activity->RegardingUserID)) {
      $Title = '<div class="Title">'
         .UserAnchor($Author, 'Title Name')
         .' <span>→</span> '
         .UserAnchor($Author, 'Name')
         .'</div>';
      $Excerpt = Format::Display($Excerpt);
   } else {
      $Title = UserAnchor($Author, 'Title Name');
      $Excerpt = Format::Display($Excerpt);
   }
   ?>
<li id="Activity_<?php echo $Activity->ActivityID; ?>" class="<?php echo $CssClass; ?>">
   <?php
   if (
      $Session->IsValid()
      && ($Session->UserID == $Activity->InsertUserID
         || $Session->CheckPermission('Garden.Activity.Delete'))
      )
      echo '<div class="OptionButton">'.Anchor(T('Delete'), 'garden/activity/delete/'.$Activity->ActivityID.'/'.$Session->TransientKey().'?Return='.urlencode(Gdn_Url::Request()), 'Delete').'</div>';

   if ($PhotoAnchor != '') {
   ?>
   <div class="Photo"><?php echo $PhotoAnchor; ?></div>
   <?php } ?>
   <div class="ItemContent Activity">
      <?php echo $Title; ?>
      <div class="Excerpt"><?php echo $Excerpt; ?></div>
      <div class="Meta">
         <span class="DateCreated"><?php echo Format::Date($Activity->DateInserted); ?></span>
         <?php
         if ($Activity->AllowComments == '1' && $Session->IsValid())
            echo '<span class="AddComment">'.Anchor(T('Comment'), '#CommentForm_'.$Activity->ActivityID, 'CommentOption').'</span>';
         ?>
      </div>
   </div>
   <?php
   if ($Activity->AllowComments == '1') {
      // If there are comments, show them
      $FoundComments = FALSE;
      if (property_exists($Sender, 'CommentData') && is_object($Sender->CommentData)) {
         foreach ($Sender->CommentData->Result() as $Comment) {
            if (is_object($Comment) && $Comment->CommentActivityID == $Activity->ActivityID) {
               if ($FoundComments == FALSE)
                  echo '<ul class="DataList ActivityComments">';
                  
               $FoundComments = TRUE;
               WriteActivityComment($Comment, $Sender, $Session);
            }
         }
      }
      if ($FoundComments == FALSE)
         echo '<ul class="DataList ActivityComments Hidden">';

      if ($Session->IsValid()) {
         ?>
         <li class="CommentForm">
         <?php
            echo Anchor(T('Write a comment'), '/garden/activity/comment/'.$Activity->ActivityID, 'CommentLink');
            $CommentForm = Gdn::Factory('Form');
            $CommentForm->SetModel($Sender->ActivityModel);
            $CommentForm->AddHidden('ActivityID', $Activity->ActivityID);
            $CommentForm->AddHidden('Return', Gdn_Url::Request());
            echo $CommentForm->Open(array('action' => Url('/garden/activity/comment'), 'class' => 'Hidden'));
            echo $CommentForm->TextBox('Body', array('MultiLine' => TRUE, 'value' => ''));
            echo $CommentForm->Close('Comment');
         ?></li>
      <?php } ?>
      </ul>
   <?php } ?>
</li>
<?php
}

function WriteActivityComment($Comment, &$Sender, &$Session) {
   $Author = UserBuilder($Comment, 'Activity');
   $PhotoAnchor = UserPhoto($Author, 'Photo');
   $CssClass = 'Item ActivityComment Condensed '.$Comment->ActivityType;
   if ($PhotoAnchor != '')
      $CssClass .= ' HasPhoto';
   
?>
<li id="Activity_<?php echo $Comment->ActivityID; ?>" class="<?php echo $CssClass; ?>">
   <?php if ($PhotoAnchor != '') { ?>
   <div class="Photo"><?php echo $PhotoAnchor; ?></div>
   <?php } ?>
   <div class="ItemContent ActivityComment">
      <?php echo UserAnchor($Author, 'Title Name'); ?>
      <div class="Excerpt"><?php echo Format::Display($Comment->Story); ?></div>
      <div class="Meta">
         <span class="DateCreated"><?php echo Format::Date($Comment->DateInserted); ?></span>
         <?php
            if ($Session->UserID == $Comment->InsertUserID || $Session->CheckPermission('Garden.Activity.Delete'))
               echo Anchor(T('Delete'), 'garden/activity/delete/'.$Comment->ActivityID.'/'.$Session->TransientKey().'?Return='.urlencode(Gdn_Url::Request()), 'DeleteComment');
         ?>
      </div>
   </div>
</li>
<?php
}