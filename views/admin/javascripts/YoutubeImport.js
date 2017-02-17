jQuery(window).load(function() {

  jQuery( "#youtube-success-dialog" ).dialog({
    height: 0,
    width: 250,
    resizeable: false,
    dialogClass: "youtube-success-dialog"
  });

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
