/*
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
*/
$(document).ready(function()
{
	$("#kco_cart_summary_div a.cart_quantity_delete").unbind('click').live('click', function(){ deleteProductFromSummary($(this).attr('id')); return false; });
	$("#kco_cart_summary_div a.cart_quantity_up").unbind('click').live('click', function(){ upQuantity($(this).attr('id').replace('cart_quantity_up_', '')); return false;	});
	$("#kco_cart_summary_div a.cart_quantity_down").unbind('click').live('click', function(){ downQuantity($(this).attr('id').replace('cart_quantity_down_', '')); return false; });
	//$("#kco_cart_summary_div a.cart_quantity_input").typeWatch({ highlight: true, wait: 600, captureLength: 0, callback: function(val) { updateQty(val, true, this.el); } });
});

function showLoaderImg()
{
    $("#checkoutdiv").html('');
}
function downQuantity(id, qty)
{
    showLoaderImg();
	var val = $('input[name=quantity_'+id+']').val();
	var newVal = val;
	if(typeof(qty) === 'undefined' || !qty)
	{
		qty = 1;
		newVal = val - 1;
	}
	else if (qty < 0)
		qty = -qty;
	
	var customizationId = 0;
	var productId = 0;
	var productAttributeId = 0;
	var id_address_delivery = 0;
	var ids = 0;
	
	ids = id.split('_');
	productId = parseInt(ids[0]);
	if (typeof(ids[1]) !== 'undefined')
		productAttributeId = parseInt(ids[1]);
	if (typeof(ids[2]) !== 'undefined')
		customizationId = parseInt(ids[2]);
	if (typeof(ids[3]) !== 'undefined')
		id_address_delivery = parseInt(ids[3]);

	if (newVal > 0 || $('#product_'+id+'_gift').length)
	{
		$.ajax({
			type: 'GET',
			url: baseUri,
			async: true,
			cache: false,
			dataType: 'json',
			data: 'controller=cart'
				+'&ajax=true'
				+'&add'
				+'&getproductprice'
				+'&summary'
				+'&id_product='+productId
				+'&ipa='+productAttributeId
				+'&id_address_delivery='+id_address_delivery
				+'&op=down'
				+ ((customizationId !== 0) ? '&id_customization='+customizationId : '')
				+'&qty='+qty
				+'&token='+static_token
				+'&allow_refresh=1',
			success: function(jsonData)
			{
				if (jsonData.hasError)
				{
					var errors = '';
					for(var error in jsonData.errors)
						//IE6 bug fix
						if(error !== 'indexOf')
							errors += jsonData.errors[error] + "\n";
					alert(errors);
					$('input[name=quantity_'+ id +']').val($('input[name=quantity_'+ id +'_hidden]').val());
				}
				else
				{
					if (jsonData.refresh)
						location.reload();
					updateCartSummary(jsonData.summary);
					//updateCustomizedDatas(jsonData.customizedDatas);
					updateHookShoppingCart(jsonData.HOOK_SHOPPING_CART);
					updateHookShoppingCartExtra(jsonData.HOOK_SHOPPING_CART_EXTRA);
					
					if (newVal === 0)
						$('#product_'+id).hide();
					
					if (typeof(getCarrierListAndUpdate) !== 'undefined')
						getCarrierListAndUpdate();
				}
			},
			error: function(XMLHttpRequest, textStatus, errorThrown) {
				if (textStatus !== 'abort')
					alert("TECHNICAL ERROR: unable to save update quantity \n\nDetails:\nError thrown: " + XMLHttpRequest + "\n" + 'Text status: ' + textStatus);
			}
		});

	}
	else
	{
		deleteProductFromSummary(id);
	}
}

