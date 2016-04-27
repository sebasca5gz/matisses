<?php
class matisses extends Module
{
	
	private $_uploadfile = 'matisses';
	
	public function __construct()
	{
		$this->name 			= basename(__FILE__,'.php');
		$this->tab 				= 'administration';
		$this->version 			= '1.0'; 
		$this->author 			= 'Arkix';
		$this->token 			= Tools::getAdminTokenLite('AdminModules');
		$this->ht_file			= _PS_ROOT_DIR_.'/.htaccess';
		parent::__construct();
		$this->displayName 		= $this->l('Matisses');
		$this->description 		= $this->l('Instalador componentes matisses');
		$this->_module 			= $this->name;
		$this->bootstrap		= true;
		$this->confirmUninstall = $this->l('Si desinstala este modulo el sitio puede no funcionar correctamente, ¿Esta seguro de continuar?');
		//Db::getInstance()->execute("UPDATE ps_category_lang SET name = 'Menu' where id_category = 2");
		
		
	}
	
	/***********************************************
	* BACKEND
	***********************************************/
	public function getContent()
	{	
		//$this->registerHook('actionAddGarantia');
		//$this->registerHook('actionCalculateShipping');
		//$this->registerHook('actionCustomerAccountAdd');
		//$this->registerHook('displayExperiencesHome');
		//$this->registerHook('displayCustomerAccount');
		//$this->registerHook('actionSortFilters');
		//$this->registerHook('actionMatChangeReference');	
		//$install[] = $this->__installPage('module-matisses-garantias','garantias');
		//$this->__installTabs('AdminTiposDanos','Tipos de daños',(int)Tab::getIdFromClassName('adminMatisses'));
		//self::hookactionListInvoice();
		if (Tools::isSubmit('updateApyKey'))
		{
			$NewApyKey = pSQL(Tools::getValue('ApyKey'));
			if(0==Db::getInstance()->getValue('SELECT count(*) as disp FROM `' . _DB_PREFIX_ . 'wsmatisses_configuration`'))
			{
				Db::getInstance()->insert('wsmatisses_configuration', array('apykey' =>	$NewApyKey));
			}else{
					Db::getInstance()->update('wsmatisses_configuration', array('apykey' =>	$NewApyKey));
				 }
			Configuration::updateValue($this->name.'_UrlWs', pSQL(Tools::getValue('url')));	
			Configuration::updateValue($this->name.'_LocationWs', pSQL(Tools::getValue('locationurl')));
			Configuration::updateValue($this->name.'_RowNumber', pSQL(Tools::getValue('RowNumber')));
			Configuration::updateValue($this->name.'_TimeRecord', abs(pSQL(Tools::getValue('TimeRecord'))));	 
		}	
		$limit = Configuration::get($this->name.'_RowNumber') ? Configuration::get($this->name.'_RowNumber') : 20;
		$ApyKey	= Db::getInstance()->ExecuteS('SELECT * FROM `' . _DB_PREFIX_ . 'webservice_account` WHERE active =1');
		$Log	= Db::getInstance()->ExecuteS('SELECT * FROM `' . _DB_PREFIX_ . 'wsmatisses_log` order by register_date desc limit '.$limit);
		foreach($Log as $d => $v)
		{
			$Log[$d]['register_date'] = date('Y-m-d H:i:s', $Log[$d]['register_date']);
		}
		foreach($ApyKey as $d => $v)
		{
			$ApyKey[$d]['selected'] = ($ApyKey[$d]['key'] == Db::getInstance()->getValue('SELECT apykey as disp FROM `' . _DB_PREFIX_ . 'wsmatisses_configuration`') 
										? 'selected' : NULL);
		}	
		$this->context->smarty->assign('path',$this->name);
		$this->context->smarty->assign('displayName',$this->displayName);
		$this->context->smarty->assign('ApyKey',$ApyKey);
		$this->context->smarty->assign('UrlWs',Configuration::get($this->name.'_UrlWs'));
		$this->context->smarty->assign('locationurl',Configuration::get($this->name.'_LocationWs'));
		$this->context->smarty->assign('RowNumber',Configuration::get($this->name.'_RowNumber'));
		$this->context->smarty->assign('TimeRecord',Configuration::get($this->name.'_TimeRecord'));
		$this->context->smarty->assign('Log',$Log);
		
		return $this->display(__FILE__, '/views/backend.tpl');
	}	
	
	/***********************************************
	*	INSTALACIÓN
	***********************************************/
	public function install()
	{
		
		// install controllers
		$install[] = $this->__installTabs('adminMatisses','Matisses',0);
		$parent = (int)Tab::getIdFromClassName('adminMatisses');
		$install[] = $this->__installTabs('adminExperiences','Experiencias',$parent);
		//images types
		$install[] = $this->__installImageTypes('experiences-home',570,145);
		
		$install[] = $this->__installPage('module-matisses-experiences','experiencias');
		
		
		$sql = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'highlights` (
				  `id_highlight` int(11) NOT NULL AUTO_INCREMENT,
				  `active` int(1) NOT NULL,
				  PRIMARY KEY (`id_highlight`),
				  KEY `active` (`active`)
				) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
				
				CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'experiences` (
				  `id_experience` int(11) NOT NULL AUTO_INCREMENT,
				  `parent` int(11) NOT NULL,
				  `id_shop_default` int(2) NOT NULL,
				  `position` int(3) NOT NULL,
				  `products` text,
				  `active` int(1) NOT NULL,
				  PRIMARY KEY (`id_experience`),
				  KEY `active` (`active`),
				  KEY `position` (`position`)
				) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
				
				CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'experiences_lang` (
				  `id_experience` int(11) NOT NULL AUTO_INCREMENT,
				  `id_shop` int(2) NOT NULL,
				  `id_lang` int(3) NOT NULL,
				  `name` varchar(200) NOT NULL,
				  `description` text,
				  `link_rewrite` varchar(200),
				  `meta_title` varchar(200),
				  `meta_keywords` text,
				  `meta_description` text,
				  PRIMARY KEY (`id_experience`,`id_shop`,`id_lang`)
				) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
				
				CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'experiences_product` (
				  `id_experience` int(11) NOT NULL AUTO_INCREMENT,
				  `id_product` int(11) NOT NULL,
				  `top` int(4) NOT NULL,
				  `left` int(4) NOT NULL,
				  PRIMARY KEY (`id_experience`,`id_product`)
				) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
				
				CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'product_store_available` (
				  `id_product` int(11) NOT NULL,
				  `id_store` int(11) NOT NULL,
				  PRIMARY KEY (`id_product`,`id_store`)
				) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=latin1;
				
				CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'category_homologation` (
				  `id_category` int(11) NOT NULL,
				  `id_matisses` int(11) NOT NULL,
				  PRIMARY KEY (`id_category`,`id_matisses`)
				) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=latin1;		
				
				DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'wsmatisses_homologacion`;
				CREATE TABLE IF NOT EXISTS `'. _DB_PREFIX_ .'wsmatisses_homologacion` (
				  `codeprestashop` varchar(30) NOT NULL,
				  `codeerp` varchar(30) NOT NULL,
				  `object` varchar(30) NOT NULL,
				  PRIMARY KEY (`codeprestashop`,`codeerp`,`object`)
				) ENGINE=InnoDB DEFAULT CHARSET=latin1;
								
		';
		
		$sql.='
				DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'wsmatisses_configuration`;
				CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'wsmatisses_configuration` (
				  `apykey` varchar(300) NOT NULL,
				  PRIMARY KEY (`apykey`)
				) ENGINE=InnoDB DEFAULT CHARSET=latin1;
				
				DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'wsmatisses_log`;
				CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'wsmatisses_log` (
				  `register_date` int(15) NOT NULL,
				  `object` varchar(30) NOT NULL,
				  `operation` varchar(30) NOT NULL,
				  `origen` varchar(30) NOT NULL,
				  `data` text NOT NULL,
				  `response` text NOT NULL,
				  `status` int(11) NOT NULL,
				  `error` text NOT NULL,
				  PRIMARY KEY (`register_date`)
				) ENGINE=InnoDB DEFAULT CHARSET=latin1;
				
				DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'wsmatisses_homologacion`;
				CREATE TABLE IF NOT EXISTS `". _DB_PREFIX_ ."wsmatisses_homologacion` (
				  `codeprestashop` varchar(30) NOT NULL,
				  `codeerp` varchar(30) NOT NULL,
				  `object` varchar(30) NOT NULL,
				  PRIMARY KEY (`codeprestashop`,`codeerp`,`object`)
				) ENGINE=InnoDB DEFAULT CHARSET=latin1;
				
				DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'wsmatisses_pagos`;
				CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'wsmatisses_pagos` (
				  `fecha` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				  `customer_id` int(11) NOT NULL,
				  `id_shop` int(11) NOT NULL,
				  `cart_id` int(11) NOT NULL,
				  `key_temporal` varchar(30) NOT NULL,
				  `key_placetopay` varchar(30) NOT NULL,
				  `key_prestashop` varchar(30) NOT NULL,
				  `key_matisses` varchar(30) NOT NULL,
				  PRIMARY KEY (`fecha`,`customer_id`,`id_shop`,`cart_id`),
				  UNIQUE KEY `key_temporal` (`fecha`),
				  UNIQUE KEY `cart_id` (`cart_id`),
				  KEY `customer_id` (`customer_id`),
				  KEY `id_shop` (`id_shop`),
				  KEY `key_placetopay` (`key_placetopay`),
				  KEY `key_prestashop` (`key_prestashop`),
				  KEY `key_matisses` (`key_matisses`)
				) ENGINE=InnoDB DEFAULT CHARSET=latin1;		
		';
		
		Configuration::updateValue($this->name.'_RowNumber',20);
		Configuration::updateValue($this->name.'_TimeRecord', -4);	
		
		if(!file_exists(_PS_IMG_DIR_.'highlights'))
			mkdir(_PS_IMG_DIR_.'highlights',755);
			
		if(!file_exists(_PS_IMG_DIR_.'experiences'))
			mkdir(_PS_IMG_DIR_.'experiences',755);	
		
		if (!parent::install() 
			|| in_array(0,$install) 
			|| !Db::getInstance()->Execute($sql)
			|| !$this->registerHook('displayFacebookLogin')
			|| !$this->registerHook('header')
			|| !$this->registerHook('displayFooterProduct')
			|| !$this->registerHook('displayAvailableProduct')
			|| !$this->registerHook('displaySchemesProduct')
			|| !$this->registerHook('displayExperiencesHome')
			|| !$this->registerHook('displayCustomerAccount')
			|| !$this->registerHook('actionSortFilters')
			|| !$this->registerHook('moduleRoutes')
			
			|| $this->registerHook('actionCustomerAccountUpdate')
			|| $this->registerHook('actionValidateProductsAvailableCart')
			|| $this->registerHook('actionCustomerAccountAdd')
			|| $this->registerHook('actionProductCartSave')
			|| $this->registerHook('actionPaymentProccess')
			|| $this->registerHook('actionAddGarantia')
			|| $this->registerHook('actionListGarantia')
			|| $this->registerHook('actionAddCommetsGarantia')
			|| $this->registerHook('actionListInvoice')
			|| $this->registerHook('actioncalculateAditionalCosts')
			|| $this->registerHook('actionOrderDetail')
			|| $this->registerHook('actionCalculateShipping')
			|| $this->registerHook('trackOrder')
			|| $this->registerHook('actionMatChangeReference')			
			
			)
			return false;
			
		Tools::generateHtaccess($this->ht_file, null, null, '', Tools::getValue('PS_HTACCESS_DISABLE_MULTIVIEWS'), false, Tools::getValue('PS_HTACCESS_DISABLE_MODSEC'));	
		return true;
	}
	
	
	private function __installPage($page=NULL,$title=NULL, $url_rewrite=NULL, $description=NULL)
	{
		try{
			
			if($page && $title)
			{
				$meta = new Meta();
				$meta->page 		= $page;
				$meta->title 		= $title;
				
				if($description)
					$meta->description	= $description;
					
				$meta->url_rewrite	= $url_rewrite ? $url_rewrite : Tools::link_rewrite($title);
				$meta->add();
				return true; 
			}
		}catch (Exception $e) {
			return false;
		}				
	}
	
	private function __uninstallPage($page=NULL)
	{
		try{
			if($page)
			{
				$page = Meta::getMetaByPage($page, $this->context->language->id);
				if($page['id_meta'])
					Db::getInstance()->delete('ps_meta', 'id_meta='.$page['id_meta']);
				return true; 
			}
		}catch (Exception $e) {
			return false;
		}				
	}	
	
	private function __installTabs($class_name,$name,$parent=0,$page=NULL,$title=NULL,$description=NULL, $url_rewrite=NULL)
	{
		try{
			$id_tab = (int)Tab::getIdFromClassName($class_name);
			if(!$id_tab)
			{
				$tab = new Tab();
				$tab->active = 1;
				$tab->class_name = $class_name;
				$tab->name = array();
				foreach (Language::getLanguages(true) as $lang)
					$tab->name[$lang['id_lang']] = $name;
					
				$tab->id_parent = $parent;
				$tab->module 	= $this->name;
				$tab->add(); 
				if($page && $title)
				{
					$meta = new Meta();
					$meta->page 		= $page;
					$meta->title 		= $title;
					
					if($description)
						$meta->description	= $description;
						
					if($url_rewrite)
					$meta->url_rewrite	= Tools::link_rewrite($url_rewrite);
					$meta->add(); 
					
				}
			}else{
					$this->__uninstallTabs($class_name);
					self::__installTabs($class_name,$name,$parent,$page,$title,$description, $url_rewrite);
				 }
			return true;
			
		}catch (Exception $e) {
			return false;
		}
		
	}
	/***********************************************
	*	INSTALACIÓN
	***********************************************/	
	public function uninstall()
	{
		
		$uninstall[] = $this->__uninstallTabs('adminExperiences');
		$uninstall[] = $this->__uninstallTabs('adminDestacados');
		$uninstall[] = $this->__uninstallTabs('adminWebservices');
		$uninstall[] = $this->__uninstallTabs('adminMatisses');
		$uninstall[] = $this->__uninstallImageTypes('experiences-home');
		// pages
		$uninstall[] = $this->__uninstallPage('module-matisses-experiences');
		
		$sql = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'highlights`;
				DROP TABLE IF EXISTS `'._DB_PREFIX_.'experiences`;
				DROP TABLE IF EXISTS `'._DB_PREFIX_.'experiences_lang`;
				DROP TABLE IF EXISTS `'._DB_PREFIX_.'experiences_product`;
				DROP TABLE IF EXISTS `'._DB_PREFIX_.'product_store_available`;
				DROP TABLE IF EXISTS `'._DB_PREFIX_.'category_homologation`;
				
				DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'wsmatisses_configuration`;
				DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'wsmatisses_log`;
				DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'wsmatisses_homologacion`;
				DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'wsmatisses_pagos`;				
		';
		
		if(file_exists(_PS_IMG_DIR_.'highlights'))
			Tools::deleteDirectory(_PS_IMG_DIR_.'highlights');
			
		if(file_exists(_PS_IMG_DIR_.'experiences'))
			Tools::deleteDirectory(_PS_IMG_DIR_.'experiences');	
		
		if (!parent::uninstall() 
			|| in_array(0,$uninstall) 
			|| !Db::getInstance()->Execute($sql)
			)
			return false;
		return true;
	}
	
	private function __installImageTypes($name,$width,$height,$p=false,$c=false,$m=false,$su=false,$sc=false,$st=false)
	{
		try{
			$ImageType = new ImageType();
			$ImageType->name 	= $name;
			$ImageType->width 	= $width;
			$ImageType->height 	= $height;
			$ImageType->products 		= $p;
			$ImageType->categories 		= $c;
			$ImageType->manufacturers 	= $m;
			$ImageType->suppliers 		= $su;
			$ImageType->scenes 			= $sc;
			$ImageType->stores 			= $st;
			$ImageType->add();
			return true;
		}catch (Exception $e) {
			return false;
		}
	}
	
	
	private function __uninstallImageTypes($name)
	{
		try{
			Db::getInstance()->Execute('DELETE FROM '._DB_PREFIX_.'image_type WHERE name="'.$name.'" ');
			return true;
		}catch (Exception $e) {
			return false;
		}
	}
	
	private function __uninstallTabs($class_name)
	{
		try{
			$id_tab = (int)Tab::getIdFromClassName($class_name);
			if ($id_tab)
			{
				$tab = new Tab($id_tab);
				$tab->delete();
			}
			return true;
		}catch (Exception $e) {
			return false;
		}
	}
	/*********************************************
	* WEB SERVICES
	*********************************************/
	public function loadProducts()
	{
		require_once dirname(__FILE__).'/webservice/wsproduct.php';
		$ws = new wsproduct;
		$ws->init();
	}
	
	
	/*********************************************
	* HOOKS
	*********************************************/
	
	public function hookactionMatChangeReference($params)
	{
		return str_replace('0000000000000','',$params['reference']);
	}
	
	public function hookactionCalculateShipping($params)
	{
		//echo "<pre>"; print_r($params); echo "</pre>";
		$shipping_cost = $params['total_shipping'];
		$id_address = key($params['delivery_option']);
		$id_carrier = str_replace(',','',current(array_values($params['delivery_option'])));
		
		if($id_carrier == 6)
		{
			if($id_address)
			{
				$Address = new Address($id_address);
				$State 	 = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
									SELECT *
									FROM `'._DB_PREFIX_.'state`
									WHERE `id_state` = '.(int)$Address->id_state
								);
				
				$salesWarehouseDTO['salesWarehouseDTO']['destinationCityCode'] = $State['iso_code'];
				
				
				foreach($params['products_cart'] as $k => $product)
				{
					$salesWarehouseDTO['salesWarehouseDTO']['items'][$k]['itemCode'] = $product['reference'];
					$salesWarehouseDTO['salesWarehouseDTO']['items'][$k]['quantity'] = $product['quantity'];
				}
				
				$salesWarehouseDTO = $this->array_to_xml($salesWarehouseDTO,false);
				$response 	= $this->wsmatisses_get_data('inventoryItem','quoteShipping','pruebas',$salesWarehouseDTO,true);
				if($response['return']['code']=='0101001')
					$shipping_cost = $response['return']['detail'];
			}
			return $shipping_cost;
		}else{
				$shipping_cost;
			 }
	}
	
	
	public function hookactionSortFilters($params)
	{
		$filters = $params['filters'];
		
		unset($keyfilter);
		foreach($filters as $k => $filter)
		{
			if(!$keyfilter)
			{
				if(strstr($filter['name'],'material'))
				{
					$keyfilter = $k;
					$filters[$k]['name'] = $this->l('material');
				}
			}else{
				if(strstr($filter['name'],'material'))
				{
					unset($values);
					$values = $filter['values'];
					if(sizeof($values))
					{
						unset($keymat);
						$keymat = key($values);
						$filters[$keyfilter]['values'][$keymat]['nbr'] = $values[$keymat]['nbr'];
						$filters[$keyfilter]['values'][$keymat]['name'] =$values[$keymat]['name'];
						$filters[$keyfilter]['values'][$keymat]['url_name'] = $values[$keymat]['url_name'];
						$filters[$keyfilter]['values'][$keymat]['meta_title'] = $values[$keymat]['meta_title'];
						$filters[$keyfilter]['values'][$keymat]['link'] = $values[$keymat]['link'];
						$filters[$keyfilter]['values'][$keymat]['rel'] = $values[$keymat]['rel'];
						unset($filters[$k]); 
					}
				}
			}
		}
		return $filters;
	}
	
	public function hookdisplayCustomerAccount($params)
	{
		return $this->display(__FILE__, 'views/templates/hook/garantias.tpl');
	}
	
	
	public function hookdisplayExperiencesHome($params)
	{
		require_once dirname(__FILE__)."/classes/Experiences.php";
		$Experiences = new Experiences();
		$list = $Experiences->getExperiences();
		foreach($list as $k => $exp)
		{
			$list[$k]['image'] = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].'/img/experiences/'.$exp['id_experience'].'-home.jpg';
		}
		$this->context->smarty->assign('experiences', $list);
		return $this->display(__FILE__, 'views/templates/hook/experiences_home.tpl');
		
	}
	
	
	public function hookmoduleRoutes() {
	/*
		return array(
				'module-maticces-experiences' => array(
                    'controller' => 'new',
                    'rule' =>  'news/new/{id_news}-{cat_news}-{page_cat}/{cat_rewrite}/{rewrite}.html',
                    'keywords' => array(
                        'id_news'   => array('regexp' => '[0-9]+',   'param' => 'id_news'),
                        'cat_news'  => array('regexp' => '[0-9]*',   'param' => 'cat_news'),
                        'cat_rewrite'  => array('regexp' => '[_a-zA-Z0-9-\pL]*', 'param' => 'cat_rewrite'),
                        'page_cat'      => array('regexp' => '[0-9]*',   'param' => 'page_cat'),
                        'rewrite'   => array('regexp' => '[_a-zA-Z0-9-\pL]*',   'param' => 'rewrite'),
                    ),
                    'params' => array(
						'fc' => 'module',
                        'module' => 'news',
                        'controller' => 'new'
                    )
                )
				 
    	);	
*/
    }	
	
	public function hookHeader($params)
	{
        global $smarty;
        global $cookie;
		$this->page_name = Dispatcher::getInstance()->getController();
		$this->context->controller->addJS($this->_path.'js/fblogin.js');
		
		if(in_array($this->page_name, array('category')))
		{
			$this->context->controller->addJS($this->_path.'js/product_colors.js');
		}
		
		
		if(in_array($this->page_name, array('product')))
		{
			$this->context->controller->addJqueryUI('ui.tabs');
			$this->context->controller->addJS($this->_path.'js/producttabs.js');
		}
		
		if(in_array($this->page_name, array('index')))
		{
			$this->context->controller->addJqueryPlugin('bxslider');
			$this->context->controller->addJS($this->_path.'js/experiences_home.js');
		}
		//Assign cutomer information
        if ($cookie->isLogged()) {
            $id = (int)$this->context->cookie->id_customer;
            $sql = "select id_customer,CONCAT_WS(' ',firstname, lastname) as 'name', email, charter, newsletter from "._DB_PREFIX_."customer ";
            $sql .= "where id_customer = '".$id."'";
            $res = Db::getInstance()->executeS($sql);
            
            $this->context->smarty->assign(array(
                'idcustomer' => $res[0]['id_customer'],
                'customername' => $res[0]['name'],
                'customeremail' => $res[0]['email'],
                'customercharter' => $res[0]['charter'],
                'customernewsletter' => $res[0]['newsletter']
		    ));
        }
        
        
        $this->context->smarty->assign(array(
            'idlanguage' => $this->context->language->id
        ));
        
        
        // Assing parent subcategories 
        if (in_array($this->page_name, array('category'))) {
            $idcategory = Tools::getValue('id_category');
            $cat = new Category($idcategory);
            $parent = $cat->getParentsCategories($this->context->language->id);
            $parents = array();
            
            foreach($parent as $row){
                
                if ($row['level_depth'] == 3) {
                    array_push($parents,
                               array(
                                    'id' => $row['id_category'],
                                    'name' => $row['name']
                    ));   
                } else if ($row['level_depth'] > 3) {
                    array_push($parents,
                               array(
                                    'id' => $row['id_category'],
                                    'name' => $row['name'],
                                    'parents' => array($row['id_parent'])
                    ));
                }               
            }

            $this->context->smarty->assign(array(
                'parents' => json_encode($parents)
		    ));
        }
        
        // Assing current product Chaordic
        if (in_array($this->page_name, array('product'))) {
            
            $id_product = (int)Tools::getValue('id_product');
            $link = new LinkCore();
            $images = Image::getImages($this->context->language->id, $id_product);
            $product = new Product($id_product);
            $cat = Product::getProductCategoriesFull($product->id,$this->context->language->id);
            $tags = Tag::getProductTags($id_product);
            $categoriesp = array();
            $tagsproduct = array();
            $attr = $product->getAttributeCombinaisons($this->context->language->id);
            $attrcolors = array();
            $skuattr = array();
            
            // attributes
            foreach($attr as $row){
                if ($row['group_name'] == 'Color') {
                    array_push($attrcolors,$row['attribute_name']);
                }
                
                $tempattr = array();
                /*$tempattr['id'] = $row['id_attribute'];*/
                $tempattr['sku'] = $row['reference'];
                $tempattr['specs'] = array('color' => $row['attribute_name']);
                
                if ($row['quantity'] > 0) { 
                    $tempattr['status'] = 'available';
                } else {
                    $tempattr['status'] = 'unavailable';
                }
                array_push($skuattr,$tempattr);
            }
            
            if (!empty($attrcolors)) {
                $this->context->smarty->assign(array(
                    'productcolors' => json_encode($attrcolors)
                ));
            }
            
            if (!empty($skuattr)) {
                $this->context->smarty->assign(array(
                    'productskuattr' => json_encode($skuattr)
                ));
            }
            
            // categories
            foreach($cat as $row){
                array_push($categoriesp, 
                           array(
                               'id' => $row['id_category'],
                                'name' => $row['name']
                            ));
            }
            
            //tags
            foreach($tags as $tag){
                foreach ($tag as $item) {
                    array_push($tagsproduct,array('name' => $item));
                }
            }
            
            // parent categories of product
            $idcategory = $product->getDefaultCategory();
            $categ = new Category($idcategory);
            $parent = $categ->getParentsCategories($this->context->language->id);
            $parents = array();
            
            foreach($parent as $row){
                
                if ($row['level_depth'] == 3) {
                    array_push($parents,
                               array(
                                    'id' => $row['id_category'],
                                    'name' => $row['name']
                    ));   
                } else if ($row['level_depth'] > 3) {
                    array_push($parents,
                               array(
                                    'id' => $row['id_category'],
                                    'name' => $row['name'],
                                    'parents' => array($row['id_parent'])
                    ));
                } else {
                    array_push($parents,
                               array(
                                    'id' => $row['id_category'],
                                    'name' => $row['name']
                    )); 
                }  
            }
           
            // assign data of product
            $this->context->smarty->assign(array(
                'idproduct' => $product->id,
                'nameproduct' => $product->getProductName($product->id),
                'linkproduct' => $product->getLink(),
                'descproduct' => strip_tags($product->description[1]),
				'imageproduct' => $link->getImageLink($product->link_rewrite, (int)$images[0]["id_image"], 'home_default'),
				'priceproduct' => $product->getPriceWithoutReduct(),
                'categoriesp' => json_encode($categoriesp),
                'tagsproduct' => json_encode($tagsproduct),
				'statusproduct' => $product->quantity,
                'productcondition' => $product->condition,
                'parents' => json_encode($parents)
		    ));
        }
        
        // Assing cart info to Chaordic
        if (in_array($this->page_name, array('order'))) {
            $id_cart = $this->context->cart->id;
            $cartbyurl = false;
            // reconstruct cart
            $getparams = $_SERVER['QUERY_STRING'];
            
            if (isset($getparams)) {
                $cart = null;
                $vars = explode('&',$getparams);
                $urlproducts = array();
                $cont = 0; 
                
                foreach($vars as $row) {
                    $findsku = strchr($row,'sku');
                    
                    if ($findsku) {
                        $cartbyurl = true;
                        $sk = explode('=', $findsku);
                        array_push($urlproducts,array(
                            'sku' => $sk[1],
                            'qty' => ''
                        ));
                    }
                    
                    $findqty = strchr($row,'qty'); 
                    
                    if ($findqty) {
                        $qt = explode('=', $findqty);
                        $urlproducts[$cont]['qty'] = $qt[1];
                        $cont++;
                    }
                } 
                
                $idcus = (int)($this->context->cookie->id_customer);
                if (!empty($idcustomer)) {
                    $idcus = $idcustomer;
                }
                
                // get cart id if exists
                if ($this->context->cookie->id_cart)
                {
                    $cart = new Cart($this->context->cookie->id_cart);
                }

                // create new cart if needed
                if (!isset($cart) || !$cart->id)
                {
                    $cart = new Cart();
                    $cart->id_customer = $idcus;
                    $cart->id_address_delivery = (int)  (Address::getFirstCustomerAddressId($cart->id_customer));
                    $cart->id_address_invoice = $cart->id_address_delivery;
                    $cart->id_lang = (int)($this->context->language->id);
                    $cart->id_currency = (int)($this->context->cookie->id_currency);
                    $cart->id_carrier = 1;
                    $cart->recyclable = 0;
                    $cart->gift = 0;
                    $cart->add();
                    $this->context->cookie->id_cart = (int)($cart->id);    
                    $cart->update();
                }
                
                foreach($urlproducts as $row) {
                    $prod = Product::searchByName($this->context->language->id,$row['sku']);
                    
                    if ($prod) {
                        $quant = $row['qty'];
                        
                        if (empty($quant)) {
                            $quant = 1;
                        }
                        
                        if ($prod[0]['quantity'] > 0) {
                            $cart->updateQty($quant, $prod[0]['id_product']);
                            $cart->update();
                        }
                    }
                } 
            }
            
            // Current products in shopping cart
            $productsc = $params['cart']->getProducts(true);
            $prodincart = array();
            
            foreach ($productsc as $row) {
                array_push($prodincart, array(
                        'product' => array(
                                    'id' => (string)$row['id_product'],
                                    'sku' => (string)$row['reference'],
                                    'price' => (int)$row['price_wt']
                        ),
                        'quantity' => (int)$row['cart_quantity']
                ));
            }
            
            $this->context->smarty->assign(array(
                'idcart' => $id_cart,
                'cartbyurl' => $cartbyurl,
                'prodincart' => json_encode($prodincart)
		    ));
        }
        
        // Get products in order confirmation
        if (in_array($this->page_name, array('orderconfirmation'))) {
            
            if (isset($_GET['id_order'])) {
                $order = new Order($_GET['id_order']);
                $signature = $_GET['key'];
                $productorders = $order->getProducts();
                $result = array();
                
                foreach ($productorders as $prod) {
                    $product = array();
                    $product['product'] = array(
                        "id" => $prod['product_id'],
                        "sku" => $prod['product_reference'],
                        "price" => $prod['product_price']
                    );
                    $product['quantity'] = (int)$prod['product_quantity'];
                    array_push($result,$product);
                }

                $this->context->smarty->assign(array(
                    'orderproducts' => json_encode($result),
                    'signature' => md5($signature)
                ));
            }
        }
	}
    
    public function searchByReference($skus) {
        $references = $skus;
        
        if (!empty($references)) {
            $idproducts = array();
            $idlanguage = $this->context->language->id;
            
            foreach ($references as $row) {
                $prod = Product::searchByName($idlanguage,$row);
                $idprod = $prod[0]['id_product'];
                array_push($idproducts,$idprod);
            }
            
            return $idproducts;   
        } else {
            return null;
        }
    }
	
	public function hookdisplayFooterProduct($params)
	{
		$this->context->smarty->assign(array(
											'product' => $params['product'],
											));
		return $this->display(__FILE__, 'views/templates/hook/product_tabs.tpl');
	}
	
	
	public function hookdisplayFacebookLogin($params)
	{
		$this->context->controller->addJS($this->_path.'js/fblogin.js');
		return $this->display(__FILE__, 'views/templates/hook/facebook_login.tpl');
	}
	
	public function hookdisplayAvailableProduct($params)
	{
		$stores = Db::getInstance()->ExecuteS('SELECT * FROM '._DB_PREFIX_.'store WHERE codmatisses in("'.str_replace(',',",",$params['product']->stores).'")');
		$this->context->smarty->assign('stores',$stores);	
		return $this->display(__FILE__, 'views/templates/hook/product_available.tpl');
	}
	
	public function hookdisplaySchemesProduct($params)
	{
		$sketch 		= $params['product']->sketch;
		$video 			= $params['product']->video;
		$three_sixty 	= $params['product']->three_sixty;
		
		if($video)
			$video= 'https://www.youtube.com/embed/'.$video;
		
		if($sketch)
			$sketch = '/modules/'.$this->name.'/files/'.$params['product']->reference.'/plantilla/'.$sketch;
		
		$this->context->smarty->assign('schemas',array(
			'sketch' => $sketch,
			'video' => $video,
			'three_sixty' => $three_sixty,
		));	
		return $this->display(__FILE__, 'views/templates/hook/product_schemes.tpl');
	}
	
	
	/*********************************************
	* AJAX 
	*********************************************/
	
	public function processAjaxCallback()
	{
		$option 	= $_REQUEST['option'];
		$request 	= $_REQUEST['request'];
		switch($option)
		{
			case 'fblogin': 
				
				$Customer = Customer::getCustomersByEmail($_REQUEST['request']['email']);
				if(sizeof($Customer)>0)
				{
					
					$response['haserror'] 	= false;
					$response['action']		= 'reload';
					$response['url']		= $this->createFacebookLogin($Customer);
					
				}else{
						$response['haserror'] 	= false;
						$response['action']		= 'create';
						$response['data']		= $request;
					 }
				
			
			break;
			default:
				$response['haserror'] 	= true;
				$response['message']	= $this->l('No data sending');
		}
		
		return json_encode($response);
	}
	
	private function createFacebookLogin($CurrentCustomer)
	{
		$customer = new Customer($CurrentCustomer[0]['id_customer']);
		$this->context->cookie->id_compare = isset($this->context->cookie->id_compare) ? $this->context->cookie->id_compare: CompareProduct::getIdCompareByIdCustomer($customer->id);
		$this->context->cookie->id_customer = (int)($customer->id);
		$this->context->cookie->customer_lastname = $customer->lastname;
		$this->context->cookie->customer_firstname = $customer->firstname;
		
		$this->context->cookie->customer_secondname = $customer->secondname;
		$this->context->cookie->customer_surname = $customer->surname;
		$this->context->cookie->customer_charter = $customer->charter;
		
		
		$this->context->cookie->logged = 1;
		$customer->logged = 1;
		$this->context->cookie->is_guest = $customer->isGuest();
		$this->context->cookie->passwd = $customer->passwd;
		$this->context->cookie->email = $customer->email;
		// Add customer to the context
		$this->context->customer = $customer;
		
		if(empty($this->context->cart))
		{
			$this->context->cart = new Cart($id_cart);
			$this->context->cart->id_currency = 1;
		}
		$id_carrier = (int)$this->context->cart->id_carrier;
		$this->context->cart->id_carrier = 0;
		$this->context->cart->setDeliveryOption(null);
		$this->context->cart->id_address_delivery = (int)Address::getFirstCustomerAddressId((int)($customer->id));
		$this->context->cart->id_address_invoice = (int)Address::getFirstCustomerAddressId((int)($customer->id));
		$this->context->cart->id_customer = (int)$customer->id;
		$this->context->cart->secure_key = $customer->secure_key;
		if ($this->ajax && isset($id_carrier) && $id_carrier && Configuration::get('PS_ORDER_PROCESS_TYPE'))
		{
			$delivery_option = array($this->context->cart->id_address_delivery => $id_carrier.',');
			$this->context->cart->setDeliveryOption($delivery_option);
		}
		$this->context->cart->save();
		$this->context->cookie->id_cart = (int)$this->context->cart->id;
		$this->context->cookie->write();
		$this->context->cart->autosetProductAddress();
		// Login information have changed, so we check if the cart rules still apply
		CartRule::autoRemoveFromCart($this->context);
		CartRule::autoAddToCart($this->context);
		return 'index.php?controller='.(($this->authRedirection !== false) ? urlencode($this->authRedirection) : $back);
	}
