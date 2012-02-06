<?php if (!defined('APPLICATION')) exit();
$CatList = '';
$DoHeadings = C('Vanilla.Categories.DoHeadings');
$MaxDisplayDepth = C('Vanilla.Categories.MaxDisplayDepth');
$ChildCategories = '';



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
?>

<section class="headline">
	<h2>Forumskategorier</h2>
	<div class="toolbar top">
		<a class="new-post" href="#">Nye indlæg</a>
		&middot;
		<a class="mark-as-read" href="#">Marker læst</a>
		&middot;
		<a class="like" href="#"><img src="/Vanilla/themes/Lydmaskinen/design/images/like.gif" alt="like" />Synes godt om</a>
	</div>
	<ul class="iconlist">
	<?php 
		renderSelectedCategories(
			$this->CategoryData->Result(), 
			array(
				'Computer &amp; Software', 
				'Andet Musikudstyr &amp; Lydteknik',
				'Branche &amp; Teori',
				'Studiebygning &amp; Akustik',
				'Live Lyd &amp; PA'
			)
		); 
	?>
	</ul>
</section>

<a href="#" class="banner">
	<img src="/Vanilla/themes/Lydmaskinen/banners/dpa.jpg">
</a>

<section>
	<ul class="iconlist">
		<?php 
			renderSelectedCategories(
				$this->CategoryData->Result(), 
				array(
					'Din Musik', 
					'Blogs',
					'Backstage',
					'After Party'
				)
			); 
		?>
	</ul>
	<div class="toolbar bottom">
		<p id="stats">
			
			<?php
			foreach ($this->CategoryData->Result() as $Category) {
				$CountDiscussions = $CountDiscussions + $Category->CountDiscussions;
				$CountComments = $CountComments + $Category->CountComments;
			};
			?>
			
			Emner: <?=$CountDiscussions?>  &middot;  Indl&aelig;g: <?=$CountComments?>  &middot;  Medlemmer: <?=$this->numMembers?>  &middot;  Online: <span id="numOnlineInFooter">-</span>
		</p>
	</div>
</section>

<input type='button' value='Nyt emne' onClick='location.href="post/discussion/<?=(array_key_exists('CategoryID', $Data) ? '/'.$Data['CategoryID'] : '')?>"; return false;'/>



