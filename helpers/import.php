<?php
/**
 * YoutubeImport
 *
 * @copyright Copyright 2014 UCSC Library Digital Initiatives
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * The YoutubeImport import helper class.
 *
 * @package YoutubeImport
 */
class YoutubeImport_ImportHelper
{
    
    /**
     * @var string Youtube API key for this plugin
     */
    public static $youtube_api_key = 'AIzaSyDI8ApsA7MBIK4M1Ubs9k4-Rk7_KOeYJ5w';
    
    /**
     * @var string Google app name associated with this plugin
     */
    public static $appName = "OmekaYouTubeImport";

    /**
     *Parse the Youtube url parameter 
     *
     *@return $string $setID A unique identifier for the Youtube collection
     */
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

    public static function CreatePlayerElement(){
        static::_createElement('Player','html for embedded player to stream video content');
    }

    public static function CreateThumbnailElement(){
        static::_createElement('Imported Thumbnail','If a thumbnail images was imported for an embedded video, its id is recorded here and the thumbnail is hidden on pages displaying the embedded video itself.');
    }

    private static function _createElement($name,$description) {
        if(element_exists(ElementSet::ITEM_TYPE_NAME,$name))
            return;

        $db = get_db();
        $table = $db->getTable('ItemType');
        $mpType = $table->findByName('Moving Image');
        if(!is_object($mpType)) {
            $mpType = new ItemType();
            $mpType->name = "Moving Image";
            $mpType->description = "A series of visual representations imparting an impression of motion when shown in succession. Examples include animations, movies, television programs, videos, zoetropes, or visual output from a simulation.";
            $mpType->save();
        }
        $mpType->addElements(array(
            array(
                'name'=>$name,
                'description'=>$description)));
        $mpType->save();
    }
    
    /**
     *Fetch metadata from a Youtube video and prepare it
     *
     *@param string $itemID The Youtube video ID from which to extract metadata
     *@param object $service The youtube API php interface instance
     *@param int $collection The ID of the collection to which to add the new item
     *@param string $ownerRole The name of the dublin core field to which to 
     *add the Youtube user info
     *@param boolean public Indicates whether the new omeka item should be public
     *@return array An array containing metadata associated with the 
     *given youtube video in the correct format to save as an omeka item,
     *and urls of files associated
     */
    public static function GetVideo($itemID,$service,$collection=0,$ownerRole='Publisher',$public=0)
    {

        $part = "id,snippet,contentDetails,player,status,recordingDetails";
        
        $response = $service->videos->listVideos($part, array(
	    'id'=>$itemID,
	    'maxResults'=>1
	));
        
        if (empty($response)) 
            throw new Exception("No video found."); 

        $items = $response->items;

        if (empty($items)) 
            throw new Exception('No video found for itemID '.$itemID);

        $video = $items[0];

        //todo format date if necessary
        $datePublished = $video['snippet']['publishedAt'];

        try{
            $recordingDetails = $video['recordingDetails'];
        } catch (Exception $e) {
            die('exception');
            $recordingDetails = array();
        }

        //recordingDetails are only returned for authenticated requests, apparently!
        //or maybe users can hide them from the public.

        $dateRecorded = "";
        if(!empty($recordingDetails['RecordingDate']))
            $dateRecorded = $recordingDetails['RecordingDate'];

        $spatialCoverage = "";
        if(!empty($recordingDetails['locationDescription']))
            $spatialCoverage .= $recordingDetails['locationDescription']."<br>";
        if(!empty($recordingDetails['locationDescription']))
            $spatialCoverage .= $recordingDetails['locationDescription']."<br>";
        
        if(!empty($recordingDetails['location']))
            foreach($recordingDetails['location'] as $label=>$number)
	        $spatialCoverage .= "$label = $number<br>";

        $publisher = "";
        if(!empty($video['snippet']['channelTitle']))
            $publisher .= $video['snippet']['channelTitle']."<br>published via YouTube.com"; 
        

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
		"Source"=>array('http://youtu.be/'.$video['id']),
		"Rights"=>array($license)
	    )
	);

        if(!empty($ownerRole))
            $maps['Dublin Core'][$ownerRole]=array($publisher);

        if (plugin_is_active('DublinCoreExtended'))
        {
	    $maps["Dublin Core"]["License"]=array($license);
	    $maps["Dublin Core"]["Rights Holder"]=array($rightsHolder);
	    $maps["Dublin Core"]["Date Submitted"]=array($datePublished);
	    //$maps["Dublin Core"]["Date Created"]=array($dateRecorded);
	    //$maps["Dublin Core"]["Spatial Coverage"]=array($spatialCoverage);
        }

        if(!element_exists(ElementSet::ITEM_TYPE_NAME,'Player'))
            static::CreatePlayerElement();
        if(!element_exists(ElementSet::ITEM_TYPE_NAME,'Imported Thumbnail'))
            static::CreateThumbnailElement();

        $playerHtml = str_replace('/>','></iframe>',$video['player']['embedHtml']);

        $maps[ElementSet::ITEM_TYPE_NAME]["Player"]=array($playerHtml);
        $maps[ElementSet::ITEM_TYPE_NAME]["Imported Thumbnail"]=array($video['snippet']->thumbnails->default->url);
        
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

        $returnFiles = array($video['snippet']->thumbnails->default->url);

        return(array(
	    'post' => $returnPost,
	    'files' => $returnFiles
	));

    }


}