// FUNCIONES DE INTEGRACION CON CLIENTES
	
	/*******************************************************
	*	@author:	Sebastian casta�o
	*	@package:	Matisses integracion (Funciones de integracion)
	*	@summary:	Metodo que genera el xml que se envia a sap
					cuando se carga un producto al carrito o
					cuando se ingresa al listado de productos 
					para validar su existencia en sap
	*******************************************************/
	public function hookactionCustomerAccountAdd($params,$factura=false)
	{
		require_once dirname(__FILE__)."/classes/template.php";
		$customer 		= new Customer();
		$InfCustomer	= $_POST['email'] ? $customer->searchByName(pSQL($_POST['email'])) :  $customer->searchByName(pSQL($params['email']));
		$customer->id	= $InfCustomer[0]['id_customer'];
		
		$InfAddresses	= $customer->getAddresses((int)Configuration::get('PS_LANG_DEFAULT'));
		$opt = $_POST['wsactualizar'] ? 'modify' : 'add';
		$infoxml[0]['operation'] 		= $opt;
		$infoxml[0]['source'] 			= 'prestashop';
		$infoxml[0]['id'] 				= $InfCustomer[0]['charter'].'CL';
		$infoxml[0]['lastName1'] 		= strtoupper($InfCustomer[0]['lastname']);
		$infoxml[0]['lastName2']		= strtoupper($InfCustomer[0]['secondname']);
        $infoxml[0]['legalName']		= strtoupper($InfCustomer[0]['lastname'].($InfCustomer[0]['secondname'] ? ' '.$InfCustomer[0]['secondname']: '').' '.$InfCustomer[0]['firstname']);
        $infoxml[0]['names']			= strtoupper($InfCustomer[0]['firstname']);
		$infoxml[0]['email']			= $InfCustomer[0]['email'];
		$infoxml[0]['gender']			= 3;
        $infoxml[0]['salesPersonCode'] 	= ""; // se envia vacio esto se llena por default en sap;
		if(sizeof($InfAddresses)>0)
		{
			$infoxml[0]['defaultBillingAddress'] = '';
			$infoxml[0]['defaultShippingAddress'] = '';
		}
		
		
		$cont = 0;
		foreach($InfAddresses as $d => $v) 
		{
			$addresses[$d]['addressName']	= $InfAddresses[$d]['alias'];
            $addresses[$d]['address']		= $InfAddresses[$d]['address1'];
			$addresses[$d]['cityCode']		= $InfAddresses[$d]['state_iso'];
			$addresses[$d]['cityName']		= $InfAddresses[$d]['state'];
			$addresses[$d]['stateCode']		= Country::getIsoById(Country::getIdByName((int)Configuration::get('PS_LANG_DEFAULT'),$InfAddresses[$d]['country']));
            $addresses[$d]['stateName']		= $InfAddresses[$d]['country'];
			$addresses[$d]['email']			= $InfCustomer[0]['email'];
            $addresses[$d]['addressType']	= $cont ==0 ? 'F' : 'E'; //envio por defecto 
            $addresses[$d]['mobile']		= $InfAddresses[$d]['phone_mobile'];
            $addresses[$d]['phone']			= $InfAddresses[$d]['phone'];
			if($cont==1)
				break;
			$cont++;
		}
		$infoxml[0]['addresses'] = $addresses;
		//print_r($infoxml);
		//die();
		$xml = new Template(dirname(__FILE__)."/xml/sap_customer.xml");
		$xml->addParam('infoxml',$infoxml);
		$xml = $xml->output();
		
		
 		if($this->wsmatisses_client('customer',$opt,'prestashop',$xml))
			$this->wsmatisses_homologacion('customer','id',$customer->id,$infoxml[0]['id']);
	}
	
	/*******************************************************
	*	@author:	Sebastian casta�o
	*	@package:	Matisses integracion (Funciones de integracion)
	*	@summary:	Metodo que genera el xml de clientes a 
					enviar a sap cuando un cliente actualiza 
					su informacion en el prestashop.
	*******************************************************/
	public function hookactionCustomerAccountUpdate($params,$facturar=false, $Adresses = array(), $id_customer = NULL)
	{
		require_once dirname(__FILE__)."/classes/template.php";
		
		
		$customer 		= new Customer($id_customer);
		if($customer->email)
		{
			$params[0]->email = $customer->email;
			$params[0]->id = $customer->id;
		}
		
		$InfCustomer	= $customer->searchByName(pSQL($params[0]->email));
		$customer->id	= $params[0]->id;
		$InfAddresses	= $customer->getAddresses((int)Configuration::get('PS_LANG_DEFAULT'));
		$infoxml[0]['operation'] 		= 'modify';
		$infoxml[0]['source'] 			= 'prestashop';
		$infoxml[0]['id'] 				= $InfCustomer[0]['charter'].'CL';
		$infoxml[0]['lastName1'] 		= strtoupper($InfCustomer[0]['lastname']);
		$infoxml[0]['lastName2']		= strtoupper($InfCustomer[0]['secondname']);
        $infoxml[0]['legalName']		= strtoupper($InfCustomer[0]['lastname'].($InfCustomer[0]['secondname'] ? ' '.$InfCustomer[0]['secondname']: '').' '.$InfCustomer[0]['firstname']);
        $infoxml[0]['names']			= strtoupper($InfCustomer[0]['firstname']);
		
		if(sizeof($Adresses)> 0)
		{
			$infoxml[0]['defaultBillingAddress'] = $Adresses['invoice'];
			$infoxml[0]['defaultShippingAddress'] = $Adresses['delivery'];
		}
		
		$infoxml[0]['email']			= $InfCustomer[0]['email'];
        $infoxml[0]['salesPersonCode'] 	= ""; // se envia vacio esto se llena por default en sap;
		
		foreach($InfAddresses as $d => $v) 
		{
			$addresses[$d]['addressName']	= $InfAddresses[$d]['alias'];
            $addresses[$d]['address']		= $InfAddresses[$d]['address1'];
			$addresses[$d]['cityCode']		= $InfAddresses[$d]['state_iso'];
			$addresses[$d]['cityName']		= $InfAddresses[$d]['state'];
			$addresses[$d]['stateCode']		= Country::getIsoById(Country::getIdByName((int)Configuration::get('PS_LANG_DEFAULT'),$InfAddresses[$d]['country']));
            $addresses[$d]['stateName']		= $InfAddresses[$d]['country'];
			$addresses[$d]['email']			= $InfCustomer[0]['email'];
            $addresses[$d]['addressType']	= 'F'; //envio por defecto 
            $addresses[$d]['mobile']		= $InfAddresses[$d]['phone_mobile'];
            $addresses[$d]['phone']			= $InfAddresses[$d]['phone'];
		}
		$infoxml[0]['addresses'] = $addresses;
		$xml = new Template(dirname(__FILE__)."/xml/sap_customer.xml");
		$xml->addParam('infoxml',$infoxml);
		$xml = $xml->output();
 		if($this->wsmatisses_client('customer',$infoxml[0]['operation'],'prestashop',$xml))
			$this->wsmatisses_homologacion('customer','id',$customer->id,$infoxml[0]['id']); 
	}
