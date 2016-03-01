<?php

class giftlistadministrarModuleFrontController extends ModuleFrontController {
    public $uploadDir = __DIR__. "../../../uploads/";
    
    public function initContent() {
		if(!$this->context->customer->isLogged()){
			Tools::redirect('my-account');
		}
		parent::initContent ();
		$list = new GiftListModel();
        $edit = 0;
        $res = null;
        if(Tools::getValue("url") != "nuevo"){
            $edit = 1;
            if(!$res = $list->getListBySlug(Tools::getValue('url')))
            {
                Tools::redirect($this->context->link->getModuleLink('giftlist', 'listas'));
            }
        }
		$this->context->smarty->assign (array(
            'event_type' => Db::getInstance ()->executeS ( "SELECT * FROM `" . _DB_PREFIX_ . "event_type`" ),
            'list_link' => $this->context->link->getmoduleLink("giftlist","listas"),
            'edit' => $edit,
            'data' => $res
		));
		$this->setTemplate ( 'administrar.tpl' );
	}
    
    public function init(){
		parent::init();
    }
    
    public function setMedia() {
		parent::setMedia ();
		$this->addJS ( array (
            _MODULE_DIR_ . '/giftlist/views/js/vendor/datetimepicker/jquery.datetimepicker.min.js',
			_MODULE_DIR_ . '/giftlist/views/js/vendor/validation/jquery.validate.min.js',
			_MODULE_DIR_ . '/giftlist/views/js/vendor/mask/jquery.mask.min.js',
            _MODULE_DIR_ . '/giftlist/views/js/ax-administrar.js'
		) );
		$this->addCSS ( array (
            _MODULE_DIR_ . '/giftlist/views/css/vendor/datetimepicker/jquery.datetimepicker.css',
		) );
	}
    
    public function __construct() {
		parent::__construct ();
	}
    
    	/**
	 * upload image from list
	 * @return boolean|string|NULL
	 */
	private function _uploadImage(){
		if ($_FILES['image']['name'] != '') {
			$file = Tools::fileAttachment('image');
			$sqlExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
			$mimeType = array('image/png', 'image/x-png','image/jpeg','image/gif');
			if(!$file || empty($file) || !in_array($file['mime'], $mimeType))
				return false;
			else {
				move_uploaded_file($file['tmp_name'], $this->uploadDir . Db::getInstance()->Insert_ID(). ".". $sqlExtension);
				$image_name = Db::getInstance()->Insert_ID(). ".". $sqlExtension;
			}
			@unlink($file);
			return isset($image_name) ?_MODULE_DIR_."giftlist/uploads/" . $image_name : false;
		}
		return false;
	}
    
    public function postProcess() {
        $id = Tools::getValue("id_list");
		if (Tools::isSubmit ('saveList')) {
			$this->_saveList($id);
		}
	}
    
    private function _saveList($id = 0){
		if($id != 0){
			$list = new GiftListModel ($id);
		}else{
			$list = new GiftListModel ();
		}
		$list->id_creator = $this->context->customer->id;
		$list->name = Tools::getValue ( 'name' );
		$list->event_type = Tools::getValue ( 'event_type' );
		$list->event_date = date("Y-m-d H:i:s", strtotime(Tools::getValue ( 'event_date' )));
		$list->public = Tools::getValue( 'public' ) == "on" ? 1 : 0;
		$list->guest_number = Tools::getValue ( 'guest_number' );
		$list->recieve_bond = Tools::getValue ( 'recieve_bond' ) == "on" ? 1 : 0;
		$list->edit = Tools::getValue ( 'can_edit' ) == "on" ? 1 : 0;
		$list->max_amount = Tools::getValue ( "max_amount" );
		$list->address_after = NULL;
		$list->code = $list->returnCode();
		$list->url = $list->slugify($list->name);
		$list->message = Tools::getValue('message');
		$dirC = array(
				'country' => "Colombia",
				'city'    => Tools::getValue('city'),
				'town'    => Tools::getValue('town'),
				'address' => Tools::getValue('address'),
				'tel'     => Tools::getValue('tel'),
				'cel'     => Tools::getValue('cel')
		);
        $dirC2 = array(
				'country' => "Colombia",
				'city_2'    => Tools::getValue('city_2'),
				'town_2'    => Tools::getValue('town_2'),
				'address_2' => Tools::getValue('address_2'),
				'tel_2'     => Tools::getValue('tel_2'),
				'cel_2'     => Tools::getValue('cel_2')
		);
        
        
		$list->info_creator = Tools::jsonEncode($dirC);
        $list->info_creator_2 = Tools::jsonEncode($dirC2);
		$id == 0 ? $list->created_at = date ( "Y-m-d H:i:s" ) : $list->updated_at = date ( "Y-m-d H:i:s" );
		try {
			if ($list->save()){
				$list->image = !$this->_uploadImage() ? $list->image : $this->_uploadImage();
                $list->id_cocreator = $list->setCocreator($list-id,Tools::getValue ( 'email_cocreator' ));
                $dirCC =  array(
                    'country' => "Colombia",
                    'city_co'    => Tools::getValue('city_co'),
                    'town_co'    => Tools::getValue('town_co'),
                    'address_co' => Tools::getValue('address_co'),
                    'tel_co'     => Tools::getValue('tel_co'),
                    'cel_co'     => Tools::getValue('cel_co')
                );
                $dirCC2 =  array(
                    'country' => "Colombia",
                    'city_co_2'    => Tools::getValue('city_co_2'),
                    'town_co_2'    => Tools::getValue('town_co_2'),
                    'address_co_2' => Tools::getValue('address_co_2'),
                    'tel_co_2'     => Tools::getValue('tel_co_2'),
                    'cel_co_2'     => Tools::getValue('cel_co_2')
                );
                $list->info_cocreator = Tools::jsonEncode($dirCC);
                $list->info_cocreator_2 = Tools::jsonEncode($dirCC2);
				$list->update();
				$this->context->smarty->assign ( array (
						'response' => "Se ha creado la lista",
						'error' => false
				));
			}
			else
				$this->context->smarty->assign ( array (
						'response' => _ERROR_,
						'error' => true
				));
		} catch ( Exception $e ) {
			$this->context->smarty->assign ( array (
					'response' => $e->getMessage(),
					'error' => true
			));
		}
        Tools::redirect($this->context->link->getModuleLink('giftlist', 'descripcion',array("url" => $list->url)));
	}
}