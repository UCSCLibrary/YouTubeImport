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

<div class="field">
    <div id="youtube-apikey-label" class="two columns alpha">
        <label for="youtube_height"><?php echo __('YouTube API Key'); ?></label>
    </div>
    <div class="inputs five columns omega">
   <?php echo get_view()->formText('youtube_apikey', get_option('youtube_apikey'), 
        array()); ?>
   <p class = "explanation">Enter a YouTube Data API V3 key, as YouTube rate limits the default key. See <a href="https://developers.google.com/youtube/v3">https://developers.google.com/youtube/v3</a>.</p>
    </div>
</div>