<?php
/**
 * FlickrImport
 *
 * @copyright Copyright 2014 UCSC Library Digital Initiatives
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * The FlickrImport index controller class.
 *
 * @package FlickrImport
 */
class FlickrImport_ImportJob extends Omeka_Job_AbstractJob
{
  public function perform()
  {

    require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'phpFlickr' . DIRECTORY_SEPARATOR . 'phpFlickr.php';

    $f = new phpFlickr(self::$api_key);

    //$this->_addItem('8121451738',$f);
    //die();
    // if(!isset($_REQUEST['flickrsetID']))
    //die('VARS WRONG ERRORZ!');//TODO error handling
    //$setID = $_REQUEST['flickrsetID'];

    $setID = '72157627190265749';
    $photoIDs = $this->_getPhotoIDs($setID,$f);

    $selecting=false;
    if(isset($_REQUEST['selecting']))
      $selecting=true;
    if(isset($_REQUEST['selected']))
      $selected=$_REQUEST['selected'];

    $items = array();

    echo("adding photos: <br>");
    print_r($photoIDs);
    echo("<br><br>");

    foreach ($photoIDs as $photoID)
      {
	if(!$selecting || $selected[$photoID])
	  $items[] = $this->_addPhoto($photoID,$f);
	echo("photo added:".$photoID);
      }
  }


    private function _getPhotoIDs($setID,$f,$type='unknown')
    {
      $ids=array();
      
      $list = $f->photosets_getPhotos($setID,"o_dims,url_sq,url_t,url_s,url_m,url_o");
      foreach($list['photoset']['photo'] as $photo)
	{
	  $ids[]=$photo['id'];
	}

      return $ids;
    }

    private function _getPhotoFiles($itemID,$f)
    {

      $sizes = $f->photos_getSizes($itemID);
      $files = array();
      $i=0;
      foreach($sizes as $file)
	{
	  if($file['label']=='Original')
	    {
	      /*
	      $tmpname = tempnam(sys_get_temp_dir(),"Flk");
	      //echo('ok, we\'re gonna try to upload it...');
	      file_put_contents($tmpname,file_get_contents($file['source']));
	      //echo('done, one way or another<br>');
	      $size = getImageSize($tmpname);
	      $error = 0;
	  
	      $files['name'][] = basename($file['source']);
	      $files['type'][] = $size['mime'];
	      $files['size'][] = filesize($tmpname);
	      $files['tmp_name'][] = $tmpname;
	      $files['error'][] = $error;
	      */
	      $files[]=$file['source'];
	    }
	}

      return($files);
    }


    private function _getPhotoPost($itemID, $f)
    {
      $response = $f->photos_getInfo($itemID);
      if($response['stat']=="ok")
	$photoInfo = $response['photo'];
      else
	die("Error retrieving info from Flickr: ".$response['stat']);

      $licenses=array();
      $licenseArray = $f->photos_licenses_getInfo();
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
		    37=>array(),//contributor (flickr user)
		    47=>array($licenses[$photoInfo['license']]),//rights
		    46=>array(),
		    42=>array(),
		    7=>array($photoInfo['originalformat'])
		    
		    );
      
      $Elements = array();
      foreach ($maps as $elementID => $elementTexts)
	{
	  foreach($elementTexts as $elementText)
	    {
	      $Elements[$elementID] = array(
					    array(
						  'text' => $elementText,
						  'html' => "",
						  )
					    );
	    }
	}

      $tags = "";
      foreach($photoInfo['tags']['tag'] as $tag)
	{
	  $tags .= $tag['raw'];
	  $tags .=",";
	}

      $tags = substr($tags,0,-2);
      return( array(
		    'Elements'=>$Elements,
		    'item_type_id'=>'6',      //a still image
		    'tags-to-add'=>$tags,
		    'tags-to-delete'=>''
		    ) );

    }


    private function _addPhoto($itemID,$f)
    {
      $post = $this->_getPhotoPost($itemID,$f);
      
      $files = $this->_getPhotoFiles($itemID,$f);
      
      $class = $this->_helper->db->getDefaultModelName(); 
      $varName = $this->view->singularize($class);

      $record = new $class(); //new item
      //if ($this->getRequest()->isPost()) {

      $record->setPostData($post);

      if ($record->save(false)) {
	$successMessage = $this->_getAddSuccessMessage($record);
	if ($successMessage != '') {
	  $this->_helper->flashMessenger($successMessage, 'success');
	}
	//$this->_redirectAfterAdd($record);
      } else {
	$this->_helper->flashMessenger($record->getErrors());
      }

      insert_files_for_item($record,'Url',$files);

      $this->view->$varName = $record;
      //echo'done';
    }


}