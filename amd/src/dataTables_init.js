define([
    "jquery",
    "local_kdashboard/dataTables",
    "local_kdashboard/dataTables.buttons",
    "local_kdashboard/dataTables.buttons.html5",
    "local_kdashboard/dataTables.buttons.print",
    "local_kdashboard/dataTables.responsive",
    "local_kdashboard/jszip",
], function($) {
    var dataTables_init = {
        language: "en",

        init: function(tableid, params) {

            var langClass = $('body').attr('class').match(/lang-\w+/);
            if (langClass) {
                var language = langClass[0].replace('lang-', '');
                dataTables_init.language = language.replace(/_(\w+)/, (_, match) => `-${match.toUpperCase()}`);
            }

            if (!params) {
                var params_json = $(`#tableparams_${tableid}`).val();
                params = JSON.parse(params_json);
            }

            var renderer = {
                mustacheRenderer: function(data, type, row, info) {
                    if (type == "sort") {
                        var d = data.replace(/<[^>]*>/g, '');
                        if (/^\d/.test(data)) {
                            d = d.split(",").join(".");
                            d = parseFloat(d);

                            return d;
                        }
                        return d;

                    } else if (type != 'display') {
                        return data;
                    }

                    var columns = info.settings.aoColumns;
                    var col = info.col;
                    var column = columns[col];
                    var columnname = column.data;

                    if (row[`${columnname}_mustache`]) {
                        return row[`${columnname}_mustache`];
                    } else {
                        return data;
                    }
                },
                numberRenderer: function(data, type, row, info) {
                    if (type == "sort") {
                        if (/^\d/.test(data)) {
                            data = data.split(",").join(".");
                            data = parseFloat(data);

                            return data;
                        }
                        return data;
                    }
                    if (data === null) {
                        return "";
                    }
                    if (type != 'display') {
                        return data;
                    }

                    data = dataTables_init.numberFormat(data, 2);

                    return '<div class="text-center text-nowrap">' + data + '</div>';
                },
                currencyRenderer: function(data, type, row, info) {
                    if (type != 'display') {
                        return data;
                    }

                    return '<div class="text-center text-nowrap">R$ ' + data + '</div>';
                },
                filesizeRenderer: function(data, type, row, info) {
                    if (type != 'display') {
                        return data;
                    }

                    if (data == null || data < 1) {
                        return '0 b';
                    } else if (data < 1000) {
                        return data + ' b';
                    } else if (data < 1000 * 1000) {
                        data = data / (1000);
                        return data.toFixed(2) + ' Kb';
                    } else if (data < 1000 * 1000 * 1000) {
                        data = data / (1000 * 1000);
                        return data.toFixed(2) + ' Mb';
                    } else if (data < 1000 * 1000 * 1000 * 1000) {
                        data = data / (1000 * 1000 * 1000);
                        return data.toFixed(2) + ' Gb';
                    } else {
                        return data.toFixed(2) + ' Tb';
                    }
                },
                dateRenderer: function(data, type, row, info) {
                    if (type != 'display') {
                        return data;
                    }

                    if (data < 1000) {
                        return "";
                    }

                    function twoDigit($value) {
                        if ($value < 10) {
                            return '0' + $value;
                        }
                        return $value;
                    }

                    var a = new Date(data * 1000);
                    var year = a.getFullYear();
                    var month = twoDigit(a.getMonth() + 1);
                    var day = twoDigit(a.getDate());

                    var result = M.util.get_string('date_renderer_format', "local_kdashboard");
                    result = result.replace("day", day);
                    result = result.replace("month", month);
                    result = result.replace("year", year);

                    return '<div class="text-nowrap">' + result + '</div>';
                },
                datetimeRenderer: function(data, type, row, info) {
                    if (type != 'display') {
                        return data;
                    }

                    if (data < 1000) {
                        return "";
                    }

                    function twoDigit($value) {
                        if ($value < 10) {
                            return '0' + $value;
                        }
                        return $value;
                    }

                    var a = new Date(data * 1000);
                    var year = a.getFullYear();
                    var month = twoDigit(a.getMonth() + 1);
                    var day = twoDigit(a.getDate());
                    var hour = twoDigit(a.getHours());
                    var min = twoDigit(a.getMinutes());

                    var result = M.util.get_string('datetime_renderer_format', "local_kdashboard");
                    result = result.replace("day", day);
                    result = result.replace("month", month);
                    result = result.replace("year", year);
                    result = result.replace("hour", hour);
                    result = result.replace("min", min);

                    return '<div class="text-nowrap">' + result + '</div>';
                },
                visibleRenderer: function(data, type, row, info) {
                    if (type != 'display') {
                        if (type == 'filter') {
                            if (!data) {
                                return M.util.get_string('invisible', "local_kdashboard");
                            } else {
                                return M.util.get_string('visible', "local_kdashboard");
                            }
                        }
                        return data;
                    }

                    if (!data) {
                        return '<div class="status-pill grey"  title="' +
                            M.util.get_string('invisible', "local_kdashboard") +
                            '"></div>';
                    } else {
                        return '<div class="status-pill green" title="' + M.util.get_string('visible', "local_kdashboard") +
                            '"></div>';
                    }
                },
                statusRenderer: function(data, type, row, info) {
                    if (type != 'display') {
                        if (type == 'filter') {
                            if (data) {
                                return M.util.get_string('inactive', "local_kdashboard");
                            } else {
                                return M.util.get_string('active', "local_kdashboard");
                            }
                        }
                        return data;
                    }

                    if (data) {
                        return '<div class="status-pill grey"  title="' +
                            M.util.get_string('inactive', "local_kdashboard") +
                            '"></div>';
                    } else {
                        return '<div class="status-pill green" title="' +
                            M.util.get_string('active', "local_kdashboard") +
                            '"></div>';
                    }
                },
                deletedRenderer: function(data, type, row, info) {
                    if (type != 'display') {
                        if (type == 'filter') {
                            if (!data) {
                                return M.util.get_string('notification_status_deleted', "local_kdashboard");
                            } else {
                                return M.util.get_string('active', "local_kdashboard");
                            }
                        }
                        return data;
                    }

                    if (!data) {
                        return `<div class="status-pill grey"
                                     title="${M.util.get_string('notification_status_deleted', "local_kdashboard")}"></div>`;
                    } else {
                        return `<div class="status-pill green"
                                     title="${M.util.get_string('active', "local_kdashboard")}"></div>`;
                    }
                },
                trueFalseRenderer: function(data, type, row, info) {
                    if (type != 'display') {
                        return data;
                    }

                    if (data == 0 || data == false || data == 'false') {
                        return M.util.get_string('no');
                    } else {
                        return M.util.get_string('yes');
                    }
                },
                userphotoRenderer: function(data, type, row, info) {
                    if (type != 'display') {
                        return data;
                    }

                    return `<img class="media-object"
                                 src="${M.cfg.wwwroot}/local/kdashboard/profile-image.php?type=photo_user&id=${data}"
                                 style="width:35px;height:35px" />`;
                },
                secondsRenderer: function(data, type, row, info) {
                    if (type != 'display') {
                        return data;
                    }

                    var tempo = parseInt(data);
                    if (isNaN(tempo) || tempo < 1) {
                        return '00:00:00';
                    }

                    var min = parseInt(tempo / 60);
                    var hor = parseInt(min / 60);

                    min = min % 60;
                    if (min < 10) {
                        min = "0" + min;
                        min = min.substr(0, 2);
                    }

                    var seg = tempo % 60;
                    if (seg <= 9) {
                        seg = "0" + seg;
                    }

                    if (hor <= 9) {
                        hor = "0" + hor;
                    }

                    return `${hor}:${min}:${seg}`;
                },
                timeRenderer: function(data, type, row, info) {
                    if (type != 'display') {
                        return data;
                    }

                    var tempo = parseInt(data);
                    if (isNaN(tempo) || tempo < 1) {
                        return '00:00:00';
                    }

                    var min = parseInt(tempo / 60);
                    var hor = parseInt(min / 60);

                    min = min % 60;
                    if (min < 10) {
                        min = "0" + min;
                        min = min.substr(0, 2);
                    }

                    var seg = tempo % 60;
                    if (seg <= 9) {
                        seg = "0" + seg;
                    }

                    if (hor <= 9) {
                        hor = "0" + hor;
                    }

                    return `${hor}:${min}:${seg}`;
                },
            };

            var newColumnDefs = [];
            var iterate = $.each(params.columnDefs, function(id, columnDef) {
                switch (columnDef.render) {
                    case "numberRenderer":
                        columnDef.render = renderer.numberRenderer;
                        break;
                    case "currencyRenderer":
                        columnDef.render = renderer.currencyRenderer;
                        break;
                    case "filesizeRenderer":
                        columnDef.render = renderer.filesizeRenderer;
                        break;
                    case "dateRenderer":
                        columnDef.render = renderer.dateRenderer;
                        break;
                    case "datetimeRenderer":
                        columnDef.render = renderer.datetimeRenderer;
                        break;
                    case "visibleRenderer":
                        columnDef.render = renderer.visibleRenderer;
                        break;
                    case "statusRenderer":
                        columnDef.render = renderer.statusRenderer;
                        break;
                    case "deletedRenderer":
                        columnDef.render = renderer.deletedRenderer;
                        break;
                    case "trueFalseRenderer":
                        columnDef.render = renderer.trueFalseRenderer;
                        break;
                    case "userphotoRenderer":
                        columnDef.render = renderer.userphotoRenderer;
                        break;
                    case "secondsRenderer":
                        columnDef.render = renderer.secondsRenderer;
                        break;
                    case "timeRenderer":
                        columnDef.render = renderer.timeRenderer;
                        break;

                    default:
                        columnDef.render = renderer.mustacheRenderer;
                        break;
                }
                newColumnDefs.push(columnDef);
            });
            $.when(iterate).done(function() {

                params.columnDefs = newColumnDefs;
                params.oLanguage = {
                    sEmptyTable: M.util.get_string('datatables_sEmptyTable', "local_kdashboard"),
                    sInfo: M.util.get_string('datatables_sInfo', "local_kdashboard"),
                    sInfoEmpty: M.util.get_string('datatables_sInfoEmpty', "local_kdashboard"),
                    sInfoFiltered: M.util.get_string('datatables_sInfoFiltered', "local_kdashboard"),
                    sInfoPostFix: M.util.get_string('datatables_sInfoPostFix', "local_kdashboard"),
                    sInfoThousands: M.util.get_string('datatables_sInfoThousands', "local_kdashboard"),
                    sLengthMenu: M.util.get_string('datatables_sLengthMenu', "local_kdashboard"),
                    sLoadingRecords: M.util.get_string('datatables_sLoadingRecords', "local_kdashboard"),
                    sProcessing: M.util.get_string('datatables_sProcessing', "local_kdashboard"),
                    sErrorMessage: M.util.get_string('datatables_sErrorMessage', "local_kdashboard"),
                    sZeroRecords: M.util.get_string('datatables_sZeroRecords', "local_kdashboard"),
                    sSearch: "",
                    sSearchPlaceholder: M.util.get_string('datatables_sSearch', "local_kdashboard"),
                    buttons: {
                        print_text: M.util.get_string('datatables_buttons_print_text', "local_kdashboard"),
                        copy_text: M.util.get_string('datatables_buttons_copy_text', "local_kdashboard"),
                        csv_text: M.util.get_string('datatables_buttons_csv_text', "local_kdashboard"),
                        copySuccess1: M.util.get_string('datatables_buttons_copySuccess1', "local_kdashboard"),
                        copySuccess_: M.util.get_string('datatables_buttons_copySuccess_', "local_kdashboard"),
                        copyTitle: M.util.get_string('datatables_buttons_copyTitle', "local_kdashboard"),
                        copyKeys: M.util.get_string('datatables_buttons_copyKeys', "local_kdashboard"),
                        pageLength: {
                            '_': M.util.get_string('datatables_buttons_pageLength_', "local_kdashboard"),
                            '-1': M.util.get_string('datatables_buttons_pageLength_1', "local_kdashboard"),
                        }
                    },
                    select: {
                        rows: {
                            _: M.util.get_string('datatables_buttons_select_rows_', "local_kdashboard"),
                            0: "",
                            1: M.util.get_string('datatables_buttons_select_rows1', "local_kdashboard"),
                        }
                    },
                    oPaginate: {
                        sNext: M.util.get_string('datatables_oPaginate_sNext', "local_kdashboard"),
                        sPrevious: M.util.get_string('datatables_oPaginate_sPrevious', "local_kdashboard"),
                        sFirst: M.util.get_string('datatables_oPaginate_sFirst', "local_kdashboard"),
                        sLast: M.util.get_string('datatables_oPaginate_sLast', "local_kdashboard"),
                    },
                    oAria: {
                        sSortAscending: M.util.get_string('datatables_oAria_sSortAscending', "local_kdashboard"),
                        sSortDescending: M.util.get_string('datatables_oAria_sSortDescending', "local_kdashboard"),
                    }
                };
                params.responsive = true;
                params.locale = dataTables_init.language;

                if (params.export_title) {
                    params.buttons = [
                        'pageLength',
                        {
                            extend: 'print',
                            text: M.util.get_string('datatables_buttons_print_text', "local_kdashboard"),
                            title: params.export_title
                        }, {
                            extend: 'pdf',
                            text: "PDF",
                            title: params.export_title
                        }, {
                            extend: 'excel',
                            text: 'Excel',
                            title: params.export_title
                        }, {
                            extend: 'csv',
                            text: M.util.get_string('datatables_buttons_csv_text', "local_kdashboard"),
                            title: params.export_title
                        }, {
                            extend: 'copy',
                            text: "Copy",
                            title: params.export_title
                        },
                    ];
                    params.dom = 'frtipB';
                    params.select = true;
                }

                var count_error = 0;
                $.fn.dataTable.ext.errMode = function(settings, helpPage, message) {
                    if (count_error < 20) {
                        var _processing = $("#" + tableid + "_processing");
                        setTimeout(function() {
                            _processing.show().html(
                                "<div style='color:#e91e63'>" +
                                M.util.get_string('datatables_sErrorMessage', "local_kdashboard", "<span class='counter'>30</span>") +
                                "</div>");
                        }, 500);

                        var timer = 30;
                        var _inteval = setInterval(function() {
                            if (--timer <= 0) {
                                _processing.html(M.util.get_string('datatables_sProcessing', "local_kdashboard"));
                                clearInterval(_inteval);
                                window[tableid].ajax.reload();
                            }
                            _processing.find(".counter").html(timer);
                        }, 1000);
                    }
                    count_error++;
                };

                var preDrawCallback_complete = false;
                params.preDrawCallback = function(settings) {

                    if (preDrawCallback_complete) return;
                    preDrawCallback_complete = true;

                    var element = $("<div class='group-controls' style='display:none'>");
                    var wrapper = $("#" + tableid + "_wrapper");
                    wrapper.prepend(element);
                    wrapper.find(".dataTables_length").appendTo(element);
                    wrapper.find(".dataTables_filter").appendTo(element);

                    wrapper.find(".footer")
                        .css({"justify-content": "space-between"})
                        .prepend("<div class='dataTables_navigate_area d-flex align-items-center'></div>");

                    var $area = wrapper.find(".dataTables_navigate_area");
                    wrapper.find(".dataTables_info").appendTo($area);
                    wrapper.find(".dataTables_paginate").appendTo($area);
                };

                params.infoCallback = function(settings, start, end, max, total, pre) {
                    if (end) {
                        $("#" + tableid + "_wrapper .group-controls").show(200);
                    } else {
                        $("#" + tableid + "_wrapper .group-controls").hide(200);
                    }
                };

                window[tableid] = $("#" + tableid).DataTable(params);
            });
        },

        numberFormat: function(number, decimals) {

            let options = {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals
            };

            let formatted = number.toLocaleString(dataTables_init.language, options);

            return formatted;

            var decPoint = M.util.get_string('decsep', "langconfig");
            var thousandsSep = M.util.get_string('thousandssep', "langconfig");

            if (decPoint !== "," || thousandsSep !== ".") {
                formatted = formatted.replace(",", "TEMP").replace(".", thousandsSep).replace("TEMP", decPoint);
            }

            return formatted;
        },


        click: function(tableid, clickchave, clickurl) {
            $('#' + tableid + ' tbody').on('click', 'tr', function() {
                var data = window[tableid].row(this).data();
                dataTables_init._click_internal(data, clickchave, clickurl)
            });
        },

        _click_internal: function(data, clickchave, clickurl) {
            $.each(clickchave, function(id, chave) {
                clickurl = clickurl.replace('{' + chave + '}', data[chave]);
            });

            location.href = clickurl;
        }
    };

    return dataTables_init;
});
