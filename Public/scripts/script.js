function makecheck (form, val) {
    if (!window.check_on_off) {
        window.check_on_off = 0;
    }
    if (window.check_on_off % 2 == 0) {
        val = true;
    } else {
        val = false;
    }
    var options = form.elements['option[]'] ;
    if (options.length == undefined) {
        var total_length = 1;
    } else {
        var total_length = options.length;
    }
    for (var i = 0; i < total_length; i++) {
        if (total_length != 1) {
            options[i].checked = val ;
        } else {
            options.checked = val;
        }
    }
    window.check_on_off++;
}
/**
 * How to use:
 * submitGetHelper(form)
 * submitGetHelper(form, 'http://abc.com/search')
 */
function submitGetHelper (form, action) {
    var obj, index, query = [], resultName = [], resultArrayName = [], ignoreName = [],
        firstSearchNameStr, keyQuery, itemName, itemVal,
        pathname = window.location.pathname.split('/'),
        searchname = window.location.search;
    if (isEmpty(searchname) === false) {
        firstSearchNameStr = searchname.substring(0, 1);
        if (firstSearchNameStr === '?') {
            searchname = searchname.substring(1);
        }
        query = searchname.split('&');
    }
    $(form.elements).each(function (i, item) {
        itemName = $(item).attr('name');
        itemVal = $(item).val();
        if (isEmpty(itemName) === false) {
            if (isEmpty(itemVal) === true) {
                query = $.grep(query, function (el, i) {
                    if (el.indexOf(itemName) === 0) {
                        return false; /* remove the element in the array */
                    }
                    return true; /* keep the element in the array */
                });
            } else if (typeof itemVal === 'string') {
                if (resultName.indexOf(itemName) === -1 && ignoreName.indexOf(itemName) === -1) {
                    itemVal = itemVal.replace(/(<([^>]+)>)/ig, '');
                    resultName.push(itemName);
                    query = $.grep(query, function (el, i) {
                        if (el.indexOf(itemName) === 0) {
                            return false; /* remove the element in the array */
                        }
                        return true; /* keep the element in the array */
                    });
                    query.push(itemName + '=' + itemVal);
                    index = $.inArray(itemName, pathname);
                    if (index > 0) {
                        pathname.splice(index, 2);
                    }
                }
            } else if (Object.prototype.toString.call(itemVal) === '[object Array]') {
                if (resultArrayName.indexOf(itemName) === -1 && ignoreName.indexOf(itemName) === -1) {
                    if (isEmpty(itemVal) === false) {
                        query = $.grep(query, function (el, i) {
                            if (el.indexOf(itemName) === 0) {
                                return false;
                            }
                            return true;
                        });
                        jQuery.each(itemVal, function (indexArray, value) {
                            itemVal = value.replace(/(<([^>]+)>)/ig, '');
                            resultArrayName.push(itemName);
                            query.push(itemName + '=' + itemVal);
                            index = $.inArray(itemName, pathname);
                            if (index > 0) {
                                pathname.splice(index, 2);
                            }
                        })
                    }
                }
            }
        }
    });
    var queryResult = query.join('&'),
        baseRedirectUrl = null;
    if (isEmpty(action) === false) {
        baseRedirectUrl = action;
    } else {
        baseRedirectUrl = pathname.join('/').replace(/\/+$/,'');
    }
    window.location.href = baseRedirectUrl + (queryResult && ('/?' + queryResult));
    return false;
}
function initTinyMCE (Obj, type) {
    // Original Object
    var objResult = {
        mode : "exact",
        theme : "advanced",
        skin : "o2k7",
        plugins : "autolink,lists,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,inlinepopups,autosave,phpimage,safari,spellchecker",
        theme_advanced_toolbar_location : "top",
        theme_advanced_toolbar_align : "left",
        theme_advanced_statusbar_location : "bottom",
        theme_advanced_resizing : true,
        content_css : BASE_FRONT + "/jscripts/content.css",
        style_formats : [{
            title : 'WeFit',
            inline : 'div',
            classes : 'wefit'
        }]
    };
    // add more config
    if (type == 'full' || type === undefined) {
        $.extend(objResult, {
            theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect",
            theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,phpimage,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
            theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
            theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak,restoredraft"
        });
    } else {
        if (type == 'basic') {
            $.extend(objResult, {
                theme_advanced_buttons1 : "code,bold,italic,underline,strikethrough,forecolor,backcolor,pastetext,pasteword,|,outdent,indent,blockquote,|,phpimage,link,unlink,emotions,iespell,media,preview,table",
                theme_advanced_buttons2 : "styleselect,formatselect,fontselect,fontsizeselect,undo,redo,",
                theme_advanced_buttons3 : "",
                theme_advanced_buttons4 : ""
            });
        }
    }
    // add config from UI
    $.extend(objResult, Obj);
    // init
    tinyMCE.init(objResult);
}
$(document).ready(function(){
    var formSort   = $(".form-sort"),
        formSortDd = $(".form-sort dd"),
        chosenObject = $(".chosen-select"),
        dateObject = $('input[name="birthday"], input[name="from_date"], input[name="to_date"]');
    if (formSort.sortable) {
        $(".form-sort").sortable({
            revert: true
        });
    }
    if (formSortDd.disableSelection) {
        $(".form-sort dd").disableSelection();
    }
    if (dateObject.datepicker) {
        dateObject.datepicker({
            changeMonth: true,
            changeYear: true,
            yearRange: "1950:" + new Date().getFullYear(),
            dateFormat: 'yy-mm-dd',
            /**
             * fix bug when change month/year not update new date
             */
            onChangeMonthYear: function(y, m, i){
                var d = i.selectedDay;
                $(this).datepicker('setDate', new Date(y, m - 1, d));
            },
            beforeShow: function () { /* fix bug datetimepicker behind plus icon */
                setTimeout(function () {
                    $('.ui-datepicker').css('z-index', 3);
                }, 0);
            }
        });
    }
    /* initTinyMCE({mode : 'specific_textareas', editor_selector : 'mceEditorArea', base_root: BASE_FRONT}, 'basic'); */
    if (chosenObject.chosen) {
        chosenObject.chosen();
    }
    $('.tabs .actived a').tab('show');
    $('.tabs a').click(function (e) {
        e.preventDefault();
        $(this).tab('show');
    });
    /**
     * combobox multi select
     * document: http://www.bootply.com/tagged/combobox
     */
    if ($().select2) {
        $(".multiselect").select2();
    }
});
/**
 * hash password before post to controller
 * hashPasswordBeforeSubmit(this, ['password1', 'password2'], 2)
 */
function hashPasswordBeforeSubmit (form, fieldName, times) {
    if ($.isArray(fieldName) === true) {
        $.each(fieldName, function (index, value) {
            var result = form[value].value;
            for (var i = 0; i < times; i++) {
                result = $.md5(result);
            }
            form[value].value = result;
        });
    }
}