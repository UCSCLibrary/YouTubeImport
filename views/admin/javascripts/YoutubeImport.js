
function parseURL(url){

    var arr = url.split("sets");
    
    console.log(arr);

    if(arr.length==1)
    {
	var glpurl = "https://api.flickr.com/services/rest/?api_key=a664b4fdddb9e009f43e8a6012b1a392&format=json&jsoncallback=?&method=flickr.urls.lookupGallery&url="+encodeURIComponent(url);
console.log(glpurl);
	jQuery.getJSON( glpurl , {format: "json"})
	    .done(function( msg ) {
		getPhotoIDs([msg.gallery.id,"gallery"]);
	    });

    } else {
	var setID = arr[arr.length-1];
	setID = setID.replace(/\//g,"");
	getPhotoIDs([setID,"photoset"]);
    }
    console.log("boo");
}

function getPhotoIDs(args){
    var setID = args[0];
    var type = args[1];
    console.log("setID: "+setID);
    console.log("type: "+type);

    var apiBase = "https://api.flickr.com/services/rest/?api_key=a664b4fdddb9e009f43e8a6012b1a392&format=json&jsoncallback=?&method=flickr.";

    if(type == "gallery")
	url = apiBase+"galleries.getPhotos&gallery_id="+setID;
    else
	url = apiBase+"photosets.getPhotos&photoset_id="+setID;
	
    console.log(url);
    jQuery.getJSON( url , {format: "json"})
	.done(function( msg ) {
	    if(type=="gallery")
		addPhoto(msg.photos.photo,0);
	    else
		addPhoto(msg.photoset.photo,0);    
	});    
}

function addPhoto(photos,i){

    var urlBase = "https://api.flickr.com/services/rest/?api_key=a664b4fdddb9e009f43e8a6012b1a392&format=json&jsoncallback=?&method=flickr.photos.getSizes&photo_id=";

    var htmlBegin = '<div class="previewPicDiv"><input type="checkbox" name="flickr-selected['+photos[i].id+']" class="previewCheck" checked="checked"/><img class="previewPic" src="';
    var htmlMiddle = '" ><label class="previewLabel">';
    var htmlEnd = "</previewLabel></div>";

    //console.log(photos);
    console.log("pulling: "+photos[i].id);
    jQuery.getJSON( urlBase+photos[i].id , {format: "json"})
	.done(function( msg ) {
	    console.log(msg.sizes.size[2].source);
	    console.log(photos[i].title);
	    jQuery('#previewThumbs').append(htmlBegin+msg.sizes.size[2].source+htmlMiddle+photos[i].title+htmlEnd);
	    addPhoto(photos,++i);
	}); 

}


jQuery( document ).ready(function(){
    
    jQuery('#flickr-select').click(function(e){
	jQuery('#previewThumbs').append('<input type="hidden" name="flickr-selecting" value="true"/>');
	parseURL(jQuery("#flickr-url").val());
    });

});
