<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . '/../../classes/GiftList.php';
include_once __DIR__ . '/../../classes/ListProductBond.php';
include_once _PS_CLASS_DIR_ . "stock/StockAvailable.php";
define("_ERROR_", "Ha ocurrido un error");

class giftlistlistasModuleFrontController extends ModuleFrontController {
	public $module;

	public function initContent() {
		if(!$this->context->customer->isLogged()){
			Tools::redirect('my-account');
		}
		parent::initContent ();
		$list = new GiftListModel();
		$event_type = Db::getInstance ()->executeS ( "SELECT * FROM `" . _DB_PREFIX_ . "event_type`" );
		$_SESSION['event'] = $event_type;
		$this->context->smarty->assign ( array (
			'all_lists' => $list->getListByCreatorId($this->context->customer->id),
			'shared_lists' => $list->getSharedListByCoCreatorId($this->context->customer->id),
            'admin_link' => $this->context->link->getModuleLink('giftlist', 'administrar',array("url"=> "nuevo")),
			'description_link' => $this->context->link->getModuleLink('giftlist', 'descripcion',array('url' => "")),
			'form' => _MODULE_DIR_ ."giftlist/views/templates/front/partials/form_save_list.php",
			'share_list' => _MODULE_DIR_ ."giftlist/views/templates/front/partials/share_email.php"
		) );
		$this->setTemplate ( 'listas.tpl' );
	}

	public function init(){
		parent::init();
		if($this->ajax)
		{
			if(!empty(Tools::getValue('method'))){
				switch(Tools::getValue('method')){
					case "delete" :
						$this->_ajaxProcessDeleteList(Tools::getValue('id_list'));
						break;
					case "addProduct" :
						$this->_addProduct(Tools::getValue('id'),Tools::getValue('data'));
						break;
					case "share":
						$this->_shareList();
						break;
					default:
						die();
						break;
				}
			}
		}
	}

	public function setMedia() {
		parent::setMedia ();
		$this->addJS ( array (
			_MODULE_DIR_ . '/giftlist/views/js/vendor/datetimepicker/jquery.datetimepicker.min.js',
			_MODULE_DIR_ . '/giftlist/views/js/vendor/validation/jquery.validate.min.js',
			_MODULE_DIR_ . '/giftlist/views/js/vendor/mask/jquery.mask.min.js',
			_MODULE_DIR_ . '/giftlist/views/js/listas.js'
		) );
		$this->addCSS ( array (
			_MODULE_DIR_ . '/giftlist/views/css/vendor/datetimepicker/jquery.datetimepicker.css',
			_MODULE_DIR_ . '/giftlist/views/css/listas.css'
		) );
	}

	public function __construct() {
		$this->module = Module::getInstanceByName ( Tools::getValue ( 'module' ) );
		if (! $this->module->active)
			Tools::redirect ( 'index' );

		$this->page_name = 'module-' . $this->module->name . '-' . Dispatcher::getInstance ()->getController ();
		parent::__construct ();
	}

