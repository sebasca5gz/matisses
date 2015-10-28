{*
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
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
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<!-- Pro Block Newsletter module-->
<div id="newsletter_block_left" class="col-sm-8 col-xs-12">
    <h4>{l s='Subscribe to our newletter' mod='problocknewsletter'}</h4>
    <div class="block_content">
        <form action="{$link->getPageLink('index')|escape:'html':'UTF-8'}" method="post">
            <div class="form-group{if isset($msg) && $msg } {if $nw_error}form-error{else}form-ok{/if}{/if}" >
                <input class="inputNew form-control grey newsletter-input" id="newsletter-input" type="text" name="email" size="18" value="{if isset($msg) && $msg}{$msg}{elseif isset($value) && $value}{$value}{else}{l s='Enter your e-mail' mod='problocknewsletter'}{/if}" />
                <button type="submit" name="submitNewsletter" class="btn_standard">
                    {l s='Subscribe' mod='problocknewsletter'}
                </button>
                <input type="hidden" name="action" value="0" />
            </div>
        </form>
    </div>
</div>
<!-- /Pro Block Newsletter module-->
{strip}
{if isset($msg) && $msg}
{addJsDef msg_newsl=$msg|@addcslashes:'\''}
{/if}
{if isset($nw_error)}
{addJsDef nw_error=$nw_error}
{/if}
{addJsDefL name=placeholder_problocknewsletter}{l s='Enter your e-mail' mod='problocknewsletter' js=1}{/addJsDefL}
{if isset($msg) && $msg}
    {addJsDefL name=alert_problocknewsletter}{l s='Newsletter : %1$s' sprintf=$msg js=1 mod="problocknewsletter"}{/addJsDefL}
{/if}
{/strip}
