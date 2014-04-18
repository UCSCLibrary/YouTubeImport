<?php echo head(array('title' => 'Flickr Import')); ?>

<?php echo flash(); ?>
<form action="<?php echo absolute_url('flickr-import/index/import');?>">
<?php echo $this->testvar;?>

<div class="field">
 <div id="flickr-url-label" class="two columns alpha">
<label for="flickr-url"><?php echo __('Photoset/Gallery URL'); ?></label>
</div>
   <div class="inputs five columns omega">
   <?php echo $this->formText('flickr-url',$this->testvar,array()) ?>
<p class="explanation"><?php echo __( 'Full url of the photoset or gallery on Flickr (example: https://www.flickr.com/photos/sdasmarchives/sets/72157643807500044/)' ); ?></p>
   </div>
</div>

<div class="field">
 <div id="flickr-collection-label" class="two columns alpha">
<label for="flickr-collection"><?php echo __('Collection'); ?></label>
</div>
   <div class="inputs three columns omega">
   <?php echo $this->formSelect('flickr-collection',null,array('id' => 'flickr-collection'),$this->form_collection_options); ?>
<p class="explanation"><?php echo __( 'To which collection would you like to add the flickr photos?' ); ?></p>
   </div>
</div>

<div class="field">
 <div id="flickr-userrole-label" class="two columns alpha">
<label for="flickr-userrole"><?php echo __('User Role'); ?></label>
</div>
   <div class="inputs three columns omega">
   <?php echo $this->formSelect('flickr-userrole',null,array('id' => 'flickr-userrole'),$this->form_userrole_options); ?>
<p class="explanation"><?php echo __( 'What role should the Flickr user have in the imported Omeka item metadata?' ); ?></p>
   </div>

</div>

<div class="field">
<div id="flickr-public-label" class="two columns alpha">
<label for="flickr-public">Privacy</label>
</div>
<div id="flickr-public-div" class="inputs three columns alpha">
<input type="checkbox" name="flickr-public" value="1" checked="checked"/>
<p class="explanation"><?php echo __( 'Would you like to make the new items public?' ); ?></p>
</div>
</div>

<div class="field">
<div id="flickr-public-label" class="two columns alpha">
<label for="flickr-public">Select Items</label>
</div>
<div id="flickr-public-div" class="inputs five columns alpha">
<input type="radio" name="flickr-selecting" value="false" checked="checked"/> Import all items <br>
<input id="flickr-select" type="radio" name="flickr-selecting" value="true" /> Manually select items to import
</div>
</div>

<div class="field" id="previewThumbs"></div>

<div class="field">
<button type="submit">Upload photos</button>
</div>


</form>
<?php echo foot(); ?>
  