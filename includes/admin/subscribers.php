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
class BASubscriber extends AdvNewsletters
{
    public function mailList()
    {
        $fields_list = array(

            'id' => array(
                'title' => $this->l('ID'),
                'search' => false,
            ),
            'shop_name' => array(
                'title' => $this->l('Shop'),
                'search' => false,
            ),
            'gender' => array(
                'title' => $this->l('Gender'),
                'search' => false,
            ),
            'lastname' => array(
                'title' => $this->l('Lastname'),
                'search' => false,
            ),
            'firstname' => array(
                'title' => $this->l('Firstname'),
                'search' => false,
            ),
            'email' => array(
                'title' => $this->l('Email'),
                'search' => false,
            ),
            'lang' => array(
                'title' => $this->l('Language'),
                'search' => false,
            ),
            'subscribed' => array(
                'title' => $this->l('Enabled'),
                'type' => 'bool',
                'active' => 'validate',
                'search' => false,
            ),
            'newsletter_date_add' => array(
                'title' => $this->l('Subscribed on'),
                'type' => 'date',
                'search' => false,
            )
        );

        if (!Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE'))
            unset($fields_list['shop_name']);

        $helper_list = New HelperList();
        $helper_list->module = $this;
        $helper_list->title = $this->l('Newsletter registrations');
        $helper_list->shopLinkType = '';
        $helper_list->no_link = true;
        $helper_list->show_toolbar = true;
        $helper_list->simple_header = false;
        $helper_list->identifier = 'id';
        $helper_list->table = 'merged';
        $helper_list->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name . '&task=subscribers';
        $helper_list->token = Tools::getAdminTokenLite('AdminModules');
        $helper_list->actions = array('viewCustomer', 'unsubscribe');

        $helper_list->bulk_actions = array(
            'unsubscribe' => array(
                'text' => $this->l('Unsubscribe selected'),
                'icon' => 'icon-trash',
                'confirm' => $this->l('Unsubscribe selected items?')
            ),
            'enable' => array(
                'text' => $this->l('Enable selected'),
                'icon' => 'icon-off',
                'confirm' => $this->l('Enable selected items?')
            )
        );

        // This is needed for displayEnableLink to avoid code duplication
        $this->_helperlist = $helper_list;

        /* Retrieve list data */
        $subscribers = $this->getSubscribers();
        $helper_list->listTotal = count($subscribers);

        /* Paginate the result */
        $page = ($page = Tools::getValue('submitFilter' . $helper_list->table)) ? $page : 1;
        $pagination = ($pagination = Tools::getValue($helper_list->table . '_pagination')) ? $pagination : 50;
        $subscribers = $this->paginateSubscribers($subscribers, $page, $pagination);

        return $helper_list->generateList($subscribers, $fields_list);
    }

    public function displayViewCustomerLink($token = null, $id, $name = null)
    {
        $this->smarty->assign(array(
            'href' => 'index.php?controller=AdminCustomers&id_customer=' . (int)$id . '&updatecustomer&token=' . Tools::getAdminTokenLite('AdminCustomers'),
            'action' => $this->l('View'),
            'disable' => !((int)$id > 0),
        ));

        return $this->display("advnewsletters", 'views/templates/admin/subscribers/list_action_viewcustomer.tpl');
    }

    public function displayEnableLink($token, $id, $value, $active, $id_category = null, $id_product = null, $ajax = false)
    {
        $this->smarty->assign(array(
            'ajax' => $ajax,
            'enabled' => (bool)$value,
            'url_enable' => $this->_helperlist->currentIndex . '&' . $this->_helperlist->identifier . '=' . $id . '&' . $active . ($ajax ? '&action=' . $active . $this->_helperlist->table . '&ajax=' . (int)$ajax : '') . '&token=' . $token
        ));

        return $this->display("advnewsletters", 'views/templates/admin/subscribers/list_action_enable.tpl');
    }

    public function displayUnsubscribeLink($token = null, $id, $name = null)
    {
        $this->smarty->assign(array(
            'href' => $this->_helperlist->currentIndex . '&unsubscribe&' . $this->_helperlist->identifier . '=' . $id . '&token=' . $token,
            'action' => $this->l('Unsubscribe'),
        ));

        return $this->display("advnewsletters", 'views/templates/admin/subscribers/list_action_unsubscribe.tpl');
    }

    public function getSubscribers()
    {
        $dbquery = new DbQuery();
        $dbquery->select('c.`id_customer` AS `id`, s.`name` AS `shop_name`, gl.`name` AS `gender`, c.`lastname`, c.`firstname`, c.`email`, c.`newsletter` AS `subscribed`, c.`newsletter_date_add`');
        $dbquery->from('customer', 'c');
        $dbquery->leftJoin('shop', 's', 's.id_shop = c.id_shop');
        $dbquery->leftJoin('gender', 'g', 'g.id_gender = c.id_gender');
        $dbquery->leftJoin('gender_lang', 'gl', 'g.id_gender = gl.id_gender AND gl.id_lang = ' . (int)$this->context->employee->id_lang);
        $dbquery->where('c.`newsletter` = 1');
        /*if ($this->_searched_email)
            $dbquery->where('c.`email` LIKE \'%' . pSQL($this->_searched_email) . '%\' ');*/

        $customers = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($dbquery->build());

        $dbquery = new DbQuery();
        $dbquery->select('CONCAT(\'N\', n.`id`) AS `id`, s.`name` AS `shop_name`, NULL AS `gender`, NULL AS `lastname`, NULL AS `firstname`, n.`email`, n.`active` AS `subscribed`, n.`newsletter_date_add`');
        $dbquery->from('newsletter_subscriber', 'n');
        $dbquery->leftJoin('shop', 's', 's.id_shop = n.id_shop');

        $non_customers = Db::getInstance()->executeS($dbquery->build());

        $subscribers = array_merge($customers, $non_customers);

        return $subscribers;
    }

    public function paginateSubscribers($subscribers, $page = 1, $pagination = 50)
    {
        if (count($subscribers) > $pagination)
            $subscribers = array_slice($subscribers, $pagination * ($page - 1), $pagination);

        return $subscribers;
    }

    public function caseSubscriber()
    {
        $html = "";

        if (Tools::isSubmit('validate')) {
            //Enable or disable the newsletter

            $id = Tools::getValue('id');
            if (preg_match('/(^N)/', $id)) {
                $id = (int)substr($id, 1);

                $sql = 'UPDATE ' . _DB_PREFIX_ . 'newsletter_subscriber SET active = (1 - active) WHERE id = ' . $id;
                Db::getInstance()->execute($sql);
            } else {
                $c = new Customer((int)$id);
                $c->newsletter = (int)!$c->newsletter;
                $c->update();
            }
            $html .= $this->displayConfirmation($this->l('Update successful'));
        } elseif (Tools::isSubmit('unsubscribe')) {
            //TODO - TEST Unsubscribe the email
            $id = Tools::getValue('id');

            $email = null;

            if (preg_match('/(^N)/', $id)) {
                $dbquery = new DbQuery();

                $id = (int)substr($id, 1);

                $dbquery->select('n.`email` AS `email`');
                $dbquery->from('newsletter_subscriber', 'n');
                $dbquery->where('n.`id` = ' . $id);

                $email = (boolean)(Db::getInstance()->executeS($dbquery->build())[0]["email"]);
            } else {
                $c = new Customer((int)$id);
                $email = $c->email;
            }

            $this->unregister($email, isNewsletterRegistered($email));
            $html .= $this->displayConfirmation($this->l('Update successful'));
        }

        return $html . $this->mailList();
    }
}