function upQuantity(id, qty)
{
    showLoaderImg();
	if (typeof(qty) === 'undefined' || !qty)
		qty = 1;
	var customizationId = 0;
	var productId = 0;
	var productAttributeId = 0;
	var id_address_delivery = 0;
	var ids = 0;
	ids = id.split('_');
	productId = parseInt(ids[0]);
	if (typeof(ids[1]) !== 'undefined')
		productAttributeId = parseInt(ids[1]);
	if (typeof(ids[2]) !== 'undefined')
		customizationId = parseInt(ids[2]);
	if (typeof(ids[3]) !== 'undefined')
		id_address_delivery = parseInt(ids[3]);
	$.ajax({
		type: 'GET',
		url: baseUri,
		async: true,
		cache: false,
		dataType: 'json',
		data: 'controller=cart'
			+'&ajax=true'
			+'&add'
			+'&getproductprice'
			+'&summary'
			+'&id_product='+productId
			+'&ipa='+productAttributeId
			+'&id_address_delivery='+id_address_delivery
			+ ( (customizationId !== 0) ? '&id_customization='+customizationId : '')
			+'&qty='+qty
			+'&token='+static_token
			+'&allow_refresh=1',
		success: function(jsonData)
		{
			if (jsonData.hasError)
			{
				var errors = '';
				for(var error in jsonData.errors)
					//IE6 bug fix
					if(error !== 'indexOf')
						errors += jsonData.errors[error] + "\n";
				alert(errors);
				$('input[name=quantity_'+ id +']').val($('input[name=quantity_'+ id +'_hidden]').val());
			}
			else
			{
				if (jsonData.refresh)
					location.reload();
				updateCartSummary(jsonData.summary);
				//updateCustomizedDatas(jsonData.customizedDatas);
				updateHookShoppingCart(jsonData.HOOK_SHOPPING_CART);
				updateHookShoppingCartExtra(jsonData.HOOK_SHOPPING_CART_EXTRA);
				if (typeof(getCarrierListAndUpdate) !== 'undefined')
					getCarrierListAndUpdate();
			}
		},
		error: function(XMLHttpRequest, textStatus, errorThrown) {
			if (textStatus !== 'abort')
				alert("TECHNICAL ERROR: unable to save update quantity \n\nDetails:\nError thrown: " + XMLHttpRequest + "\n" + 'Text status: ' + textStatus);
		}
	});
}


