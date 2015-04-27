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
class YouTubeImportPlugin extends Omeka_Plugin_AbstractPlugin
{
  /**
   * @var array Hooks for the plugin.
   */
    protected $_hooks = array(
        'define_acl',
        'install',
        'admin_head',
        'after_save_item',
        'config',
        'config_form'
    );

  /**
   * @var array Filters for the plugin.
   */
    protected $_filters = array('admin_navigation_main','filterElement'=>array('Display','Item',"Item Type Metadata","Player"),'display_elements');

  /**
   * @var array Options for the plugin.
   */
    protected $_options = array('youtube_width'=>640,'youtube_height'=>360);

    public function hookAfterSaveItem($args){
        if(element_exists(ElementSet::ITEM_TYPE_NAME,'Player')) {          
          $item = $args['record'];                                
          $element = $this->_db->getTable("Element")->findByElementSetNameAndElementName('Item Type Metadata',"Player");
          if($players = $this->_db->getTable("ElementText")->findBy(array('record_id'=>$item->id,'element_id'=>$element->id))) {
              if(!is_array($players))
                  $players = array($players);
              foreach ($players as $player) {
                  $player->html = 1;
                  $player->save();
              }
          }
      }
  }

    public function filterDisplayElements($elementSets){

        if(! $item = get_current_record('item', false))
            return $elementSets;

        if(!metadata($item,array('Item Type Metadata','Player')))
            return $elementSets;
        
        $newElementSets = array();
        foreach ($elementSets as $set => $elements) {
            $newElements = $elements;
            if($set==="Moving Image Item Type Metadata") {
                $newElements = array();
                foreach ($elements as $key => $element) {
                    if($key==="Player") 
                        $playerElement = $element;
                     else 
                        $newElements[$key] = $element;
                }
            }           
            $newElementSets[$set] = $newElements;
        }

        $newElementSets = array_merge(array('Player'=>array(''=>$playerElement)),$newElementSets);
        
        return isset($playerElement) ? $newElementSets : $elementSets;
    }

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
      if(!is_object($mpType)) {
          $mpType = new ItemType();
          $mpType->name = "Moving Image";
          $mpType->description = "A series of visual representations imparting an impression of motion when shown in succession. Examples include animations, movies, television programs, videos, zoetropes, or visual output from a simulation.";
      }
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
      if(element_exists(ElementSet::ITEM_TYPE_NAME,'Player')){
          $playerElement = $this->_db->getTable("Element")->findByElementSetNameAndElementName("Item Type Metadata","Player");
          queue_js_string("var playerElementId = ".$playerElement->id.';');
          queue_js_file('YoutubeImport');
      }
      queue_css_file('YoutubeImport');
  }
  
    public function hookConfig() {
        if(isset($_REQUEST['youtube_width']))
            set_option('youtube_width',$_REQUEST['youtube_width']);
        if(isset($_REQUEST['youtube_height']))
            set_option('youtube_height',$_REQUEST['youtube_height']);
    }

    public function hookConfigForm(){
        include_once(dirname(__FILE__).'/forms/config_form.php');
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
    $args['acl']->allow('contributor','YoutubeImport_Index');
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
		   'uri' => url('you-tube-import'),
		   'resource' => 'YoutubeImport_Index',
		   'privilege' => 'index'
		   );
    return $nav;
  }

  public function filterElement($text,$args) {
      if(strpos($text,'iframe') > 0) {
          $wpo = strpos($text,'width=')+7;
          $text = substr_replace($text,get_option('youtube_width'),$wpo,3);
          $hpo = strpos($text,'height=')+8;
          $text = substr_replace($text,get_option('youtube_height'),$hpo,3);
      }
      return $text;
  }
    
}
