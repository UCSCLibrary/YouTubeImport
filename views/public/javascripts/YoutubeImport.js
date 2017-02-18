jQuery(window).load(function() {
  var youtubeImportedThumb = jQuery("div#moving-image-item-type-metadata-imported-thumbnail div.element-text p").html();
  youtubeImportedThumb = youtubeImportedThumb || jQuery("div#moving-image-item-type-metadata-imported-thumbnail div.element-text").html();
  jQuery('a img[title="'+youtubeImportedThumb+'"]').hide();
  var adminImageDiv =   jQuery('a img[title="'+youtubeImportedThumb+'"]').parents("div.admin-thumb.panel");
  if(youtubeImportedThumb && adminImageDiv.children().length == 1)
    adminImageDiv.hide();
});
