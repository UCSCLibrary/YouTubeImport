<?php

$head = array('bodyclass' => 'you-tube-import primary', 
              'title' => html_escape(__('YouTube Import | Import Video')));
echo head($head);
echo flash(); 
if(isset($successDialog)) 
    echo '<div id="youtube-success-dialog" title="&#x2714; SUCCESS"></div>';
echo $form; 
echo foot(); 
