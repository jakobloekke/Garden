<?php if (!defined('APPLICATION')) exit();

// Define the plugin:
$PluginInfo['Lydmaskinen'] = array(
    'Name' => 'Lydmaskinen',
    'Description' => 'Adds customized functionality for Lydmaskinen.dk',
    'Version' => '1',
    'RequiredApplications' => FALSE,
    'RequiredTheme' => FALSE,
    'RequiredPlugins' => FALSE,
    'HasLocale' => TRUE,
    'RegisterPermissions' => FALSE,
    'Author' => "Jakob Løkke Madsen",
    'AuthorEmail' => 'jakob@jakobloekkemadsen.com',
    'AuthorUrl' => 'http://www.jakobloekkemadsen.com',
    'Hidden' => FALSE
);

class LydmaskinenPlugin extends Gdn_Plugin {

    // Show user link to original poster in post overview items
    public function CategoriesController_AfterCountMeta_Handler($Sender) {
        echo "<span class='MItem StartedBy'>".sprintf(T('Started by %1$s'), UserAnchor($Sender->EventArguments['FirstUser']))."</span>";
    }

    // The meta header for initial discussion topic
    public function DiscussionController_AfterDiscussionMeta_Handler($Sender) {
        $this->_renderCommentToolbar($Sender);
        $this->_renderCommentProfileBar($Sender);
    }

    // The meta header for comments
    public function DiscussionController_AfterCommentMeta_Handler($Sender) {
       $this->_renderCommentProfileBar(&$Sender);
    }





    // Utility methods:
    protected function _renderCommentToolbar(&$Sender) {
        include("views/comment_profile_bar.php");
    }

    protected function _renderCommentProfileBar(&$Sender) {
        include("views/comment_profile_bar.php");
    }

    public function Setup() {
      // No setup required.
    }
}