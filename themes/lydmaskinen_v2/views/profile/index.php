<?php if (!defined('APPLICATION')) exit(); ?>
<div class="Profile">
    <?php include($this->FetchViewLocation('user'))?>

    <?php include($this->FetchViewLocation('tabs'))?>
    <div class="TabContent">
        <?php include($this->FetchViewLocation($this->_TabView, $this->_TabController, $this->_TabApplication))?>
    </div>
</div>