function updateCartSummary(json)
{
	// Update products prices + discount
	var i;
	var nbrProducts = 0;

	if (typeof json === 'undefined')
		return;

	$('.cart_quantity_input').val(0);

	product_list = {};
	for (i=0;i<json.products.length;i++)
		product_list[json.products[i].id_product+'_'+json.products[i].id_product_attribute+'_'+json.products[i].id_address_delivery] = json.products[i];

	if (!$('.multishipping-cart:visible').length)
	{
		for (i=0;i<json.gift_products.length;i++)
		{
			if (typeof(product_list[json.gift_products[i].id_product+'_'+json.gift_products[i].id_product_attribute+'_'+json.gift_products[i].id_address_delivery]) !== 'undefined')
				product_list[json.gift_products[i].id_product+'_'+json.gift_products[i].id_product_attribute+'_'+json.gift_products[i].id_address_delivery].quantity -= json.gift_products[i].cart_quantity;
		}
	}
	else
	{
		for (i=0;i<json.gift_products.length;i++)
		{
			if (typeof(product_list[json.gift_products[i].id_product+'_'+json.gift_products[i].id_product_attribute+'_'+json.gift_products[i].id_address_delivery]) === 'undefined')
				product_list[json.gift_products[i].id_product+'_'+json.gift_products[i].id_product_attribute+'_'+json.gift_products[i].id_address_delivery] = json.gift_products[i];
		}
	}

	for (i in product_list)
	{
		// if reduction, we need to show it in the cart by showing the initial price above the current one
		var reduction = product_list[i].reduction_applies;
		var reduction_type = product_list[i].reduction_type;
		var reduction_symbol = '';
		var initial_price_text = '';
		var initial_price = '';

		if (typeof(product_list[i].price_without_quantity_discount) !== 'undefined')
			initial_price = formatCurrency(product_list[i].price_without_quantity_discount, currencyFormat, currencySign, currencyBlank);

		var current_price = '';
		var product_total = '';
		var product_customization_total = '';

		if (priceDisplayMethod !== 0)
		{
			current_price = formatCurrency(product_list[i].price, currencyFormat, currencySign, currencyBlank);
			product_total = product_list[i].total;
			product_customization_total = product_list[i].total_customization;
		}
		else
		{
			current_price = formatCurrency(product_list[i].price_wt, currencyFormat, currencySign, currencyBlank);
			product_total = product_list[i].total_wt;
			product_customization_total = product_list[i].total_customization_wt;
		}

		var current_price_class ='price';
		var price_reduction = '';
		if (reduction && typeof(initial_price) !== 'undefined')
		{
			if (reduction_type == 'amount')
				price_reduction = product_list[i].reduction_formatted;
			else
			{
				var display_price = 0;
				if (priceDisplayMethod !== 0)
					display_price = product_list[i].price;
				else
					display_price = product_list[i].price_wt;

				price_reduction = ps_round((product_list[i].price_without_quantity_discount - display_price)/product_list[i].price_without_quantity_discount * -100);
				reduction_symbol = '%';
			}

			if (initial_price !== '' && product_list[i].price_without_quantity_discount > product_list[i].price)
			{
				initial_price_text = '<li class="price-percent-reduction small">&nbsp;'+price_reduction+reduction_symbol+'&nbsp;</li><li class="old-price">' + initial_price + '</li>';
				current_price_class += ' special-price';
			}
		}

		var key_for_blockcart = product_list[i].id_product + '_' + product_list[i].id_product_attribute + '_' + product_list[i].id_address_delivery;
		var key_for_blockcart_nocustom = product_list[i].id_product + '_' + product_list[i].id_product_attribute + '_' + ((product_list[i].id_customization && product_list[i].quantity_without_customization != product_list[i].quantity)? 'nocustom' : '0') + '_' + product_list[i].id_address_delivery;

		$('#product_price_' + key_for_blockcart).html('<li class="' + current_price_class + '">' + current_price + '</li>' + initial_price_text);
		if (typeof(product_list[i].customizationQuantityTotal) !== 'undefined' && product_list[i].customizationQuantityTotal > 0)
			$('#total_product_price_' + key_for_blockcart).html(formatCurrency(product_customization_total, currencyFormat, currencySign, currencyBlank));
		else
			$('#total_product_price_' + key_for_blockcart).html(formatCurrency(product_total, currencyFormat, currencySign, currencyBlank));
		if (product_list[i].quantity_without_customization != product_list[i].quantity)
			$('#total_product_price_' + key_for_blockcart_nocustom).html(formatCurrency(product_total, currencyFormat, currencySign, currencyBlank));

		$('input[name=quantity_' + key_for_blockcart_nocustom + ']').val(product_list[i].id_customization? product_list[i].quantity_without_customization : product_list[i].cart_quantity);
		$('input[name=quantity_' + key_for_blockcart_nocustom + '_hidden]').val(product_list[i].id_customization? product_list[i].quantity_without_customization : product_list[i].cart_quantity);
		if (typeof(product_list[i].customizationQuantityTotal) !== 'undefined' && product_list[i].customizationQuantityTotal > 0)
			$('#cart_quantity_custom_' + key_for_blockcart).html(product_list[i].customizationQuantityTotal);
		nbrProducts += parseInt(product_list[i].quantity);
	}

	// Update discounts
	if (json.discounts.length === 0)
	{
		$('.cart_discount').each(function(){$(this).remove();});
		$('.cart_total_voucher').remove();
	}
	else
	{
		if ($('.cart_discount').length === 0)
			location.reload();

		if (priceDisplayMethod !== 0)
			$('#total_discount').html(formatCurrency(json.total_discounts_tax_exc, currencyFormat, currencySign, currencyBlank));
		else
			$('#total_discount').html(formatCurrency(json.total_discounts, currencyFormat, currencySign, currencyBlank));

		$('.cart_discount').each(function(){
			var idElmt = $(this).attr('id').replace('cart_discount_','');
			var toDelete = true;

			for (i=0;i<json.discounts.length;i++)
			{
				if (json.discounts[i].id_discount === idElmt)
				{
					if (json.discounts[i].value_real !== '!')
					{
						if (priceDisplayMethod !== 0)
							$('#cart_discount_' + idElmt + ' td.cart_discount_price span.price-discount').html(formatCurrency(json.discounts[i].value_tax_exc * -1, currencyFormat, currencySign, currencyBlank));
						else
							$('#cart_discount_' + idElmt + ' td.cart_discount_price span.price-discount').html(formatCurrency(json.discounts[i].value_real * -1, currencyFormat, currencySign, currencyBlank));

					}
					toDelete = false;
				}
			}
			if (toDelete)
				$('#cart_discount_' + idElmt + ', #cart_total_voucher').fadeTo('fast', 0, function(){ $(this).remove(); });
		});
	}

	// Block cart
	if (priceDisplayMethod !== 0)
	{
		$('#cart_block_shipping_cost').html(formatCurrency(json.total_shipping_tax_exc, currencyFormat, currencySign, currencyBlank));
		$('#cart_block_wrapping_cost').html(formatCurrency(json.total_wrapping_tax_exc, currencyFormat, currencySign, currencyBlank));
		$('#cart_block_total').html(formatCurrency(json.total_price_without_tax, currencyFormat, currencySign, currencyBlank));
	} else {
		$('#cart_block_shipping_cost').html(formatCurrency(json.total_shipping, currencyFormat, currencySign, currencyBlank));
		$('#cart_block_wrapping_cost').html(formatCurrency(json.total_wrapping, currencyFormat, currencySign, currencyBlank));
		$('#cart_block_total').html(formatCurrency(json.total_price, currencyFormat, currencySign, currencyBlank));
	}

	$('#cart_block_tax_cost').html(formatCurrency(json.total_tax, currencyFormat, currencySign, currencyBlank));
	$('.ajax_cart_quantity').html(nbrProducts);

	// Cart summary
	$('#summary_products_quantity').html(nbrProducts+' '+(nbrProducts > 1 ? txtProducts : txtProduct));
	if (priceDisplayMethod !== 0)
	{
		$('#total_product').html(formatCurrency(json.total_products, currencyFormat, currencySign, currencyBlank));
		$('#kco_total_products').html(formatCurrency(json.total_products, currencyFormat, currencySign, currencyBlank));
	}
	else
	{
		$('#total_product').html(formatCurrency(json.total_products_wt, currencyFormat, currencySign, currencyBlank));
		$('#kco_total_products').html(formatCurrency(json.total_products_wt, currencyFormat, currencySign, currencyBlank));
	}
	$('#total_price').html(formatCurrency(json.total_price, currencyFormat, currencySign, currencyBlank));
	$('#kco_total_price').html(formatCurrency(json.total_price, currencyFormat, currencySign, currencyBlank));
	$('#total_price_without_tax').html(formatCurrency(json.total_price_without_tax, currencyFormat, currencySign, currencyBlank));
	$('#kco_total_price_without_tax').html(formatCurrency(json.total_price_without_tax, currencyFormat, currencySign, currencyBlank));
	$('#total_tax').html(formatCurrency(json.total_tax, currencyFormat, currencySign, currencyBlank));
	$('#kco_total_tax').html(formatCurrency(json.total_tax, currencyFormat, currencySign, currencyBlank));

	if (json.total_shipping > 0)
	{
		if (priceDisplayMethod !== 0)
		{
			$('#total_shipping').html(formatCurrency(json.total_shipping_tax_exc, currencyFormat, currencySign, currencyBlank));
			$('#klarna_shipping_price').html(formatCurrency(json.total_shipping_tax_exc, currencyFormat, currencySign, currencyBlank));
		}
		else
		{
			$('#total_shipping').html(formatCurrency(json.total_shipping, currencyFormat, currencySign, currencyBlank));
			$('#klarna_shipping_price').html(formatCurrency(json.total_shipping, currencyFormat, currencySign, currencyBlank));
		}
	}
	else
	{
		$('#total_shipping').html(freeShippingTranslation);
		$('#klarna_shipping_price').html(freeShippingTranslation);
	}

	/*if (json.free_ship > 0 && !json.is_virtual_cart)
	{
		$('.cart_free_shipping').fadeIn();
		//$('#free_shipping').html(formatCurrency(json.free_ship, currencyFormat, currencySign, currencyBlank));
	}
	else
		$('.cart_free_shipping').hide();*/

	if (json.total_wrapping > 0)
	{
		$('#total_wrapping').html(formatCurrency(json.total_wrapping, currencyFormat, currencySign, currencyBlank));
		$('#total_wrapping').parent().show();
	}
	else
	{
		$('#total_wrapping').html(formatCurrency(json.total_wrapping, currencyFormat, currencySign, currencyBlank));
		$('#total_wrapping').parent().hide();
	}
	if (window.ajaxCart !== undefined)
		ajaxCart.refresh();
		
	updateKCO();
}

