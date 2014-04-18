<?php echo head(array('title' => 'Youtube Import')); ?>

<?php echo flash(); ?>

<?php

if(!isset($this->collection))
  $this->collection = 0;
?>

<form>

<div class="field">
 <div id="youtube-url-label" class="two columns alpha">
<label for="youtube-url"><?php echo __('Youtube URL'); ?></label>
</div>
   <div class="inputs five columns omega">
   <?php echo $this->formText('youtube-url',"",array()) ?>
<p class="explanation"><?php echo __( 'Paste the full url of the Youtube video you would like to import'); ?></p>
   </div>
</div>

<div class="field" id="youtube-number-div">
 <div id="youtube-number-label" class="two columns alpha">
<label for="youtube-number"><?php echo __('Number of Photos'); ?></label>
</div>
<div class="inputs five columns omega">
  <label><input type="radio" name="youtube-number" value="single" id="youtube-single-radio" checked="checked">Single Video</label><br>
  <label><input type="radio" name="youtube-number" value="multiple" id="youtube-multiple-radio">Multiple Videos (channel or user)</label>
<p class="explanation"><?php echo __( 'Are you importing a single video, or multiple videos from a channel or user?' ); ?></p>
   </div>
</div>


<div class="field" id="youtube-select-div">
<div id="youtube-public-label" class="two columns alpha">
<label for="youtube-public">Select Items</label>
</div>
<div id="youtube-public-div" class="inputs five columns alpha">
<label><input type="radio" name="youtube-selecting" value="false" checked="checked"/> Import all items</label> <br>
<label><input id="youtube-select" type="radio" name="youtube-selecting" value="true" /> Select items to import</label>
<p class="explanation"><?php echo __( 'If you are importing photos from a photoset or gallery, this option allows you to select which photos to import from a list of thumbnails.' ); ?></p>
</div>
</div>

<div class="field">
 <div id="youtube-collection-label" class="two columns alpha">
<label for="youtube-collection"><?php echo __('Collection'); ?></label>
</div>
   <div class="inputs three columns omega">
   <?php echo $this->formSelect('youtube-collection',$this->collection,array('id' => 'youtube-collection'),$this->form_collection_options); ?>
<p class="explanation"><?php echo __( 'To which collection would you like to add the youtube video?' ); ?></p>
   </div>
</div>

<div class="field">
 <div id="youtube-userrole-label" class="two columns alpha">
<label for="youtube-userrole"><?php echo __('User Role'); ?></label>
</div>
   <div class="inputs three columns omega">
   <?php echo $this->formSelect('youtube-userrole',"37",array('id' => 'youtube-userrole'),$this->form_userrole_options); ?>
<p class="explanation"><?php echo __( 'What role should the Youtube user have in the imported Omeka item metadata?' ); ?></p>
   </div>

</div>

<div class="field">
<div id="youtube-public-label" class="two columns alpha">
<label for="youtube-public">Public Visibility</label>
</div>
<div id="youtube-public-div" class="inputs three columns alpha">
<input type="checkbox" name="youtube-public" value="1" checked="checked"/>
<p class="explanation"><?php echo __( 'Would you like to make the new items public?' ); ?></p>
</div>
</div>


<div class="field" id="previewThumbs"></div>

<div class="field">
<button name="youtube-import-submit" type="submit">Import photos</button>
</div>


</form>
<?php echo foot(); ?>
  