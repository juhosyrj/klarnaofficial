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
{if $klarna_footer_layout == 'blue-black' || $klarna_footer_layout == 'white' || $klarna_footer_layout == 'blue+tuv' || $klarna_footer_layout == 'white+tuv'}
    <div class="klarna-widget klarna-logo-tooltip"
        data-eid="{$kco_footer_eid|escape:'html':'UTF-8'}"
        data-locale="{$kco_footer_locale|escape:'html':'UTF-8'}"
        data-logo-name="{$klarna_footer_layout|escape:'html':'UTF-8'}"
        data-logo-width="120">
    </div>
{else}
    {if ($kco_footer_locale|escape:'html':'UTF-8' == 'nl_nl')}
        <img src="https://cdn.klarna.com/1.0/shared/image/generic/logo/nl_nl/basic/blue-black.png?width=100" />
    {elseif ($kco_footer_locale|escape:'html':'UTF-8' == 'da_dk')}
        <div class="klarna-widget klarna-logo-tooltip"
            data-eid="{$kco_footer_eid|escape:'html':'UTF-8'}"
            data-locale="da_dk"
            data-logo-name="blue-black"
            data-logo-width="120">
        </div>
    {else}
        <div class="klarna-widget klarna-{if $kco_footer_active}badge{else}logo{/if}-tooltip"
            data-eid="{$kco_footer_eid|escape:'html':'UTF-8'}"
            data-locale="{$kco_footer_locale|escape:'html':'UTF-8'}"
            data-badge-name="{$klarna_footer_layout|escape:'html':'UTF-8'}"
            data-badge-width="385">
        </div>
    {/if}
{/if}
<script async src="https://cdn.klarna.com/1.0/code/client/all.js"></script>