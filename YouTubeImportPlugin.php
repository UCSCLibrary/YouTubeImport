<?php
/**
 * YouTube Import plugin
 *
 * @copyright Copyright 2014 UCSC Library Digital Initiatives
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */


/**
 * YouTube Import plugin class
 */
class YoutubeImportPlugin extends Omeka_Plugin_AbstractPlugin
{
  /**
   * @var array Hooks for the plugin.
   */
  protected $_hooks = array('define_acl','install','admin_head');

  /**
   * @var array Filters for the plugin.
   */
  protected $_filters = array('admin_navigation_main');

  /**
   *When the plugin installs, create a new metadata element
   *called Player associated with Moving Pictures
   *
   *@return void
   */
  public function hookInstall(){

    if(element_exists(ElementSet::ITEM_TYPE_NAME,'Player'))
      return;

    $db = get_db();
    $table = $db->getTable('ItemType');
    $mpType = $table->findByName('Moving Image');
    $mpType->addElements(array(
			       array(
				     'name'=>'Player',
				     'description'=>'html for embedded player to stream video content'
				     )
			       ));
    $mpType->save();
  
  }

  /**
   *When the plugin loads on the admin side, 
   *queue the css file
   *
   *@return void
   */
  public function hookAdminHead(){
    queue_css_file('YoutubeImport');
  }

  /**
   * Define the plugin's access control list.
   *
   *@param array $args Arguments passed from Zend
   *@return void
   */
  public function hookDefineAcl($args)
  {
    $args['acl']->addResource('YoutubeImport_Index');
  }

   
  /**
   * Add the Youtube Import link to the admin main navigation.
   * 
   * @param array $nav Navigation array.
   * @return array $nav Filtered navigation array.
   */
  public function filterAdminNavigationMain($nav)
  {
    $nav[] = array(
		   'label' => __('YouTube Import'),
		   'uri' => url('youtube-import'),
		   'resource' => 'YoutubeImport_Index',
		   'privilege' => 'index'
		   );
    return $nav;
  }
    
}
