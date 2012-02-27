<?php if (!defined('APPLICATION')) exit();
/**
 * Created by JetBrains PhpStorm.
 * User: jakob.madsen
 * Date: 06/02/12
 * Time: 14.37
 * To change this template use File | Settings | File Templates.
 */
class Lydmaskinen_v2ThemeHooks implements Gdn_IPlugin
{

    public function Setup() {
        return TRUE;
    }

    public function OnDisable() {
        return TRUE;
    }

    public function Base_Render_Before($Sender) {

    }

    // Custom post meta header
    public function DiscussionController_lydmaskinenPost_Handler($Sender) {

        // Get vars ready
        $Author = $Sender->EventArguments['Author'];
        $Discussion = $Sender->EventArguments['Discussion'];


        // Build the post
        ?>
        <div class="Meta">
            <span class="Author">
                <?= UserPhoto($Author)?>
                <?= UserAnchor($Author)?>
            </span>

            <span class="MItem DateCreated">
                <?= Anchor(Gdn_Format::Date($Discussion->DateInserted, 'html'), $Discussion->Url, 'Permalink', array('rel' => 'nofollow')); ?>
            </span>

            <?php
            // Include source if one was set
            if ($Source = GetValue('Source', $Comment)) { echo Wrap(sprintf(T('via %s'), T($Source.' Source', $Source)), 'span', array('class' => 'MItem Source')); };

            // Add your own options or data as spans with 'MItem' class
            $Sender->FireEvent('InsideCommentMeta');

            // Add Options
            WriteCommentOptions($Comment);
            ?>
            <div class="CommentInfo">
                <?php
                $Sender->FireEvent('CommentInfo');
                ?>
            </div>
            <?php $Sender->FireEvent('AfterCommentMeta'); ?>

        </div>

        <div class="Message">
            <?= FormatBody($Discussion)?>
        </div>

        <?php $Sender->FireEvent('AfterCommentBody');

    }

}
