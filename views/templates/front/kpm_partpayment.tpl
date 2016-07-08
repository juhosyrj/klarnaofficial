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
{capture name=path}{l s='Pay by part payment' mod='klarnaofficial'}{/capture}

{if isset($errormsg)}<div class="alert alert-warning">{$errormsg|escape:'html':'UTF-8'}</div>{/if}

<h1 class="page-heading">{l s='Pay by part payment' mod='klarnaofficial'}</h1>

<div class="box cheque-box">
	<h3 class="page-subheading">{l s='Fill out your information below' mod='klarnaofficial'}</h3>
	<div id="kpmresult" class="alert alert-warning"></div>
	
	<form action="{$kpm_postback_url|escape:'htmlall':'UTF-8'}" class="std" method="post">
	<div class="row">
		<div class="col-xs-12 col-lg-8">
		{foreach from=$kpm_pclasses item=kpm_plan}
				<input type="radio" value="{$kpm_plan.pclass_id|escape:'htmlall':'UTF-8'}" id="{$kpm_plan.pclass_id|escape:'htmlall':'UTF-8'}" name="kpm_pclass"{if $kpm_plan.pclass_id==$kpm_pclass} checked="checked"{/if} />
				<label for="{$kpm_plan.pclass_id|escape:'htmlall':'UTF-8'}" class="kpm_description">{$kpm_plan.title|escape:'htmlall':'UTF-8'}</label> <label class="kpm_description_full" for="{$kpm_plan.pclass_id|escape:'htmlall':'UTF-8'}">{$kpm_plan.extra_info|escape:'htmlall':'UTF-8'}{if $kpm_plan.pclass_id==-1} <strong>{l s='(Invoice)' mod='klarnaofficial'}</strong> {l s='Invoice fee' mod='klarnaofficial'} {convertPrice price=$klarna_invoice_fee}{/if}</label>
				<a href="{$kpm_plan.terms.uri|escape:'htmlall':'UTF-8'}{if $kpm_plan.pclass_id==-1}{$klarna_invoice_fee|escape:'htmlall':'UTF-8'}{/if}" target="_blank" class="kpm_terms_link">({l s='Terms' mod='klarnaofficial'})</a>
				{if $kpm_plan.use_case != ''}<br /><label class="alert alert-success">{$kpm_plan.use_case|escape:'quotes':'UTF-8'}</label>{/if}<br />
		{/foreach}
        {if isset($special_usecase)}
        <label class="alert alert-success">{$special_usecase|escape:'quotes':'UTF-8'}</label>
        {/if}
		</div>
		
		<div class="col-xs-12 col-lg-4">
		<p class="required">
				<sup>*</sup>{l s='Required fields' mod='klarnaofficial'}
		</p>
		
		<fieldset>
			{if $kpm_fields.ssn}
				<div class="required form-group">
					<label for="kpm_ssn" class="required">{l s='Social security number' mod='klarnaofficial'}</label>
					<div class="button-group-space">
						<input type="text" id="kpm_ssn" name="kpm_ssn" class="form-control" value="{$kpm_ssn|escape:'htmlall':'UTF-8'}" />
						{if $kpm_iso_code=='SE' || $kpm_iso_code=='se'}<input type="button" value="{l s='Fetch' mod='klarnaofficial'}" onclick="javascript:kpmfetchaddress($('#kpm_ssn').val());" id="kpmgetaddress" name="getaddress" class="btn btn-default button-getaddress" />{/if}
					</div>
				</div>
			{/if}
			{if $kpm_fields.birthdate}
				<div class="form-group">
					<label for="kpm_birthdate">{l s='Birthdate' mod='klarnaofficial'}</label>
					<input type="text" id="kpm_birthdate" name="kpm_birthdate" class="form-control" value="{$kpm_birthdate|escape:'htmlall':'UTF-8'}" />
				</div>
			{/if}
			{if $kpm_fields.gender}
				<div class="form-group">
					<label for="kpm_gender">{l s='Gender' mod='klarnaofficial'}</label>
					<select class="form-control" name="kpm_gender" id="kpm_gender">
					<option value="1"{if $kpm_gender==1} selected="selected"{/if}>{l s='Male' mod='klarnaofficial'}</option>
					<option value="2"{if $kpm_gender==1} selected="selected"{/if}>{l s='Female' mod='klarnaofficial'}</option>
					</select>
				</div>
			{/if}
			{if $kpm_fields.firstname}
				<div class="form-group required">
					<label for="kpm_firstname" class="required">{l s='Firstname' mod='klarnaofficial'}</label>
					<input type="text" id="kpm_firstname" class="form-control" name="kpm_firstname" value="{$kpm_firstname|escape:'htmlall':'UTF-8'}" />
				</div>
			{/if}
			{if $kpm_fields.lastname}
				<div class="form-group required">
					<label for="kpm_lastname" class="required">{l s='Lastname' mod='klarnaofficial'}</label>
					<input type="text" id="kpm_lastname" class="form-control" name="kpm_lastname" value="{$kpm_lastname|escape:'htmlall':'UTF-8'}" />
				</div>
			{/if}
			<div class="form-group">
				<label for="kpm_company">{l s='Company' mod='klarnaofficial'}</label>
				<input type="text" id="kpm_company" class="form-control" name="kpm_company" value="{$kpm_company|escape:'htmlall':'UTF-8'}" />
			</div>
			{if $kpm_fields.streetname}
				<div class="form-group">
					<label for="kpm_coname">{l s='c/o address' mod='klarnaofficial'}</label>
					<input type="text" id="kpm_coname" class="form-control" name="kpm_coname" value="{$kpm_coname|escape:'htmlall':'UTF-8'}" />
				</div>
			{/if}
			{if $kpm_fields.streetname}
				<div class="form-group required">
					<label for="kpm_streetname" class="required">{l s='Street name' mod='klarnaofficial'}</label>
					<input type="text" id="kpm_streetname" class="form-control" name="kpm_streetname" value="{$kpm_streetname|escape:'htmlall':'UTF-8'}" />
				</div>
			{/if}
			{if $kpm_fields.housenumber}
				<div class="form-group">
					<label for="kpm_housenumber">{l s='House number' mod='klarnaofficial'}</label>
					<input type="text" id="kpm_housenumber" class="form-control" name="kpm_housenumber" value="{$kpm_housenumber|escape:'htmlall':'UTF-8'}" />
				</div>
			{/if}
			{if $kpm_fields.housenumberext}
				<div class="form-group">
					<label for="kpm_housenumberext">{l s='House number extension' mod='klarnaofficial'}</label>
					<input type="text" id="kpm_housenumberext" class="form-control" name="kpm_housenumberext" value="{$kpm_housenumberext|escape:'htmlall':'UTF-8'}" />
				</div>
			{/if}
			{if $kpm_fields.zipcode}
				<div class="form-group required">
					<label for="kpm_zipcode" class="required">{l s='Zip code' mod='klarnaofficial'}</label>
					<input type="text" id="kpm_zipcode" class="form-control" name="kpm_zipcode" value="{$kpm_zipcode|escape:'htmlall':'UTF-8'}" />
				</div>
			{/if}
			{if $kpm_fields.city}
				<div class="form-group required">
					<label for="kpm_city" class="required">{l s='City' mod='klarnaofficial'}</label>
					<input type="text" id="kpm_city" class="form-control" name="kpm_city" value="{$kpm_city|escape:'htmlall':'UTF-8'}" />
				</div>
			{/if}
			{if $kpm_fields.country}
				<div class="form-group required">
					<label for="kpm_country" class="required">{l s='Country' mod='klarnaofficial'}</label>
					<input type="text" id="kpm_country" class="form-control" name="kpm_country" value="{$kpm_country|escape:'htmlall':'UTF-8'}" />
				</div>
			{/if}
			{if $kpm_fields.phone}
				<div class="form-group">
					<label for="kpm_phone">{l s='Phone' mod='klarnaofficial'}</label>
					<input type="text" id="kpm_phone" class="form-control" name="kpm_phone" value="{$kpm_phone|escape:'htmlall':'UTF-8'}" />
				</div>
			{/if}
			{if $kpm_fields.mobilephone}
				<div class="form-group required">
					<label for="kpm_mobilephone" class="required">{l s='Mobile phone' mod='klarnaofficial'}</label>
					<input type="text" id="kpm_mobilephone" class="form-control" name="kpm_mobilephone" value="{$kpm_mobilephone|escape:'htmlall':'UTF-8'}" />
				</div>
			{/if}
			{if $kpm_fields.email}
				<div class="form-group required">
					<label for="kpm_email" class="required">{l s='E-mail' mod='klarnaofficial'}</label>
					<input type="text" id="kpm_email" class="form-control" name="kpm_email" value="{$kpm_email|escape:'htmlall':'UTF-8'}" />
				</div>
			{/if}
			<p>
				{l s='All deliveries will be to your registered address from the country registry.' mod='klarnaofficial'}
			</p>
			<br />
			<input type="hidden" name="kpmshipping" value="{l s='Shipping' mod='klarnaofficial'}" />
			<input type="hidden" name="kpmwrapping" value="{l s='Gift wrapping' mod='klarnaofficial'}" />
		</fieldset>
		</div>
	</div>
</div>
	<p class="cart_navigation clearfix">
		<a class="button-exclusive btn btn-default" href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html':'UTF-8'}"><i class="icon-chevron-left"></i>{l s='Other payment methods' mod='klarnaofficial'}</a>
			<button name="confirmkpm" id="confirmkpm" class="button btn btn-default button-medium{if $kpm_iso_code=='DE' || $kpm_iso_code=='de'} hidden{/if}" type="submit" onclick="javascript:$('#confirmkpm').hide();">
				<span>{l s='I confirm my order' mod='klarnaofficial'}<i class="icon-chevron-right right"></i></span>
			</button>
	</p>
</form>
<script type="text/javascript">
{literal}
$(".kpm_terms_link").fancybox({'width':'360px','autoScale':true,'transitionIn':'swing','transitionOut':'swing','type':'iframe'});
{/literal}
</script>
{addJsDef kpm_md5key=$kpm_md5key}
{addJsDef kpm_getaddress_url=$kpm_getaddress_url}