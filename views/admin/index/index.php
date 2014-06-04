<?php

$head = array('bodyclass' => 'history-log primary', 
              'title' => html_escape(__('YouTube Import | Import Video')));
echo head($head);
?>
<?php echo flash(); ?>
<?php echo $form; ?>
<?php echo foot(); ?>