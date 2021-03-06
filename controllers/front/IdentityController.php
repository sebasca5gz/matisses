<?php
/*
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class IdentityControllerCore extends FrontController
{
	public $auth = true;
	public $php_self = 'identity';
	public $authRedirection = 'identity';
	public $ssl = true;

	public function init()
	{
		parent::init();
		$this->customer = $this->context->customer;
	}

	/**
	 * Start forms process
	 * @see FrontController::postProcess()
	 */
	public function postProcess()
	{
		$origin_newsletter = (bool)$this->customer->newsletter;
		if (Tools::isSubmit('submitIdentity'))
		{
			$email = trim(Tools::getValue('email'));

			if(Tools::getValue('confirmation'))
				$_POST[passwd2] = Tools::getValue('confirmation');
			
			
			if (!Tools::getValue('medio'))
				$this->errors[] = Tools::displayError('Debes seleccionar el medio por el cual deseas ser contactado');

			
			if(Tools::getValue('passwd') && !Tools::getValue('passwd2'))
				$this->errors[] = sprintf(Tools::displayError('Debes confirmar tu %s'),'<b>'.Tools::displayError('Contraseña').'</b>');
			
			
			if(Tools::getValue('passwd') && Tools::getValue('passwd2'))
			{
				if (Tools::getValue('passwd') != Tools::getValue('passwd2'))
					$this->errors[] = sprintf(Tools::displayError('The %s do not match'),'<b>'.Tools::displayError('Passwords').'</b>');
			}
	
			if(!Tools::getValue('charter'))
				$this->errors[] = sprintf(Tools::displayError('The %s is required'),'<b>'.Tools::displayError('Charter').'</b>');
			
			if(Tools::getValue('charter'))
			{
				$exists = Db::getInstance()->getValue('SELECT count(*) FROM '._DB_PREFIX_.'customer WHERE charter = "'.Tools::getValue('charter').'" and id_customer != '.$this->context->customer->id);
				if($exists)
					$this->errors[] = sprintf(Tools::displayError('La %s ya se encuentra registrada'),'<b>'.Tools::displayError('Charter').' </b>');
			}
		
			

			if (Tools::getValue('months') != '' && Tools::getValue('days') != '' && Tools::getValue('years') != '')
				$this->customer->birthday = (int)Tools::getValue('years').'-'.(int)Tools::getValue('months').'-'.(int)Tools::getValue('days');
			elseif (Tools::getValue('months') == '' && Tools::getValue('days') == '' && Tools::getValue('years') == '')
				$this->customer->birthday = null;
			else
				$this->errors[] = Tools::displayError('Invalid date of birth.');

			if (Tools::getIsset('old_passwd'))
				$old_passwd = trim(Tools::getValue('old_passwd'));

			if (!Validate::isEmail($email))
				$this->errors[] = Tools::displayError('This email address is not valid');
			elseif ($this->customer->email != $email && Customer::customerExists($email, true))
				$this->errors[] = Tools::displayError('An account using this email address has already been registered.');
			elseif (!Tools::getIsset('old_passwd') || (Tools::encrypt($old_passwd) != $this->context->cookie->passwd && $this->customer->facebookid != 0))
				$this->errors[] = Tools::displayError('The password you entered is incorrect.');
			elseif (Tools::getValue('passwd') != Tools::getValue('confirmation'))
				$this->errors[] = Tools::displayError('The password and confirmation do not match.');
			else
			{
				$prev_id_default_group = $this->customer->id_default_group;

				// Merge all errors of this file and of the Object Model
				$this->errors = array_merge($this->errors, $this->customer->validateController());
			}
			
			if (!count($this->errors))
			{
				$this->customer->id_default_group = (int)$prev_id_default_group;
				$this->customer->firstname = Tools::ucwords(Tools::getValue('firstname'));
				$this->customer->secondname = Tools::ucwords(Tools::getValue('secondname'));
				$this->customer->lastname = Tools::ucwords(Tools::getValue('lastname'));
				$this->customer->surname = Tools::ucwords(Tools::getValue('surname'));
				$this->customer->id_gender = Tools::getValue('id_gender');

				if (Configuration::get('PS_B2B_ENABLE'))
				{
					$this->customer->website = Tools::getValue('website'); // force update of website, even if box is empty, this allows user to remove the website
					$this->customer->company = Tools::getValue('company');
				}

				if (!Tools::getIsset('newsletter'))
					$this->customer->newsletter = 0;
				elseif (!$origin_newsletter && Tools::getIsset('newsletter'))
					if ($module_newsletter = Module::getInstanceByName('blocknewsletter'))
						if ($module_newsletter->active)
							$module_newsletter->confirmSubscription($this->customer->email);

				if (!Tools::getIsset('optin'))
					$this->customer->optin = 0;
				if (Tools::getValue('passwd'))
					$this->context->cookie->passwd = $this->customer->passwd;
				if ($this->customer->update())
				{
					$this->context->cookie->customer_lastname = $this->customer->lastname;
					$this->context->cookie->customer_firstname = $this->customer->firstname;
					
					$this->context->cookie->customer_secondname = $this->customer->secondname;
					$this->context->cookie->customer_surname = $this->customer->surname;
					$this->context->cookie->customer_charter = $this->customer->charter;
					
					$this->context->smarty->assign('confirmation', 1);
				}
				else
					$this->errors[] = Tools::displayError('The information cannot be updated.');
			}
		}
		else
			$_POST = array_map('stripslashes', $this->customer->getFields());

		return $this->customer;
	}
	/**
	 * Assign template vars related to page content
	 * @see FrontController::initContent()
	 */
	public function initContent()
	{
		parent::initContent();

		if ($this->customer->birthday)
			$birthday = explode('-', $this->customer->birthday);
		else
			$birthday = array('-', '-', '-');

		/* Generate years, months and days */
		$this->context->smarty->assign(array(
				'years' => Tools::dateYears(),
				'sl_year' => $birthday[0],
				'months' => Tools::dateMonths(),
				'sl_month' => $birthday[1],
				'days' => Tools::dateDays(),
				'sl_day' => $birthday[2],
				'errors' => $this->errors,
				'genders' => Gender::getGenders(),
				'showpassword' => strtolower($this->customer->customer_acount_type) == 'facebook' ? 'hidden' : '',
				'facbookid' => $this->customer->facbookid,
			));

		// Call a hook to display more information
		$this->context->smarty->assign(array(
			'HOOK_CUSTOMER_IDENTITY_FORM' => Hook::exec('displayCustomerIdentityForm'),
		));

		$newsletter = Configuration::get('PS_CUSTOMER_NWSL') || (Module::isInstalled('blocknewsletter') && Module::getInstanceByName('blocknewsletter')->active);
		$this->context->smarty->assign('newsletter', $newsletter);
		$this->context->smarty->assign('optin', (bool)Configuration::get('PS_CUSTOMER_OPTIN'));

		$this->context->smarty->assign('field_required', $this->context->customer->validateFieldsRequiredDatabase());

		$this->setTemplate(_PS_THEME_DIR_.'identity.tpl');
	}

	public function setMedia()
	{
		parent::setMedia();
		$this->addCSS(_THEME_CSS_DIR_.'identity.css');
		$this->addJS(_PS_JS_DIR_.'validate.js');
	}

}
