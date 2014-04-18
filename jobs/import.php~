<?php
/**
 * FlickrImport
 *
 * @copyright Copyright 2014 UCSC Library Digital Initiatives
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * The FlickrImport import job class.
 *
 * @package FlickrImport
 */
//class FlickrImport_ImportJob
class FlickrImport_ImportJob extends Omeka_Job_AbstractJob
{
  private static $flickr_api_key = 'a664b4fdddb9e009f43e8a6012b1a392';
  private $url;
  private $type;
  private $setID;
  private $collection=0;    //create new colllection by default
  private $selecting=false;  //import all images in set by default
  private $selected=array();
  private $public = false;  //create private omeka items by default
  private $ownerRole = 37; //flickr owner is contributor by default
  private $f;
  

  public function perform()
  {
    Zend_Registry::get('bootstrap')->bootstrap('Acl');

    require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'phpFlickr' . DIRECTORY_SEPARATOR . 'phpFlickr.php';

    $this->f = new phpFlickr(self::$flickr_api_key);

    $this->setID = $this->_parseURL();

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

  public function setUserRole($role)
  {
    $this->ownerRole = $role;
  }

  public function setType($type)
  {
    $this->type = $type;
  }

  private function _parseURL()
  {
    if ($this->type=='photoset')
      {
	$arr = explode('/',$this->url);
	$setID = $arr[count($arr)-2];
      }
    else if ($this->type=='gallery')
      {
	$response = $this->f->urls_lookupGallery($this->url);
	//print_r($response);
	//die();
	if($response['stat']=="ok")
	  $setID = $response['gallery']['id'];
	else
	  $setID =-1;
      }
    return($setID);
  }

  private function _getPhotoIDs($type='unknown')
  {
    $ids=array();

    $list = $this->f->photosets_getPhotos($this->setID);

    if(empty($list) || ( $list['stat']=='fail' && $list['err']['code']==1 ) )
      {
	//photoset not found on flickr. Check if it's a gallery
	$response = $this->f->galleries_getPhotos($this->setID);
	$list['photoset']=$response['photos'];
      }

    foreach($list['photoset']['photo'] as $photo)
      {
	$ids[]=$photo['id'];
      }

    return $ids;
  }

  private function _getPhotoFiles($itemID)
  {

    $sizes = $this->f->photos_getSizes($itemID);
    $files = array();
    $i=0;
    foreach($sizes as $file)
      {
	if($file['label']=='Original')
	  {
	    $files[]=$file['source'];
	  }
	//TODO maybe do something with Flickr derivative images?
      }

    return($files);
  }


  private function _getPhotoPost($itemID)
  {
    $response = $this->f->photos_getInfo($itemID);
    if($response['stat']=="ok")
      $photoInfo = $response['photo'];
    else
      die("Error retrieving info from Flickr: ".$response['stat']);

    $licenses=array();
    $licenseArray = $this->f->photos_licenses_getInfo();
    foreach($licenseArray as $license)
      {
	$licenses[$license['id']]=$license['name'];
      }

    $datetimetaken = $photoInfo['dates']['taken'];
    $granularity = $photoInfo['dates']['takengranularity'];

    switch($granularity)
      {
      case 0:
	$date = date('Y-m-d H:i:s',strtotime($datetimetaken));
	break;
      case 4:
	$date = date('Y-m',strtotime($datetimetaken));
	break;
      case 6:
	$date = date('Y',strtotime($datetimetaken));
	break;
      case 8:
	$date = "circa ".date('Y',strtotime($datetimetaken));
	break;
	  
      }

    $maps = array(
		  50=>array($photoInfo['title']),
		  41=>array($photoInfo['description']),
		  40=>array($date),
		  47=>array($licenses[$photoInfo['license']]),//rights
		  46=>array(),
		  42=>array(),
		  7=>array($photoInfo['originalformat'])
		  );

    if($this->ownerRole > 0)
      {
        if($photoInfo['owner']['realname']!="")
	  $maps[$this->ownerRole] = $photoInfo['owner']['realname'];
	else
	  $maps[$this->ownerRole] = $photoInfo['owner']['username'];

      }
      
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

    $tags = "";
    foreach($photoInfo['tags']['tag'] as $tag)
      {
	$tags .= $tag['raw'];
	$tags .=",";
      }

    $tags = substr($tags,0,-2);

    $returnArray = array(
			 'Elements'=>$Elements,
			 'item_type_id'=>'6',      //a still image
			 'tags-to-add'=>$tags,
			 'tags-to-delete'=>'',
			 'collection_id'=>$this->collection
			 );
    if($this->public)
      $returnArray['public']="1";

    return($returnArray );

  }


  private function _makeDuplicateCollection($type='unknown')
  {
    // die("setID: ".$this->setID."<br>");

    if($type=="photoset")
      {
	$setInfo = $this->f->photosets_getInfo($this->setID);
      }
    else if ($type=="gallery")
      {
	$response = $this->f->galleries_getInfo($this->setID);

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

  private function _addPhoto($itemID)
  {
    $post = $this->_getPhotoPost($itemID);
      
    $files = $this->_getPhotoFiles($itemID);

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


}