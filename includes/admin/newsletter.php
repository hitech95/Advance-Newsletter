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
class BANewsletterAdmin extends AdvNewsletters
{
    const SEND_SUBSCRIBED = 'subscribers';
    const SEND_CUSTUMER = 'customers';
    const SEND_BOTH = 'subscribers_customers';

    private $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = Db::getInstance();
    }

    public function newsletterList()
    {
        $fields_list = array(
            'name' => array(
                'title' => $this->l('Name'),
                'width' => 100,
                'type' => 'text',
                'align' => 'left name',
                'search' => false
            ),
            'sent' => array(
                'title' => $this->l('Send'),
                'active' => 'status',
                'width' => 100,
                'type' => 'bool',
                'align' => 'center',
                'search' => false
            ),
            'date' => array(
                'title' => $this->l('Date'),
                'width' => 100,
                'type' => 'date',
                'align' => 'center',
                'search' => false
            ),
            'total_queue' => array(
                'title' => $this->l('Queue'),
                'width' => 100,
                'align' => 'center',
                'search' => false
            )
        );

        $helper_list = new HelperList();

        $helper_list->module = $this;
        $helper_list->title = 'Newletter Manager';
        $helper_list->shopLinkType = '';
        $helper_list->no_link = true;
        $helper_list->show_toolbar = true;
        $helper_list->simple_header = false;
        $helper_list->identifier = 'id';
        $helper_list->table = $this->name . '_newsletter';
        $helper_list->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name . '&task=newsletters';
        $helper_list->list_id = $this->name . '_newsletter';
        $helper_list->token = Tools::getAdminTokenLite('AdminModules');
        $helper_list->actions = array('edit', 'delete', 'clearQueue');

        $helper_list->toolbar_btn['new'] = array(
            'href' => AdminController::$currentIndex . '&configure=' . $this->name . '&add' . $this->name
                . '_newsletter&task=newsletters&token=' . Tools::getAdminTokenLite('AdminModules'),
            'desc' => $this->l('Add new')
        );

        $helper_list->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete selected'),
                'icon' => 'icon-trash',
                'confirm' => $this->l('Delete selected items?')
            ),
            'clean_queue' => array(
                'text' => $this->l('Clear queue'),
                'icon' => 'icon-off',
                'confirm' => $this->l('Clear the queue of selected items?')
            )
        );

        // This is needed for displayEnableLink to avoid code duplication
        $this->_helperlist = $helper_list;

        /* Retrieve list data */
        $newsletters = $this->getNewsletter();
        $helper_list->listTotal = count($newsletters);

        /* Paginate the result */
        $page = ($page = Tools::getValue('submitFilter' . $helper_list->table)) ? $page : 1;
        $pagination = ($pagination = Tools::getValue($helper_list->table . '_pagination')) ? $pagination : 50;
        $newsletters = $this->paginateNewsletter($newsletters, $page, $pagination);

        return $helper_list->generateList($newsletters, $fields_list);
    }

    public function displayEditLink($token = null, $id, $name = null)
    {
        $dbquery = new DbQuery();
        $dbquery->select('n.`status` AS `sent`');
        $dbquery->from('newsletter_campain', 'n');
        $dbquery->where('n.`id` = ' . $id);
        $status_old = (bool)($this->db->executeS($dbquery->build())[0]['sent']);

        $this->smarty->assign(array(
            'action' => $this->l('Edit'),
            'href' => $this->_helperlist->currentIndex . '&update' . $this->name . '_newsletter' . '&' . $this->_helperlist->identifier . '=' . $id . '&token=' . $token,
            'disable' => $status_old,
        ));

        return $this->display("advnewsletters", 'views/templates/admin/newsletters/list_action_editnewsletter.tpl');
    }

    public function displayClearQueueLink($token = null, $id, $name = null)
    {
        $this->smarty->assign(array(
            'href' => $this->_helperlist->currentIndex . '&clearqueue' . $this->name . '_newsletter' . '&' . $this->_helperlist->identifier . '=' . $id . '&token=' . $token,
            'action' => $this->l('Clear Queue'),
        ));

        return $this->display("advnewsletters", 'views/templates/admin/newsletters/list_action_clearqueue.tpl');
    }

    public function paginateNewsletter($newsletters, $page = 1, $pagination = 50)
    {
        if (count($newsletters) > $pagination)
            $newsletters = array_slice($newsletters, $pagination * ($page - 1), $pagination);

        return $newsletters;
    }

    public function newsletterDetails($id = -1)
    {
        // Get default language
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

        //Send Option Method
        $options = array(
            array(
                'id_option' => 'subscribers',
                'name' => $this->l('Subscribers')
            ),
            array(
                'id_option' => 'customers',
                'name' => $this->l('Customers')
            ),
            array(
                'id_option' => 'subscribers_customers',
                'name' => $this->l('Subscribers & Customers')
            ),
        );

        // Init Fields form array
        $fields_form[0]['form'] = array(
            'legend' => array(
                'title' => ($id >= 0) ? $this->l('Edit Newsletter') : $this->l('New Newsletter'),
            ),
            'input' => array(
                array(
                    'type' => 'hidden',
                    'name' => 'advnewsletters_newsletter_id'
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Name'),
                    'name' => 'advnewsletters_newsletter_name',
                    'required' => true,
                    'desc' => $this->l('Newsletter name.')
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Template'),
                    'name' => 'advnewsletters_newsletter_template',
                    'required' => true,
                    'options' => array(
                        'query' => $this->getTemplates(true),
                        'id' => 'id',
                        'name' => 'name'
                    ),
                    'class' => 'chosen',
                    'desc' => $this->l('The template of the message')
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Send'),
                    'name' => 'advnewsletters_newsletter_status',
                    'is_bool' => true,
                    'required' => true,
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
                    'desc' => $this->l('Once saved,the system add emails in queue to send them.')
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Send to'),
                    'name' => 'advnewsletters_newsletter_sendto',
                    'options' => array(
                        'query' => $options,
                        'id' => 'id_option',
                        'name' => 'name'
                    ),
                    'desc' => $this->l('Who is receiving this communication?')
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('All customers'),
                    'name' => 'advnewsletters_newsletter_all_customers',
                    'is_bool' => true,
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
                    'desc' => $this->l('Send to all customers?')
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Send to'),
                    'name' => 'advnewsletters_newsletter_customers',
                    'multiple' => true,
                    'options' => array(
                        'query' => $this->getCustomers(),
                        'id' => 'id',
                        'name' => 'name',
                        'default' => array(
                            'value' => '',
                            'label' => ''
                        ),
                    ),
                    'class' => 'chosen',
                    'desc' => $this->l('Select the customers for this message')
                )
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right'
            ),
        );

        $helper = new HelperForm();

        // Module, token and currentIndex
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name . "&task=newsletters";

        // Language
        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;

        // Title and toolbar
        $helper->title = $this->displayName;
        $helper->show_toolbar = true;
        $helper->toolbar_scroll = false;
        $helper->submit_action = 'submit' . $this->name;

        $helper->toolbar_btn = array(
            'save' =>
                array(
                    'desc' => $this->l('Save'),
                    'href' => AdminController::$currentIndex . '&configure=' . $this->name . '&save' . $this->name .
                        '&token=' . Tools::getAdminTokenLite('AdminModules') . "&task=newsletters",
                ),
            'back' => array(
                'href' => AdminController::$currentIndex . '&token=' . Tools::getAdminTokenLite('AdminModules') . "&task=newsletters",
                'desc' => $this->l('Back to list')
            )
        );

        $newsletterArray = $this->loadNewsletterDetails($id);

        foreach ($newsletterArray as $key => $value) {
            $helper->fields_value['advnewsletters_newsletter_' . $key] = $value;
        }
        $helper->fields_value['advnewsletters_newsletter_id'] = $id;

        $this->context->controller->addJS($this->_path . 'views/js/backoffice.js');

        return $helper->generateForm($fields_form);
    }

    public function loadNewsletterDetails($id)
    {
        $newsletterArray = array();

        if ($id >= 0) {
            $dbquery = new DbQuery();
            $dbquery->select('*');
            $dbquery->from('newsletter_campain', 'c');
            $dbquery->where('c.`id` = ' . (int)$id);

            $newsletterQuery = $this->db->executeS($dbquery->build());

            foreach ($newsletterQuery as $campaign) {
                $newsletterArray['name'] = $campaign['name'];
                $newsletterArray['template'] = $campaign['id_template'];
                $newsletterArray['status'] = $campaign['status'];
                $newsletterArray['sendto'] = $campaign['method'];

                $campaign['customers'] = unserialize($campaign['customers']);
                $newsletterArray['all_customers'] =
                    ($newsletterArray['sendto'] != self::SEND_SUBSCRIBED && is_null($campaign['customers'])) ? 1 : 0;

                if (is_null($campaign['customers'])) {
                    $newsletterArray['customers[]'] = array();
                } else {
                    $newsletterArray['customers[]'] = $campaign['customers'];
                }
            }
        } else {
            $newsletterArray['name'] = '';
            $newsletterArray['template'] = '';
            $newsletterArray['status'] = '0';
            $newsletterArray['sendto'] = '0';
            $newsletterArray['all_customers'] = '';
            $newsletterArray['customers[]'] = array();
        }

        return $newsletterArray;
    }


    public function saveNewsletterDetails()
    {
        //TODO - Validate form

        $status_old = false;

        $id = (int)Tools::getValue('advnewsletters_newsletter_id');
        $name = Tools::getValue('advnewsletters_newsletter_name');
        $template = (int)Tools::getValue('advnewsletters_newsletter_template');
        $status = (int)Tools::getValue('advnewsletters_newsletter_status');
        $send_to = Tools::getValue('advnewsletters_newsletter_sendto');
        $costumers_all = (int)Tools::getValue('advnewsletters_newsletter_all_customers');
        $costumers = Tools::getValue('advnewsletters_newsletter_customers');

        if ($send_to == self::SEND_SUBSCRIBED || (bool)$costumers_all) {
            $costumers = null;
        } else {
            array_map('intval', $costumers);
        }

        $costumers_ser = serialize($costumers);

        if ($id >= 0) {
            //Update only if not sent else return false
            $dbquery = new DbQuery();
            $dbquery->select('n.`status` AS `sent`');
            $dbquery->from('newsletter_campain', 'n');
            $dbquery->where('n.`id` = ' . $id);
            $status_old = (bool)($this->db->executeS($dbquery->build())[0]['sent']);

            if ($status_old) {
                return false;
            }

            $this->db->update('newsletter_campain', array(
                'status' => $status,
                'id_template' => $template,
                'status' => $status,
                'method' => $send_to,
                'name' => $name,
                'customers' => $costumers_ser,
            ), "id = " . $id);
        } else {
            $date = date('y-m-d');
            $this->db->insert('newsletter_campain', array(
                'status' => $status,
                'id_template' => $template,
                'status' => $status,
                'method' => $send_to,
                'name' => $name,
                'customers' => $costumers_ser,
                'date' => $date,
            ));

            $id = (int)$this->db->Insert_ID();
        }

        if ($status == 1 && !$status_old) {
            //TODO - Generate Queue
            $this->generateQueue($id);
        }
        return true;
    }

    public function generateQueue($id)
    {
        // Get default language
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

        $mailQueue = array();
        $details = $this->loadNewsletterDetails($id);

        //Load the template
        $dbquery = new DbQuery();
        $dbquery->select('*');
        $dbquery->from('newsletter_template_lang', 't');
        $dbquery->where('t.`id_template` = ' . $details['template']);

        $templateQuery = $this->db->executeS($dbquery->build());
        $templateArray = array();
        foreach ($templateQuery as $template) {
            $templateArray[$template['id_lang']] = $template;
        }

        //if type contains subscribers
        if ($details['sendto'] != self::SEND_CUSTUMER) {
            echo "Send Also to Subscribers\n";
            $subscribers = $this->getSubscribers();

            foreach ($subscribers as $subscriber) {
                //Add mails to mailqueue
                $language_id = $default_lang;

                if (preg_match('/(^N)/', $subscriber['id'])) {
                    $language_id = $subscriber['id_lang'];
                }

                $template = $templateArray[$language_id];

                $email = array(
                    'to' => $subscriber['email'],
                    'subject' => $template['subject'],
                    'body' => $template['content'],
                    'id_newsletter' => $id,
                );

                $mailQueue[$subscriber['email']] = $email;
            }
        }

        if ($details['sendto'] != self::SEND_SUBSCRIBED) {
            echo "Send Also to customers\n";

            $customers = array();

            //Load all Customers else get by array
            if ((bool)$details['all_customers']) {
                $customers = $this->getCustomers();
            } else {
                $idCustomersString = implode(",", $details['customers[]']);

                $dbquery = new DbQuery();
                $dbquery->select('c.`id_customer` AS `id`, c.`email` AS `email`');
                $dbquery->from('customer', 'c');
                $dbquery->where('c.`id_customer` IN ( ' . pSQL($idCustomersString) . ' )');

                $customers = $this->db->executeS($dbquery->build());
            }

            foreach ($customers as $customer) {
                //Add mails to mailqueue
                if (!array_key_exists($customer['email'], $mailQueue)) {
                    $template = $templateArray[$default_lang];

                    $email = array(
                        'to' => $customer['email'],
                        'subject' => $template['subject'],
                        'body' => $template['content'],
                        'id_newsletter' => $id,
                    );

                    $mailQueue[$customer['email']] = $email;
                }
            }
        }

        foreach ($mailQueue as $mail) {
            //Add mails to DB
            $bodyMail = str_replace("[email]", $mail['to'], $mail['body']);
            $bodyMail1 = $bodyMail . " < p style = 'text-align:center;' ><a href = '" . Tools::getShopProtocol()
                . Tools::getHttpHost() . __PS_BASE_URI__
                . "index.php?controller=unsubscribe&fc=module&module=banewsletters&email="
                . $mail['to'] . "' > Unsubscriber</a ></p > ";

            $this->db->insert('newsletter_queue', array(
                'to' => pSQL($mail['to']),
                'subject' => $mail['subject'],
                'body' => Tools::htmlentitiesUTF8($bodyMail1),
                'id_newsletter' => $mail['id_newsletter']
            ));
        }
    }

    public function caseNewsletter()
    {
        $html = "";
        $this->context->controller->addJS($this->_path . 'views/js/ajax_send_mail_test.js');
        $adminControllers = AdminController::$currentIndex;
        $token = '&token=' . Tools::getAdminTokenLite('AdminModules');
        $configAndTask = '&configure=' . $this->name . '&task=newsletters';

        if (Tools::isSubmit('submit' . $this->name)) {
            //Save Newsletter
            $this->saveNewsletterDetails();

            return $html .= $this->newsletterList();
        } elseif (Tools::isSubmit('submitCancel') || Tools::isSubmit('submitReset' . $this->name . '_newsletter')) {
            Tools::redirectAdmin($adminControllers . $token . $configAndTask);
        } elseif (Tools::isSubmit('update' . $this->name . '_newsletter')) {
            //Edit Newsletter
            $idNewsletter = Tools::getValue('id');

            $html .= $this->newsletterDetails((int)$idNewsletter);
        } elseif (Tools::isSubmit('add' . $this->name . '_newsletter')) {
            //New Newsletter
            $html .= $this->newsletterDetails();
        } elseif (Tools::isSubmit('status' . $this->name . '_newsletter')) {
            //TODO - Enable only if disabled... if enabled do nothing.
            $id = Tools::getValue('id');
            $sql = "UPDATE " . _DB_PREFIX_ . "ba_newsletter SET status = 1 - status WHERE id = " . (int)$id;
            $this->db->execute($sql);
            $html .= $this->newsletterList();
        } elseif (Tools::isSubmit('delete' . $this->name . '_newsletter')) {
            //TODO - Delete only if disabled... if enabled do nothing.
            /*$id = Tools::getValue('id');
            $this->db->delete('ba_newsletter', "id = " . (int)$id);
            $this->db->delete('ba_newsletter_maillisting', "id_newsletter = " . (int)$id);*/
            $html .= $this->newsletterList();
        } elseif (Tools::isSubmit('submitBulkdelete' . $this->name . '_newsletter')) {
            //TODO - Delete only if disabled... if enabled do nothing.
            /*
            $idArray = Tools::getValue('banewsletters_newsletterBox');
            $idString = implode(",", $idArray);
            $this->db->delete('ba_newsletter', "id IN(" . pSQL($idString) . ")");
            foreach ($idArray as $rowId) {
                $this->db->delete('ba_newsletter_maillisting', "id_newsletter = " . (int)$rowId);
            }*/
            $html .= $this->newsletterList();
        } else {
            $html .= $this->newsletterList();
        }
        return $html;
    }


    /**
     *function updateReport
     * @param idNewsletter newsletter
     * @param $fieldName update
     * @param increase steps
     */
    public function updateReport($idNewsletter, $fieldName, $increase = 1)
    {
        $date = date('y-m-d');
        $sql = "SELECT count(*) FROM " . _DB_PREFIX_ . "ba_newsletter_report WHERE
        id_newsletter = '" . (int)$idNewsletter . "' AND date_time = '" . $date . "'";
        $numberNewsletter = $this->db->getValue($sql);
        if ($numberNewsletter >= 1) {
            $sql = "UPDATE " . _DB_PREFIX_ . "ba_newsletter_report SET $fieldName = $fieldName + $increase
            WHERE id_newsletter = " . (int)$idNewsletter . " AND date_time = '$date'";
            $this->db->query($sql);
        } else {
            //var_dump($sql);
            $this->db->insert('ba_newsletter_report', array(
                'id_newsletter' => (int)$idNewsletter,
                'date_time' => $date,
                'number_view' => 0,
                'number_click' => 0,
                'number_send' => $increase
            ));
        }
    }
}
