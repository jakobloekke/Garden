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
        return true;
    }

    public function DiscussionController_AfterBuildPager_Handler($Sender) {
        return true;
    }

    public function Controller_AfterJsCdns_Handler($sender) {
        return true;
    }

}
