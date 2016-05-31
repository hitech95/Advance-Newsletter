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

class BaNewslettersSendMailTestModuleFrontController extends ModuleFrontController
{
    public function __construct()
    {
        return parent::__construct();
    }
    /**
     * @see FrontController::postProcess()
     */
    public function run()
    {
        $this->sendMailTest();
    }
    private function sendMailTest()
    {
        
        require_once(_PS_SWIFT_DIR_.'Swift.php');
        require_once(_PS_SWIFT_DIR_.'Swift/Connection/SMTP.php');
        require_once(_PS_SWIFT_DIR_.'Swift/Connection/NativeMail.php');
        require_once(_PS_SWIFT_DIR_.'Swift/Plugin/Decorator.php');
        if (Tools::getValue('banewsletters_token') != sha1(_COOKIE_KEY_.'banewsletters')) {
            die;
        }
        $newsletter=new banewsletters();
        $replyTo = Configuration::get('replyTo');
        $replyNameTo = Configuration::get('replyNameTo');
        $id_lang=Tools::getValue('id_lang');
        if (Configuration::get('statusSMTP')=='1') {
            $userName = Configuration::get('userName');
            $passWord = Configuration::get('passWord');
            $fromMail = Configuration::get('fromMail');
            $fromNameMail = Configuration::get('fromNameMail');
            $host = Configuration::get('host');
            $port = Configuration::get('port');
            $secure = Configuration::get('secure');
            try {
                $secureTlsOrSsl=(($secure == 'tls') ? Swift_Connection_SMTP::ENC_TLS : Swift_Connection_SMTP::ENC_SSL);
                $secureSMTP=($secure == null) ? Swift_Connection_SMTP::ENC_OFF : $secureTlsOrSsl;
                $smtp = new Swift_Connection_SMTP($host, $port, $secureSMTP);
                $smtp->setUsername($userName);
                $smtp->setpassword($passWord);
                $smtp->setTimeout(5);
                $swift = new Swift($smtp, Configuration::get('mailDomainName'));
                $subject = "No Subject";
                if (Tools::getValue('ba_newsletter_subject_'.$id_lang) !=false) {
                    $subject = Tools::getValue('ba_newsletter_subject_'.$id_lang);
                }
                $body = 'No Body <p style="text-align:center;"><a href="'
                .Tools::getShopProtocol().Tools::getHttpHost().__PS_BASE_URI__
                .'index.php?controller=unsubscribe&fc=module&module=banewsletters&email='
                .Tools::getvalue('emailTest').'">Unsubscriber</a></p>';
                if (Tools::getValue('bodyMail') !=false) {
                    $body = Tools::getValue('bodyMail')
                    .'<p style="text-align:center;"><a href="'.Tools::getShopProtocol().Tools::getHttpHost()
                    .__PS_BASE_URI__
                    .'index.php?controller=unsubscribe&fc=module&module=banewsletters&email='
                    .Tools::getvalue('emailTest').'">Unsubscriber</a></p>';
                }
                $message = new Swift_Message($subject, $body, 'text/html');
                $message->setReplyTo(array($replyTo =>  $replyNameTo));
                $message->setCharset('utf-8');
                $swift_Address = new Swift_Address($fromMail, $fromNameMail);
                if ($swift->send($message, Tools::getValue('emailTest'), $swift_Address)==1) {
                    echo $newsletter->l("The newsletter(s) have been sent successfully.");
                } else {
                    echo $newsletter->l("Error while sending email.");
                }
                $swift->disconnect();
            } catch (Swift_ConnectionException $e) {
                var_dump($e->getMessage());
            } catch (Swift_Message_MimeException $e) {
                var_dump($e->getMessage());
            }
        } else {
            $connection = new Swift_Connection_NativeMail();
            $swift = new Swift($connection, Configuration::get('PS_MAIL_DOMAIN'));
            $subject = "No Subject";
            if (Tools::getValue('ba_newsletter_subject_'.$id_lang) !=false) {
                $subject = Tools::getValue('ba_newsletter_subject_'.$id_lang);
            }
            
            $body = "No Body".'<a href="'.Tools::getShopProtocol().Tools::getHttpHost().__PS_BASE_URI__
            .'index.php?controller=unsubscribe&fc=module&module=banewsletters&email='.Tools::getvalue('emailTest')
            .'">Unsubscriber</a>';
            if (Tools::getValue('bodyMail') !=false) {
                $body = Tools::getValue('bodyMail').'<br/><p style="text-align:center;"><a href="'
                .Tools::getShopProtocol().Tools::getHttpHost().__PS_BASE_URI__
                .'index.php?controller=unsubscribe&fc=module&module=banewsletters&email='
                .Tools::getvalue('emailTest').'">Unsubscriber</a></p>';
            }
            $message = new Swift_Message($subject, $body, 'text/html');
            $message->headers->setEncoding('Q');
            $message->setReplyTo(array($replyTo =>  $replyNameTo));
            $message->setCharset('utf-8');
            //Trả về 1 gửi thành công trả về 0 thất bại
            $swift_Address=new Swift_Address(Configuration::get('fromMail'), Configuration::get('fromNameMail'));
            if ($swift->send($message, Tools::getValue('emailTest'), $swift_Address)==1) {
                echo $newsletter->l("The newsletter(s) have been sent successfully.");
            } else {
                echo $newsletter->l("Error while sending email.");
            }
            $swift->disconnect();
            ShopUrl::resetMainDomainCache();
        }
    }
}
