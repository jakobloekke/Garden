<?php if (!defined('APPLICATION')) exit();
$Session = Gdn::Session();
include($this->FetchViewLocation('helper_functions', 'discussions', 'vanilla'));
WriteCheckController();
?>

<section class="headline Categories">

    <h2><?= $this->Data('Title')?></h2>

    <?php

    if ($this->DiscussionData->NumRows() > 0 || (isset($this->AnnounceData) && is_object($this->AnnounceData) && $this->AnnounceData->NumRows() > 0)) {

    ?>

    <ul class="iconlist DataList Discussions">
       <?php include($this->FetchViewLocation('discussions'))?>
    </ul>

    <?php
       $PagerOptions = array('RecordCount' => $this->Data('CountDiscussions'), 'CurrentRecords' => $this->Data('Discussions')->NumRows());
       if ($this->Data('_PagerUrl')) {
          $PagerOptions['Url'] = $this->Data('_PagerUrl');
       }
       echo PagerModule::Write($PagerOptions);

    } else {

    ?>

       <div class="Empty"><?= T('No discussions were found.')?></div>

    <?php
    }
    ?>

</section>