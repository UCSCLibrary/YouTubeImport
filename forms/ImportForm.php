<?php
/**
 * YoutubeImport Form for defining import parameters
 *
 * @package     YouTubeImport
 * @copyright   2014 UCSC Library Digital Initiatives
 * @license     
 */

/**
 * YoutubeImport form class
 */
class Youtube_Form_Import extends Omeka_Form
{

    /**
     * Construct the import form.
     *
     *@return void
     */
    public function init()
    {
        parent::init();
        $this->_registerElements();
    }

    /**
     * Define the form elements.
     *
     *@return void
     */
    private function _registerElements()
    {
        //URL:
        $this->addElement('text', 'youtubeurl', array(
	    'label'         => __('YouTube URL'),
	    'description'   => __('Paste the full URL of the YouTube video you would like to import'),
	    'validators'    =>array(
		array('callback',false,array('callback'=>array($this,'validateYoutubeUrl'),'options'=>array()))
	    ),
	    'order'         => 1,
	    'required'      => true,
            'title'         => 'Copy the URL from your browser’s URL bar or the YouTube “share” link'
	)
	);

	// Collection:
        $this->addElement('select', 'youtubecollection', array(
	    'label'         => __('Collection'),
	    'description'   => __('Select a collection'),
	    'value'         => '0',
	    'order'         => 2,
	    'multiOptions'       => $this->_getCollectionOptions()
	)
	);

	// Responsibility (User Role):
        $this->addElement('select', 'youtubeuserrole', array(
	    'label'         => __('Responsibility'),
	    'description'   => __('The YouTube user / channel is the ______ of the video'),
	    'value'         => 'Publisher',
	    'order'         => 3,
	    
	    'multiOptions'       => $this->_getRoleOptions(),
            'title' => 'This will determine the Dublin Core field in which the YouTube user/channel name will appear.'
	)
	);


        
        // Visibility (public vs private):
        $this->addElement('checkbox', 'youtubepublic', array(
            'label'         => __('Public Visibility'),
            'description'   => __('Would you like to make the video public in Omeka?'),
	    'order'         => 4
	)
	);

        if(version_compare(OMEKA_VERSION,'2.2.1') >= 0)
            $this->addElement('hash','youtube_token');

        // Submit:
        $this->addElement('submit', 'youtube-import-submit', array(
            'label' => __('Import Video')
        ));

	//Display Groups:
        $this->addDisplayGroup(
	    array(
		'youtubeurl',
		'youtubecollection',
		'youtubeuserrole',
		'youtubepublic'
	    ),
	    'fields'
	);

        $this->addDisplayGroup(
	    array(
		'youtube-import-submit'
	    ), 
	    'submit_buttons'
	);

    }

    /**
     *Process the form data and import the photos as necessary
     *
     *@return bool $success true if successful 
     */
    public static function ProcessPost()
    {
        //if we have a short url, expand it
        $_REQUEST['youtubeurl'] = self::_resolveShortUrl($_REQUEST['youtubeurl']);
        
        try {
	    if(self::_importSingle())
	        return('Go to Items or Collections to view imported videos.');

        } catch(Exception $e) {
	    throw new Exception('Error importing video. '.$e->getMessage());
        }
        return(true);
    }

    /**
     * Import a single video in real time (not in the background).
     *
     * This function relies on the import form output being in the
     * $_POST variable. The form should be validated before calling this.
     *
     * @return bool $success true if no error, false otherwise
     */
    private static function _importSingle()
    {

        require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'import.php';
        require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'Google' . DIRECTORY_SEPARATOR . 'Client.php';
        require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'Google' . DIRECTORY_SEPARATOR . 'Service' . DIRECTORY_SEPARATOR . 'YouTube.php';

        $client = new Google_Client();
        $client->setApplicationName("Omeka_Youtube_Import");
        $client->setDeveloperKey(YoutubeImport_ImportHelper::$youtube_api_key);
  	
        try{
	    $service = new Google_Service_YouTube($client);
        }catch(Exception $e) {
	    throw $e;
        }

        if(isset($_REQUEST['youtubeurl']))
	    $url = $_REQUEST['youtubeurl'];
        else
            throw new UnexpectedValueException('URL of YouTube video was not set');


        if(isset($_REQUEST['youtubecollection']))
	    $collection = $_REQUEST['youtubecollection'];
        else
	    $collection = 0;

        if(isset($_REQUEST['youtubeuserrole']))
	    $ownerRole = $_REQUEST['youtubeuserrole'];
        else
	    $ownerRole = 0;

        if(isset($_REQUEST['youtubepublic']))
	    $public = $_REQUEST['youtubepublic'];
        else 
	    $public = false;
        try{
	    $videoID = YoutubeImport_ImportHelper::ParseURL($url);
	    $response =  YoutubeImport_ImportHelper::GetVideo($videoID,$service,$collection,$ownerRole,$public);
            
	    $post = $response['post'];
	    $files = $response['files'];

        } catch (Exception $e) {
	    throw $e;
        }

        $record = new Item();

        $record->setPostData($post);
        
        if (!$record->save(false)) {
            throw new Exception($record->getErrors());
        }

        if(!empty($files)&&!empty($record))
        {
	    if(!insert_files_for_item($record,'Url',$files))
	        throw new Exception("Error attaching files");
        }
        
        return(true);

    }

