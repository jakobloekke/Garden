<?php if (!defined('APPLICATION')) exit(); ?>
<h1><?php echo $this->Data('Title'); ?></h1>

<?php
echo $this->Form->Open();
echo $this->Form->Errors();
?>

<?php if (is_array($this->Data('ThemeInfo.Options.Styles'))): ?>
<h3><?php echo T('Styles'); ?></h3>
<div class="Info">
   <?php echo T('This theme has different styles.', 'This theme has several different styles. Select the style you want and click save.'); ?>
</div>

<table class="SelectionGrid ThemeStyles">
   <tbody>
<?php
$Alt = FALSE;
$Cols = 3;
$Col = 0;
$Classes = array('FirstCol', 'MiddleCol', 'LastCol');

foreach ($this->Data('ThemeInfo.Options.Styles') as $Key => $Value) {
   if ($Col == 0)
      echo '<tr>';

   $Active = '';
   if ($this->Data('ThemeOptions.Styles.Key') == $Key || (!$this->Data('ThemeOptions.Styles.Key') && $Value == '%s'))
      $Active = ' Active';

   echo "<td id=\"{$Key}_td\" class=\"{$Classes[$Col]}$Active\">";
   echo '<h4>',T($Key),'</h4>';

   // Look for a screenshot for for the style.
   $Screenshot = SafeGlob(PATH_THEMES.DS.$this->Data('ThemeFolder').DS.'design'.DS.ChangeBasename('screenshot.*', $Value), 0, array('gif','jpg','png'));
   if (is_array($Screenshot) && count($Screenshot) > 0) {
      $Screenshot = basename($Screenshot[0]);
      echo Img('/themes/'.$this->Data('ThemeFolder').'/design/'.$Screenshot, array('alt' => T($Key), 'width' => '160'));
   }

   $Disabled = $Active ? ' Disabled' : '';
   echo '<div class="Buttons">',
      Anchor(T('Select'), '#', 'SmallButton SelectThemeStyle'.$Disabled, array('Key' => $Key)),
      '</div>';

   echo '</td>';

   $Col = ($Col + 1) % 3;
   if ($Col == 0)
      echo '</tr>';
}
   if ($Col > 0)
      echo '<td colspan="'.(3 - $Col).'">&nbsp;</td></tr>';

?>
   </tbody>
</table>

<?php endif; ?>

<?php if (is_array($this->Data('ThemeInfo.Options.Text'))): ?>
<h3><?php echo T('Text'); ?></h3>
<div class="Info">
   <?php echo T('This theme has customizable text.', 'This theme has text that you can customize.'); ?>
</div>

<ul>
<?php foreach ($this->Data('ThemeInfo.Options.Text') as $Code => $Default) {
  echo '<li>',
   $this->Form->Label('@'.$Code, 'Text_'.$Code),
   $this->Form->TextBox('Text_'.$Code, array('MultiLine' => TRUE)),
   '</li>';
}
?>
</ul>

<?php endif; ?>

<?php
echo $this->Form->Close('Save');