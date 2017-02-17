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
class YouTubeImport_IndexController extends Omeka_Controller_AbstractActionController
{    

    /**
     * The default action to display the import from and process it.
     *
     * This action runs before loading the main import form. It 
     * processes the form output if there is any, and populates
     * some variables used by the form.
     *
     * @param void
     * @return void
     */
    public function indexAction()
    {
        include_once(dirname(dirname(__FILE__))."/forms/ImportForm.php");
        $form = new Youtube_Form_Import();

        //initialize flash messenger for success or fail messages
        $flashMessenger = $this->_helper->FlashMessenger;

        try{
            if ($this->getRequest()->isPost()){
	        if($form->isValid($this->getRequest()->getPost()))
	            $successMessage = Youtube_Form_Import::ProcessPost();
	        else 
	            $flashMessenger->addMessage('Invalid Youtube video data! Check your form entries.','error');
            } 
        } catch (Exception $e){
            $flashMessenger->addMessage($e->getMessage(),'error');
        }

        if(isset($successMessage)) {
            $flashMessenger->addMessage($successMessage,'success');
            $this->view->successDialog = true;
        }
        $this->view->form = $form;

    }


}
