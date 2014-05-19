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
    return($videoID);
  }
  
  public static function GetVideo($itemID,$service,$collection=0,$public=0)
  {

    $part = "id,snippet,contentDetails,player,status,recordingDetails";
    
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

    $dateRecorded = "";
    if(!empty($video['recordingDetails']['RecordingDate']))
      $dateRecorded = $video['recordingDetails']['RecordingDate'];

    $spatialCoverage = "";
    if(!empty($video['recordingDetails']['locationDescription']))
       $spatialCoverage .= $video['recordingDetails']['locationDescription']."<br>";
    if(!empty($video['recordingDetails']['locationDescription']))
       $spatialCoverage .= $video['recordingDetails']['locationDescription']."<br>";
    
    if(!empty($video['recordingDetails']['location']))
      foreach($video['recordingDetails']['location'] as $label=>$number)
	$spatialCoverage .= "$label = $number<br>";


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
	$maps["Dublin Core"]["Date Created"]=array($dateRecorded);
	$maps["Dublin Core"]["Spatial Coverage"]=array($spatialCoverage);
      }

    if(!element_exists(ElementSet::ITEM_TYPE_NAME,'Player'))
      die('ERRRORZ!');

    $playerHtml = str_replace('/>','></iframe>',$video['player']['embedHtml']);

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


}