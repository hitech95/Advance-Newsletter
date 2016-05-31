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
 * @author    Buy-Addons <hatt@buy-addons.com>
 * @copyright 2007-2015 PrestaShop SA
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 * @since 1.6
 */
class SendMail extends banewsletters
{
    public function processSendMail()
    {
        require_once(_PS_SWIFT_DIR_ . '/swift_required.php');
        require_once(_PS_MODULE_DIR_ . 'banewsletters/includes/newsletter.php');

        //$newsletter = new BANewsletterAdmin();

        $replyTo = Configuration::get('replyTo');
        $replyNameTo = Configuration::get('replyNameTo');

        $fromMail = Configuration::get('fromMail');
        $fromNameMail = Configuration::get('fromNameMail');

        $id_shop = Context::getContext()->shop->id;
        $configuration = Configuration::getMultiple(array(
            'PS_MAIL_METHOD',
            'PS_MAIL_SERVER',
            'PS_MAIL_USER',
            'PS_MAIL_PASSWD',
            'PS_MAIL_SMTP_ENCRYPTION',
            'PS_MAIL_SMTP_PORT',
        ), null, null, $id_shop);

        // Returns immediatly if emails are deactivated
        if ($configuration['PS_MAIL_METHOD'] == 3) {
            return true;
        }

        while (true) {
            $db = Db::getInstance(_PS_USE_SQL_SLAVE_);
            $sql = "SELECT * FROM " . _DB_PREFIX_ . "newsletter_queue LIMIT 0,1";
            $emailAddressArray = $db->ExecuteS($sql);

            if (!empty($emailAddressArray)) {

                $transport = null;
                if ($configuration['PS_MAIL_METHOD']) {

                    if (!isset($configuration['PS_MAIL_SMTP_PORT'])) {
                        $configuration['PS_MAIL_SMTP_PORT'] = 'default';
                    }
                    if (empty($configuration['PS_MAIL_SERVER']) || empty($configuration['PS_MAIL_SMTP_PORT'])) {
                        Tools::dieOrLog(Tools::displayError('Error: invalid SMTP server or SMTP port'), false);
                        return false;
                    }

                    $transport = Swift_SmtpTransport::newInstance($configuration['PS_MAIL_SERVER'], $configuration['PS_MAIL_SMTP_PORT']);
                    $transport->setTimeout(5);
                    if (!empty($configuration['PS_MAIL_USER'])) {
                        $transport->setUsername($configuration['PS_MAIL_USER']);
                    }
                    if (!empty($configuration['PS_MAIL_PASSWD'])) {
                        $transport->setPassword($configuration['PS_MAIL_PASSWD']);
                    }
                    if (isset($configuration['PS_MAIL_SMTP_ENCRYPTION'])) {
                        $transport->setEncryption($configuration['PS_MAIL_SMTP_ENCRYPTION']);
                    }
                } else {
                    $transport = Swift_MailTransport::newInstance();
                }

                $mailer = Swift_Mailer::newInstance($transport);

                foreach ($emailAddressArray as $emailAddress) {

                    $body = Tools::htmlentitiesDecodeUTF8($emailAddress['body']);

                    $message = Swift_Message::newInstance($emailAddress['subject'], $body, 'text/html');
                    $message->setReplyTo(array($replyTo => $replyNameTo));
                    $message->setFrom(array($fromMail => $fromNameMail));
                    $message->setTo(array($emailAddress['to']));
                    $message->setCharset('utf-8');

                    $mailer->send($message);

                    ShopUrl::resetMainDomainCache();

                    //TODO - Stats recording
                    /*//update table report
                    $newsletter->updateReport((int)$emailAddress['id_newsletter'], 'number_send', 1);
                    //end update table report*/


                    $db->delete('newsletter_queue', 'id=' . $emailAddress['id']);
                    sleep((int)Configuration::get('sleepMail'));
                }
            } else {
                break;
            }
        }
    }
}