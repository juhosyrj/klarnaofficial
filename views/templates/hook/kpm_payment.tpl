{*
* 2015 Prestaworks AB
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to info@prestaworks.se so we can send you a copy immediately.
*
*  @author    Prestaworks AB <info@prestaworks.se>
*  @copyright 2015 Prestaworks AB
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of Prestaworks AB
*}
{if $KCO_SHOW_IN_PAYMENTS}
<div class="row">
	<div class="col-xs-12 col-md-12">
        <p class="payment_module">
            <a 
            class="klarnacheckout_account" 
            href="{$link->getModuleLink('klarnaofficial', 'checkoutklarna')|escape:'html':'UTF-8'}" 
            title="{l s='Klarna Checkout' mod='klarnaofficial'}">
            	<img src="https://cdn.klarna.com/1.0/shared/image/generic/logo/{$KPM_LOGO_ISO_CODE|escape:'htmlall':'UTF-8'}/basic/{$KPM_LOGO|escape:'htmlall':'UTF-8'}.png?width=200" />
				{l s='Klarna Checkout' mod='klarnaofficial'}
            </a>
        </p>
    </div>
</div>
{/if}
<div class="row">
	<div class="col-xs-12 col-md-12">
        <p class="payment_module">
            <a 
            class="klarnacheckout_account" 
            href="{$link->getModuleLink('klarnaofficial', 'kpmpartpayment')|escape:'html':'UTF-8'}" 
            title="{l s='Pay by Invoice / Partpayment' mod='klarnaofficial'}">
            	<img src="https://cdn.klarna.com/1.0/shared/image/generic/logo/{$KPM_LOGO_ISO_CODE|escape:'htmlall':'UTF-8'}/basic/{$KPM_LOGO|escape:'htmlall':'UTF-8'}.png?width=200" />
				{l s='Pay by Invoice / Partpayment' mod='klarnaofficial'}
            </a>
        </p>
    </div>
</div>