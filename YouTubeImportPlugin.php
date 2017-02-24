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
        'upgrade',
        'admin_head',
        'public_head',
        'after_save_item',
        'config',
        'config_form'
    );

    /**
     * @var array Filters for the plugin.
     */
    protected $_filters = array('admin_navigation_main',
                                'filterElement'=>array('Display',
                                                       'Item',
                                                       "Item Type Metadata",
                                                       "Player"),
                                'display_elements',
                                'exhibit_attachment_markup');

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

        //if there is not a current item record for this page, do nothing
        if(! $item = get_current_record('item', false))
            return $elementSets;

        // if there is no youtube player on this item, do nothing
        if(!metadata($item,array('Item Type Metadata','Player')))
            return $elementSets;
        
        //loop through all metadata elements and extract the "Player"
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

        //if the player element was found, put it in its own section
        //at the top and then include the rest of the metadata as normal
        if(isset($playerElement))
            return array_merge(array(''=>array(''=>$playerElement)),$newElementSets);
        return $elementSets;
    }

    /**
     *When the plugin installs, create a new metadata element
     *called Player associated with Moving Pictures
     *
     *@return void
     */
    public function hookInstall(){
        YoutubeImport_ImportHelper::CreateThumbnailElement;
    }

    public function hookUpgrade($oldVersion,$newVersion){
        if($oldVersion < 1.2) {
            YoutubeImport_ImportHelper::CreateThumbnailElement;
        }          
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

    /**
     *When the plugin loads on the public side, 
     *queue the css & js files
     *
     *@return void
     */
    public function hookPublicHead(){
        queue_js_file('YoutubeImport');
        queue_css_file('YoutubeImport');
    }


    /**
     * Display video widgets instead of thumbnails for youtube objects, 
     *queue the css & js files
     *
     *@return void
     */    
    public function filterExhibitAttachmentMarkup($html,$args){

        //do nothing if the iframe is already displayed 
        // (for example, by the vimeo import plugin)
        if(strpos($html,'iframe'))
            return $html;

        // Do not break if the plugin hasn't installed correctly
        if(!element_exists(ElementSet::ITEM_TYPE_NAME,'Player'))
            return $html;

        //Retrieve the item being attached
        $attachment = $args['attachment'];
        $item = $attachment->getItem();

        // do nothing unless the item has a player element defined
        if(!$player = metadata($item,array("Item Type Metadata","Player")))
            return $html;
        
        //get the file that would display with the attachment
        $file = $attachment->getFile();        
        //get the imported thumbnail filename saved with the item
        $thumb = metadata($item,array("Item Type Metadata","Imported Thumbnail"));
        
        //if the attachment image is not the imported thumbnail,
        // do nothing (let the thumbnail display as usual)
        if($file->getProperty('filename') != $thumb && $file->getProperty('original_filename') != $thumb)
            return $html;

        //otherwise, replace the thumbnail with the player
        $dom = new DOMDocument;
        $dom->loadHTML($html);        
        $playerElement = $this->createElementFromHtml($player,$dom);
        $imgs= $dom->getElementsByTagName('img');
        foreach($imgs as $i) $img = $i;
        $video = $img->parentNode->replaceChild($playerElement,$img);
        $html = $dom->saveHTML();
        
        //add a default caption if necessary
        if (!is_string($attachment['caption']) || $attachment['caption'] == '')
            $html.='<div class="exhibit-item-caption">'.exhibit_builder_link_to_exhibit_item("<p>See more information about this video</p>",array(),$item).'</div>';
        
        return $html;
    }

    public function createElementFromHtml($html,$dom) {
        $tmpDoc = new DOMDocument();
        libxml_use_internal_errors(true);
        $tmpDoc->loadHTML($html);
        libxml_clear_errors();
        foreach ($tmpDoc->getElementsByTagName('body')->item(0)->childNodes as $node) 
            return  $dom->importNode($node);
    }

    /**
     * Handle the result of the plugin config form
     */
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
