<?php
include_once __DIR__ . '/../../classes/GiftList.php';

class giftlistbuscarModuleFrontController extends ModuleFrontController {
	public $uploadDir;
	public $module;

	public function initContent() {
		parent::initContent ();
        $this->display_column_left = false;
        $this->display_column_right = false;
		$list = new GiftListModel();
        $name = Tools::getValue("name");
        $lastName = Tools::getValue("lastname");
        $code = Tools::getValue("code");
        $res = array();
        if(trim($name) && trim($lastName)){
            $res = $list->searchByCustomerNames($name,$lastName);
		}elseif(trim($code)){
			$res = $list->searchByCode($code);
			if($res != ""){
				Tools::redirect($this->context->link->getModuleLink('giftlist', 'descripcion', $res));
			}
			$res = array();
		}
			
		$this->context->smarty->assign ( array (
            "lists" => $res,
            'parameter' => $name . $lastName,
			'description_link' => $this->context->link->getModuleLink('giftlist', 'descripcion',array('url' => "")),
			'items_per_page' => 5
		) );
		$this->setTemplate ( 'searchList.tpl' );
	}
	
	public function init(){
		parent::init();
		if($this->ajax){
            $name = Tools::getValue("name");
            $lastName = Tools::getValue("lastname");
            $code = Tools::getValue("code");          
			$list = new GiftListModel();
			if(trim($name) && trim($lastName)){
				$res = $list->searchByCustomerNames($name,$lastName);
				die(Tools::jsonEncode($res));
			}elseif(trim($code)){
				$res = $list->searchByCode($code);
				if($res != ""){
					die(Tools::jsonEncode($this->context->link->getModuleLink('giftlist', 'descripcion', $res)));
				}
			}
		}
	}
	
	public function setMedia() {
		parent::setMedia ();
		$this->addJS ( array (
			_MODULE_DIR_ . '/giftlist/views/js/vendor/jplist/jplist.core.min.js',
			_MODULE_DIR_ . '/giftlist/views/js/vendor/jplist/jplist.pagination-bundle.min.js',
			_MODULE_DIR_ . '/giftlist/views/js/vendor/jplist/jplist.textbox-filter.min.js',
			_MODULE_DIR_ . '/giftlist/views/js/buscar.js'
		) );
		$this->addCSS ( array (
			_MODULE_DIR_ . '/giftlist/views/css/vendor/jplist/jplist.core.min.css',
			_MODULE_DIR_ . '/giftlist/views/css/vendor/jplist/jplist.pagination-bundle.min.css',
			_MODULE_DIR_ . '/giftlist/views/css/vendor/jplist/jplist.textbox-filter.min.css',
			_MODULE_DIR_ . '/giftlist/views/css/ax-lista-de-regalos.css'
		) );
	}
	
	public function __construct() {
        $this->uploadDir = __DIR__."../../../uploads/";
		$this->module = Module::getInstanceByName ( Tools::getValue ( 'module' ) );
		if (! $this->module->active)
			Tools::redirect ( 'index' );
		
		$this->page_name = 'module-'.$this->module->name.'-'.Dispatcher::getInstance()->getController();
		parent::__construct ();
	}
}