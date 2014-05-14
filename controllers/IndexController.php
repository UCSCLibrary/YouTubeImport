<?php
/**
 * YoutubeImport
 *
 * @copyright Copyright 2014 UCSC Library Digital Initiatives
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * The YoutubeImport index controller class.
 *
 * @package YoutubeImport
 */
class YoutubeImport_IndexController extends Omeka_Controller_AbstractActionController
{    

  public function indexAction()
  {
    if(isset($_REQUEST['youtube-import-submit']) )
      {
	if(isset($_REQUEST['youtube-number']) && $_REQUEST['youtube-number']=='single')
	  $this->_importSingle();

	if(isset($_REQUEST['youtube-number']) && $_REQUEST['youtube-number']=='multiple')
	  $this->_importMultiple();

      }

    $this->view->form_collection_options = $this->_getFormCollectionOptions();
      
  }

  
  private function _importMultiple()
  {
     require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'jobs' . DIRECTORY_SEPARATOR . 'import.php';

    if(isset($_REQUEST['youtube-url']))
      $url = $_REQUEST['youtube-url'];
    else
      die("ERROR WITH PHOTOSET ID POST VAR");

    if(isset($_REQUEST['youtube-collection']))
      $collection = $_REQUEST['youtube-collection'];
    else
      $collection = 0;

    //this is not yet implemented in the view or javascript
    if(isset($_REQUEST['youtube-selecting'])&&$_REQUEST['youtube-selecting']=="true")
      {
	$selecting = true;
	$selected = $_REQUEST['youtube-selected'];
      } 
    else 
      {
	$selecting = false;
	$selected = array();
      }

    if(isset($_REQUEST['youtube-public']))
      $public = $_REQUEST['youtube-public'];
    else 
      $public = false;

    $options = array(
		     'url'=>$url,
		     'collection'=>$collection,
		     'selecting'=>$selecting,
		     'selected'=>$selected,
		     'public'=>$public
		     );

    $dispacher = Zend_Registry::get('job_dispatcher');

    $dispacher->sendLongRunning('YoutubeImport_ImportJob',$options);
    //Zend_Registry::get('bootstrap')->getResource('jobs')->sendLongRunning('YoutubeImport_ImportJob',);

    $flashMessenger = $this->_helper->FlashMessenger;
    $flashMessenger->addMessage('Your Youtube videos are now being imported. This process may take a few minutes. You may continue to work while the photos are imported in the background. You may notice some strange behavior while the photos are uploading, but it will all be over soon.',"success");
  }

  private function _importSingle()
  {

    require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'jobs' . DIRECTORY_SEPARATOR . 'import.php';
    require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'Google' . DIRECTORY_SEPARATOR . 'Client.php';
    require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'Google' . DIRECTORY_SEPARATOR . 'Service' . DIRECTORY_SEPARATOR . 'YouTube.php';

    $client = new Google_Client();
    $client->setApplicationName("Omeka _Youtube_Import");
    $client->setDeveloperKey(YoutubeImport_ImportJob::$youtube_api_key);
  	
    $service = new Google_Service_YouTube($client);

    if(isset($_REQUEST['youtube-url']))
      $url = $_REQUEST['youtube-url'];
    else
      die("ERROR WITH youtube ID POST VAR");


    if(isset($_REQUEST['youtube-collection']))
      $collection = $_REQUEST['youtube-collection'];
    else
      $collection = 0;

    if(isset($_REQUEST['youtube-public']))
      $public = $_REQUEST['youtube-public'];
    else 
      $public = false;

    $videoID = YoutubeImport_ImportJob::ParseURL($url);
    $response =  YoutubeImport_ImportJob::GetVideo($videoID,$service,$collection,$public);
    $post = $response['post'];
    $files = $response['files'];

    $record = new Item();

    $record->setPostData($post);
    
    ob_start();

    if ($record->save(false)) {
      // Succeed silently, since we're in the background	
    } else {
      error_log($record->getErrors());
    }

    if(!empty($files)&&!empty($record))
      {
	insert_files_for_item($record,'Url',$files);
      }
   
    ob_end_clean();
    
    $flashMessenger = $this->_helper->FlashMessenger;
    $flashMessenger->addMessage('Your youtube video was imported into Omeka successfully','success');

  }

  /**
   * Get an array to be used in formSelect() containing all collections.
   * 
   * @return array
   */
  private function _getFormCollectionOptions()
  {
    $collections = get_records('Collection',array(),'0');
    $options = array('0'=>'No Collection');
    foreach ($collections as $collection)
      {
	$titles = $collection->getElementTexts('Dublin Core','Title');
	if(isset($titles[0]))
	  {
	    $title = $titles[0];
	    $options[$collection->id]=$title;
	  }
      }
    return $options;
  }



}
