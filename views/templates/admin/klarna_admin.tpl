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
{if $address_check_done}		
	<div class="alert alert-success">
		{l s='Address check done!' mod='klarnaofficial'}
	</div>
{/if}
{if $isSaved}	
	<div class="alert alert-success">
		{l s='Settings updated' mod='klarnaofficial'}
	</div>
{/if}
{if $errorMSG!=''}	
	<div class="alert alert-danger">
		 {$errorMSG|escape:'htmlall':'UTF-8'}
	</div>
{/if}
{if $invoice_fee_not_found}
	<div class="alert alert-danger">
		{l s='Invoice fee product not found!' mod='klarnaofficial'}
	</div>
{/if}
<link href="{$module_dir|escape:'htmlall':'UTF-8'}views/css/klarnacheckout_admin.css" rel="stylesheet" type="text/css" media="all" />
<script type="text/javascript" src="{$module_dir|escape:'htmlall':'UTF-8'}views/js/admin.js"></script>

<div class="row">
<div class="col-xs-4">
	<div class="panel">
		<div class="panel-heading"><i class="icon-cogs"></i> {l s='What does it support?' mod='klarnaofficial'}</div>
		<div class="row">
			<p>{l s='Use the tabs below to navigate and activate the different payment methods depending on what you want to use. This integration supports Klarna KPM and Klarna Checkout. There is also a few other settings to laborate with, feel free to use whatever suits you best!' mod='klarnaofficial'}</p>
		</div>
	</div>
</div>
<div class="col-xs-2">
	<div class="panel">
		<div class="panel-heading"><i class="icon-question"></i> {l s='Documentation' mod='klarnaofficial'}</div>
		<div class="row">
			<p>{l s='Link and information to documentation comes here...' mod='klarnaofficial'}</p>
			<p>
				<a href="{$module_dir|escape:'htmlall':'UTF-8'}doc/index.html" target="_blank" id="fancydocs" class="btn btn-default" title="{l s='Read documentation here' mod='klarnaofficial'}">
					<i class="icon-file-text"></i> {l s='Read documentation here' mod='klarnaofficial'}
				</a>
			</p>
		</div>
	</div>
</div>
<div class="col-xs-2">
	<div class="panel">
		<div class="panel-heading"><i class="icon-info"></i> {l s='Compatibility information' mod='klarnaofficial'}</div>
		<div class="row">
			<p>{l s='This core module for Klarna API was developed for and is compatible with:' mod='klarnaofficial'}</p>
			<p><span class="label label-success">PrestaShop 1.6.x</span></p>
		</div>
	</div>
</div>
<div class="col-xs-2">
	<div class="panel join">
		<div class="panel-heading"><i class="icon-send"></i> {l s='Join together with Klarna' mod='klarnaofficial'}</div>
		<div class="row">
			<p>{l s='Don\'t you have an account with Klarna yet? Hit the button below!' mod='klarnaofficial'}</p>
			<p>
                <a href="https://www.klarna.com/international/business/prestashop" target="_blank" class="btn btn-default" title="{l s='Register account here' mod='klarnaofficial'}">
					<i class="icon-send"></i> {l s='Register Klarna account here' mod='klarnaofficial'}
				</a>
			</p>
		</div>
	</div>
</div>
</div>

