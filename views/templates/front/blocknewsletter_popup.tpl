{**
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
*  @author    Buy-Addons <hatt@buy-addons.com>
*  @copyright 2007-2016 PrestaShop SA
*  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
* @since 1.6
*}
<link href="//netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.css" rel="stylesheet">
<div class="ba_newsletter_popup col-sm-4">
	<div class="block_content">
		<h3 class="page-subheading" style="padding-bottom: 10px;margin-bottom: 20px;">{l s='Sign up for our newsletter' mod='banewsletters'}</h3>
		<form onsubmit="return false;" id="ba_newsletter_popup_form">
			<div class="ba_newsletter_mail_list checkbox">
				<ul>
					<li style="margin-bottom:7px;"><input class="form-control" id="ba_newsletter_popup_input" type="text" name="email" placeholder="Email Address"></li>
					{foreach $mailListAllArray item=mailList}
					<li style="margin-bottom: 7px;"><input class="ba_newsletter_mail_list" name="mail_list[]" type="checkbox" value="{$mailList.id|escape:'htmlall':'UTF-8'}" />{$mailList.name|escape:'htmlall':'UTF-8'}</li>
					{/foreach}
					<li class="ba_newsletter_submit"><input type="button" class="btn btn-default" value="Subscribe" id="ba_newsletter_popup_btn_subscriber"></li>
				</ul>
			</div>
		</form>
	</div>
	<span class="close_popup_ba_newsletter"><i class="icon-remove-circle"></i></span>
</div>
<div class="ba_newsletter_popup_background"></div>
