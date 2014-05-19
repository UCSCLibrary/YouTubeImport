<?php
/**
 * YoutubeImport
 *
 * @copyright Copyright 2014 UCSC Library Digital Initiatives
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * The YoutubeImport import job class.
 *
 * @package YoutubeImport
 */
class YoutubeImport_ImportJob extends Omeka_Job_AbstractJob
{
  public static $youtube_api_key = 'AIzaSyDI8ApsA7MBIK4M1Ubs9k4-Rk7_KOeYJ5w';
  public static $appName = "OmekaYouTubeImport";
  private $url;
  private $type;
  private $videoID;
  private $collection=0;    //create new colllection by default
  private $selecting=false;  //import all images in set by default
  private $selected=array();
  private $public = false;  //create private omeka items by default
  private $service;
  

  public function perform()
  {
    Zend_Registry::get('bootstrap')->bootstrap('Acl');
    
  	require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'Google' . DIRECTORY_SEPARATOR . 'Client.php';
    require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'Google' . DIRECTORY_SEPARATOR . 'Services' . DIRECTORY_SEPARATOR . 'YouTube.php';

    $client = new Google_Client();
    $client->setApplicationName(self::$appName);
    $client->setDeveloperKey(self::$youtube_api_key);
  	
    $this->service = new Google_Service_YouTube($client);
    
    $this->videoID = self::$ParseURL($this->url);

	/*

    if($this->collection == 0)
      $this->collection = $this->_makeDuplicateCollection($this->type);

    $photoIDs = $this->_getPhotoIDs();

    $items = array();

    echo("adding photos: <br>");
    print_r($photoIDs);
    echo("<br><br>");

    echo("selected");
    print_r($this->selected);
    //die();

    foreach ($photoIDs as $photoID)
      {
	if(!$this->selecting || isset($this->selected[$photoID]))
	  $items[] = $this->_addPhoto($photoID);
	echo("photo added:".$photoID);
      }

    //log the list of added items maybe? Or check whether we got them all?

    */
  }

  
  public function setUrl($url)
  {
    $this->url = $url;
  }

  public function setCollection($collection)
  {
    $this->collection = $collection;
  }

  public function setSelected($selected)
  {
    $this->selected = $selected;
  }

  public function setSelecting($selecting)
  {
    $this->selecting = $selecting;
  }

  public function setPublic($public)
  {
    $this->public = $public;
  }

  public static function ParseURL($url)
  {
    $parsed = parse_url($url);
    
    if(!empty($parsed['query']))
      {
	parse_str($parsed['query'],$parsed);
	$videoID = $parsed['v'];
      } elseif (isset($parsed['path'])) {
      $videoID = str_replace("/","",$parsed['path']);
      }
    
      /*
    $strings = explode("/",$url);
    $videoID = $strings[count($strings)-1];
    $strings = explode("=",$url);
    $videoID = $strings[count($strings)-1];
      */

	//TODO get youtube video ID from url
    return($videoID);
  }
  
  private function _getVideoIDs($type='unknown')
  {
	  /*
    $ids=array();

    $list = $this->f->photosets_getPhotos($this->videoID);

    if(empty($list) || ( $list['stat']=='fail' && $list['err']['code']==1 ) )
      {
	//photoset not found on flickr. Check if it's a gallery
	$response = $this->f->galleries_getPhotos($this->videoID);
	$list['photoset']=$response['photos'];
      }

    foreach($list['photoset']['photo'] as $photo)
      {
	$ids[]=$photo['id'];
      }

    return $ids;
    */
  }

