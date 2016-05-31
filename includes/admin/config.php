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
class ConfigSMTP extends AdvNewsletters
{
    public function getContent()
    {
        $output = null;

        if (Tools::isSubmit('submit' . $this->name)) {
            if (!$this->saveData()) {
                $output .= $this->displayError($this->l('Invalid Configuration value'));
            } else {
                $output .= $this->displayConfirmation($this->l('Settings updated'));
            }
        }

        return $output . $this->displayForm();
    }

    public function displayForm()
    {
        // Get default language
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

        // Init Fields form array
        $fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('General Settings'),
            ),
            'input' => array(
                array(
                    'type' => 'switch',
                    'label' => $this->l('Show Popup'),
                    'name' => 'advnewsletters_show_popup',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    )
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Would you like to send a verification email after subscription?'),
                    'name' => 'advnewsletters_send_verification',
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('No')
                        )
                    ),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Would you like to send a confirmation email after subscription?'),
                    'name' => 'advnewsletters_send_confirmation',
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('No')
                        )
                    ),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Welcome voucher code'),
                    'name' => 'advnewsletters_voucher',
                    'class' => 'fixed-width-md',
                    'desc' => $this->l('Leave blank to disable by default.')
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right'
            )
        );

        $fields_form[1]['form'] = array(
                'legend' => array(
                    'title' => $this->l('Mail Settings')
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Mail domain name'),
                        'name' => 'advnewsletters_mailDomainName',
                        'size' => 20,
                        'required' => true
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('From Mail'),
                        'name' => 'advnewsletters_fromMail',
                        'size' => 20,
                        'required' => true
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('From Mail Name'),
                        'name' => 'advnewsletters_fromNameMail',
                        'size' => 20,
                        'required' => true
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Pause for seconds between mail'),
                        'name' => 'advnewsletters_sleepMail',
                        'size' => 20,
                        'required' => true
                    )
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right'
                )
        );

        $helper = new HelperForm();

        // Module, token and currentIndex
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name."&task=setting";

        // Language
        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;

        // Title and toolbar
        $helper->title = $this->displayName;
        $helper->show_toolbar = true;
        $helper->toolbar_scroll = false;
        $helper->submit_action = 'submit'.$this->name;
        $helper->toolbar_btn = array(
            'save' =>
                array(
                    'desc' => $this->l('Save'),
                    'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
                        '&token='.Tools::getAdminTokenLite('AdminModules')."&task=setting",
                ),
            'back' => array(
                'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules')."&task=setting",
                'desc' => $this->l('Back to list')
            )
        );

        $helper->fields_value['advnewsletters_send_verification'] = Configuration::get('advnewsletters_send_verification');
        $helper->fields_value['advnewsletters_send_confirmation'] = Configuration::get('advnewsletters_send_confirmation');
        $helper->fields_value['advnewsletters_voucher'] = Configuration::get('advnewsletters_voucher');

        $helper->fields_value['advnewsletters_fromMail'] = Configuration::get('advnewsletters_fromMail');
        $helper->fields_value['advnewsletters_fromNameMail'] = Configuration::get('advnewsletters_fromNameMail');
        $helper->fields_value['advnewsletters_sleepMail'] = Configuration::get('advnewsletters_sleepMail');
        $helper->fields_value['advnewsletters_mailDomainName'] = Configuration::get('advnewsletters_mailDomainName');
        $helper->fields_value['advnewsletters_show_popup'] = Configuration::get('advnewsletters_show_popup');

        return $helper->generateForm($fields_form);
    }

    public function saveData()
    {
        //TODO - Validate the Data
        $mailDomainName = Tools::getValue('advnewsletters_send_verification');
        Configuration::updateValue('advnewsletters_send_verification', $mailDomainName);

        $mailDomainName = Tools::getValue('advnewsletters_send_confirmation');
        Configuration::updateValue('advnewsletters_send_confirmation', $mailDomainName);

        $mailDomainName = Tools::getValue('advnewsletters_voucher');
        Configuration::updateValue('advnewsletters_voucher', $mailDomainName);

        $mailDomainName = Tools::getValue('advnewsletters_mailDomainName');
        Configuration::updateValue('advnewsletters_mailDomainName', $mailDomainName);

        $fromMail = Tools::getValue('advnewsletters_fromMail');
        Configuration::updateValue('advnewsletters_fromMail', $fromMail);

        $fromNameMail = Tools::getValue('advnewsletters_fromNameMail');
        Configuration::updateValue('advnewsletters_fromNameMail', $fromNameMail);

        $sleepMail = Tools::getValue('advnewsletters_sleepMail');
        Configuration::updateValue('advnewsletters_sleepMail', $sleepMail);

        $ba_newsletters_show_checkbox = Tools::getIsset("advnewsletters_show_popup");
        Configuration::updateValue('advnewsletters_show_popup', $ba_newsletters_show_checkbox);

        //TODO - Data Validation
        return true;
    }
}
