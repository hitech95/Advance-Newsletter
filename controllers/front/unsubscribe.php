<?php
/**
* 2007-2015 PrestaShop
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
*  @copyright 2007-2015 PrestaShop SA
*  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
* @since 1.6
*/

class BaNewslettersUnsubscribeModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
        $this->unsubscribe();
        $this->setTemplate('unsubscribe.tpl');
    }
    private function unsubscribe()
    {
        
        $email = Tools::getValue('email');
        if ($email != false) {
            $sql="SELECT id FROM "._DB_PREFIX_."ba_subscriber WHERE email='".pSQL($email)."'";
            $userArr = Db::getInstance()->ExecuteS($sql);
            if (count($userArr)>0) {
                Db::getInstance()->delete('ba_subscriber', 'id='.(int)$userArr[0]['id']);
                Db::getInstance()->delete('ba_maillist_subscriber', 'id_subscriber='.(int)$userArr[0]['id']);
            }
        }
    }
}
