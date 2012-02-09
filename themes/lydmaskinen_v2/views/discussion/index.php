<?php if (!defined('APPLICATION')) exit();
$Session = Gdn::Session(); 
if (!function_exists('WriteComment'))
   include $this->FetchViewLocation('helper_functions', 'discussion');

// Wrap the discussion related content in a div.
echo '<section class="headline MessageList Discussion '.CssClass($this->Data('Discussion')).'">';
?>

<h2><?=$this->Data('Discussion.Name')?></h2>



    <div class="Options">

        <?php
        WriteBookmarkLink();
        WriteDiscussionOptions();
        WriteAdminCheck();
        ?>

    </div>


<?php
// Write the initial discussion.
if ($this->Data('Page') == 1) {
   include $this->FetchViewLocation('discussion', 'discussion');
   echo '</section>'; // close discussion wrap
} else {
   echo '</section>'; // close discussion wrap
}

echo '<div class="CommentsWrap">';

// Write the comments.
$this->Pager->Wrapper = '<span %1$s>%2$s</span>';
echo '<span class="BeforeCommentHeading">';
$this->FireEvent('CommentHeading');
echo $this->Pager->ToString('less');
echo '</span>';

echo '<h2 class="CommentHeading">'.T('Comments').'</h2>';

?>
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

echo '<div class="P">';
$this->Pager->Wrapper = '<div %1$s>%2$s</div>';
echo $this->Pager->ToString('more');
echo '</div>';
echo '</div>';

WriteCommentForm();
