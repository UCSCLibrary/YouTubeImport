<?php

/**
 * @package     YouTubeImport
 * @copyright   2014 UCSC Library Digital Initiatives
 * @license     
 */

class Youtube_Form_Import extends Omeka_Form
{



    /**
     * Construct the report generation form.
     */
    public function init()
    {
        parent::init();
        $this->_registerElements();
    }

    /**
     * Define the form elements.
     */
    private function _registerElements()
    {

      //URL:
      $this->addElement('text', 'youtube-url', array(
						    'label'         => __('Youtube URL'),
						    'description'   => __('Paste the full url of the Youtube video you would like to import'),
						    'order'         => 1,
						    'required'      => true
						    )
			);
      /*
        //number (single or multiple):
        $this->addElement('radio', 'youtube-number', array(
            'label'         => __('Number of Videos'),
            'description'   => __('Please indicate whether you importing a single video, or multiple videos from a channel or user.'),
            'value'         => 'single',
	    'order'         => 2,
	    'multiOptions'       => array(
					  'single'=>'Single Video',
					  'multiple'=>'Multiple Videos'
					  )
							   )
			  );

        //Items:
        $this->addElement('radio', 'youtube-selecting', array(
            'label'         => __('Select Items'),
            'description'   => __('If you are importing videos from a channel or user collection, this option allows you to select which videos to import from a list of thumbnails.'),
            'value'         => 'false',
	    'order'         => 3,
	    'multiOptions'       => array(
	    "false"=>"Import all items",
	    "true"=>"Select items to import"
	    )
							   )
			  );
      */
        // Visibility (public vs private):
        $this->addElement('checkbox', 'youtube-public', array(
            'label'         => __('Public Visibility'),
            'description'   => __('Would you like to make the video public in Omeka?'),
            'checked'         => 'checked',
	    'order'         => 3
							   )
			  );

	// Collection:
        $this->addElement('select', 'youtube-collection', array(
							'label'         => __('Collection'),
							'description'   => __('To which collection would you like to add the YouTube video?'),
							'value'         => '0',
							'order'         => 2,
							'required'      => true,
							'multiOptions'       => $this->_getCollectionOptions()
							)
			  );



        // Submit:
        $this->addElement('submit', 'youtube-import-submit', array(
            'label' => __('Import Video')
        ));

	//Display Groups:
        $this->addDisplayGroup(
			       array(
				     'youtube-url',
				     'youtube-collection',
				     'youtube-public'
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

    public static function ProcessPost()
    {
      //echo ("processed");

    }

    private function _getCollectionOptions()
    {
      $collections = get_records('Collection',array(),'0');
      $options = array('0'=>'All Collections');
      foreach ($collections as $collection)
	{
	  $titles = $collection->getElementTexts('Dublin Core','Title');
	  if(isset($titles[0]))
	    $title = $titles[0];
	  $options[$collection->id]=$title;
	}

      return $options;
    }

}
