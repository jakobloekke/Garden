<?php if (!defined('APPLICATION')) exit();
$Session = Gdn::Session(); 
if (!function_exists('WriteComment'))
   include $this->FetchViewLocation('helper_functions', 'discussion');

// Wrap the discussion related content in a div.
echo '<section class="headline MessageList Discussion '.CssClass($this->Data('Discussion')).'">';
?>

<h2><?=$this->Data('Discussion.Name')?></h2>


    <?php /*<div class="Options">

        <?php
        WriteBookmarkLink();
        WriteDiscussionOptions();
        WriteAdminCheck();
        ?>

    </div>*/?>


<?php
// Write the initial discussion.
if ($this->Data('Page') == 1) {
   include $this->FetchViewLocation('discussion', 'discussion');
}
echo '</section>'; // close discussion wrap
?>


<a href="#" class="banner">
    <img src="<?= Url('/themes/lydmaskinen_v2/banners/dpa.jpg')?>">
</a>


<div class="CommentsWrap">
    <?php
    // Write the comments.
    $this->Pager->Wrapper = '<span %1$s>%2$s</span>';
    ?>

    <span class="BeforeCommentHeading">
        <?php
        $this->FireEvent('CommentHeading');
        echo $this->Pager->ToString('less');
        ?>
    </span>

    <ul class="MessageList DataList Comments">
        <?php include $this->FetchViewLocation('comments'); ?>
    </ul>

    <?php
    $this->FireEvent('AfterDiscussion');
    if($this->Pager->LastPage()) {
       $LastCommentID = $this->AddDefinition('LastCommentID');
       if(!$LastCommentID || $this->Data['Discussion']->LastCommentID > $LastCommentID)
          $this->AddDefinition('LastCommentID', (int)$this->Data['Discussion']->LastCommentID);
       $this->AddDefinition('Vanilla_Comments_AutoRefresh', Gdn::Config('Vanilla.Comments.AutoRefresh', 0));
    }
    ?>

    <div class="P">
    <?php
        $this->Pager->Wrapper = '<div %1$s>%2$s</div>';
        echo $this->Pager->ToString('more');
    ?>
    </div>
</div>

<?php
WriteCommentForm();
?>