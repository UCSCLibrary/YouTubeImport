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
class FlickrImport_IndexController extends Omeka_Controller_AbstractActionController
{    
 

  public function indexAction()
  {
    $this->view->form_collection_options = $this->_getFormCollectionOptions();
    $this->view->form_userrole_options = $this->_getFormUserRoleOptions();
    require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'phpFlickr' . DIRECTORY_SEPARATOR . 'phpFlickr.php';
      
  }

  public function importAction()
  {
    if(isset($_REQUEST['flickr-url']))
      $url = $_REQUEST['flickr-url'];
    else
      die("ERROR WITH PHOTOSET ID POST VAR");

    $type = $this->_getType($_REQUEST['flickr-url']);

    if(isset($_REQUEST['flickr-collection']))
      $collection = $_REQUEST['flickr-collection'];
    else
      $collection = 0;

    //this is not yet implemented in the view or javascript
    if(isset($_REQUEST['flickr-selecting'])&&$_REQUEST['flickr-selecting']=="true")
      {
	$selecting = true;
	$selected = $_REQUEST['flickr-selected'];
      } 
    else 
      {
	$selecting = false;
	$selected = array();
      }

    if(isset($_REQUEST['flickr-public']))
      $public = $_REQUEST['flickr-public'];
    else 
      $public = false;


    if(isset($_REQUEST['flickr-userrole']))
      $userRole = $_REQUEST['flickr-userrole'];
    else
      $userRole = 0;

    $options = array(
		     'url'=>$url,
		     'type'=>$type,
		     'collection'=>$collection,
		     'selecting'=>$selecting,
		     'selected'=>$selected,
		     'public'=>$public,
		     'userRole'=>$userRole
		     );

    require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'jobs' . DIRECTORY_SEPARATOR . 'import.php';

    //(new FlickrImport_ImportJob)->perform();

    $dispacher = Zend_Registry::get('job_dispatcher');

    $dispacher->sendLongRunning('FlickrImport_ImportJob',$options);
    //Zend_Registry::get('bootstrap')->getResource('jobs')->sendLongRunning('FlickrImport_ImportJob',);

 
  }

  /**
   * Get an array to be used in formSelect() containing all collections.
   * 
   * @return array
   */
  private function _getFormCollectionOptions()
  {
    $collections = get_records('Collection',array(),'0');
    $options = array('0'=>'Create New Collection');
    foreach ($collections as $collection)
      {
	if(isset($collection->getElementTexts('Dublin Core','Title')[0]))
	  {
	    $title = $collection->getElementTexts('Dublin Core','Title')[0];
	    $options[$collection->id]=$title;
	  }
      }
    return $options;
  }

  /**
   * Get an array to be used in formSelect() containing possible roles for users.
   * 
   * @return array
   */
  private function _getFormUserRoleOptions()
  {
    $options = array(
		     '0'=>'No Role',
		     '37'=>'Contributor',
		     '39'=>'Creator',
		     '45'=>'Publisher'
		     );
    return $options;
  }

  private function _getType($url)
  {
    $rv="";
    if (strpos($url, 'sets'))
      $rv="photoset";
    else if (strpos($url,'galleries'))
      $rv="gallery";

    return $rv;
  }

}
