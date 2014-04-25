<?php
/**
 * YouTube Import plugin
 *
 * @copyright Copyright 2014 UCSC Library Digital Initiatives
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

//require_once dirname(__FILE__) . '/helpers/SedMetaFunctions.php';

/**
 * YouTube Import plugin.
 */
class YoutubeImportPlugin extends Omeka_Plugin_AbstractPlugin
{
    /**
     * @var array Hooks for the plugin.
     */
    //protected $_hooks = array('admin_head');
  protected $_hooks = array('initialize','define_acl','admin_head','install');

  /**
   * @var array Filters for the plugin.
   */
  protected $_filters = array('admin_navigation_main');

  /**
   * @var array Options and their default values.
   */
  protected $_options = array('');

  public function hookInitialize()
  {
    require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'jobs' . DIRECTORY_SEPARATOR . 'import.php';
  }


  public function hookAdminHead()
  {
    queue_js_file('YoutubeImport');
    queue_css_file('YoutubeImport');
  }
  /*
  private function _addItemType()
  {
    $newType = insert_item_type(
				array(
				      'name'=>'Embedded Video',
				      'description'=>''
				      ),
				array(
				      array(
					    'name'=>'embed_html',
					    'description'=>'html for embedded player'
					    )
				      )
		     );
    $newTypeId = $newType->id;
  }
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
   * Define the plugin's access control list.
   */
  public function hookDefineAcl($args)
  {
    $args['acl']->addResource('YoutubeImport_Index');
  }

   
  /**
   * Add the Youtube Import link to the admin main navigation.
   * 
   * @param array Navigation array.
   * @return array Filtered navigation array.
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

    private function _getUrl($urlstring)
    {
      $self = $_SERVER['PHP_SELF'];
      return($self.$urlstring);
    }
    
}
