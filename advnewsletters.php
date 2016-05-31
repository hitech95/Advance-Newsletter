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
if (!defined('_PS_VERSION_'))
    exit;

class AdvNewsletters extends Module
{
    const GUEST_NOT_REGISTERED = -1;
    const CUSTOMER_NOT_REGISTERED = 0;
    const GUEST_REGISTERED = 1;
    const CUSTOMER_REGISTERED = 2;

    public function __construct()
    {


        require_once "includes/admin/newsletter.php";
        require_once "includes/admin/templates.php";
        require_once "includes/admin/subscribers.php";
        require_once "includes/admin/config.php";
        require_once "includes/admin/report.php";

        $this->name = "advnewsletters";
        $this->tab = "advertising_marketing";
        $this->version = "1.0.16";
        $this->author = "hitech95";
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Advanced Newsletter');
        $this->description = $this->l('Prestashop Advanced Newsletter');

        //TODO - What are those for?
        //$this->secure_key = Tools::encrypt($this->name);
        //$this->module_key = '87814996d98d3f4643562a9d0282cd42';
        $this->languagesArr = Language::getLanguages(false);
    }

    public function install()
    {
        if (!parent::install() || !$this->registerHook(array('header', 'footer', 'actionCustomerAccountAdd')))
            return false;

        Configuration::updateValue('advnewsletters_salt', Tools::passwdGen(16));

        return $this->createTable() && $this->saveDefaultConfig();
    }

    public function uninstall()
    {
        $this->dropTable();
        return parent::uninstall();
    }

