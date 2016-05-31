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
class BATemplate extends AdvNewsletters
{
    protected $db_fields = array('name', 'subject', 'content');

    public function templateList()
    {
        $fields_list = array(
            'id' => array(
                'title' => $this->l('ID'),
                'search' => false,
            ),
            'name' => array(
                'title' => $this->l('Name'),
                'width' => 100,
                'type' => 'text',
                'align' => 'left name',
                'search' => false
            ),
            'status' => array(
                'title' => $this->l('Status'),
                'active' => 'status',
                'width' => 100,
                'type' => 'bool',
                'align' => 'center',
                'class' => 'center',
                'orderby' => false,
                'search' => false,
            )
        );

        $helper_list = new HelperList();

        $helper_list->module = $this;
        $helper_list->title = 'Templates Manager';
        $helper_list->shopLinkType = '';
        $helper_list->no_link = true;
        $helper_list->show_toolbar = true;
        $helper_list->simple_header = false;
        $helper_list->identifier = 'id';
        $helper_list->table = $this->name . '_template';
        $helper_list->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name . '&task=templates';
        $helper_list->list_id = $this->name . '_template';
        $helper_list->token = Tools::getAdminTokenLite('AdminModules');
        $helper_list->actions = array('edit', 'delete');

        $helper_list->toolbar_btn['new'] = array(
            'href' => AdminController::$currentIndex . '&configure=' . $this->name . '&add' . $this->name
                . '_template&task=templates&token=' . Tools::getAdminTokenLite('AdminModules'),
            'desc' => $this->l('Add new')
        );

        $helper_list->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete selected'),
                'icon' => 'icon-trash',
                'confirm' => $this->l('Delete selected items?')
            )
        );

        /* Retrieve list data */
        $templates = $this->getTemplates();
        $helper_list->listTotal = count($templates);

        /* Paginate the result */
        $page = ($page = Tools::getValue('submitFilter' . $helper_list->table)) ? $page : 1;
        $pagination = ($pagination = Tools::getValue($helper_list->table . '_pagination')) ? $pagination : 50;
        $templates = $this->paginateTemplates($templates, $page, $pagination);

        return $helper_list->generateList($templates, $fields_list);
    }

    public function paginateTemplates($templates, $page = 1, $pagination = 50)
    {
        if (count($templates) > $pagination)
            $templates = array_slice($templates, $pagination * ($page - 1), $pagination);

        return $templates;
    }

    public function templateDetails($id = -1)
    {
        // Get default language
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

        // Init Fields form array
        $fields_form[0]['form'] = array(
            'legend' => array(
                'title' => ($id >= 0) ? $this->l('Edit Template') : $this->l('New Template'),
            ),
            'input' => array(
                array(
                    'type' => 'hidden',
                    'name' => 'advnewsletters_template_id'
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Name'),
                    'name' => 'advnewsletters_template_name',
                    'required' => true,
                    'desc' => $this->l('Template name, will be the newsletter name.'),
                    'lang' => true
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Subject'),
                    'name' => 'advnewsletters_template_subject',
                    'required' => true,
                    'desc' => $this->l('Template subject, will be the newsletter subject.'),
                    'lang' => true
                ),
                array(
                    'type' => 'textarea',
                    'label' => $this->l('Body'),
                    'name' => 'advnewsletters_template_content',
                    'required' => true,
                    'desc' => $this->l('The body of the newsletter.'),
                    'rows' => 10,
                    'cols' => 100,
                    'autoload_rte' => true,
                    'lang' => true
                ),
                //TODO - Add select with template list
                array(
                    'type' => 'switch',
                    'label' => $this->l('Status'),
                    'name' => 'advnewsletters_template_status',
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
                //TODO - Make the test button work
                array(
                    'type' => 'textbutton',
                    'label' => $this->l('Test Email'),
                    'name' => 'advnewsletters_template_test_email',
                    'button' => array(
                        'label' => 'Test',
                        'attributes' => array(
                            'onclick' => 'alert(\'something done\');',
                            'class' => 'send-test-mail'
                        )
                    ),
                    'class' => 'test-mail',
                    'desc' => $this->l('Insert an email to test the template.')
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
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name . "&task=templates";

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
                        '&token=' . Tools::getAdminTokenLite('AdminModules') . "&task=templates",
                ),
            'back' => array(
                'href' => AdminController::$currentIndex . '&token=' . Tools::getAdminTokenLite('AdminModules') . "&task=templates",
                'desc' => $this->l('Back to list')
            )
        );

        $templatesArray = $this->loadTemplateDetails($id);

        foreach ($templatesArray as $key => $value) {
            $helper->fields_value['advnewsletters_template_' . $key] = $value;
        }
        $helper->fields_value['advnewsletters_template_id'] = $id;
        $helper->fields_value["advnewsletters_template_test_email"] = '';

        $helper->tpl_vars = array(
            'languages' => $this->getLanguagesList(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm($fields_form);
    }

    public function loadTemplateDetails($id)
    {
        if ($id >= 0) {
            $dbquery = new DbQuery();
            $dbquery->select('*');
            $dbquery->from('newsletter_template', 't');
            $dbquery->innerJoin('newsletter_template_lang', 'l', 't.id = l.id_template');
            $dbquery->where('t.`id` = ' . (int)$id);

            $templatesArrayQuery = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($dbquery->build());
            $templatesArray = array();

            foreach ($this->db_fields as $key) {
                $templatesArray[$key] = array();
                foreach ($templatesArrayQuery as $templates) {
                    $id_lang = (int)$templates["id_lang"];
                    $templatesArray[$key][$id_lang] = $templates[$key];
                    if ($key == 'content') {
                        $templatesArray[$key][$id_lang] =
                            Tools::htmlentitiesDecodeUTF8($templatesArray[$key][$id_lang]);
                    }
                }
            }

            $templatesArray["status"] = $templatesArrayQuery[0]['status'];
        } else {
            foreach ($this->db_fields as $key) {
                $arr_language = Language::getLanguages(false);
                foreach ($arr_language as $value_lang) {
                    $templatesArray[$key][$value_lang['id_lang']] = '';
                }
            }
            $templatesArray["status"] = '1';
        }

        return $templatesArray;
    }

    public function saveTemplateDetails()
    {
        //TODO - Validate form
        $db = Db::getInstance(_PS_USE_SQL_SLAVE_);

        $id = (int)Tools::getValue('advnewsletters_template_id');
        $status = (int)Tools::getValue('advnewsletters_template_status');


        if ($id >= 0) {
            $db->update('newsletter_template', array(
                'status' => $status
            ), "id = " . $id);

            $db->delete('newsletter_template_lang', "id_template = " . $id);
        } else {
            $db->insert('newsletter_template', array(
                'status' => $status
            ));
            $id = (int)$db->Insert_ID();
        }

        $languages = Language::getLanguages(false);
        $idLangDefault = (int)(Configuration::get('PS_LANG_DEFAULT'));

        foreach ($languages as $language) {
            $data = array();

            foreach ($this->db_fields as $field) {
                $data[$field] = Tools::getValue('advnewsletters_template_' . $field . '_' . $language['id_lang']);
                if ($data[$field] == null || empty($data[$field])) {
                    $data[$field] = Tools::getValue('advnewsletters_template_' . $field . '_' . $idLangDefault);
                }
            }

            $data['content'] = Tools::htmlentitiesUTF8($data['content']);

            $db->insert('newsletter_template_lang', array(
                'id_template' => $id,
                'id_lang' => (int)$language['id_lang'],
                'name' => pSQL($data['name']),
                'subject' => pSQL($data['subject']),
                'content' => $data['content']
            ));
        }

        return true;
    }

    public function caseTemplate()
    {
        $html = "";

        $adminControllers = AdminController::$currentIndex;
        $token = '&token=' . Tools::getAdminTokenLite('AdminModules');
        $configAndTask = '&configure=' . $this->name . '&task=templates';


        if (Tools::isSubmit('submit' . $this->name)) {
            if (!$this->saveTemplateDetails()) {
                $html .= $this->displayError($this->l('Invalid data'));
            } else {
                $html .= $this->displayConfirmation($this->l('Settings updated'));
            }
            $html .= $this->templateList();
        } elseif (Tools::isSubmit('submitCancel') || Tools::isSubmit('submitReset' . $this->name . '_template')) {
            //Redirect to template list
            Tools::redirectAdmin($adminControllers . $token . $configAndTask);
        } elseif (Tools::isSubmit('update' . $this->name . '_template')) {
            //Update a specific template
            $idTemplate = Tools::getValue('id');

            $html .= $this->templateDetails((int)$idTemplate);
        } elseif (Tools::isSubmit('add' . $this->name . '_template')) {
            //Add a new template
            $html .= $this->templateDetails();
        } elseif (Tools::isSubmit('status' . $this->name . '_template')) {
            $idTemplate = Tools::getValue('id');

            $sql = "UPDATE " . _DB_PREFIX_ . "newsletter_template SET status = (1-status) WHERE id=" . (int)$idTemplate;
            Db::getInstance()->execute($sql);

            $html .= $this->displayConfirmation($this->l('Update successful'));
            $html .= $this->templateList();
        } elseif (Tools::isSubmit('delete' . $this->name . '_template')) {
            $idTemplate = Tools::getValue('id');

            $sql = "DELETE FROM " . _DB_PREFIX_ . "newsletter_template WHERE id=" . (int)$idTemplate;
            Db::getInstance()->execute($sql);

            $html .= $this->displayConfirmation($this->l('Update successful'));
            $html .= $this->templateList();
        } elseif (Tools::isSubmit('submitBulkdelete' . $this->name . '_template')) {
            $idTemplateArray = Tools::getValue('banewsletters_templateBox');
            $idTemplateString = implode(",", $idTemplateArray);

            $sql = "DELETE FROM " . _DB_PREFIX_ . "newsletter_template WHERE id IN (" . pSQL($idTemplateString) . ")";
            Db::getInstance()->execute($sql);

            $html .= $this->templateList();
        } else {
            $html .= $this->templateList();
        }

        return $html;
    }
}
