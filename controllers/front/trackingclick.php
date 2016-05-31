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

class BaNewslettersTrackingClickModuleFrontController extends ModuleFrontController
{
    /**
     * @see FrontController::postProcess()
     */
    public function run()
    {
        $this->trackingClick();
    }
    private function trackingClick()
    {
        //nếu chưa có cookie thì cập nhật click vào bảng ps_ba_newsletter và chuyển trang
        //nếu có rồi thì chỉ chuyển trang
        $cookie = new Cookie('ba_click_tracking');
        if ($cookie->ba_click_tracking != "1") {
            //$cookie = new Cookie('ba_click_tracking');
            $cookie->ba_click_tracking = 1;
            $cookie->setExpire(time()+2*60*60);
            $cookie->write();
            $sql = "UPDATE "._DB_PREFIX_."ba_newsletter SET email_click = email_click + 1
            WHERE id=".(int)Tools::getValue('idNewsletter');
            Db::getInstance(_PS_USE_SQL_SLAVE_)->query($sql);
            //cập nhật number_click vào bảng report
            require_once(_PS_MODULE_DIR_.'banewsletters/includes/newsletter.php');
            $newsletter = new BANewsletterAdmin();
            $newsletter->updateReport((int)Tools::getValue('idNewsletter'), 'number_click', 1);
            //chuyển trang
            $url = urldecode(Tools::getValue('data'));
            Tools::redirect($this->validateURL($url));
            
        } else {
            $url = urldecode(Tools::getValue('data'));
            Tools::redirect($this->validateURL($url));
        }
    }
    private function validateURL($url)
    {
        //chyen thanh chu thuong
        $url = Tools::strtolower($url);
        //kiem tra co bat dau bang http khong
        if (strpos($url, 'http')===0 || strpos($url, 'https')===0) {
            return $url;
        } else {
            $url='http://'.$url;
            return $url;
        }
    }
}
