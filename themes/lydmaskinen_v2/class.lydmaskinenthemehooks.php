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

        $Author = $Sender->EventArguments['Author'];
        $Discussion = $Sender->EventArguments['Discussion'];


        // Meta header
        echo UserPhoto($Author);
        echo UserAnchor($Author);

        echo Anchor(Gdn_Format::Date($Discussion->DateInserted, 'html'), $Discussion->Url, 'Permalink', array('rel' => 'nofollow'));


        // Post body
        echo FormatBody($Discussion);


        // Post footer


    }

}
