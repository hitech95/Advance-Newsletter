{**
* 2007-2016 PrestaShop
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
*  @copyright 2007-2016 PrestaShop SA
*  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
* @since 1.6
*}
<div>
    <div class="report1550 frm templates panel col-lg-12" style="padding: 0px 10px 10px;">
        <div class="panel-heading" style="margin:0 0 10px 0;">{l s='Report View' mod='banewsletters'}</div>
        <div class="col-sm-7">
            {foreach $totalNewsletterArr item=rowTotalNewsletter}
                <div class="wmt-legend-metric-group goog-inline-block">
                    <div class="wmt-legend-metric-title">{l s='Newsletter' mod='banewsletters'}</div>
                    <div class="">
                        <div class="total-message">{$rowTotalNewsletter.total_newsletter|escape:'htmlall':'UTF-8'}</div>
                    </div>
                    <div class="color-legend-line " style="border-bottom-color:#4d90fe"></div>
                </div>
                <div class="wmt-legend-metric-group goog-inline-block">
                    <div class="wmt-legend-metric-title">{l s='Click' mod='banewsletters'}</div>
                    <div class="">
                        <div class="total-message">{if $rowTotalNewsletter.click!=""}{$rowTotalNewsletter.click|escape:'htmlall':'UTF-8'}{else}0{/if}</div>
                    </div>
                    <div class="color-legend-line " style="border-bottom-color:#dd4b39"></div>
                </div>
                <div class="wmt-legend-metric-group goog-inline-block">
                    <div class="wmt-legend-metric-title">{l s='Sent' mod='banewsletters'}</div>
                    <div class="">
                        <div class="total-message">{if $rowTotalNewsletter.send!=""}{$rowTotalNewsletter.send|escape:'htmlall':'UTF-8'}{else}0{/if}</div>
                    </div>
                    <div class="color-legend-line " style="border-bottom-color:#ff9900"></div>
                </div>
                <div class="wmt-legend-metric-group goog-inline-block">
                    <div class="wmt-legend-metric-title">{l s='View' mod='banewsletters'}</div>
                    <div class="">
                        <div class="total-message">{if $rowTotalNewsletter.view!=""}{$rowTotalNewsletter.view|escape:'htmlall':'UTF-8'}{else}0{/if}</div>
                    </div>
                    <div class="color-legend-line " style="border-bottom-color:#109618"></div>
                </div>
            {/foreach}
        </div>
        <form action="" method="POST" style="margin-top:25px;">
            <div class="input-group col-sm-2" style="float:left;margin-right:5px;">
                <span class="input-group-addon">{l s='From' mod='banewsletters'}</span>
                <input type="text" class="datepicker input-medium" name="date_from"
                       value="{$dateFrom|escape:'htmlall':'UTF-8'}"/>
                <span class="input-group-addon"><i class="icon-calendar-empty"></i></span>
            </div>
            <div class="input-group col-sm-2" style="float:left;">
                <span class="input-group-addon">{l s='To' mod='banewsletters'}</span>
                <input type="text" class="datepicker input-medium" name="date_to"
                       value="{$dateTo|escape:'htmlall':'UTF-8'}"/>
                <span class="input-group-addon"><i class="icon-calendar-empty"></i></span>
            </div>
            <div class="refine_campaign col-sm-4" style="float:left;">
                <div style="float:left;">
                    <button type="submit" value="1" name="submit_filter" class="btn btn-default">Fillter</button>
                </div>
            </div>
        </form>
        <!--Chart-->
        <div id="chart_wrapper">
            <div id="canvas-holder2">
                <canvas id="chart2" width="850" height="300"/>
            </div>
        </div>

        <!--<div id="chartjs-tooltip"></div>-->
        <!--End Chart-->
        <script>
            //datetime picker
            $(document).ready(function () {
                $(".datepicker").datepicker({
                    prevText: '',
                    nextText: '',
                    dateFormat: '{$dateFomart|escape:'htmlall':'UTF-8'}'
                });
            });
            //chart
            Chart.defaults.global.pointHitDetectionRadius = 1;
            function baGetNumber(number) {
                return number < 10 ? '0' + number : '' + number;
            }
            var dateformatchart = "{$dateFomart|escape:'htmlall':'UTF-8'}";

            function baCreatDateArray(startDate, endDate) {
                var start = new Date(startDate);
                var pushstart = $.datepicker.formatDate(dateformatchart, new Date(startDate));
                var end = new Date(endDate);
                var date = [];
                while (start <= end) {
                    date.push(pushstart);
                    var newDate = start.setDate(start.getDate() + 1);
                    start = new Date(newDate);
                    pushstart = $.datepicker.formatDate(dateformatchart, new Date(newDate));
                }
                return date;
            }

            var dateArr = baCreatDateArray("{$dateFromChart|escape:'htmlall':'UTF-8'}", "{$dateToChart|escape:'htmlall':'UTF-8'}");
            var numberDate = dateArr.length;
            if (numberDate > 31) {
                jQuery('#canvas-holder2').css('width', (numberDate * 30) + 'px');
            } else {
                jQuery('#chart_wrapper').css('overflow', 'hidden');
            }
            var dataArr = [];
            var dataClick = [];
            var dataSend = [];
            var dataView = [];
            var tmp_arr;
            {foreach $newsletterArr item=rowData}
            tmp_arr = new Array();
            tmp_arr[0] = "{$rowData.date_time|escape:'htmlall':'UTF-8'}";
            tmp_arr[1] = {$rowData.send|escape:'htmlall':'UTF-8'};
            tmp_arr[2] = {$rowData.view|escape:'htmlall':'UTF-8'};
            tmp_arr[3] = {$rowData.click|escape:'htmlall':'UTF-8'};
            dataArr[dataArr.length] = tmp_arr;
            {/foreach}

            var check;
            for (var i = 0; i < dateArr.length; i++) {
                check = false;
                for (var j = 0; j < dataArr.length; j++) {
                    if (dataArr[j][0] == dateArr[i]) {
                        check = j;
                        break;
                    }

                }
                /////////
                if (check === false) {
                    dataClick.push(0);
                    dataSend.push(0);
                    dataView.push(0);
                } else {
                    dataClick.push(dataArr[check][3]);
                    dataSend.push(dataArr[check][1]);
                    dataView.push(dataArr[check][2]);
                }
            }

            var lineChartData = {
                labels: dateArr,
                datasets: [{
                    label: "Click",
                    fillColor: "#dd4b39",
                    strokeColor: "#dd4b39",
                    pointColor: "#dd4b39",
                    pointStrokeColor: "#fff",
                    pointHighlightFill: "#000",
                    pointHighlightStroke: "#fff",
                    data: dataClick
                }, {
                    label: "Send",
                    fillColor: "#ff9900",
                    strokeColor: "#ff9900",
                    pointColor: "#ff9900",
                    pointStrokeColor: "#fff",
                    pointHighlightFill: "#000",
                    pointHighlightStroke: "#fff",
                    data: dataSend
                }, {
                    label: "View",
                    fillColor: "#109618",
                    strokeColor: "#109618",
                    pointColor: "#109618",
                    pointStrokeColor: "#fff",
                    pointHighlightFill: "#000",
                    pointHighlightStroke: "#fff",
                    data: dataView
                }]
            };

            window.onload = function () {

                var ctx2 = document.getElementById("chart2").getContext("2d");
                window.myLine = new Chart(ctx2).Line(lineChartData, {
                    responsive: true,
                    datasetFill: false
                });
            };
            //End chart
        </script>

        <div class="clearfix">
            <table class="table report table-responsive">
                <tr>
                    <th class="title_box">Name Newsletter</th>
                    <th class="title_box center">Click</th>
                    <th class="title_box center">View</th>
                    <th class="title_box center">Sent</th>
                </tr>
                {foreach $reportArr item=rowReport}
                    <tr class="report_content">
                        <td>{$rowReport.name_newsletter|escape:'htmlall':'UTF-8'}</td>
                        <td class="center">{$rowReport.click|escape:'htmlall':'UTF-8'}</td>
                        <td class="center">{$rowReport.view|escape:'htmlall':'UTF-8'}</td>
                        <td class="center">{$rowReport.send|escape:'htmlall':'UTF-8'}</td>
                    </tr>
                {/foreach}
            </table>
        </div>
    </div>
</div>