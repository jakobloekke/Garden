<?php if (!defined('APPLICATION')) exit();
if (count($this->Data('Activities'))) {
   echo '<ul class="DataList Activities Notifications">';
   include($this->FetchViewLocation('activities', 'activity', 'dashboard'));
   echo '</ul>';
   echo PagerModule::Write(array('CurrentRecords' => count($this->Data('Activities'))));
} else {
   ?>
<div class="Empty"><?php echo T('You do not have any notifications yet.'); ?></div>
   <?php
}