// FUNCIONES DE INTEGRACION CON PRODUCTOS	
	/*******************************************************
	*	@author:	Sebastian casta�o
	*	@package:	Matisses integracion (Funciones de integracion)
	*	@summary:	Metodo que genera el xml que se envia a sap
					cuando se carga un producto al carrito o
					cuando se ingresa al listado de productos 
					para validar su existencia en sap
	*******************************************************/
	public function hookactionProductCartSave($params)
	{
		
		$headers = get_headers(Configuration::get($this->name.'_UrlWs'));
		
		
		/*
        if(is_soap_fault($headers) || empty($headers)){
            die(Tools::jsonEncode(array(
                'hasError' => true,
                'errors' => array("En este momento no es posible agregar al carrito"),
            )));
        }*/
		
		if(!strstr($headers[0],'200'))
			return false;
			
		//ini_set('display_errors',false);
		//error_reporting(~E_NOTICE);
		$id_cart 	= is_object($this->context->cart->id) ? $this->context->cart->id : $this->context->cookie->id_cart;
		$products				= $params['id_product'];
		$id_product_attribute	= $params['id_product_attribute'];
		if($id_product_attribute!=0)
		{
			$reference = self::getReferenceByIdProductAttribute($id_product_attribute,$products);
			
			
			
			if(!$reference)
				return false;
			
				
			$response 	= $this->wsmatisses_get_data('inventoryItem','listWebEnabledStock','sap',$this->array_to_xml(array('inventoryItemDTO'=>array('itemCode'=>$reference)),false));
			
			$reference	= $response['inventoryItemDTO']['itemCode'];
			require_once dirname(__FILE__)."/wsclasses/ws_product.php";
			$ws_product = new ws_product();
			if($reference)
			{
				$stock = $response['inventoryItemDTO']['stock']; 
				$StockSum=0;
				foreach($stock as $d => $v)
				{
					$StockSum 			+= $stock[$d]['quantity'];
				}
				StockAvailable::setQuantity($products,$id_product_attribute,$StockSum);
				return StockAvailable::getQuantityAvailableByProduct((int)$products,(int)$id_product_attribute)>0 ? true : false;
			}else{
					return false;
					
				 }
			
		}else{
				if(!$products)
				{
					$cart= new Cart();
					$cart->id = $id_cart;
					$products = $cart->getProducts();
				}
				
				if(is_array($products))
				{
				}else{
						$product 	= new Product($products);
						if(!$product->reference || empty($product->reference))
							return false;
							
						$reference 	= $product->reference;
						$response 	= $this->wsmatisses_get_data('inventoryItem','listWebEnabledStock','sap',$this->array_to_xml(array('inventoryItemDTO'=>array('itemCode'=>$reference)),false));
						$datos['inventoryChangesDTO']['changes'][0]	= $response['inventoryItemDTO'];
						if(is_array($datos))
						{
							require_once dirname(__FILE__)."/wsclasses/ws_product.php";
							$ws_product = new ws_product();
							if($ws_product->product_listStockChanges($datos))
							{
								$return = StockAvailable::getQuantityAvailableByProduct((int)$products)>0 ? true : false;
							}else{
								$return = false;
							}
						}else{
								$return = false;
							 }	 
						return $return; 
					 }
			}
		return false;	
	}
	
	public function hookactioncalculateAditionalCosts($params)
	{
		return self::wsmatissess_calculateAditionalCosts($params); 				
	}
	
	public function wsmatissess_getReferencesByModel($params)
	{
		
		require_once dirname(__FILE__)."/classes/nusoap/nusoap.php";
		$client 	= new nusoap_client(Configuration::get($this->name.'_UrlWs'), array("trace"=>1,"exceptions"=>0)); 
		$inventoryItemDTO['inventoryItemDTO']['model'] 		= $params;
		
		$inventoryItemDTO 	= self::array_to_xml($inventoryItemDTO,false);
		$s 			= array('genericRequest' => array('data'		=>$inventoryItemDTO,
														'object'	=>'inventoryItem',
														'operation'	=>'listItemsByModel',
														'source'	=>'prestashop')
												); 
		
		$result = $client->call('callService', $s);
		//echo "<pre>"; print_r($result); echo "</pre>";
		$result = $this->xml_to_array($result['return']['detail']);
		
		return $result['inventoryItemListDTO']['items'];
		//echo "<pre>"; print_r($result); echo "</pre>";
		//return $result['additionalCostsDTO']['installationCost'].'_'.$result['additionalCostsDTO']['deliveryCost']; 		
	}
    
    public function wsmatissess_getVIPGift($params)
	{
		
		require_once dirname(__FILE__)."/classes/nusoap/nusoap.php";
		$client 	= new nusoap_client(Configuration::get($this->name.'_UrlWs'), array("trace"=>1,"exceptions"=>0));
		$s 			= array('genericRequest' => array('data'		=>$params,
														'object'	=>'gift',
														'operation'	=>'get',
														'source'	=>'pruebas')
												);
		$result = $client->call('callService', $s);
        return $result;
	}
	
	public function wsmatissess_calculateAditionalCosts($params)
	{
		
		require_once dirname(__FILE__)."/classes/nusoap/nusoap.php";
		$client 	= new nusoap_client(Configuration::get($this->name.'_UrlWs'), array("trace"=>1,"exceptions"=>0)); 
		$OrderParametersDTO['orderParametersDTO']['cityCode'] 		= $params['cityCode'];
		$OrderParametersDTO['orderParametersDTO']['orderTotal'] 	= $params['orderTotal'];
		
		
		
		foreach($params['ItemCodes'] as $d => $v)
			$OrderParametersDTO['orderParametersDTO']['ItemCodes'][]['ItemCodes'] 	= $params['ItemCodes'][$d]['ItemCodes'];
		
		
		$OrderParametersDTO 	= self::array_to_xml($OrderParametersDTO,false);
		$s 			= array('genericRequest' => array('data'		=>$OrderParametersDTO,
														'object'	=>'order',
														'operation'	=>'calculateAdditionalCosts',
														'source'	=>'prestashop')
												); 
		
		$result = $client->call('callService', $s);
		$result = $this->xml_to_array($result['return']['detail']);
		return $result['additionalCostsDTO']['installationCost'].'_'.$result['additionalCostsDTO']['deliveryCost']; 			
	}
	
	public function hooktrackOrder($params)
	{
		require_once dirname(__FILE__)."/classes/nusoap/nusoap.php";
		$client 	= new nusoap_client(Configuration::get($this->name.'_UrlWs'), array("trace"=>1,"exceptions"=>0)); 
		$orderDTO = array();
		$orderDTO['orderDTO']['header']['sapOrderId']		= $params['sapOrderId'];
		
		require_once dirname(__FILE__)."/classes/nusoap/nusoap.php";
		$client = new nusoap_client(Configuration::get($this->name.'_UrlWs'), true); 
		$s 		= array('genericRequest' => array(
													'data'		=> $this->array_to_xml($orderDTO,false),
													'object'	=> 'order',
													'operation'	=> 'trackOrder',
													'source'	=> 'prestashop'
												 )); 
												 
		$result = $client->call('callService', $s);
		$temp = $this->xml_to_array($result['return']['detail']);
		//echo '<br><br><br><br><br><br><br><br><br><br><br><pre>';echo print_r($temp);echo '</pre>';
		$cadena = $temp['orderTrackingInfoDTO']['trackingInfo'];
		
		//echo '<br><br><pre>';echo print_r($cadena);echo '</pre>';
		
/*		echo '<br><br><br>otra prueba<br><br><br>';
		foreach ($cadena as $c){
			$datos[] = array(
				'date' =>  $c['date'],
				'itemCode' =>  $c['itemCode'],
				'status' =>  $c['status']
			); 
		}
		
		
		echo '<br><br><pre>';print_r($datos);echo '</pre>';
		$var = array();
		$var['nombre'] = 'paulo';
		$var['apellido'] = 'ossa';
		
		//return $this->xml_to_array($result['return']['detail']);			
		return $var;*/
		return $cadena;
	}
	
	public function hookactionPaymentConfirmation($params)
	{
		//echo "<pre>"; print_r($params); echo "</pre>";
	}
	
	public function hookactionPaymentProccess($params)
	{
		if($params['status']=='ok')
			self::wsmatisses_registrar($params);
			
		if($params['status']=='fail' || $params['status']=='rejected')
			self::wsmatisses_anular($params);			
	}
	
	public function hookactionAddCommetsGarantia($params)
	{
		require_once dirname(__FILE__)."/classes/nusoap/nusoap.php";
		$client 	= new nusoap_client(Configuration::get($this->name.'_UrlWs'), true); 
		$comment['requestCommentDTO']['comment'] 	= $params['comment'];
		$comment['requestCommentDTO']['requestID'] 	= $params['id_garantia'];
		$comment 	= self::array_to_xml($comment,false);
		$s 			= array('genericRequest' => array('data'		=>$comment,
														'object'	=>'serviceRequest',
														'operation'	=>'addComment',
														'source'	=>'prestashop')
												); 
		$result = $client->call('callService', $s);
		return $result;	
	}
	
	
	public function hookactionListGarantia($params)
	{
		require_once dirname(__FILE__)."/classes/nusoap/nusoap.php";
		$client 	= new nusoap_client(Configuration::get($this->name.'_UrlWs'), true);
		$s 			= array('genericRequest' => array('data'		=>$params['id_garantia'],
														'object'	=>'serviceRequest',
														'operation'	=>'getRequestHistory',
														'source'	=>'prestashop')
												); 
		$result = $client->call('callService', $s);
		$result = $this->xml_to_array($result['return']['detail']);
		return $result;	
	}
	
	public function hookactionListInvoice($params)
	{
		require_once dirname(__FILE__)."/classes/nusoap/nusoap.php";
		$client 	= new nusoap_client(Configuration::get($this->name.'_UrlWs'), true);
		$invoice['customerDTO']['id'] 		= $params['id'];	
		$invoice 	= self::array_to_xml($invoice,false);
		$s 			= array('genericRequest' => array('data'		=>$invoice,
														'object'	=>'order',
														'operation'	=>'listCustomerOrders',
														'source'	=>'prestashop')
												); 
		$result = $client->call('callService', $s);
		$result = $this->xml_to_array($result['return']['detail']);
		return $result;	
	}
	
	public function hookactionAddGarantia($params)
	{
		require_once dirname(__FILE__)."/classes/nusoap/nusoap.php";
		$client 	= new nusoap_client(Configuration::get($this->name.'_UrlWs'), true); 
		$garantia['serviceRequestDTO']['customerId'] 	= $params['customerId'];
		$garantia['serviceRequestDTO']['description'] 	= $params['description'];
		$garantia['serviceRequestDTO']['invoiceNumber'] = $params['invoiceNumber'];
		$garantia['serviceRequestDTO']['itemCode'] 		= $params['itemCode'];
		$garantia['serviceRequestDTO']['subject'] 		= $params['subject'];	
		
		foreach($params['images'] as $d => $v)
			$garantia['serviceRequestDTO']['images'][]['imageName'] = $v;
			
		foreach($params['problems'] as $d => $v)
			$garantia['serviceRequestDTO']['problems'][]['name'] = $v; 	
			
		$garantia 	= self::array_to_xml($garantia,false);
		$s 			= array('genericRequest' => array('data'		=>$garantia,
														'object'	=>'serviceRequest',
														'operation'	=>'createServiceRequest',
														'source'	=>'prestashop')
												); 
		
		$result = $client->call('callService', $s);
		//print_r($result);
		return $result;	 			
	}
	public function wsmatisses_registrar($params)
	{
/*		echo "<pre>";
		print_r($params);
		echo "</pre>";*/
		
		require_once dirname(__FILE__)."/classes/nusoap/nusoap.php";
		$client 	= new nusoap_client(Configuration::get($this->name.'_UrlWs'), true); 
		$order['incomingPaymentDTO']['nroFactura'] = Db::getInstance()->getValue('SELECT id_factura FROM `' . _DB_PREFIX_ . 'cart` WHERE id_cart= "'.$params['id_order'].'"');
		$order['incomingPaymentDTO']['nroTarjeta'] = '1111';
		$order['incomingPaymentDTO']['voucher']    = $params['receipt'];
		$order['incomingPaymentDTO']['franquicia'] = $params['franchise_name'];
		$order['incomingPaymentDTO']['tipo']       = 'CREDITO';
		$order 		= self::array_to_xml($order,false);
		$s 			= array('genericRequest' => array('data'		=>$order,
														'object'	=>'order',
														'operation'	=>'addPayment',
														'source'	=>'prestashop')
												); 
		$result = $client->call('callService', $s); 
	}
	
	public function wsmatisses_anular($params)
	{
		require_once dirname(__FILE__)."/classes/nusoap/nusoap.php";
		$client 	= new nusoap_client(Configuration::get($this->name.'_UrlWs'), true); 
		
		$order['orderDTO']['header']['prestashopOrderId'] = $params['id_order'];
		$order 		= self::array_to_xml($order,false);
		$s 			= array('genericRequest' => array('data'		=>$order,
														'object'	=>'order',
														'operation'	=>'voidInvoice',
														'source'	=>'prestashop')
												); 
		$result = $client->call('callService', $s); 
	}
	
	public function hookactionValidateProductsAvailableCart()
	{
		
		$continue	= true;
		$cart		= new Cart();
		$cart->id 	= $this->context->cookie->id_cart;
		$products 	= $cart->getProducts();
		
		if(sizeof($products))
		{	
			foreach($products as $d => $v)
			{
				if(false == $this->hookactionProductCartSave(array('id_product' => (int)$products[$d]['id_product'],'id_product_attribute' => (int)$products[$d]['id_product_attribute'])))
				{
					
					$Product = new Product((int)$products[$d]['id_product']);
					echo $this->l('Lo siento! - el producto').'<h5>'.$Product->reference.' - '.current($Product->name).'</h5> ya no se encuentra disponible para la venta';
					exit;
				}
				$stock	= StockAvailable::getQuantityAvailableByProduct((int)$products[$d]['id_product'],(int)$products[$d]['id_product_attribute']);
				if($products[$d]['quantity']> $stock)
				{
					$Product = new Product((int)$products[$d]['id_product']);
					echo $this->l('Lo siento! - el producto').'<h5>'.$Product->reference.' - '.current($Product->name).'</h5> tiene <b>'.$stock.'</b> Unidad(es) disponibles para la venta, por favor elimine <b>'.(abs($products[$d]['quantity']-$stock).'</b> Unidad(es) de su orden para continuar con el proceso');
					exit;
				}
			}
		}else{
			
				echo $this->l('Lo siento! - se ha presentado un error al intentar generar el pago');
				exit;
			 }
		if($continue)
			return $this->wsmatisses_createInvoice($products);
	}
	// FUNCIONES DE COMUNICACION CON SAP	
	public function wsmatisses_facturar($params)
	{
		set_time_limit(0);
 		require_once dirname(__FILE__)."/classes/nusoap/nusoap.php";
		$client 	= new nusoap_client(Configuration::get($this->name.'_UrlWs'), true); 
		$order['orderDTO']['header']['prestashopOrderId'] = $params['id_order'];
		$order 		= self::array_to_xml($order,false);
		$s 			= array('genericRequest' => array('data'		=>$order,
														'object'	=>'order',
														'operation'	=>'createInvoice',
														'source'	=>'prestashop')
												); 
		$result = $client->call('callService', $s); 
		if(empty($result))
		{
			echo "<p>";print_r($result['return']['detail']);echo"</p>";
			return false;	
		}
		if(substr($result['return']['code'],4,1)==9)
		{
			echo "<p>";print_r($result['return']['detail']);echo"</p>";
			return false;
		}else{
				$sql = 'UPDATE `' . _DB_PREFIX_ . 'cart` set id_factura ="'.$result['return']['detail'].'" where id_cart = "'.$params['id_order'].'"';
				Db::getInstance()->Execute($sql);
				return true;
			 }
		echo "<p>";print_r($result['return']['detail']);echo"</p>";
		return false;	 
	}
	
	/*******************************************************
	*	@author:	Sebastian casta�o
	*	@package:	Matisses integracion (Funciones de Comunicacion sap)
	*	@summary:	Metodo que realiza peticiones al sap enviando los 
					parametros respectivos para cada opcion
	*******************************************************/
	public function wsmatisses_client($objeto,$operacion,$origen,$datos,$boolean=true)
	{
		$xml 	= NULL;
		$action = NULL;
		$version= NULL;
		$status	= NULL;
		$error	= NULL;
		
		if(!$objeto) 	$error = $this->array_to_xml(array('response' => array('code'=>9000, 'detail'=>$this->l('Missing object param'))));
		if(!$operacion) $error = $this->array_to_xml(array('response' => array('code'=>9001, 'detail'=>$this->l('Missing operation param'))));
		if(!$origen) 	$error = $this->array_to_xml(array('response' => array('code'=>9002, 'detail'=>$this->l('Missing origin param'))));
		if(!$datos) 	$error = $this->array_to_xml(array('response' => array('code'=>9003, 'detail'=>$this->l('Missing data param'))));
		$wsdl 		= Configuration::get($this->name.'_UrlWs');
		$client 	= new SoapClient($wsdl,array("trace"=>1,"exceptions"=>0));
		$location 	= Configuration::get($this->name.'_LocationWs');
		$request 	= $datos;
		try{
			$response = $client->__doRequest($request, $location, $action, $version);
			$return = true;
		} catch(SoapFault $ex){
			$response = $ex->getMessage();
			$return = false;
		} 
		$this->log($objeto,$operacion,$origen,$xml,$response,$status,$error);
		return $boolean ? $return : $response;
	}
	
	public function wsmatisses_get_data($objeto,$operacion,$origen,$datos=NULL, $return = false)
	{
		//set_time_limit(15);
		require_once dirname(__FILE__)."/classes/nusoap/nusoap.php";
		$client = new nusoap_client(Configuration::get($this->name.'_UrlWs'), true); 
		//echo "<pre>"; print_r($client); echo "</pre>";
		//die();
		$s 		= array('genericRequest' => array('data'=>$datos,'object'=>$objeto,'operation'=>$operacion,'source'=>$origen));
		
		$result = $client->call('callService', $s);
		if($client->error_str)
		{
			return array('error_string' => $client->error_str);
		}
		if($return)
			return $result;  
		
		if(!$result['return'])
			return false;
			
		
		if('0101002' == $result['return']['code'])
		{
			//echo ak;
			$datos 		= $this->xml_to_array(utf8_encode($result['return']['detail']));
		}else{
				$datos 		= $this->xml_to_array(utf8_encode($result['return']['detail']));
			 }
		
		return $datos;
	}
	
	public function wsmatisses_listStockChanges()
	{		
		require_once dirname(__FILE__)."/classes/template.php";
		$datos 		= $this->wsmatisses_get_data('inventoryItem','listStockChanges','sap',5);
		if(is_array($datos))
		{
			require_once dirname(__FILE__)."/wsclasses/ws_product.php";
			$ws_product = new ws_product();
			$ws_product->product_listStockChanges($datos);
		}	
	}
	
	public function wsmatisses_getStockChanges()
	{
		$ddf = fopen(dirname(__FILE__).'/loadproducts.log','a');
		$time = time();
		fwrite($ddf,"	----------------------------------------------------------------------------------\n");
		fwrite($ddf,"	".date("H:i:s")." INICIA CONSULTA REFERENCIAS \n");
		fwrite($ddf,"	----------------------------------------------------------------------------------\n");
		fclose($ddf);
		ini_set('display_errors',false);
		require_once dirname(__FILE__)."/classes/template.php";
		$datos 		= $this->wsmatisses_get_data('inventoryItem','listReferencesWithStock','prestashop','');	
		if(is_array($datos))
		{
			$ddf = fopen(dirname(__FILE__).'/loadproducts.log','a');
			$products = $datos['inventoryChangesDTO']['changes'];
			fwrite($ddf,"	".date("H:i:s")." Referencias encontradas ".sizeof($products)." \n");
			fwrite($ddf,"	".date("H:i:s")." Validando imagenes de las referencias encontradas \n");
			foreach($products as $d => $v)
			{
				fwrite($ddf,"		".date("H:i:s")." Referencia: ".$products[$d]['itemCode']." ");
				$path = dirname(__FILE__).'/files/'.$products[$d]['itemCode'].'/';
				if(!file_exists($path))
				{
					fwrite($ddf," - sin carpeta de imagenes eliminada del arreglo\n");
					unset($products[$d]);
				}else{
						if(sizeof(glob($path."/images/*.jpg"))==0)
						{
							fwrite($ddf," - sin imagenes eliminada eliminada del arreglo\n");
							unset($products[$d]);
						}else{
								fwrite($ddf," - Con imagenes\n");
							 }
					 }
			}
			require_once dirname(__FILE__)."/wsclasses/ws_loadProducts.php";
			$ws_product = new ws_loadProducts();
			$ws_product->_products = $products;
			fwrite($ddf,"	".date("H:i:s")." Referencias con imagenes ".sizeof($products)."\n");
			fwrite($ddf,"	----------------------------------------------------------------------------------\n");
			fwrite($ddf,"	".date("H:i:s")." TERMINE CONSULTA REFERENCIAS - TARDE: ".(time()-$time)." SEG\n");
			fwrite($ddf,"	----------------------------------------------------------------------------------\n");
			fclose($ddf);
			
			$ws_product->loadProducts(); 
		}
		return true;	
	}
	
	public function wsmatisses_getInfoProduct($reference)
	{
		ini_set('display_errors',false);	
		require_once dirname(__FILE__)."/classes/template.php";
		$data['inventoryItemDTO']['itemCode'] = $reference;
		$datos 		= $this->wsmatisses_get_data('inventoryItem','getItemInfo','prestashop',$this->array_to_xml($data,false));
		return $datos['inventoryItemDTO']; 
	}
	public function wsmatisses_getModelInfo()
	{
		ini_set('display_errors',false);	
		require_once dirname(__FILE__)."/classes/template.php";
		$datos 		= $this->wsmatisses_get_data('inventoryItem','listModelsWithStock','prestashop','');
		if($datos['error_string'])
			return $datos;
		
		return $datos['webEnabledModelsDTO']['models']; 
	}	
	
	public function wsmatisses_listDetailedLastDayStockChanges()
	{
		require_once dirname(__FILE__)."/classes/template.php";
		$datos 		= $this->wsmatisses_get_data_product('inventoryItem','listDetailedLastDayStockChanges','sap',NULL);
		if($datos)
		{
			require_once dirname(__FILE__)."/wsclasses/ws_product.php";
			$datos = new SimpleXMLElement(utf8_encode($datos));
			$ws_product = new ws_product();
			$ws_product->product_listDetailedLastDayStockChanges($datos);
		}
	}
	
	public function customerExists($cedula) 
	{
		require_once dirname(__FILE__)."/classes/nusoap/nusoap.php";
		$client 	= new nusoap_client(Configuration::get($this->name.'_UrlWs'), true); 
		$customerDTO['customerDTO']['id'] = $cedula.'CL';
		$customerDTO= self::array_to_xml($customerDTO,false);
		
		//print_r($customerDTO);
		$s 			= array('genericRequest' => array('data'		=>$customerDTO,
														'object'	=>'customer',
														'operation'	=>'get',
														'source'	=>'prestashop')
												); 
		$result = $client->call('callService', $s);
		return $result['return']['code']!='0101909' ? true : false;
	}
	
	public function wsmatisses_createInvoice($products)
	{
		if(!is_array($products))
			return false;
		
		if(!is_object($this->context->customer))
			return false;
			
		
		$Addresses = Db::getInstance()->getRow('SELECT 
													(SELECT alias from '._DB_PREFIX_.'address WHERE id_address = a.id_address_delivery) as delivery, 
													(SELECT alias from '._DB_PREFIX_.'address WHERE id_address = a.id_address_invoice) as invoice  
												FROM '._DB_PREFIX_.'cart as a
												WHERE id_cart = '.$this->context->cookie->id_cart.' 	
												');	
 	
		$this->hookactionCustomerAccountUpdate(array('email'=>$this->context->cookie->email),true, $Addresses,$this->context->cookie->id_customer);
			
	
		$orderDTO = array();
		$orderDTO['orderDTO']['header']['prestashopOrderId']= $this->context->cookie->id_cart;
		$orderDTO['orderDTO']['header']['customerId']		= $this->context->customer->charter;
		foreach($products as $d => $v)
		{
			$orderDTO['orderDTO']['detail'][$d]['itemCode'] = $products[$d]['reference'];
			$orderDTO['orderDTO']['detail'][$d]['quantity'] = $products[$d]['quantity'];
		}	
		$response = Db::getInstance()->getRow("SELECT * FROM `" . _DB_PREFIX_ . "wsmatisses_pagos` 
												WHERE customer_id=".$this->context->customer->id.
												" AND id_shop = ". $this->context->customer->id_shop.
												" AND cart_id = ".$this->context->cookie->id_cart);
		if(!$response)
		{
			Db::getInstance()->insert('wsmatisses_pagos', array(
															'customer_id' 	=> $this->context->customer->id,
															'id_shop' 		=> $this->context->customer->id_shop,
															'cart_id'		=> $this->context->cookie->id_cart,
															'key_temporal'	=> $orderDTO[$d]['header']['prestashopOrderId']
															));
																
			$response = Db::getInstance()->getRow("SELECT * FROM `" . _DB_PREFIX_ . "wsmatisses_pagos` 
												WHERE customer_id=".$this->context->customer->id.
												" AND id_shop = ". $this->context->customer->id_shop.
												" AND cart_id = ".$this->context->cookie->id_cart);												
		
		}
		if($response) 
		{
			//ini_set('display_errors',true);
			require_once dirname(__FILE__)."/classes/nusoap/nusoap.php";
			$client = new nusoap_client(Configuration::get($this->name.'_UrlWs'), true); 
			
			$orderDTO['orderDTO']['synchronous'] = 'true';
			$xml	= $this->array_to_xml($orderDTO,false);
			$s 		= array('genericRequest' => array(
														'data'		=> $xml,
														'object'	=> 'order',
														'operation'	=> 'add',
														'source'	=> 'prestashop'
													 )); 
													 
													 
			$result = $client->call('callService', $s);
			
			
			
 			if($result && $result['return']['code']=='0201001')
			{
				if($result['return']['code']=='0201902')
				{
					$this->hookactionCustomerAccountAdd(array('email'=>$this->context->cookie->email),true);
				}
				if(array_key_exists($result['return']['code'],array('0201902'=>'',
																	'0201903'=>'',
																	'0201904'=>'',
																	'0201905'=>'',
																	'0201906'=>'',
																	'0201907'=>''))){
					echo $this->l($result['return']['code'].' - Lo siento! - se ha presentado un error durante la operaci�n, no se puede continuar con el proceso de compra');
					echo '<p>'.$result['return']['detail'].'</p>';
					exit;
				}
				
				
				if($result['return']['code']=='0201901')
				{
					$return = true;
				}
				
				// actualizo el cliente
				
				if(self::wsmatisses_facturar(array('id_order' => $orderDTO['orderDTO']['header']['prestashopOrderId'])))
				{
					return true;
				}else{
							echo (
								$this->l('Lo siento! - se ha presentado un error durante la operación, no se puede continuar con el proceso de compra
											<br>
											No se pudo validar el estado del proceso.')
								);
							exit;
					 }
			}else{
					
					echo ($this->l('Lo siento! - se ha presentado un error durante la operación, no se puede continuar con el proceso de compra').'<p>'.utf8_encode($result['return']['detail']).'</p>');
					exit;
				 } 
			
			//return true;
		}
		return false; 
	}
	/*******************************************************
	*	@author:	Sebastian casta�o
	*	@package:	Matisses integracion (Funciones de Comunicacion sap)
	*	@summary:	Metodo que recibe peticiones desde sap enviando los 
					parametros respectivos para cada opcion, instanciando
					la clase respectiva dependiendo del parametro objeto
					enviado
	*******************************************************/
	public function wsmatisses_server($datos,$objeto,$operacion,$origen)
	{
		$response['error'] 		= array();
		$response['data']  		=$datos;
		$response['response']	= NULL;	
		
		$objeto 	= empty($objeto) ? $datos['object']: $objeto;
		$operacion 	= empty($operacion) ? $datos['operation'] : $operacion;
		$origen 	= empty($origen) ? $datos['source'] : $origen;
		$datos 		= sizeof($datos)>1 ? $datos['data'] : $datos;
		(!$objeto) ? array_push($response['error'],'Missing object param') : NULL;
		(!$operacion) ? array_push($response['error'],'Missing object param') : NULL;
		(!$origen) ? array_push($response['error'],'Missing object param') : NULL;
		(!$datos) ? array_push($response['error'],'Missing object param') : NULL;
		
		if(!$response['error'])
		{
			$objeto 	= strtolower(trim($objeto));
			$operacion 	= strtolower(trim($operacion));
			$class		= 'ws_'.trim(strtolower($objeto));
			$file		= dirname(__FILE__)."/wsclasses/".$class .'.php';
			if(file_exists($file))
			{
				$function 	= trim(strtolower($objeto)).'_'.trim(strtolower($operacion));
				require_once $file; 
				$class		= new $class;
				if(method_exists($class,$function))
				{
					eval('$response = $class->'.$function.'($datos);');
				}else{
						$response['response'] =  'Unknowlage options';
						array_push($response['error'], "objet: $objeto");
						array_push($response['error'], "operation: $operacion");
						array_push($response['error'], "source: $origen");
					 }
			}else{
						$response['response'] =  'Service not active';
						array_push($response['error'], "objet: $objeto");
						array_push($response['error'], "operation: $operacion");
						array_push($response['error'], "source: $origen");
				 }
		}else{
				$response['response'] =  'Unknowlage options';
				array_push($response['error'], "objet: $objeto");
				array_push($response['error'], "operation: $operacion");
				array_push($response['error'], "source: $origen");
			 }
	
		if(sizeof($response['error']>0))
		{
			$code 		= '9999';
			$errors	 	= $this->array_to_xml(array('response' => $response['error']));
			$response 	= $this->array_to_xml(array('response' => array('code'=>$code, 'detail'=>$response['response'])));
		}
		$this->log($objeto,$operacion,$origen,$datos,$response,($errors !='' ? true : false),$errors); 	 
		return $response;
	}
// FIN FUNCIONES DE AYUDA
	/*******************************************************
	*	@author:	Sebastian casta�o
	*	@package:	Matisses integracion (Helpers)
	*	@summary:	Metodo que registra todas las transacciones
					hechas desde el prestashop al sap y viceversa
	*******************************************************/	
	public function log($object,$operation,$origen,$data,$response=NULL,$status=NULL,$error =NULL)
	{
		Db::getInstance()->insert('wsmatisses_log', array(
															'register_date' => time(),
															'object'		=> pSQL($object),
															'operation'		=> pSQL($operation),
															'origen'		=> pSQL($origen),
															'data'			=> pSQL(str_replace('<','&lt;',str_replace('>','&gt;',$data))),
															'response'		=> pSQL($response),
															'status'		=> pSQL($status),
															'error'		    => pSQL($error)
															));												
		if(Configuration::get($this->name.'_TimeRecord'))
			Db::getInstance()->execute("delete from `" . _DB_PREFIX_ . "wsmatisses_log` WHERE register_date <= ".strtotime('-'.Configuration::get($this->name.'_TimeRecord').' hours'));													
	}
	
	/*******************************************************
	*	@author:	Sebastian casta�o
	*	@package:	Matisses integracion (Helpers)
	*	@summary:	Metodo que registra todas las transacciones
					hechas desde el prestashop al sap y viceversa
					todas las transacciones que se registren,
					se visualizan en el panel de administracion del
					modulo
	*******************************************************/
	public function validate($field,$type,$value,$obligatory)
	{
		global $errors;
		if(!$obligatory)
			if(empty($value)) return false;
		switch($type)
		{
			case 'isNumeric': 			!is_numeric($value) ? $errors[sizeof($errors)+1] = $field.' - '.$this->l('Invalid numeric value'): NULL; 	break;
			case 'isBool': 				!is_bool((bool)$value) ? $errors[sizeof($errors)+1] = $field.' - '.$this->l('Invalid bool value'): NULL; 		break;
			case 'isFloat': 			!is_float((float)$value) ? $errors[sizeof($errors)+1] = $field.' - '.$this->l('Invalid float value'): NULL;  	break;
			case 'isInt': 				!is_numeric($value) ? $errors[sizeof($errors)+1] = $field.' - '.$this->l('Invalid int value'): NULL;	break;
			case 'isNullOrUnsignedId': 	!is_numeric($value) ? $errors[sizeof($errors)+1] = $field.' - '.$this->l('Invalid integral and unsigned value'): NULL; 	break;
			case 'isSerializedArray':	!preg_match("/^a:[0-9]+:{.*;}$/",$value) ? $errors[sizeof($errors)+1] = $field.' - '.$this->l('Invalid PHP serialized data.'): NULL; break;
			case 'isBirthDate':			!preg_match("/^([0-9]{4})-((0?[1-9])|(1[0-2]))-((0?[1-9])|([1-2][0-9])|(3[01]))( [0-9]{2}:[0-9]{2}:[0-9]{2})?$/",$value) ? $errors[sizeof($errors)+1] = $field.' - '.$this->l('invalid date, in YYYY-MM-DD format.'): NULL; break;
			case 'isColor': 			!preg_match("/^(#[0-9a-fA-F]{6}|[a-zA-Z0-9-]*)$/",$value) ? $errors[sizeof($errors)+1] = $field.' - '.$this->l('invalid valid HTML/CSS color, in #xxxxxx format or text format.'): NULL; break;
			case 'isEmail':				!preg_match("/^[a-z0-9!#$%&\'*+\/=?^`{}|~_-]+[.a-z0-9!#$%&\'*+\/=?^`{}|~_-]*@[a-z0-9]+[._a-z0-9-]*\.[a-z0-9]+$/ui",$value) ? $errors[sizeof($errors)+1] = $field.' - '.$this->l('Invalid e-mail address.'): NULL;  break;
			case 'isImageSize':			!preg_match("/^[0-9]{1,4}$/",$value) ? $errors[sizeof($errors)+1] = $field.' - '.$this->l('Invalid image size, between 0 and 9999.'): NULL;  break;
			case 'isLanguageCode':		!preg_match("/^[a-zA-Z]{2}(-[a-zA-Z]{2})?$/",$value) ? $errors[sizeof($errors)+1] = $field.' - '.$this->l('Invalid language code, in XX or XX-XX format.'): NULL;   break;
			case 'isLanguageIsoCode':	!preg_match("/^[a-zA-Z]{2,3}$/",$value) ? $errors[sizeof($errors)+1] = $field.' - '.$this->l('Invalid ISO language code, in XX or XXX format.'): NULL;  break;
			case 'isLinkRewrite':		!preg_match("/^[_a-zA-Z0-9-]+$/",$value) ? $errors[sizeof($errors)+1] = $field.' - '.$this->l('Invalid link rewrite.'): NULL;  break;
			case 'isMd5':				!preg_match("/^[a-f0-9A-F]{32}$/",$value) ? $errors[sizeof($errors)+1] = $field.' - '.$this->l('Invalid  MD5 string: 32 characters, mixing lowercase, uppercase and numerals.'): NULL;  break;
			case 'isNumericIsoCode':	!preg_match("/^[0-9]{2,3}$/",$value) ? $errors[sizeof($errors)+1] = $field.' - '.$this->l('Invalid ISO code, in 00 or 000 format.'): NULL;  break;
			case 'isPasswd':			!preg_match("/^[.a-zA-Z_0-9-!@#$%\^&*()]{5,32}$/",$value) ? $errors[sizeof($errors)+1] = $field.' - '.$this->l('Invalid password, in. between 5 and 32 characters long.'): NULL;  break;
			case 'isPasswdAdmin':		!preg_match("/^[.a-zA-Z_0-9-!@#$%\^&*()]{8,32}$/",$value) ? $errors[sizeof($errors)+1] = $field.' - '.$this->l('Invalid password, between 8 and 32 characters long.'): NULL;  break;
			case 'isPhpDateFormat':		!preg_match("/^[^<>]+$/",$value) ? $errors[sizeof($errors)+1] = $field.' - '.$this->l('Invalid PHP date � in fact, a string without \'<\' nor \'>\'.'): NULL;  break;
			case 'isReference':			!preg_match("/^[^<>;={}]*$/",$value) ? $errors[sizeof($errors)+1] = $field.' - '.$this->l('Invalid product reference.'): NULL;  break;
			case 'isUrl':				!preg_match("/^[~:#,%&_=\(\)\.\? \+\-@\/a-zA-Z0-9]+$/",$value) ? $errors[sizeof($errors)+1] = $field.' - '.$this->l('Invalid URL'): NULL;  break;
			case 'isCatalogName':		!preg_match("/^[^<>;=#{}]*$/u",$value) ? $errors[sizeof($errors)+1] = $field.' - '.$this->l('Invalid product or category name'): NULL;  break;
			case 'isCarrierName':		!preg_match("/^[^<>;=#{}]*$/u",$value) ? $errors[sizeof($errors)+1] = $field.' - '.$this->l('Invalid carrier name.'): NULL;  break;
			case 'isConfigName':		!preg_match("/^[a-zA-Z_0-9-]+$/",$value) ? $errors[sizeof($errors)+1] = $field.' - '.$this->l('Invalid configuration key.'): NULL;  break;
			case 'isGenericName':		!preg_match("/^[^<>;=#{}]*$/u",$value) ? $errors[sizeof($errors)+1] = $field.' - '.$this->l('Invalid standard name'): NULL;  break;
			case 'isImageTypeName':		!preg_match("/^[a-zA-Z0-9_ -]+$/",$value) ? $errors[sizeof($errors)+1] = $field.' - '.$this->l('Invalid image type.'): NULL;  break;
			case 'isName':				!preg_match("/^[^0-9!<>,;?=+()@#\"�{}_$%:]*$/",$value) ? $errors[sizeof($errors)+1] = $field.' - '.$this->l('Invalid name.'): NULL;  break;
			case 'isTplName':			!preg_match("/^[a-zA-Z0-9_-]+$/",$value) ? $errors[sizeof($errors)+1] = $field.' - '.$this->l('Invalid template name.'): NULL;  break;
			case 'isAddress':			!preg_match("/^[^!<>?=+@{}_$%]*$/u",$value) ? $errors[sizeof($errors)+1] = $field.' - '.$this->l('Invalid postal address.'): NULL;  break;
			case 'isCityName':			!preg_match("/^[^!<>;?=+@#\"�{}_$%]*$/u",$value) ? $errors[sizeof($errors)+1] = $field.' - '.$this->l('Invalid city name.'): NULL;  break;
			case 'isCoordinate':		!preg_match("/^\-?[0-9]{1,8}\.[0-9]{1,8}$/s",$value) ? $errors[sizeof($errors)+1] = $field.' - '.$this->l('Invalid latitude-longitude coordinates, in 00000.0000 form.'): NULL;  break;
			case 'isMessage':			!preg_match("/[<>{}]/i",$value) ? $errors[sizeof($errors)+1] = $field.' - '.$this->l('Invalid message.'): NULL;  break;
			case 'isPhoneNumber':		!preg_match("/^[+0-9. ()-]*$/",$value) ? $errors[sizeof($errors)+1] = $field.' - '.$this->l('Invalid phone number.'): NULL;  break;
			case 'isPostCode':			!preg_match("/^[a-zA-Z 0-9-]+$/",$value) ? $errors[sizeof($errors)+1] = $field.' - '.$this->l('Invalid postal code.'): NULL;  break;
			case 'isStateIsoCode':		!preg_match("/^[a-zA-Z0-9]{2,3}((-)[a-zA-Z0-9]{1,3})?$/",$value) ? $errors[sizeof($errors)+1] = $field.' - '.$this->l('Invalid state ISO code.'): NULL;  break;
			case 'isZipCodeFormat':		!preg_match("/^[NLCnlc -]+$/",$value) ? $errors[sizeof($errors)+1] = $field.' - '.$this->l('Invalid zipcode format.'): NULL;  break;
			case 'isAbsoluteUrl':		!preg_match("/^https?:\/\/[:#%&_=\(\)\.\? \+\-@\/a-zA-Z0-9]+$/",$value) ? $errors[sizeof($errors)+1] = $field.' - '.$this->l('Invalid absolute URL.'): NULL;  break;
			case 'isDniLite':			!preg_match("/^[0-9A-Za-z-.]{1,16}$/U",$value) ? $errors[sizeof($errors)+1] = $field.' - '.$this->l('Invalid  DNI (Documento Nacional de Identidad) identifier. Specific to Spanish shops.'): NULL;  break;
			case 'isEan13':				!preg_match("/^[0-9]{0,13}$/",$value) ? $errors[sizeof($errors)+1] = $field.' - '.$this->l('Invalid barcode (EAN13).'): NULL;  break;
			case 'isLinkRewrite':		!preg_match("/^[_a-zA-Z0-9-]+$/",$value) ? $errors[sizeof($errors)+1] = $field.' - '.$this->l('Invalid friendly URL.'): NULL;  break;
			case 'isUpc':				!preg_match("/^[0-9]{0,12}$/",$value) ? $errors[sizeof($errors)+1] = $field.' - '.$this->l('Invalid barcode (UPC).'): NULL;  break;
			case 'len32':				strlen($value)>32 ? $errors[sizeof($errors)+1] = $field.' - '.$this->l('32 characters allowed'): NULL;  break;
			case 'len128':				strlen($value)>128 ? $errors[sizeof($errors)+1] = $field.' - '.$this->l('128 characters allowed'): NULL;  break;
			case 'len255':				strlen($value)>255 ? $errors[sizeof($errors)+1] = $field.' - '.$this->l('255 characters allowed'): NULL;  break;
		}
		if(is_array($errors))
		{
			foreach($errors as $d => $v)
			{
				if(!$v) unset($errors[$d]);
			}
		}
		return $errors;
	}
	
	public function xml_match($xml,$key='return')
	{
		$xml		= eregi_replace("[\n|\r|\n\r]", '', $xml);
		$pattern	= '/<'.trim($key).'>.*<\/'.trim($key).'>/';
		preg_match_all($pattern,$xml,$matches);
		return trim(current(current($matches)));
	}
	
	/*******************************************************
	*	@author:	Sebastian casta�o
	*	@package:	Matisses integracion (Helpers)
	*	@summary:	Metodo que convierte un xml a un array
					este metodo es depende de los metodos
					xml2array, NormalizeArray, Normalize
	*******************************************************/
	public function xml_to_array($xml)
	{
		$xml = $this->xml2array($xml);
		$xml = $this->NormalizeArray($xml);
		return $xml;
	}
	
	/*******************************************************
	*	@author:	Sebastian casta�o
	*	@package:	Matisses integracion (Helpers)
	*	@summary:	Metodo que apoya la funcionalidad del metodo
					xml_to_array, extrae los valores del xml
					y los asocio tag = value en un array
	*******************************************************/
	private function xml2array($contents, $get_attributes = 1, $priority = 'tag',$xml_parser=NULL)
	{
		$parser = xml_parser_create('');
		xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8");
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
		if($xml_parser)
			xml_set_character_data_handler($xml_parser, 'characterData'); 
		xml_parse_into_struct($parser, trim($this->Normalize($contents,true)), $xml_values);
		xml_parser_free($parser);
	
		if (!$xml_values)
			return; //Hmm...
		$xml_array = array ();
		$parents = array ();
		$opened_tags = array ();
		$arr = array ();
		$current = & $xml_array;
		$repeated_tag_index = array (); 
	
		foreach ($xml_values as $data)
		{
			
			unset ($attributes, $value);
			extract($data);
			$result = array ();
			$attributes_data = array ();
			if (isset ($value))
			{
				if ($priority == 'tag')
					$result = $value;
				else
					$result['value'] = $value;
			}
			if (isset ($attributes) and $get_attributes)
			{
				foreach ($attributes as $attr => $val)
				{
					if ($priority == 'tag')
						$attributes_data[$attr] = $val;
					else
						$result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
				}
			}
			if ($type == "open")
			{ 
				$parent[$level -1] = & $current;
				if (!is_array($current) or (!in_array($tag, array_keys($current))))
				{
					$current[$tag] = $result;
					if ($attributes_data)
						$current[$tag . '_attr'] = $attributes_data;
					$repeated_tag_index[$tag . '_' . $level] = 1;
					$current = & $current[$tag];
				}
				else
				{
					if (isset ($current[$tag][0]))
					{
						$current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
						$repeated_tag_index[$tag . '_' . $level]++;
					}
					else
					{ 
						$current[$tag] = array (
							$current[$tag],
							$result
						); 
						$repeated_tag_index[$tag . '_' . $level] = 2;
						if (isset ($current[$tag . '_attr']))
						{
							$current[$tag]['0_attr'] = $current[$tag . '_attr'];
							unset ($current[$tag . '_attr']);
						}
					}
					$last_item_index = $repeated_tag_index[$tag . '_' . $level] - 1;
					$current = & $current[$tag][$last_item_index];
				}
			}
			elseif ($type == "complete")
			{
				if (!isset ($current[$tag]))
				{
					$current[$tag] = $result;
					$repeated_tag_index[$tag . '_' . $level] = 1;
					if ($priority == 'tag' and $attributes_data)
						$current[$tag . '_attr'] = $attributes_data;
				}
				else
				{
					if (isset ($current[$tag][0]) and is_array($current[$tag]))
					{
						$current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
						if ($priority == 'tag' and $get_attributes and $attributes_data)
						{
							$current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
							
						}
						$repeated_tag_index[$tag . '_' . $level]++;
					}
					else
					{
						$current[$tag] = array (
							$current[$tag],
							$result
						); 
						$repeated_tag_index[$tag . '_' . $level] = 1;
						if ($priority == 'tag' and $get_attributes)
						{
							if (isset ($current[$tag . '_attr']))
							{ 
								$current[$tag]['0_attr'] = $current[$tag . '_attr'];
								unset ($current[$tag . '_attr']);
							}
							if ($attributes_data)
							{
								$current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
							}
						}
						$repeated_tag_index[$tag . '_' . $level]++; //0 and 1 index is already taken
					}
				}
			}
			elseif ($type == 'close')
			{
				$current = & $parent[$level -1];
			}
		}
		return ($xml_array);
	}
	
	/*******************************************************
	*	@author:	Sebastian casta�o
	*	@package:	Matisses integracion (Helpers)
	*	@summary:	Metodo que apoya la funcionalidad del metodo
					xml_to_array
	*******************************************************/
	private function NormalizeArray($array)
	{
		if(!is_array($array))
			return false;
		
		foreach($array as $d => $v)
		{
			is_array($array[$d]) ? $array[$d]=$this->NormalizeArray($array[$d]) : $array[$d] = $this->Normalize($v);
		}
		return $array;
	}
	
	/*******************************************************
	*	@author:	Sebastian casta�o
	*	@package:	Matisses integracion (Helpers)
	*	@summary:	Metodo que apoya la funcionalidad del metodo
					xml_to_array
	*******************************************************/
	private function Normalize($content, $way=NULL)
	{
		$original = array(
							"�","�",'�','�','�',
							'�','�','�','�','�',
							'�','�'
						 ); 
		$change = array (
							"#aacute;","#eacute;","#iacute;","#oacute;","#uacute;",
						 	"#Aacute;","#Eacute;","#Iacute;","#Oacute;","#Uacute;",
							"#ntilde;","#Ntilde;"
							);
	  	if(true==$way)
		{
			$content = str_replace($original,$change,$content);
		}else{
				$content = str_replace($change,$original,$content);
			 }
		return $content;	 
	}
	
	/*******************************************************
	*	@author:	Sebastian casta�o
	*	@package:	Matisses integracion (Helpers)
	*	@summary:	Metodo que convierte un objeto a un array
	*******************************************************/
	public function object2array($object)
	{
		return json_decode(json_encode($object), true);
	}
	
	/*******************************************************
	*	@author:	Sebastian casta�o
	*	@package:	Matisses integracion (Helpers)
	*	@summary:	Convierte un array a un xml
	*******************************************************/
	public function array_to_xml($array,$header=true)
	{ 
	
		foreach($array as $d => $v)
		{
			if(is_array($array[$d]))
			{
				$xml.=$this->array_to_xml_parse_array($array[$d],$d);
			}else{
					$xml.= "<$d>".(empty($v) ? ' ' :  $v)."</$d>";
				 }
		}
		return ($header ? '<?xml version="1.0" encoding="utf-8"?>' : NULL).$xml;
	}
	
	/*******************************************************
	*	@author:	Sebastian casta�o
	*	@package:	Matisses integracion (Helpers)
	*	@summary:	Convierte un array a un xml metodo de apoyo
					al metodo array_to_xml
	*******************************************************/
	private function array_to_xml_parse_array($array, $key)
	{
		$xml.= "<$key>";
		foreach($array as $d => $v)
		{
			if(is_array($v))
			{
				$xml.=$this->array_to_xml_parse_array($v,(is_numeric($d) ? $key : $d));
			}else{
				$xml.="<$d>".(empty($v) ? ' ' :  $v)."</$d>";
			}
		}
		$xml.= "</$key>";
		$xml = str_replace("<$key><$key>","<$key>",$xml);
		$xml = str_replace("</$key></$key>","</$key>",$xml);
		return $xml;
	}
	
	/*******************************************************
	*	@author:	Sebastian casta�o
	*	@package:	Matisses integracion (Helpers)
	*	@summary:	Metodo que homologa keys del prestashop al sap
					y viceversa 
	*******************************************************/
	private function wsmatisses_homologacion($clase,$type,$id_ps,$id_sap)
	{
		Db::getInstance()->insert('wshomologacion', 
									array('clase'=> pSQL($clase), 'type' => pSQL($type), 'id_ps' => pSQL($id_ps), 'id_sap' => pSQL($id_sap))
									,false, true,Db::INSERT_IGNORE,true
									); 
	}
	public function getReferenceByIdProductAttribute($id_product_attribute,$id_product)
	{
		if(!$id_product_attribute)
			return false;
			
		return Db::getInstance()->getValue('SELECT reference 
											FROM `' . _DB_PREFIX_ . 'product_attribute` 
											WHERE id_product ="'.$id_product.'"
											AND id_product_attribute = "'.$id_product_attribute.'"');	
	}
	public function getbyisocity($city)
	{
		return Country::getByIso(trim($city));
	}
	
	public function getbyisostate($state)
	{
		return State::getIdByIso(trim($state));
	}
	
}	
?>