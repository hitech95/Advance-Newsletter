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
<div class="panel col-md-12 export">
	<form action="" method="POST">
		<div class="panel-heading">{l s='Import' mod='banewsletters'}</div>
		<div class="control-group col-md-12">
			<label class="control-label">{l s='Mail list' mod='banewsletters'}: </label>
			<div class="controls">
				{foreach $mailListAllArray item=rowMailList}
				<label class="control-label" style="margin-right:5px;"><input class="id_mail_list" type="checkbox" name="id_mail_list[]" value="{$rowMailList.id|escape:'htmlall':'UTF-8'}">{$rowMailList.name|escape:'htmlall':'UTF-8'}</label>
				{/foreach}
			</div>
		</div>
		<div class="control-group col-md-12">
			<input type="submit" name="exportSubscriber" value="{l s='Export' mod='banewsletters'}" class="btn btn-default">
		</div>
	</form>
</div>