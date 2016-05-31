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

class BAReport extends AdvNewsletters
{
    private $Db;
    public function __construct()
    {
        parent::__construct();
        $this->Db = Db::getInstance(_PS_USE_SQL_SLAVE_);
    }
    // add UI jquery from prestashop Core
    public function addJqueryUI($component, $theme = 'base', $check_dependencies = true)
    {
        $ui_path = array();
        if (!is_array($component)) {
            $component = array($component);
        }
        foreach ($component as $ui) {
            $ui_path = Media::getJqueryUIPath($ui, $theme, $check_dependencies);
            $this->context->controller->addCSS($ui_path['css']);
            $this->context->controller->addJS($ui_path['js']);
        }
    }
    public function report()
    {
        $this->context->controller->addJS($this->_path.'views/js/chart/Chart.min.js');
        $this->addJqueryUI('ui.datepicker');


        $sql = "SELECT count(id_newsletter) as total_newsletter,
                       SUM(number_click) as click,
                       SUM(number_send) as send,
                       SUM(number_view) as view,
                       date_time FROM "._DB_PREFIX_."newsletter_report ";

        $dateFormat=$this->context->language->date_format_lite;

        $dateFormat = str_replace("Y", "yy", $dateFormat);
        $dateFormat = str_replace("d", "dd", $dateFormat);
        $dateFormat = str_replace("m", "mm", $dateFormat);

        $this->smarty->assign('dateFomart', $dateFormat);

        $dateFormatLite=$this->context->language->date_format_lite;
        
        if (Tools::isSubmit('submit_filter')) {
            $dateFrom = Tools::getValue('date_from');
            $dateFromtmp = $this->formatDate($dateFrom, $dateFormatLite);
            $dateFrom = $this->convertDateTo($dateFromtmp, $dateFormatLite);
            $this->smarty->assign('dateFrom', $dateFrom);
            
            $dateTo = Tools::getValue('date_to');
            $dateTotmp = $this->formatDate($dateTo, $dateFormatLite);
            $dateTo = $this->convertDateTo($dateTotmp, $dateFormatLite);
            $this->smarty->assign('dateTo', $dateTo);

            $dateFromChart = $this->convertDateTo($dateFromtmp, "m/d/Y");
            $this->smarty->assign('dateFromChart', $dateFromChart);

            $dateToChart = $this->convertDateTo($dateTotmp, "m/d/Y");
            $this->smarty->assign('dateToChart', $dateToChart);

            $sql.="WHERE date_time>='".$dateFromtmp."' AND date_time<='".$dateTotmp."' ";
        } else {
            $dateTo = date("Y-m-d");
            $this->smarty->assign('dateTo', Tools::displayDate($dateTo));
            $this->smarty->assign('dateToChart', $dateTo);

            $dateFrom = date("Y-m-d", strtotime('+7 days ago'));
            $this->smarty->assign('dateFrom', Tools::displayDate($dateFrom));
            $this->smarty->assign('dateFromChart', $dateFrom);
            $sql.="WHERE date_time>='".$dateFrom."' AND date_time<='".$dateTo."' ";
        }
        $sql.=" GROUP BY date_time ORDER BY date_time";
        $newsletterArr = $this->Db->Executes($sql);

        foreach ($newsletterArr as $key => $newsletter) {
            $newsletterArr[$key]['date_time'] = $this->convertDateTo($newsletter['date_time'], $dateFormatLite);
        }
        $this->smarty->assign('newsletterArr', $newsletterArr);
        
        //Total Stats
        $sql = "SELECT count(id_newsletter) as total_newsletter, SUM(number_click) as click,
        SUM(number_send) as send, SUM(number_view) as view FROM "._DB_PREFIX_."newsletter_report";
        $totalNewsletterArr = $this->Db->Executes($sql);
        $this->smarty->assign('totalNewsletterArr', $totalNewsletterArr);

        $sql = "SELECT name as name_newsletter,
        "._DB_PREFIX_."newsletter_report.id_newsletter,
        SUM(number_click) as click, 
        SUM(number_send) as send, 
        SUM(number_view) as view FROM "._DB_PREFIX_."newsletter_report INNER JOIN "._DB_PREFIX_
        ."newsletter_campain on "._DB_PREFIX_."newsletter_report.id_newsletter="._DB_PREFIX_
        ."newsletter_campain.id";
        $sql .= " GROUP BY "._DB_PREFIX_."newsletter_report.id_newsletter";

        $reportArr = $this->Db->Executes($sql);
        $this->smarty->assign('reportArr', $reportArr);

        //get admin module
        $baAdminModule=AdminController::$currentIndex;
        $tokenModule=Tools::getAdminTokenLite('AdminModules');
        $this->smarty->assign('baAdminModule', $baAdminModule);
        $this->smarty->assign('tokenModule', $tokenModule);

        return $this->display("advnewsletters", 'views/templates/admin/report/report_view.tpl');
    }
    
    public function convertDateTo($dateString, $dateFormat)
    {
        $numberSecondDate = strtotime($dateString);
        $strDate = date($dateFormat, $numberSecondDate);
        return $strDate;
    }
    
    public function formatDate($date, $format)
    {
        $dateArr=array();
        if (strpos($date, " ")) {
            $dateArr = explode(" ", $date);
        }
        if (strpos($date, "/")) {
            $dateArr = explode("/", $date);
        }
        if (strpos($date, "-")) {
            $dateArr = explode("-", $date);
        }
        if (strpos($date, ";")) {
            $dateArr = explode(";", $date);
        }
        if (strpos($date, ":")) {
            $dateArr = explode(":", $date);
        }
        if (strpos($date, ".")) {
            $dateArr = explode(".", $date);
        }
        if (strpos($date, ",")) {
            $dateArr = explode(",", $date);
        }
        
        $formatArr=array();
        if (strpos($format, " ")) {
            $formatArr = explode(" ", $format);
        }
        if (strpos($format, "/")) {
            $formatArr = explode("/", $format);
        }
        if (strpos($format, "-")) {
            $formatArr = explode("-", $format);
        }
        if (strpos($format, ";")) {
            $formatArr = explode(";", $format);
        }
        if (strpos($format, ":")) {
            $formatArr = explode(":", $format);
        }
        if (strpos($format, ".")) {
            $formatArr = explode(".", $format);
        }
        if (strpos($format, ",")) {
            $formatArr = explode(",", $format);
        }
        $tmpArr = array();
        for ($i=0; $i < count($dateArr); $i++) {
            $tmpArr[$formatArr[$i]]=$dateArr[$i];
        }
        //var_dump($tmpArr);die;
        $dateFormatArr=array();
        foreach ($tmpArr as $key => $valueTmp) {
            if ($key == "Y" || $key == "y") {
                $dateFormatArr[0]=$valueTmp;
            } elseif ($key == "m" || $key == "M") {
                $dateFormatArr[1]=$valueTmp;
            } elseif ($key == "d" || $key == "D") {
                $dateFormatArr[2]=$valueTmp;
            }
        }
        ksort($dateFormatArr);
        $numberSecondDate = strtotime(implode("/", $dateFormatArr));
        if ($numberSecondDate == false) {
            return date("Y-m-d");
        }
        return implode("-", $dateFormatArr);
    }
}