    /**
     * Get an array to be used in formSelect() containing possible user roles.
     * 
     * @return array $options An associative array mapping dublin core elements
     * which could be associated with the Youtube usernames, to their display 
     * values in a dropdown menu.
     */
    private function _getRoleOptions()
    {
        $options = array(
	    '0'=>'No Role',
	    'Contributor'=>'Contributor',
	    'Creator'=>'Creator',
	    'Publisher'=>'Publisher'
	);
        return $options;
    }

    /**
     * Overrides standard omeka form behavior to fix radio display bug
     * 
     * @return void
     */
    public function applyOmekaStyles()
    {
        foreach ($this->getElements() as $element) {
            if ($element instanceof Zend_Form_Element_Submit) {
                // All submit form elements should be wrapped in a div with 
                // no class.
                $element->setDecorators(array(
                    'ViewHelper', 
                    array('HtmlTag', array('tag' => 'div'))
		));
            } else if($element instanceof Zend_Form_Element_Radio) {
                // Radio buttons must have a 'radio' class on the div wrapper.
                $element->getDecorator('InputsTag')->setOption('class', 'inputs radio five columns alpha');
		$element->getDecorator('FieldTag')->setOption('id', $element->getName().'field');
                $element->setSeparator('');
            } else if ($element instanceof Zend_Form_Element_Hidden 
                    || $element instanceof Zend_Form_Element_Hash) {
                $element->setDecorators(array('ViewHelper'));
            }
        }
    }


    /**
     * Get an array to be used in formSelect() containing all collections.
     * 
     * @return array $options An associative array mapping collection IDs
     *to their titles for display in a dropdown menu
     */
    private function _getCollectionOptions()
    {
        $collections = get_records('Collection',array(),'0');
        $options = array('0'=>'Assign No Collection');
        foreach ($collections as $collection)
	{
	    $titles = $collection->getElementTexts('Dublin Core','Title');
	    if(isset($titles[0]))
	        $title = $titles[0];
	    $options[$collection->id]=$title;
	}

        return $options;
    }

    /**
     * Resolve a shortened URL and return the full url
     * 
     * @param string $shortUrl The shortened Flickr url of the photo to import.
     * @return string $fullUrl The full Flickr url of the photo to import.
     */
    private static function _resolveShortUrl($shortUrl)
    {
        //if the url contains 'youtube.com', it's not a short url. just return it.
        if(strpos($shortUrl,'youtube.com'))
            return($shortUrl);

        $fullUrl = self::_resolveRedirect($shortUrl);
        return $fullUrl;
    }

    /**
     * Resolve a redirect and return the redirected url
     * 
     * @param string $url The url which is redirected.
     * @return string $fullUrl The destination url.
     */
    private static function _resolveRedirect($url)
    {
        $headers = get_headers($url);
        $headers = array_reverse($headers);
        foreach($headers as $header) {
            if (strpos($header, 'Location: ') === 0 ) {
	        $fullUrl = "https://flickr.com".str_replace('Location: ', '', $header);
	        break;
            }
        }
        return $fullUrl;
    }

    /**
     * Validate the youtube url
     *
     *@param string $url The url to be validated
     *@param array $args An empty options array for now.
     *@return bool $valid Indicates whether this url points to a valid youtube
     */
    public function validateYoutubeUrl($url,$args){
        if (!preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $url)) {
	    return false;
        }
        if(strpos($url,'//youtu.be'))
	    $url = $this->_resolveShortUrl($url);

        if(!strpos($url,'youtube.com'))
	    return false;
        
        return true;
    }
}


