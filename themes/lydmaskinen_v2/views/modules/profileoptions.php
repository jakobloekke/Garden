<?php if (!defined('APPLICATION')) exit();
/** Displays the "Edit My Profile" or "Back to Profile" buttons on the top of the profile page. */
$Controller = Gdn::Controller();
?>
<div class="ProfileOptions">
   <?php
   $Controller->FireEvent('BeforeProfileOptions');

   if ($Controller->EditMode) {
      echo UserAnchor($Controller->User, 'Button', array('Text' => T('Back to Profile')));

   } else {
      $Session = Gdn::Session();
      if ($Controller->User->UserID != $Session->UserID) {
         if ($Session->CheckPermission('Garden.Users.Edit'))
            echo Anchor(T('Edit Profile'), '/profile/edit/'.$Controller->User->UserID.'/'.rawurlencode($Controller->User->Name), 'Button');
      } else {
         if (C('Garden.UserAccount.AllowEdit')) // Don't allow account editing if it is turned off.
            echo Anchor(T('Edit My Profile'), '/profile/edit', 'Button');
      }
   }
   $Controller->FireEvent('AfterProfileOptions');
   ?>
</div>


