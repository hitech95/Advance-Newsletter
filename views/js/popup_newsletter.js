/*
* 2007-2016 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@buy-addons.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author Buy-Addons <hatt@buy-addons.com>
*  @copyright  2007-2016 PrestaShop SA
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

jQuery(document).ready(function(){
	//jQuery( ".ba_newsletter_popup_background").hide();
	//jQuery( ".ba_newsletter_popup").hide();
	jQuery( "#ba_newsletter_popup_input" ).keypress(function( event ) {
		if ( event.which == 13 ) {
			ajaxSubsriberPopup();
		}
	});
	jQuery( "#ba_newsletter_popup_btn_subscriber" ).click(function() {
		ajaxSubsriberPopup();
		setCookie('ba_newsletter_popup', '1', 365);
	});
	
	var widthSreen = screen.width;
	var heightSreen = screen.height-90;
	var widthPopupNewsleter = jQuery( ".ba_newsletter_popup").width();
	var heightPopupNewsleter = jQuery( ".ba_newsletter_popup").height();
	var top=(heightSreen-heightPopupNewsleter)/2;
	var left=(widthSreen-widthPopupNewsleter)/2;
	jQuery( ".ba_newsletter_popup").css({
		'position':'fixed',
		'top':top+'px',
		'left':left+'px'
	});
	console.log(getCookie('ba_newsletter_popup'));
	if (getCookie('ba_newsletter_popup')!="1") {
		jQuery( ".ba_newsletter_popup_background").show();
		jQuery( ".ba_newsletter_popup").show();
	}
	jQuery( ".close_popup_ba_newsletter").click(function(){
		jQuery( ".ba_newsletter_popup").hide();
		jQuery( ".ba_newsletter_popup_background").hide();
		setCookie('ba_newsletter_popup', '1', 365);
		
	});
});
//function checked email valid
function isValidEmailAddress(emailAddress) {
    var pattern = new RegExp(/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i);
    return pattern.test(emailAddress);
};

function ajaxSubsriberPopup(){
	var emailAddress = jQuery( "#ba_newsletter_popup_input" ).val();
	console.log(emailAddress);
	var datArr=jQuery('#ba_newsletter_popup_form').serializeArray(); 
	//console.log(mailLists);
	if( isValidEmailAddress(emailAddress)==true) {
		//alert(mailLists);
		jQuery.ajax({
			url:baseDir+'index.php?controller=batrackingsubscriber&fc=module&module=banewsletters&banewsletters_token='+banewsletters_token,
			data: datArr,
			type: 'POST',
			success:function(x){
				alert(x);
			}
		});
	} else {
		alert("Email address invalid");
	}
}
function setCookie(cname, cvalue, exdays) {
	var d = new Date();
	d.setTime(d.getTime() + (exdays*24*60*60*1000));
	var expires = "expires="+d.toUTCString();
	document.cookie = cname + "=" + cvalue + "; " + expires;
}
function getCookie(cname) {
	var name = cname + "=";
	var ca = document.cookie.split(';');
	for(var i=0; i<ca.length; i++) {
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1);
		if (c.indexOf(name) == 0) return c.substring(name.length,c.length);
	}
	return "";
}