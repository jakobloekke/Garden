<!doctype html>
<!--[if lt IE 7 ]> <html lang="en" class="no-js ie6"> <![endif]-->
<!--[if IE 7 ]>    <html lang="en" class="no-js ie7"> <![endif]-->
<!--[if IE 8 ]>    <html lang="en" class="no-js ie8"> <![endif]-->
<!--[if IE 9 ]>    <html lang="en" class="no-js ie9"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--> <html lang="en" class="no-js"> <!--<![endif]-->
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	
	<?php
        $this->Head->addCSS("themes/lydmaskinen_v2/design/stylesheets/screen.css");
        $this->RenderAsset('Head');
    ?>

</head>
<body id="<?= $BodyIdentifier; ?>" class="<?= $this->CssClass; ?>">

	<div id="header-container">
		<header class="wrapper clearfix">
			<a href="#" class="banner" id="topbanner">
				<img src='<?= Url("/themes/lydmaskinen_v2/banners/gajolbanner.jpg") ?>' />
			</a>
			<h1 class="leftcol" id="title"><a href="<?= Url('/'); ?>">Lydmaskinen</a></h1>

			<nav class="rightcol">
				<ul>
					<li><a id="forum" href="<?= Url('/'); ?>" class="active">Forum</a></li>
					<li><a id="news" href="#">Nyheder</a></li>
					<li><a id="marketplace" href="#">Marked</a></li>
					<li><a id="tipsandtricks" href="#">Tips &amp; Tricks</a></li>
				</ul>
			</nav>
			
			<div id="searchbox" class="Search leftcol">
                <form action="<?= Url('/search')?>" method="get">
                    <input type="text" name="Search" placeholder="Søg" />
                    <input type="submit" value="Søg" />
                </form>
			</div>




            <?php
                /**
                 * @param $count
                 * @param $cssClass CSS class that matches a color name
                 * @todo Get this into generalized function file!!!
                 * @return string
                 */
                function showBubbleIfCount($count, $cssClass) {
                    if ($count > 0) {
                        return "<span class='bubble ".$cssClass."'>".$count."</span>";
                    }
                }
            ?>
			
			<div class="toolbar rightcol">
                <?php if (Gdn::Session()->IsValid()) { ?>
                    <a class="profile" href="<?= Url('/profile')?>">Din profil</a>
                    &middot;
                    <a class="favourites" href="<?= Url('/discussions/bookmarked')?>">
                        Favoritter
                        <?= showBubbleIfCount(Gdn::Session()->User->CountBookmarks, "red") ?>
                    </a>
                    &middot;
                    <a class="yourposts" href="<?= Url('/discussions/mine')?>">
                        Dine indlæg
                        <?= showBubbleIfCount(Gdn::Session()->User->CountDiscussions, "red") ?>
                    </a>
                    &middot;
                    <a class="messages" href="#">
                        Beskeder
                        <?= showBubbleIfCount(Gdn::Session()->User->CountUnreadConversations, "red") ?>
                    </a>
                    &middot;
                    <a class="newposts" href="#">
                        Nye indlæg
                        <?= showBubbleIfCount(Gdn::Session()->User->CountUnreadDiscussions, "blue") ?>
                    </a>
                    &middot;
                    <a class="karma" href="#">
                        Karma
                        <?php //echo showBubbleIfCount(10, "green") ?>
                    </a>
				<?php } ?>

				<span class="stayright">
					<a class="rules" href="#">Regler</a>
					&middot;
                    <?php if (Gdn::Session()->IsValid()) { ?>
					    <a class="logout SignInPopup" href="<?= Url('/entry/signout')?>">Log ud</a>
                    <?php } else { ?>
                        <a class="login SignInPopup" href="<?= Url('/entry/signin')?>">Log ind</a>
                    <?php } ?>
				</span>
			</div>
		</header>
	</div>
	
	<div id="main" class="wrapper">

		<aside id="Panel" class="leftcol">
			<?php $this->RenderAsset('Panel'); ?>
		</aside>
        
		<article class="rightcol" id="Content">
			<?php $this->RenderAsset('Content'); ?>
			
			<footer>
				<a href="#" class="contact">Kontakt</a>
				&middot;
				<a href="#" class="advertise">Reklamér på Lydmaskinen.dk</a>
				&middot;
				<a href="#" class="team">Holdet bag websitet</a>
				&middot;
				<span>Copyright <?= DATE("Y") ?> Lydmaskinen.dk</span>
				
				<?php
					$this->RenderAsset('Foot');
					echo Wrap(Anchor(T('Powered by Vanilla'), C('Garden.VanillaUrl')), 'div');
				?>
				
			</footer>
			
		</article>

   </div>

	<script>
		var _gaq=[['_setAccount','UA-XXXXX-X'],['_trackPageview']]; // Change UA-XXXXX-X to be your site's ID
		(function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];g.async=1;
		g.src=('https:'==location.protocol?'//ssl':'//www')+'.google-analytics.com/ga.js';
		s.parentNode.insertBefore(g,s)}(document,'script'));
	</script>

	<?php $this->FireEvent('AfterBody'); ?>
</body>
</html>