	/**
	 * @param int $id_product
	 * @param array $data
	 */
	private function _addProduct($id_product,$data){
		$lpd = new ListProductBondModel();
		$list = new GiftListModel($data['list']);
		$prod = new ProductCore($id_product);
		$link = new LinkCore();
        $s = StockAvailable::getQuantityAvailableByProduct((int)$id_product,(int)$data['form'][3]['value']);
        if($s == 0)
            die(Tools::jsonEncode(array(
                'msg' => "No hay cantidades suficientes en el inventario",
                'error' => true))
               );
        $images = Image::getImages($this->context->language->id, $id_product);
		$lpd->id_list = $data['list'];
		$lpd->id_product = $id_product;
		$lpd->message = $data['message'];
		$lpd->option = Tools::jsonEncode($data['form']);
		$lpd->favorite = $data['fav'] == "true" ? true : false;
		$lpd->group = $this->_groupProducts($data['cant'],$data['cant_group']);
		$lpd->created_at = date ( "Y-m-d H:i:s" );
		try{
			if(!$lpd->save())
				die(Db::getInstance()->getMsgError());
			else{
				$msg = $prod->getProductName($id_product)
				." ha sido añadido a la lista de regalos ". $list->name;
				$att = null;
				$attributes = $prod->getAttributeCombinationsById($data['form'][3]['value'], $this->context->language->id);
				foreach ($attributes as $row){
					if(isset($row['group_name']))
						$att[] = [
							'value' =>$row['group_name'].": ". $row['attribute_name']
						];
				}
				die(Tools::jsonEncode(array(
					'msg' => $msg,
					'prod_name' => $prod->getProductName($id_product),
					'attributes' => $att,
					'price' => $prod->getPrice(),
					'image' => "http://".$link->getImageLink($prod->link_rewrite[1], (int)$images[0]['id_image'], 'home_default'),
					'description_link' => $this->context->link->getModuleLink('giftlist', 'descripcion',array('url' => $list->url)),
                    'error' => false
				)));
			}
		}catch (Exception $e){
			die($e->getMessage());
		}
	}

	/**
	 * @param int $id
	 */
	private function _ajaxProcessDeleteList($id){
		$list = new GiftListModel($id);
		if($list->delete()){
			$response = array(
				'msg' => "Se ha eliminado correctamente",
				'id'  => $id
			);
			die(Tools::jsonEncode($response));
		}else{
			$response = array(
				'msg' => _ERROR_,
				'id'  => 0
			);
			die(Tools::jsonEncode($response));
		}

	}
    
    private function _shareList(){
		$id_shop = (int)Context::getContext()->shop->id;
		$id_lang = $this->context->language->id;
		$list = new GiftListModel (Tools::getValue('id_list'));
		$currency = $this->context->currency;
		$customer = new CustomerCore($list->id_creator);
		$params = array(
			'{lastname}' => $customer->lastname,
			'{firstname}' => $customer->firstname,
			'{code}' => $list->code,
			'{description_link}' => $this->context->link->getModuleLink('giftlist', 'descripcion',array('url' => $list->url))
		);

		if(!empty($list->id_cocreator)){
			$customer = new CustomerCore($list->id_cocreator);
			$params['firstname_co'] = $customer->firstname;
			$params['lastname_co'] = $customer->lastname;

			MailCore::Send($id_lang, 'share-list', sprintf(
			MailCore::l('Te han compartido una lista'), 1),
			$params, Tools::getValue('email'), $customer->firstname.' '.$customer->lastname,
			null, null, null,null, _MODULE_DIR_."giftlist/mails/", true, $id_shop);
			die("Se ha compartido la lista");
		}
		MailCore::Send($id_lang, 'share-list-no-cocreator', sprintf(
		MailCore::l('Te han compartido una lista'), 1),
		$params, Tools::getValue('email'), $customer->firstname.' '.$customer->lastname,
		null, null, null,null, _MODULE_DIR_."giftlist/mails/", true, $id_shop);
		die("Se ha compartido la lista");
	}
    
    /*
    *Group products
    */
    private function _groupProducts($cant,$cant_group){
        $total = $cant;
        $i = 0;
        if($cant_group != "" && $cant_group > 0){
            while($total >= $cant_group){
                $i++;
                $total -= $cant_group;
            }
            if($cant_group > 0){
                $i++;
            }
            
            return Tools::jsonEncode( array(
                "wanted" => $cant,
                "missing"=> $cant,
                "cant" => $cant_group,
                "rest" => $total,
                "tot_groups" => $i
            ));
        }
        return Tools::jsonEncode( array(
                "wanted" => $cant,
                "missing"=> $cant,
                "cant" => $cant,
                "rest" => $total,
                "tot_groups" => 1
            ));   
    }
}