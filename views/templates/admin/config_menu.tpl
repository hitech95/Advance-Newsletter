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
<div class="number_mail_in_queue">
	{l s='Your system is having  %1$d emails in Queue' mod='banewsletters' sprintf=$numberQueue}
</div>
<ul class="nav nav-tabs">
    <li class="{if $task=="newsletters"}active{/if}"><a href="{$baseModuleUrl|escape:'htmlall':'UTF-8'}&task=newsletters">{l s='Newsletters' mod='banewsletters'}</a></li>
    <li class="{if $task=="templates"}active{/if}"><a href="{$baseModuleUrl|escape:'htmlall':'UTF-8'}&task=templates">{l s='Template' mod='banewsletters'}</a></li>
    <li class="{if $task=="subscribers"}active{/if}"><a href="{$baseModuleUrl|escape:'htmlall':'UTF-8'}&task=subscribers">{l s='Subscriber' mod='banewsletters'}</a></li>
    <li class="{if $task=="setting"}active{/if}"><a href="{$baseModuleUrl|escape:'htmlall':'UTF-8'}&task=setting">{l s='Setting' mod='banewsletters'}</a></li>
    <li class="{if $task=="report"}active{/if}"><a href="{$baseModuleUrl|escape:'htmlall':'UTF-8'}&task=report">{l s='Report' mod='banewsletters'}</a></li>
</ul>