function deleteProductFromSummary(id)
{
    showLoaderImg();
	var customizationId = 0;
	var productId = 0;
	var productAttributeId = 0;
	var id_address_delivery = 0;
	var ids = 0;
	ids = id.split('_');
	productId = parseInt(ids[0]);
	if (typeof(ids[1]) !== 'undefined')
		productAttributeId = parseInt(ids[1]);
	if (typeof(ids[2]) !== 'undefined')
		customizationId = parseInt(ids[2]);
	if (typeof(ids[3]) !== 'undefined')
		id_address_delivery = parseInt(ids[3]);
	$.ajax({
		type: 'GET',
		url: baseUri,
		async: true,
		cache: false,
		dataType: 'json',
		data: 'controller=cart'
			+'&ajax=true&delete&summary'
			+'&id_product='+productId
			+'&ipa='+productAttributeId
			+'&id_address_delivery='+id_address_delivery+ ( (customizationId !== 0) ? '&id_customization='+customizationId : '')
			+'&token=' + static_token
			+'&allow_refresh=1',
		success: function(jsonData)
		{
			if (jsonData.hasError)
			{
				var errors = '';
				for(var error in jsonData.errors)
				//IE6 bug fix
				if (error !== 'indexOf')
					errors += jsonData.errors[error] + "\n";
			}
			else
			{
				if (jsonData.refresh)
					location.reload();
				if (parseInt(jsonData.summary.products.length) === 0)
				{
					if (typeof(orderProcess) === 'undefined' || orderProcess !== 'order-opc')
						document.location.href = document.location.href; // redirection
					else
					{
						$('#center_column').children().each(function() {
							if ($(this).attr('id') !== 'emptyCartWarning' && $(this).attr('class') !== 'breadcrumb' && $(this).attr('id') !== 'cart_title')
							{
								$(this).fadeOut('slow', function () {
									$(this).remove();
								});
							}
						});
						$('#summary_products_label').remove();
						$('#emptyCartWarning').fadeIn('slow');
					}
				}
				else
				{
					$('#product_'+ id).fadeOut('slow', function() {
						$(this).remove();
						if (!customizationId)
							refreshOddRow();
					});
					
					$('#kco_product_row_' + productId + '_' + productAttributeId).fadeOut('slow', function() {
							$(this).remove();
						});

					var exist = false;
					for (i=0;i<jsonData.summary.products.length;i++)
						if (jsonData.summary.products[i].id_product === productId
							&& jsonData.summary.products[i].id_product_attribute === productAttributeId
							&& jsonData.summary.products[i].id_address_delivery === id_address_delivery)
							exist = true;

					// if all customization removed => delete product line
					if (!exist && customizationId)
					{
						$('#product_' + productId + '_' + productAttributeId + '_0_' + id_address_delivery).fadeOut('slow', function() {
							$(this).remove();
							refreshOddRow();
						});
						$('#kco_product_row_' + productId + '_' + productAttributeId).fadeOut('slow', function() {
							$(this).remove();
							refreshOddRow();
						});
					}
				}
				updateCartSummary(jsonData.summary);
				//updateCustomizedDatas(jsonData.customizedDatas);
				updateHookShoppingCart(jsonData.HOOK_SHOPPING_CART);
				updateHookShoppingCartExtra(jsonData.HOOK_SHOPPING_CART_EXTRA);
				if (typeof(getCarrierListAndUpdate) !== 'undefined')
					getCarrierListAndUpdate();
			}
		},
		error: function(XMLHttpRequest, textStatus, errorThrown) {
			if (textStatus !== 'abort')
				alert("TECHNICAL ERROR: unable to save update quantity \n\nDetails:\nError thrown: " + XMLHttpRequest + "\n" + 'Text status: ' + textStatus);
		}
	});
}

function refreshOddRow()
{
	var odd_class = 'odd';
	var even_class = 'even';
	$.each($('.cart_item'), function(i, it)
	{
		if (i === 0) // First item
		{
			if ($(this).hasClass('even'))
			{
				odd_class = 'even';
				even_class = 'odd';
			}
			$(this).addClass('first_item');
		}
		if(i % 2)
			$(this).removeClass(odd_class).addClass(even_class);
		else
			$(this).removeClass(even_class).addClass(odd_class);
	});
	$('.cart_item:last-child, .customization:last-child').addClass('last_item');
}

function updateHookShoppingCart(html)
{
	$('#HOOK_SHOPPING_CART').html(html);
}

function updateHookShoppingCartExtra(html)
{
	$('#HOOK_SHOPPING_CART_EXTRA').html(html);
}
function updateKCO()
{
	$.ajax({
		type: 'GET',
		url: kcourl,
		async: false,
		cache: false,
		data: 'kco_update=1',
		success: function(jsonData)
		{
			$("#checkoutdiv").html(jsonData);
		},
		error: function(XMLHttpRequest, textStatus, errorThrown) {
			alert(jsonData);
		}
		});
}