<!doctype html>
<!--[if lt IE 7 ]> <html lang="en" class="no-js ie6"> <![endif]-->
<!--[if IE 7 ]>    <html lang="en" class="no-js ie7"> <![endif]-->
<!--[if IE 8 ]>    <html lang="en" class="no-js ie8"> <![endif]-->
<!--[if IE 9 ]>    <html lang="en" class="no-js ie9"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--> <html lang="en" class="no-js"> <!--<![endif]-->
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	
	<?php $this->RenderAsset('Head'); ?>
	
	<link rel="shortcut icon" href="/favicon.ico">
	<link rel="apple-touch-icon" href="/apple-touch-icon.png">
	<link rel="stylesheet" href="/Vanilla/themes/Lydmaskinen/design/lydmaskinen.css?v=1">

	<script src="/Vanilla/themes/Lydmaskinen/js/libs/modernizr-1.7.min.js" type="text/javascript"></script>


</head>
<body id="<?= $BodyIdentifier; ?>" class="<?= $this->CssClass; ?>">

	<div id="header-container">
		<header class="wrapper clearfix">
			<a href="#" class="banner" id="topbanner">
				<img src="/Vanilla/themes/Lydmaskinen/banners/gajolbanner.jpg" />
			</a>
			<h1 class="leftcol" id="title"><a href="<?php echo Url('/'); ?>">Lydmaskinen</a></h1>

			<nav class="rightcol">
				<ul>
					<li><a id="forum" href="forum.html" class="active">Forum</a></li>
					<li><a id="news" href="#">Nyheder</a></li>
					<li><a id="marketplace" href="#">Marked</a></li>
					<li><a id="tipsandtricks" href="#">Tips &amp; Tricks</a></li>
				</ul>
			</nav>
			
			<div id="searchbox" class="Search leftcol">
				<?php
					$Form = Gdn::Factory('Form');
					$Form->InputPrefix = '';
					echo 
						$Form->Open(array('action' => Url('/search'), 'method' => 'get')),
						$Form->TextBox('Search'),
						$Form->Button('Go', array('Name' => '')),
						$Form->Close();
				?>
			</div>
			
			<div class="toolbar rightcol">
                <?php if (Gdn::Session()->IsValid()) { ?>
                    <a class="profile" href="/profile">Din profil</a>
                    &middot;
                    <a class="favourites" href="#">Favoritter <span class="bubble">
                        <?= Gdn::Session()->User->CountBookmarks ?></span>
                    </a>
                    &middot;
                    <a class="messages" href="#">Beskeder <span class="bubble red">-</span></a>
                    &middot;
                    <a class="newposts" href="#">Nye indlæg <span class="bubble blue">
                        <?= Gdn::Session()->User->CountUnreadDiscussions ?>
                    </span></a>
                    &middot;
                    <a class="karma" href="#">Karma <span class="bubble green">-</span></a>
				<?php } ?>

				<span class="stayright">
					<a class="rules" href="#">Regler</a>
					&middot;
                    <?php if (Gdn::Session()->IsValid()) { ?>
					    <a class="logout" href="entry/signout">Log ud</a>
                    <?php } else { ?>
                        <a class="login" href="entry/signin">Log ind</a>
                    <?php } ?>
				</span>
			</div>
		</header>
	</div>
	
	<div id="main" class="wrapper">

		<aside id="Panel">
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
				<span>© Copyright 2011 Lydmaskinen.dk</span>
				
				<?php
					$this->RenderAsset('Foot');
					echo Wrap(Anchor(T('Powered by Vanilla'), C('Garden.VanillaUrl')), 'div');
				?>
				
			</footer>
			
		</article>

   </div>

	<script src="/Vanilla/themes/Lydmaskinen/js/script.js" type="text/javascript"></script>
	<!--[if lt IE 7 ]>
	<script src="/Vanilla/themes/Lydmaskinen/js/libs/dd_belatedpng.js" type="text/javascript"></script>
	<script type="text/javascript"> DD_belatedPNG.fix('img, .png_bg');</script>
	<![endif]-->
	<script>
		var _gaq=[['_setAccount','UA-XXXXX-X'],['_trackPageview']]; // Change UA-XXXXX-X to be your site's ID
		(function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];g.async=1;
		g.src=('https:'==location.protocol?'//ssl':'//www')+'.google-analytics.com/ga.js';
		s.parentNode.insertBefore(g,s)}(document,'script'));
	</script>

	<?php $this->FireEvent('AfterBody'); ?>
</body>
</html>
