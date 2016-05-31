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

class BaNewslettersTrackingViewModuleFrontController extends ModuleFrontController
{
    /**
     * @see FrontController::postProcess()
     */
    public function run()
    {
        $this->createImage();
    }
    private function createImage()
    {
        $sql = "UPDATE "._DB_PREFIX_."ba_newsletter SET 
        email_view = email_view + 1 WHERE id= ".(int)Tools::getValue('idNewsletter');
        Db::getInstance(_PS_USE_SQL_SLAVE_)->query($sql);
        //cập nhật number_view vào bảng report
        require_once(_PS_MODULE_DIR_.'banewsletters/includes/newsletter.php');
        $newsletter = new BANewsletterAdmin();
        $newsletter->updateReport((int)Tools::getValue('idNewsletter'), 'number_view', 1);
        //gennerate image
        $image = ImageCreate(1, 1);
        //$white = ImageColorAllocate($image, 255, 255, 255);
        $white = ImageColorAllocate($image, 255, 255, 255);
        ImageFill($image, 0, 0, $white);
        // ImageString($image, 5, 30, 6, $security_code, $white);
        header("Content-Type: image/jpeg");
        ImageJpeg($image);
        ImageDestroy($image);
    }
}