<div class="tabbable">
	<ul class="nav nav-tabs">
		<li class="active"><a href="#pane1" data-toggle="tab"><i class="icon-AdminParentOrders"></i> {l s='Klarna Checkout (KCO)' mod='klarnaofficial'}</a></li>
		<li><a href="#pane2" data-toggle="tab"><i class="icon-AdminParentOrders"></i> {l s='Klarna Payment Method (KPM)' mod='klarnaofficial'}</a></li>
		<li><a href="#pane3" data-toggle="tab"><i class="icon-cogs"></i> {l s='Common settings' mod='klarnaofficial'}</a></li>
		<li><a href="#pane4" data-toggle="tab"><i class="icon-list-alt"></i> {l s='Pclasses' mod='klarnaofficial'}</a></li>
		<li><a href="#pane5" data-toggle="tab"><i class="icon-list-alt"></i> {l s='Terms and Conditions' mod='klarnaofficial'}</a></li>
		<li><a href="#pane6" data-toggle="tab"><i class="icon-list-alt"></i> {l s='Setup' mod='klarnaofficial'}</a></li>
	</ul>
	<div class="panel">
	<div class="tab-content">
		<div id="pane1" class="tab-pane active">
			<div class="tabbable row klarnacheckout-admin">
				<div class="col-lg-12 tab-content">
					<div class="sidebar col-lg-2">
						<ul class="nav nav-tabs">
							<li class="nav-item"><a href="javascript:;" title="{l s='General settings' mod='klarnaofficial'}" data-panel="1" data-fieldset="0"><i class="icon-AdminAdmin"></i>{l s='General settings' mod='klarnaofficial'}</a></li>
							<li class="nav-item"><a href="javascript:;" title="{l s='Color settings' mod='klarnaofficial'}" data-panel="1" data-fieldset="1"><i class="icon-AdminParentPreferences"></i>{l s='Color settings' mod='klarnaofficial'}</a></li>
							<li class="nav-item"><a href="javascript:;" title="{l s='Country settings' mod='klarnaofficial'}" data-panel="1" data-fieldset="2"><i class="icon-AdminParentLocalization"></i>{l s='Sweden' mod='klarnaofficial'}</a></li>
							<li class="nav-item"><a href="javascript:;" title="{l s='Country settings' mod='klarnaofficial'}" data-panel="1" data-fieldset="3"><i class="icon-AdminParentLocalization"></i>{l s='Norway' mod='klarnaofficial'}</a></li>
							<li class="nav-item"><a href="javascript:;" title="{l s='Country settings' mod='klarnaofficial'}" data-panel="1" data-fieldset="4"><i class="icon-AdminParentLocalization"></i>{l s='Finland' mod='klarnaofficial'}</a></li>
							<li class="nav-item"><a href="javascript:;" title="{l s='Country settings' mod='klarnaofficial'}" data-panel="1" data-fieldset="5"><i class="icon-AdminParentLocalization"></i>{l s='Germany' mod='klarnaofficial'}</a></li>
							<li class="nav-item"><a href="javascript:;" title="{l s='Country settings' mod='klarnaofficial'}" data-panel="1" data-fieldset="7"><i class="icon-AdminParentLocalization"></i>{l s='Austria' mod='klarnaofficial'}</a></li>
							<li class="nav-item"><a href="javascript:;" title="{l s='Country settings' mod='klarnaofficial'}" data-panel="1" data-fieldset="6"><i class="icon-AdminParentLocalization"></i>{l s='UK' mod='klarnaofficial'}</a></li>
						</ul>
					</div>
					<div id="klarnacheckout-admin" class="col-lg-10">
						{$kcoform}
					</div>
				</div>
			</div>
		</div>
		<div id="pane2" class="tab-pane">
			<div class="tabbable row klarnacheckout-admin">
				<div class="col-lg-12 tab-content">
					<div class="sidebar col-lg-2">
						<ul class="nav nav-tabs">
							<li class="nav-item"><a href="javascript:;" title="{l s='General settings' mod='klarnaofficial'}" data-panel="2" data-fieldset="0"><i class="icon-AdminAdmin"></i>{l s='General settings' mod='klarnaofficial'}</a></li>
							<li class="nav-item"><a href="javascript:;" title="{l s='Country settings' mod='klarnaofficial'}" data-panel="2" data-fieldset="1"><i class="icon-AdminParentLocalization"></i>{l s='Sweden' mod='klarnaofficial'}</a></li>
							<li class="nav-item"><a href="javascript:;" title="{l s='Country settings' mod='klarnaofficial'}" data-panel="2" data-fieldset="2"><i class="icon-AdminParentLocalization"></i>{l s='Norway' mod='klarnaofficial'}</a></li>
							<li class="nav-item"><a href="javascript:;" title="{l s='Country settings' mod='klarnaofficial'}" data-panel="2" data-fieldset="3"><i class="icon-AdminParentLocalization"></i>{l s='Finland' mod='klarnaofficial'}</a></li>
							<li class="nav-item"><a href="javascript:;" title="{l s='Country settings' mod='klarnaofficial'}" data-panel="2" data-fieldset="4"><i class="icon-AdminParentLocalization"></i>{l s='Denmark' mod='klarnaofficial'}</a></li>
							<li class="nav-item"><a href="javascript:;" title="{l s='Country settings' mod='klarnaofficial'}" data-panel="2" data-fieldset="5"><i class="icon-AdminParentLocalization"></i>{l s='Germany' mod='klarnaofficial'}</a></li>
							<li class="nav-item"><a href="javascript:;" title="{l s='Country settings' mod='klarnaofficial'}" data-panel="2" data-fieldset="6"><i class="icon-AdminParentLocalization"></i>{l s='Netherlands' mod='klarnaofficial'}</a></li>
							<li class="nav-item"><a href="javascript:;" title="{l s='Country settings' mod='klarnaofficial'}" data-panel="2" data-fieldset="7"><i class="icon-AdminParentLocalization"></i>{l s='Austria' mod='klarnaofficial'}</a></li>
						</ul>
					</div>
					<div id="klarnacheckout-admin" class="col-lg-10">
						{$kpmform}
					</div>
				</div>
			</div>
		</div>
		<div id="pane3" class="tab-pane">
			<div class="tabbable row klarnacheckout-admin">
				<div class="col-lg-12 tab-content">
					<div class="sidebar col-lg-2" style="display: none;">
						<ul class="nav nav-tabs">
							<li class="nav-item"><a href="javascript:;" title="{l s='General settings' mod='klarnaofficial'}" data-panel="3" data-fieldset="0"><i class="icon-AdminAdmin"></i>{l s='General settings' mod='klarnaofficial'}</a></li>
						</ul>
					</div>
					<div id="klarnacheckout-admin" class="col-lg-12">
						{$commonform}
					</div>
				</div>
			</div>
		</div>
		<div id="pane4" class="tab-pane">
			{$pclasslist}
		</div>
		
		<div id="pane5" class="tab-pane">
			<h3>{l s='Germany' mod='klarnaofficial'}</h3>
			<p>{l s='The following text needs to be present in your terms and conditions page under AGP/Payments.' mod='klarnaofficial'}</p>
			
				In Zusammenarbeit mit Klarna bieten wir die folgenden Zahlungsoptionen an. <br />
				Die Zahlung erfolgt jeweils an Klarna:
			<ul>
				<li>Klarna Rechnung: Zahlbar innerhalb von 14 Tagen ab Rechnungsdatum. Die
				Rechnung wird bei Versand der Ware ausgestellt und per Email
				übersandt. Die Rechnungsbedingungen finden Sie hier (https://cdn.klarna.com/1.0/shared/content/legal/terms/<strong style="color:#00aff0">EID</strong>/de_de/invoice?fee=0).</li>
				<li>Klarna Ratenkauf: Mit dem Finanzierungsservice von Klarna können Sie Ihren Einkauf
				flexibel in monatlichen Raten von mindestens 1/24 des Gesamtbetrages (mindestens
				jedoch 6,95 EUR) bezahlen. Weitere Informationen zum Klarna Ratenkauf
				einschließlich der Allgemeinen Geschäftsbedingungen und der europäischen
				Standardinformationen für Verbraucherkredite finden Sie hier. (https://cdn.klarna.com/1.0/shared/content/legal/terms/<strong style="color:#00aff0">EID</strong>/de_de/account)</li>
				<li>Sofortüberweisung</li>
				<li>Kreditkarte (Visa/ Mastercard)</li>
				<li>Lastschrift</li>
			</ul>
				Die Zahlungsoptionen werden im Rahmen von Klarna Checkout angeboten. Nähere
				Informationen und die Nutzungsbedingungen für Klarna Checkout finden Sie hier(https://cdn.klarna.com/1.0/shared/content/legal/terms/<strong style="color:#00aff0">EID</strong>/de_de/checkout).
				Allgemeine Informationen zu Klarna erhalten Sie hier (https://www.klarna.com/de).
			<br />
				Ihre Personenangaben werden von Klarna in Übereinstimmung mit den geltenden Datenschutzbestimmungen und entsprechend den Angaben in Klarnas Datenschutzbestimmungen behandelt (https://cdn.klarna.com/1.0/shared/content/policy/data/de_at/data_protection.pdf).
		</div>
		
		<div id="pane6" class="tab-pane">
			<h3>{l s='Setup' mod='klarnaofficial'}</h3>
			<p>{l s='The following button will run a setup check and see if all default addresses is set up correctly for this shop.' mod='klarnaofficial'}</p>
			<div class="form-wrapper">
				<form class="defaultForm form-horizontal" method="post" action="index.php?controller=AdminModules&configure=klarnaofficial&token={$smarty.get.token|escape:'htmlall':'UTF-8'}&module_name=klarnaofficial">
				<input type="hidden" name="runcheckup" value="1" />
			</div>
			<div class="panel-footer">
				<button id="module_form_submit_btn" class="btn btn-default pull-right" name="btnRunaddressCheckSubmit" value="1" type="submit">
					<i class="process-icon-save"></i>{l s='Run address check' mod='klarnaofficial'}</button>
				</form>
			</div>
		</div>
		
	</div>
	</div>
</div>