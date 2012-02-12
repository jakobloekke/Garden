<?php if (!defined('APPLICATION')) exit();
include($this->FetchViewLocation('helper_functions', 'categories'));
?>

<section class="headline">
    <h2>Forumskategorier</h2>
    <div class="toolbar top">
        <a class="new-post" href="#">Nye indlæg</a>
        &middot;
        <a class="mark-as-read" href="#">Marker læst</a>
        &middot;
        <a class="like" href="#"><img src="<?= Url('/themes/lydmaskinen_v2/design/images/like.gif')?>" alt="like" />Synes godt om</a>
    </div>
    <ul class="iconlist">
        <?=
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
    <img src="<?= Url('/themes/lydmaskinen_v2/banners/dpa.jpg')?>">
</a>

<section>
    <ul class="iconlist">
        <?=
        renderSelectedCategories(
            $this->CategoryData->Result(),
            array(
                'Din Musik',
                'Blogs',
                'Backstage',
                'After Party',
                'Markedsplads'
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

<?php
    $newPostLink = "post/discussion/" . (array_key_exists('CategoryID', $Data) ? '/'.$Data['CategoryID'] : '');
?>
<input type='button' value='Nyt emne' onClick='location.href="<?=$newPostLink?>"; return false;'/>