    /**
     *function createTable
     *@ not param
     * Create the DM tables when the module is installed
     */
    private function createTable()
    {
        $return = true;

        // Queue Table
        $return &= Db::getInstance()->execute('
		CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'newsletter_queue` (
			`id` int(6) NOT NULL AUTO_INCREMENT,
			`to` varchar(255) NOT NULL,
			`subject` text NOT NULL,
			`body` text NOT NULL,
			`id_newsletter` int(11),
			PRIMARY KEY(`id`)
		) ENGINE=' . _MYSQL_ENGINE_ . ' default CHARSET=utf8');

        // Subscriber Table
        $return &= Db::getInstance()->execute('
		CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'newsletter_subscriber` (
			`id` int(6) NOT NULL AUTO_INCREMENT,
			`id_shop` INTEGER UNSIGNED NOT NULL DEFAULT \'1\',
			`id_shop_group` INTEGER UNSIGNED NOT NULL DEFAULT \'1\',
			`id_lang` int(11) NOT NULL,
			`email` varchar(255) NOT NULL,
			`newsletter_date_add` DATETIME NULL,
			`ip_registration_newsletter` varchar(15) NOT NULL,
			`http_referer` VARCHAR(255) NULL,
			`active` TINYINT(1) NOT NULL DEFAULT \'0\',
			PRIMARY KEY(`id`)
		) ENGINE=' . _MYSQL_ENGINE_ . ' default CHARSET=utf8');

        // Report/Stats Table
        $return &= Db::getInstance()->execute('
		CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'newsletter_report` (
              `id_newsletter` int(11) NOT NULL,
              `date_time` date NOT NULL,
              `number_view` int(11) NOT NULL,
              `number_click` int(11) NOT NULL,
              `number_send` int(11) NOT NULL,
               PRIMARY KEY (id_newsletter, date_time)
		) ENGINE=' . _MYSQL_ENGINE_ . ' default CHARSET=utf8');

        // Newsletter Campain Table
        $return &= Db::getInstance()->execute('
		CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'newsletter_campain` (
              `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
              `name` varchar(255) NOT NULL,
              `id_template` int(11) NOT NULL,
              `status` int(1) NOT NULL,
              `method` varchar(25) NOT NULL,
              `customers` BLOB DEFAULT NULL,
              `date` date NOT NULL
		) ENGINE=' . _MYSQL_ENGINE_ . ' default CHARSET=utf8');

        // Template Table
        $return &= Db::getInstance()->execute('
		CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'newsletter_template` (
              `id` int(11) NOT NULL,
              `status` int(1) NOT NULL,
              PRIMARY KEY (`id`)
		) ENGINE=' . _MYSQL_ENGINE_ . ' default CHARSET=utf8');

        // Template Language Table
        $return &= Db::getInstance()->execute('
		CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'newsletter_template_lang` (
              `id_template` int(11) NOT NULL,
              `id_lang` int(11) NOT NULL,
              `name` varchar(255) NOT NULL,
              `subject` varchar(255) NOT NULL,
              `content` text NOT NULL,
              PRIMARY KEY (`id_template`, `id_lang`)
		) ENGINE=' . _MYSQL_ENGINE_ . ' default CHARSET=utf8');

        return $return;
    }

    /**
     *function dropTable
     *@ not param
     * Drop the tables when the module is removed
     */
    private function dropTable()
    {
        $sql = "
            DROP TABLE " . _DB_PREFIX_ . "newsletter_queue;
            DROP TABLE " . _DB_PREFIX_ . "newsletter_subscriber;
            DROP TABLE " . _DB_PREFIX_ . "newsletter_report;
            DROP TABLE " . _DB_PREFIX_ . "newsletter_campain;
            DROP TABLE " . _DB_PREFIX_ . "newsletter_template;
            DROP TABLE " . _DB_PREFIX_ . "newsletter_template_lang;
        ";
        Db::getInstance()->query($sql);
    }

    /**
     *function saveDefaultConfig
     *@ not param
     * Load the default configuration for the module
     */
    public function saveDefaultConfig()
    {
        $return = true;
        $email_template = $this->display(__FILE__, 'models/sample_email_template.html');

        //Load the default template on the DB
        $languagesArr = Language::getLanguages(false);


        $return &= Db::getInstance()->insert('newsletter_template', array(
            'id' => 1,
            'status' => 1
        ));

        foreach ($languagesArr as $languages) {
            $return &= Db::getInstance()->insert('newsletter_template_lang', array(
                'id_template' => 1,
                'id_lang' => $languages['id_lang'],
                'name' => 'Sample name',
                'subject' => 'Sample subject',
                'content' => Tools::htmlentitiesUTF8($email_template)
            ));
        }

        $emailDefault = Configuration::get('PS_SHOP_EMAIL');

        Configuration::updateValue('advnewsletters_send_verification', '0');
        Configuration::updateValue('advnewsletters_send_confirmation', '0');
        Configuration::updateValue('advnewsletters_voucher', '');
        Configuration::updateValue('advnewsletters_mailDomainName', Tools::getHttpHost());
        Configuration::updateValue('advnewsletters_fromMail', $emailDefault);
        Configuration::updateValue('advnewsletters_fromNameMail', $emailDefault);
        Configuration::updateValue('advnewsletters_sleepMail', '20');
        Configuration::updateValue('advnewsletters_show_popup', '0');

        return $return;
    }

    //Configuration Page
    public function getContent()
    {
        $html = null;

        $this->context->controller->addCSS($this->_path . 'views/css/style.css');

        // baseAdminModuleUrl
        $baseAdminModuleUrl = AdminController::$currentIndex . '&token=' . Tools::getAdminTokenLite('AdminModules') . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name . '';

        if (Configuration::get('advnewsletters_cronjob') != "1") {
            $html .= '
            <div class="bootstrap ba_error">
                <div class="module_error alert alert-info">
                    ' . $this->l("You need set up cron job in your hosting with command") . '<br/>
                    0 0 * * * php  ' . _PS_ROOT_DIR_ . '/modules/' . $this->name . '/includes/auto_sent_mail.php >> '
                . _PS_ROOT_DIR_ . '/modules/' . $this->name . '/log_cronjob.txt 2>&1 <br/>
                    <form method="POST"><input type="submit" name="ba_cronjob" class="btn btn-default" value="'
                . $this->l("Yes. I did") . '" /></form>
                </div>
            </div>';
        }

        $task = Tools::getValue('task');

        if ($task == "") {
            $task = "newsletters";
        }

        $sql = "SELECT COUNT(id) FROM " . _DB_PREFIX_ . "newsletter_queue";
        $numberQueue = (int)Db::getInstance()->getValue($sql);

        $this->smarty->assign('baseModuleUrl', $baseAdminModuleUrl);
        $this->smarty->assign('numberQueue', $numberQueue);
        $this->smarty->assign('task', $task);

        $html .= $this->display(__FILE__, 'views/templates/admin/config_menu.tpl');

        switch ($task) {
            case "newsletters":
                $newsletter = new BANewsletterAdmin();

                $html .= $newsletter->caseNewsletter();
                break;
            case "templates":
                $template = new BATemplate();

                $html .= $template->caseTemplate();
                break;
            case "subscribers":
                $subscriber = new BASubscriber();

                $html .= $subscriber->caseSubscriber();
                break;
            case "setting":
                $configSMTP = new ConfigSMTP();

                $html .= $configSMTP->getContent();
                break;
            case "report":
                $report = new BAReport();

                $html .= $report->report();
                break;
        }


        if (Tools::isSubmit('ba_cronjob')) {
            Configuration::updateValue('advnewsletters_cronjob', 1);
        }

        return $html;
    }

    public function getNewsletter()
    {
        //TODO - calculate  mails in queue
        $dbquery = new DbQuery();
        $dbquery->select('n.`id` AS `id`, n.`name` AS `name`, n.`status` AS `sent`, n.`date` AS `date`, 0 as total_queue');
        $dbquery->from('newsletter_campain', 'n');
        $dbquery->orderBy('n.`date` DESC');

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($dbquery->build());
    }

    public function getTemplates($enabled = false)
    {
        $id_lang = $this->context->language->id;

        $dbquery = new DbQuery();
        $dbquery->select('t.`id` AS `id`, l.`name` AS `name`, t.`status` AS `status`');
        $dbquery->from('newsletter_template', 't');
        $dbquery->leftJoin('newsletter_template_lang', 'l', 't.id = l.id_template');
        $dbquery->where('l.`id_lang` = ' . $id_lang);

        if ($enabled) {
            $dbquery->where('t.`status` = 1');
        }

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($dbquery->build());
    }

    public function getCustomers()
    {
        $emailQuery = ' c.`email` AS `email`';

        $dbquery = new DbQuery();
        $dbquery->select('c.`id_customer` AS `id`, CONCAT(c.`lastname`, \' \', c.`firstname`) AS name, c.`email` AS `email`');
        $dbquery->from('customer', 'c');

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($dbquery->build());
    }

    public function getSubscribers()
    {
        $dbquery = new DbQuery();
        $dbquery->select('c.`id_customer` AS `id`, s.`name` AS `shop_name`, NULL AS `id_lang`, gl.`name` AS `gender`, c.`lastname`, c.`firstname`, c.`email`, c.`newsletter` AS `subscribed`, c.`newsletter_date_add`');
        $dbquery->from('customer', 'c');
        $dbquery->leftJoin('shop', 's', 's.id_shop = c.id_shop');
        $dbquery->leftJoin('gender', 'g', 'g.id_gender = c.id_gender');
        $dbquery->leftJoin('gender_lang', 'gl', 'g.id_gender = gl.id_gender AND gl.id_lang = ' . (int)$this->context->employee->id_lang);
        $dbquery->where('c.`newsletter` = 1');

        $customers = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($dbquery->build());

        $dbquery = new DbQuery();
        $dbquery->select('CONCAT(\'N\', n.`id`) AS `id`, s.`name` AS `shop_name`, n.`id_lang` AS `id_lang`, NULL AS `gender`, NULL AS `lastname`, NULL AS `firstname`, n.`email`, n.`active` AS `subscribed`, n.`newsletter_date_add`');
        $dbquery->from('newsletter_subscriber', 'n');
        $dbquery->leftJoin('shop', 's', 's.id_shop = n.id_shop');

        $non_customers = Db::getInstance()->executeS($dbquery->build());

        $subscribers = array_merge($customers, $non_customers);

        return $subscribers;
    }


    public function getLanguagesList()
    {
        // initialize languages array
        $languages_list = array();
        // get languages
        $langs = Language::getLanguages(true, $this->context->shop->id);
        // loop through languages
        foreach ($langs as $lang) {
            // check for default language
            if ($lang['id_lang'] == $this->context->language->id) {
                $isDefault = 1;
            } else {
                $isDefault = 0;
            }
            // populate language list
            $languages_list[] = array(
                'id_lang' => $lang['id_lang'],
                'name' => $lang['name'],
                'is_default' => $isDefault,
                'iso_code' => $lang['iso_code'],
            );
        }
        // return language list
        return $languages_list;
    }


    protected function _prepareHook($params)
    {
        if (Tools::isSubmit('submitNewsletter')) {
            //Register user to the newsletter or unsubscribe
            $this->newsletterRegistration();
            if ($this->error) {
                $this->smarty->assign(
                    array(
                        'color' => 'red',
                        'msg' => $this->error,
                        'nw_value' => isset($_POST['email']) ? pSQL($_POST['email']) : false,
                        'nw_error' => true,
                        'action' => $_POST['action']
                    )
                );
            } else if ($this->valid) {
                $this->smarty->assign(
                    array(
                        'color' => 'green',
                        'msg' => $this->valid,
                        'nw_error' => false
                    )
                );
            }
        }
        $this->smarty->assign('this_path', $this->_path);
    }

    public function hookDisplayLeftColumn($params)
    {
        if (!isset($this->prepared) || !$this->prepared)
            $this->_prepareHook($params);
        $this->prepared = true;
        return $this->display(__FILE__, 'blocknewsletter.tpl');
    }

    public function hookDisplayRightColumn($params)
    {
        return $this->hookDisplayLeftColumn($params);
    }

    public function hookFooter($params)
    {
        return $this->hookDisplayLeftColumn($params);
    }

    public function hookdisplayMaintenance($params)
    {
        return $this->hookDisplayLeftColumn($params);
    }

    public function hookDisplayHeader($params)
    {
        $this->context->controller->addCSS($this->_path . '/views/css/blocknewsletter.css', 'all');
        $this->context->controller->addJS($this->_path . '/views/js/blocknewsletter.js');
    }

    /**
     * Deletes duplicates email in newsletter table
     *
     * @param $params
     *
     * @return bool
     */
    public function hookActionCustomerAccountAdd($params)
    {
        //if e-mail of the created user address has already been added to the newsletter through the blocknewsletter module,
        //we delete it from blocknewsletter table to prevent duplicates
        $id_shop = $params['newCustomer']->id_shop;
        $email = $params['newCustomer']->email;
        if (Validate::isEmail($email))
            return (bool)Db::getInstance()->execute('DELETE FROM ' . _DB_PREFIX_ . 'newsletter_subscriber WHERE id_shop=' . (int)$id_shop . ' AND email=\'' . pSQL($email) . "'");

        return true;
    }

    /**
     * Check if this mail is registered for newsletters
     *
     * @param string $customer_email
     *
     * @return int -1 = not a customer and not registered
     *                0 = customer not registered
     *                1 = registered in block
     *                2 = registered in customer
     */
    public function isNewsletterRegistered($customer_email)
    {
        $sql = 'SELECT `email`
				FROM ' . _DB_PREFIX_ . 'newsletter_subscriber
				WHERE `email` = \'' . pSQL($customer_email) . '\'
				AND id_shop = ' . $this->context->shop->id;

        if (Db::getInstance()->getRow($sql))
            return self::GUEST_REGISTERED;

        $sql = 'SELECT `newsletter`
				FROM ' . _DB_PREFIX_ . 'customer
				WHERE `email` = \'' . pSQL($customer_email) . '\'
				AND id_shop = ' . $this->context->shop->id;

        if (!$registered = Db::getInstance()->getRow($sql))
            return self::GUEST_NOT_REGISTERED;

        if ($registered['newsletter'] == '1')
            return self::CUSTOMER_REGISTERED;

        return self::CUSTOMER_NOT_REGISTERED;
    }

    /**
     * Register in block newsletter
     */
    protected function newsletterRegistration()
    {
        if (empty($_POST['email']) || !Validate::isEmail($_POST['email']))
            return $this->error = $this->l('Invalid email address.');

        /* Unsubscription */
        else if ($_POST['action'] == '1') {
            $register_status = $this->isNewsletterRegistered($_POST['email']);

            if ($register_status < 1)
                return $this->error = $this->l('This email address is not registered.');

            if (!$this->unregister($_POST['email'], $register_status))
                return $this->error = $this->l('An error occurred while attempting to unsubscribe.');

            return $this->valid = $this->l('Unsubscription successful.');
        } /* Subscription */
        else if ($_POST['action'] == '0') {
            $register_status = $this->isNewsletterRegistered($_POST['email']);
            if ($register_status > 0)
                return $this->error = $this->l('This email address is already registered.');

            $email = pSQL($_POST['email']);
            if (!$this->isRegistered($register_status)) {
                if (Configuration::get('advnewsletters_send_verification')) {
                    // create an unactive entry in the newsletter database
                    if ($register_status == self::GUEST_NOT_REGISTERED)
                        $this->registerGuest($email, false);

                    if (!$token = $this->getToken($email, $register_status))
                        return $this->error = $this->l('An error occurred during the subscription process.');

                    $this->sendVerificationEmail($email, $token);

                    return $this->valid = $this->l('A verification email has been sent. Please check your inbox.');
                } else {
                    if ($this->register($email, $register_status))
                        $this->valid = $this->l('You have successfully subscribed to this newsletter.');
                    else
                        return $this->error = $this->l('An error occurred during the subscription process.');

                    $this->confirmSubscription($email);
                }
            }
        }
    }

    /**
     * Return true if the registered status correspond to a registered user
     *
     * @param int $register_status
     *
     * @return bool
     */
    protected function isRegistered($register_status)
    {
        return in_array(
            $register_status,
            array(self::GUEST_REGISTERED, self::CUSTOMER_REGISTERED)
        );
    }

    /**
     * Subscribe an email to the newsletter. It will create an entry in the newsletter table
     * or update the customer table depending of the register status
     *
     * @param string $email
     * @param int $register_status
     *
     * @return bool
     */
    protected function register($email, $register_status)
    {
        if ($register_status == self::GUEST_NOT_REGISTERED)
            return $this->registerGuest($email);

        if ($register_status == self::CUSTOMER_NOT_REGISTERED)
            return $this->registerUser($email);

        return false;
    }

    /**
     * Unsubscribe an email to the newsletter. It will delete an entry in the newsletter table
     * or update the customer table depending of the register status
     *
     * @param string $email
     * @param int $register_status
     *
     * @return bool
     */
    protected function unregister($email, $register_status)
    {
        if ($register_status == self::GUEST_REGISTERED)
            $sql = 'DELETE FROM ' . _DB_PREFIX_ . 'newsletter_subscriber WHERE `email` = \'' . pSQL($email) . '\' AND id_shop = ' . $this->context->shop->id;
        else if ($register_status == self::CUSTOMER_REGISTERED)
            $sql = 'UPDATE ' . _DB_PREFIX_ . 'customer SET `newsletter` = 0 WHERE `email` = \'' . pSQL($email) . '\' AND id_shop = ' . $this->context->shop->id;

        if (!isset($sql) || !Db::getInstance()->execute($sql))
            return false;

        return true;
    }

    /**
     * Subscribe a customer to the newsletter
     *
     * @param string $email
     *
     * @return bool
     */
    protected function registerUser($email)
    {
        $sql = 'UPDATE ' . _DB_PREFIX_ . 'customer
				SET `newsletter` = 1, newsletter_date_add = NOW(), `ip_registration_newsletter` = \'' . pSQL(Tools::getRemoteAddr()) . '\'
				WHERE `email` = \'' . pSQL($email) . '\'
				AND id_shop = ' . $this->context->shop->id;

        return Db::getInstance()->execute($sql);
    }

    /**
     * Subscribe a guest to the newsletter
     *
     * @param string $email
     * @param bool $active
     *
     * @return bool
     */
    protected function registerGuest($email, $active = true)
    {
        $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'newsletter_subscriber (id_shop, id_shop_group, id_lang, email, newsletter_date_add, ip_registration_newsletter, http_referer, active)
				VALUES
				(' . $this->context->shop->id . ',
				' . $this->context->shop->id_shop_group . ',
				' . $this->context->language->id . ',
				\'' . pSQL($email) . '\',
				NOW(),
				\'' . pSQL(Tools::getRemoteAddr()) . '\',
				(
					SELECT c.http_referer
					FROM ' . _DB_PREFIX_ . 'connections c
					WHERE c.id_guest = ' . (int)$this->context->customer->id . '
					ORDER BY c.date_add DESC LIMIT 1
				),
				' . (int)$active . '
				)';

        return Db::getInstance()->execute($sql);
    }

    /**
     * Activate an email to the newsletter. It will update the customer table
     *
     * @param string $email
     *
     * @return bool
     */
    public function activateGuest($email)
    {
        return Db::getInstance()->execute(
            'UPDATE `' . _DB_PREFIX_ . 'newsletter_subscriber`
						SET `active` = 1
						WHERE `email` = \'' . pSQL($email) . '\''
        );
    }

    /**
     * Returns a guest email by token
     *
     * @param string $token
     *
     * @return string email
     */
    protected function getGuestEmailByToken($token)
    {
        $sql = 'SELECT `email`
				FROM `' . _DB_PREFIX_ . 'newsletter_subscriber`
				WHERE MD5(CONCAT( `email` , `newsletter_date_add`, \'' . pSQL(Configuration::get('advnewsletters_salt')) . '\')) = \'' . pSQL($token) . '\'
				AND `active` = 0';

        return Db::getInstance()->getValue($sql);
    }

    /**
     * Returns a customer email by token
     *
     * @param string $token
     *
     * @return string email
     */
    protected function getUserEmailByToken($token)
    {
        $sql = 'SELECT `email`
				FROM `' . _DB_PREFIX_ . 'customer`
				WHERE MD5(CONCAT( `email` , `date_add`, \'' . pSQL(Configuration::get('advnewsletters_salt')) . '\')) = \'' . pSQL($token) . '\'
				AND `newsletter` = 0';

        return Db::getInstance()->getValue($sql);
    }

    /**
     * Return a token associated to an user
     *
     * @param string $email
     * @param string $register_status
     *
     * @return bool
     */
    protected function getToken($email, $register_status)
    {
        if (in_array($register_status, array(self::GUEST_NOT_REGISTERED, self::GUEST_REGISTERED))) {
            $sql = 'SELECT MD5(CONCAT( `email` , `newsletter_date_add`, \'' . pSQL(Configuration::get('advnewsletters_salt')) . '\')) as token
					FROM `' . _DB_PREFIX_ . 'newsletter_subscriber`
					WHERE `active` = 0
					AND `email` = \'' . pSQL($email) . '\'';
        } else if ($register_status == self::CUSTOMER_NOT_REGISTERED) {
            $sql = 'SELECT MD5(CONCAT( `email` , `date_add`, \'' . pSQL(Configuration::get('advnewsletters_salt')) . '\' )) as token
					FROM `' . _DB_PREFIX_ . 'customer`
					WHERE `newsletter` = 0
					AND `email` = \'' . pSQL($email) . '\'';
        }

        return Db::getInstance()->getValue($sql);
    }

    /**
     * Ends the registration process to the newsletter
     *
     * @param string $token
     *
     * @return string
     */
    public function confirmEmail($token)
    {
        $activated = false;

        if ($email = $this->getGuestEmailByToken($token))
            $activated = $this->activateGuest($email);
        else if ($email = $this->getUserEmailByToken($token))
            $activated = $this->registerUser($email);

        if (!$activated)
            return $this->l('This email is already registered and/or invalid.');

        $this->confirmSubscription($email);

        return $this->l('Thank you for subscribing to our newsletter.');
    }

    /**
     * Send the confirmation mails to the given $email address if needed.
     *
     * @param string $email Email where to send the confirmation
     *
     * @note the email has been verified and might not yet been registered. Called by AuthController::processCustomerNewsletter
     *
     */
    public function confirmSubscription($email)
    {
        if ($email) {
            if ($discount = Configuration::get('advnewsletters_voucher'))
                $this->sendVoucher($email, $discount);

            if (Configuration::get('advnewsletters_send_confirmation'))
                $this->sendConfirmationEmail($email);
        }
    }

    /**
     * Send an email containing a voucher code
     *
     * @param $email
     * @param $code
     *
     * @return bool|int
     */
    protected function sendVoucher($email, $code)
    {
        return Mail::Send($this->context->language->id, 'newsletter_voucher', Mail::l('Newsletter voucher', $this->context->language->id), array('{discount}' => $code), $email, null, null, null, null, null, dirname(__FILE__) . '/mails/', false, $this->context->shop->id);
    }

    /**
     * Send a confirmation email
     *
     * @param string $email
     *
     * @return bool
     */
    protected function sendConfirmationEmail($email)
    {
        return Mail::Send($this->context->language->id, 'newsletter_conf', Mail::l('Newsletter confirmation', $this->context->language->id), array(), pSQL($email), null, null, null, null, null, dirname(__FILE__) . '/mails/', false, $this->context->shop->id);
    }

    /**
     * Send a verification email
     *
     * @param string $email
     * @param string $token
     *
     * @return bool
     */
    protected function sendVerificationEmail($email, $token)
    {
        $verif_url = Context::getContext()->link->getModuleLink(
            'blocknewsletter', 'verification', array(
                'token' => $token,
            )
        );

        return Mail::Send($this->context->language->id, 'newsletter_verif', Mail::l('Email verification', $this->context->language->id), array('{verif_url}' => $verif_url), $email, null, null, null, null, null, dirname(__FILE__) . '/mails/', false, $this->context->shop->id);
    }
}
