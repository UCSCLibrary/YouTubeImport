jQuery(window).load(function() {

  jQuery("body.you-tube-import form").tooltip();

  jQuery( "#youtube-success-dialog" ).dialog({
    height: 0,
    width: 250,
    resizeable: false,
    dialogClass: "youtube-success-dialog"
  });
  
  var youtubeImportedThumb = jQuery("div#moving-image-item-type-metadata-imported-thumbnail div.element-text p").html();
  jQuery('a img[title="'+youtubeImportedThumb+'"]').hide();
  var adminImageDiv =   jQuery('a img[title="'+youtubeImportedThumb+'"]').parents("div.admin-thumb.panel");
  if(youtubeImportedThumb && adminImageDiv.children().length == 1)
    adminImageDiv.hide();

  //reset the url input (not sure why the form reset function isn't working)
  jQuery('body.you-tube-import div#content form input#youtubeurl').val("");

  textareaId = 'Elements-'+playerElementId+'-0-text';
  jQuery('#Elements-'+playerElementId+'-0-text_parent').hide();
  jQuery('#Elements-'+playerElementId+'-0-text').show();
  jQuery('#save-changes').click(function(){
    jQuery('#Elements-'+playerElementId+'-0-html').prop('disabled', false);
    jQuery('#Elements-'+playerElementId+'-0-html').prop('checked',false);
    tinyMCE.execCommand("mceRemoveControl", false, textareaId);
    jQuery('#Elements-'+playerElementId+'-0-html').unbind();
  });
  jQuery('a[href="#item-type-metadata-metadata"]').click(function(){
    tinyMCE.execCommand("mceRemoveControl", false, textareaId);
    jQuery('#Elements-'+playerElementId+'-0-html').unbind();
    jQuery('#Elements-'+playerElementId+'-0-html').prop('disabled', true);
    jQuery('#Elements-'+playerElementId+'-0-html').prop('checked',false);
  });
  
});
