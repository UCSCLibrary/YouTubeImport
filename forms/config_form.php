<div class="field">
    <div id="youtube-width-label" class="two columns alpha">
        <label for="youtube_width"><?php echo __('Width'); ?></label>
    </div>
    <div class="inputs five columns omega">
   <?php echo get_view()->formText('youtube_width', get_option('youtube_width'), 
        array()); ?>
    <p class = "explanation">Enter the default width for display of videos imported from Youtube</p>
    </div>
</div>

<div class="field">
    <div id="youtube-height-label" class="two columns alpha">
        <label for="youtube_height"><?php echo __('Height'); ?></label>
    </div>
    <div class="inputs five columns omega">
   <?php echo get_view()->formText('youtube_height', get_option('youtube_height'), 
        array()); ?>
   <p class = "explanation">Enter the default height for display of videos imported from Youtube</p>
    </div>
</div>
