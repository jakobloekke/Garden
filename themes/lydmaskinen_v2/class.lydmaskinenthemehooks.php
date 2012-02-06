<?php if (!defined('APPLICATION')) exit();
/**
 * Created by JetBrains PhpStorm.
 * User: jakob.madsen
 * Date: 06/02/12
 * Time: 14.37
 * To change this template use File | Settings | File Templates.
 */
class LydmaskinenThemeHooks implements Gdn_IPlugin
{

    public function Setup() {
        return TRUE;
    }

    public function OnDisable() {
        return TRUE;
    }

    public function Base_Render_Before($Sender) {

    }

}
