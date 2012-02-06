<?php if (!defined('APPLICATION')) exit();
/**
 * Render options that the user has for this discussion.
 */
function GetOptions($Category, $Sender) {
   if (!Gdn::Session()->IsValid())
      return;
   
   $Result = '';
   $Options = '';
   $CategoryID = GetValue('CategoryID', $Category);

   $Result = '<div class="Options">';
   $TKey = urlencode(Gdn::Session()->TransientKey());

   // Mark category read.
   $Options .= '<li>'.Anchor(T('Mark Read'), "/vanilla/category/markread?categoryid=$CategoryID&tkey=$TKey").'</li>';

   // Follow/Unfollow category.
   if (!GetValue('Following', $Category))
      $Options .= '<li>'.Anchor(T('Follow'), "/vanilla/category/follow?categoryid=$CategoryID&value=1&tkey=$TKey").'</li>';
   else
      $Options .= '<li>'.Anchor(T('Unfollow'), "/vanilla/category/follow?categoryid=$CategoryID&value=0&tkey=$TKey").'</li>';

   // Allow plugins to add options
   $Sender->FireEvent('DiscussionOptions');

   if ($Options != '') {
         $Result .= '<span class="ToggleFlyout OptionsMenu">';
            $Result .= '<span class="OptionsTitle">'.T('Options').'</span>';
            $Result .= '<ul class="Flyout MenuItems">'.$Options.'</ul>';
         $Result .= '</span>';
      $Result .= '</div>';
      return $Result;
   }
}

/**
 * @param $AllCategories
 * @param $SelectedCategories
 * @return HTML list items for matching categories ("<li>")
 */
function renderSelectedCategories($AllCategories, $SelectedCategories) // -> (Resultset, Array of category names)
{
    foreach ($SelectedCategories as $Selected) {
        foreach ($AllCategories as $Category) {
            if ( $Category->Name == $Selected ) {
                echo
                    "<li>
					<img class='iconlist-icon' width='49' height='49' src='/Vanilla/themes/Lydmaskinen/design/images/content/$Category->UrlCode.jpg' />
					<div class='iconlist-content'>
						<h4><a href='/Vanilla/categories/$Category->UrlCode'>$Category->Name</a></h4>
						<p>$Category->Description</p>
						<p class='meta'>
							".Gdn_Format::Date($Category->DateLastComment).":
							<a href='/Vanilla/discussion/$Category->LastDiscussionID/$Category->LastDiscussionName'>$Category->LastDiscussionName</a>
							af $Category->LastCommentName
						</p>
					</div>
				</li>";
            };
        }
    }
}