  public static function GetVideo($itemID,$service,$collection=0,$public=0)
  {

    $part = "id,snippet,contentDetails,player,status,recordingDetails";
    //$part = "id,snippet,contentDetails,fileDetails,liveStreamingDetails,player,processingDetails,recordingDetails,topicDetails";
    $response = $service->videos->listVideos($part, array('id'=>$itemID,'maxResults'=>1));
    
    if (empty($response)) {
      die('twarrrr! no video found.');
    }

    $items = $response->items;

   if (empty($items)) {
      die('twarrrr! no video found for itemID '.$itemID);
    }

    $video = $items[0];

    //todo format date if necessary
    $datePublished = $video['snippet']['publishedAt'];
    //$dateRecorded = $video['contentDetails']['RecordingDate'];

    $geoLocations = array();
    //TODO loop through geolocations and add them

    if(isset($video['status']['license']))
      {
	switch($video['status']['license']) 
	  {
	  case "youtube":
	    $license = '<a href="https://www.youtube.com/static?template=terms">Standard YouTube License</a>';
	    break;

	  case "creativeCommon":
	      $license='<a href="http://creativecommons.org/licenses/by/3.0/legalcode">Creative Commons License</a>';
	    break;

	  default:
	    $license="";

	  }
      } else { $license = ""; }
    

    if ($video['contentDetails']['licensedContent'])
      {
	$license .= "<br>This video represents licensed content on YouTube, meaning that the content has been claimed by a YouTube content partner.";
	$rightsHolder = "Rights reserved by a third party";
      }else
      {
	$rightsHolder = "";
      }

    //todo: get element ID of player element we added on install

    //todo: pull any other relevant metadata from the video resource 
    //https://developers.google.com/youtube/v3/docs/videos
    //and map it to omeka elements

    $maps = array(
		  "Dublin Core"=>array(
				       "Title"=>array($video['snippet']['title']),
				       "Description"=>array($video['snippet']['description']),
				       "Date"=>array($datePublished),
				       "Source"=>array('http://YouTube.com'),
				       "Rights"=>array($license)
				       )
		  );

    if (plugin_is_active('DublinCoreExtended'))
      {
	$maps["Dublin Core"]["License"]=array($license);
	$maps["Dublin Core"]["Rights Holder"]=array($rightsHolder);
	$maps["Dublin Core"]["Date Submitted"]=array($datePublished);
	//$maps["Dublin Core"]["license"]=array($dateRecorded);
	//$maps["Dublin Core"]["license"]=array($geoLocations);
      }
		  /*
		  //extended dublin core
		  74=>  //license
		  101=>array($rightsHolder), //rights holder
		  65=>array($datePublished),
		  66=>array($dateRecorded),
		  91=>$geoLocations
		  */
		  //42=>array("File type: ".$video['fileDetails']['fileType']."  Container: ".$video['fileDetails']['container']),  // format

    if(!element_exists(ElementSet::ITEM_TYPE_NAME,'Player'))
      die('ERRRORZ!');

    $playerHtml = str_replace('/>','></iframe>',$video['player']['embedHtml']);

    /*$playerElement = $table->findByElementSetNameAndElementName(ElementSet::ITEM_TYPE_NAME,'Player');
    $maps[$playerElement->id]=array($playerHtml);
    */

    $maps[ElementSet::ITEM_TYPE_NAME]["Player"]=array($playerHtml);
      
    $Elements = array();

    $db = get_db();
    $elementTable = $db->getTable('Element');
    
    foreach ($maps as $elementSet=>$elements)
      {
	foreach($elements as $elementName => $elementTexts)
	  {
	    $element = $elementTable->findByElementSetNameAndElementName($elementSet,$elementName);
	    $elementID = $element->id;

	    $Elements[$elementID] = array();
	    if(is_array($elementTexts))
	      {
		foreach($elementTexts as $elementText)
		  {

		    //check for html tags
		    if($elementText != strip_tags($elementText)) {
		      //element text has html tags
		      $html = "1";
		    }else {
		      //plain text or other non-html object
		      $html = "0";
		    }

		    $Elements[$elementID][] = array(
						    'text' => $elementText,
						    'html' => $html
						    );
		  }
	      }
	  }
      }

    $tags = "";
    if(isset($video['snippet']->tags))
      {
	foreach($video['snippet']->tags as $tag)
	  {
	    $tags .= $tag;
	    $tags .=",";
	  }
    
	$tags = substr($tags,0,-2);
      }
    $returnPost = array(
			 'Elements'=>$Elements,
			 'item_type_id'=>'3',      //a moving image
			 'tags-to-add'=>$tags,
			 'tags-to-delete'=>'',
			 'collection_id'=>$collection
			 );
    if($public)
      $returnPost['public']="1";


    $i=0;
    $maxwidth=0;
    foreach($video['snippet']->thumbnails as $key => $file)
      {
	if($file['width']>$maxwidth)
	  $i = $key;
      }

    $returnFiles = array($video['snippet']->thumbnails->default->url);

    return(array(
		 'post' => $returnPost,
		 'files' => $returnFiles
		 ));

  }

  /*
  private function _makeDuplicateCollection($type='unknown')
  {
    // die("setID: ".$this->setID."<br>");

    if($type=="photoset")
      {
	$setInfo = $this->f->photosets_getInfo($this->videoID);
      }
    else if ($type=="gallery")
      {
	$response = $this->f->galleries_getInfo($this->videoID);

	if($response['stat']=="ok")
	  $setInfo=$response['gallery'];
	else
	  die("Error retrieving gallery info");
      }

    $maps = array(
		  50=>array($setInfo['title']),
		  41=>array($setInfo['description'])
		  );

    if($this->ownerRole > 0)
      $maps[$this->ownerRole] = array($photoInfo['username']);
      
    $Elements = array();
    foreach ($maps as $elementID => $elementTexts)
      {
	foreach($elementTexts as $elementText)
	  {
	    if($elementText != strip_tags($elementText)) {
	      //element text has html tags
	      $text = "";
	      $html = $elementText;
	    }else {
	      //plain text or other non-html object
	      $html = "";
	      $text = $elementText;
	    }

	    $Elements[$elementID] = array(array(
						'text' => $text,
						'html' => $html
						));
	  }
      }

    $postArray = array('Elements'=>$Elements) ;
    if($this->public)
      $postArray['public']="1";

    $record = new Collection();

    $record->setPostData($postArray);

    if ($record->save(false)) {
	// Succeed silently, since we're in the background
    } else {
      error_log($record->getErrors());
    }
    return($record->id);

  }
  */

  /*
  private function _addPhoto($itemID)
  {
    $post = self::GetPhotoPost($itemID,$this->f,$this->collection,$this->ownerRole,$this->public);
      
    $files = self::GetPhotoFiles($itemID);

    $record = new Item();

    $record->setPostData($post);

    if ($record->save(false)) {
	// Succeed silently, since we're in the background	
    } else {
      error_log($record->getErrors());
    }
    
    insert_files_for_item($record,'Url',$files);

    //TODO: create derivative images

  }

  */


}