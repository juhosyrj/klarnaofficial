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

$(document).ready(function(){
    $("#fancydocs").fancybox({
        'padding':  0,
        'width':    1087,
        'height':   610,
        'type':     'iframe',
    });
    
	$('.sidebar .nav-tabs a').click(function(){
		$(this).parent().addClass('active').siblings().removeClass('active');
		var fieldset_arr = $(this).attr('data-fieldset').split(',');
		var pane = $(this).attr('data-panel');
		var fieldset_dom = $('#pane'+pane+' form.defaultForm .panel');
		fieldset_dom.removeClass('selected');
		$.each(fieldset_arr,function(i,n){
			$('#pane'+pane+' .panel[id^="fieldset_'+n+'"]').addClass('selected');
		});
	});
	$('#pane1 .sidebar .nav-tabs a').each(function(){
		var fieldset_arr = $(this).attr('data-fieldset').split(',');
		if($.inArray(pwd_refer, fieldset_arr) > -1)
		{
			$(this).trigger('click');
			return false;
		}
	});
	$('#pane2 .sidebar .nav-tabs a').each(function(){
		var fieldset_arr = $(this).attr('data-fieldset').split(',');
		if($.inArray(pwd_refer, fieldset_arr) > -1)
		{
			$(this).trigger('click');
			return false;
		}
	});
	$('#pane3 .sidebar .nav-tabs a').each(function(){
		var fieldset_arr = $(this).attr('data-fieldset').split(',');
		if($.inArray(pwd_refer, fieldset_arr) > -1)
		{
			$(this).trigger('click');
			return false;
		}
	});
});