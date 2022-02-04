/**
 *
 * @author Presta-Module.com <support@presta-module.com>
 * @copyright Presta-Module
 *
 ****/

var currentColorPicker = false;
function initColorPicker() {
    $("input.colorPickerInput").ColorPicker({
        onSubmit: function(hsb, hex, rgb, el) {
            $(el).val("#"+hex);
            $(el).ColorPickerHide();
        },
        onBeforeShow: function () {
            currentColorPicker = $(this);
            $(this).ColorPickerSetColor(this.value);
        },
        onChange: function (hsb, hex, rgb) {
            $(currentColorPicker).val("#"+hex);
            if ($(currentColorPicker).parent("div").find("span input.colorPickerInput").length) $(currentColorPicker).parent("div").find("span input.colorPickerInput").val("#"+hex);
        }
    })
    .bind("keyup", function() {
        $(this).ColorPickerSetColor(this.value);
    });
    initMakeGradient();
}

function initFlags(flag_key, default_language) {
    currentFlag = $("#" + flag_key);
    currentFlag.val(default_language);
    currentFlag.unbind("change").on("change", function(e, p) {
        currentIdLang = $(this).val();
        $(".pmFlag").hide();
        $(".pmFlagLang_" + currentIdLang).show();
        $(".pmSelectFlag").val(currentIdLang);
        $(".pmSelectFlag").trigger("click");
    });
}

function initUploader(inputName, destinationUrl, allowedExtensionList, isImage, callBack) {
    var uploader = new plupload.Uploader({
        runtimes : 'html5,html4',
        browse_button : inputName + '_pickfiles',
        container: document.getElementById(inputName + '_container'),
        url : destinationUrl,
        file_data_name : 'fileUpload',
        filters : {
            // max_file_size : '10mb',
            mime_types: [
                {title : "Allowed file type", extensions : allowedExtensionList}
            ]
        },
        multi_selection : false,
        init: {
            PostInit: function() { },
            FilesAdded: function(up, files) {
                plupload.each(files, function(file) {
                    $("input[type=submit].ui-state-default").attr("disabled", "disabled").removeClass("ui-state-default").addClass("ui-state-disabled");
                    document.getElementById(inputName + '_filelist').innerHTML += '<div id="' + file.id + '">' + file.name + ' (' + plupload.formatSize(file.size) + ') <b></b></div>';
                    uploader.start();
                });
            },
            UploadProgress: function(up, file) {
                document.getElementById(file.id).getElementsByTagName('b')[0].innerHTML = '<span>' + file.percent + "%</span>";
            },
            FileUploaded: function(up, file, httpResult) {
                responseJson = $.parseJSON(httpResult.response);
                $('#' + inputName).val(responseJson.filename);
                $('#' + inputName + '_file').remove();
                if (isImage) {
                    $('#preview-' + inputName).prepend('<img src="' + _modulePath + 'uploads/temp/' + responseJson.filename + '" id="' + inputName + '_file" />');
                } else {
                    $('#preview-' + inputName).prepend('<a href="' + _modulePath + 'uploads/temp/' + responseJson.filename + '" target="_blank" class="pm_view_file_upload_link" id="' + inputName + '_file">' + pm_viewFileLabel + '</a>');
                }
                $("input[name=" + inputName + "_unlink_lang]").attr("checked","").removeAttr("checked");
                $("#preview-" + inputName).slideDown("fast");
                $("input[type=submit].ui-state-disabled").removeAttr("disabled").removeClass("ui-state-disabled").addClass("ui-state-default");
                document.getElementById(inputName + '_filelist').innerHTML = '';
                if (typeof(callBack) == 'function') {
                    callBack(responseJson.filename);
                }
            },
            Error: function(up, err) {
                alert('Error Code #' + err.code + ' : ' + err.message);
            }
        }
    });
    uploader.init();
}

(function(){
    var old = $.ui.dialog.prototype._create;
    $.ui.dialog.prototype._create = function(d){
        old.call(this, d);

        var self = this;
        var options = self.options,
        oldHeight = options.height,
        oldWidth = options.width;
        fixDialogSize(self,options,oldHeight,oldWidth);
        $(window).unbind('resize.uidialog').bind('resize.uidialog', function() {
            fixDialogSize(self,options,oldHeight,oldWidth);
        });
    };
})();

function fixDialogSize(self,options,oldHeight,oldWidth) {

    var fitHeight = options.fitHeight,
    screenHeight    = $(window).height(),
    screenWidth     = $(window).width(),
    dialogHeight    = options.height,
    dialogWidth     = options.width;

    if(!fitHeight && (screenHeight < oldHeight)) {
        fitHeight = true;
    }else if(!fitHeight && (dialogHeight < oldHeight) && (screenHeight < oldHeight)) {
        $(self).dialog( "option", "height",  screenHeight);
    }

    uiDialogTitlebarFull = $('<a href="#"><span class="ui-icon ui-icon-newwin"></span></a>')
        .addClass(
            'ui-dialog-titlebar-full ' +
            'ui-corner-all'
        )
        .attr('role', 'button')
        .hover(
            function() {
                uiDialogTitlebarFull.addClass('ui-state-hover');
            },
            function() {
                uiDialogTitlebarFull.removeClass('ui-state-hover');
            }
        )
        .toggle(
            function() {
                self._setOptions({
                    height : window.innerHeight - 10,
                    width : oldWidth
                });
                self._position('center');
                return false;
            },
            function() {
                self._setOptions({
                    height : oldHeight,
                    width : oldWidth
                });
                self._position('center');
                return false;
            }
        )
        .focus(function() {
            uiDialogTitlebarFull.addClass('ui-state-focus');
        })
        .blur(function() {
            uiDialogTitlebarFull.removeClass('ui-state-focus');
        })
        .appendTo(self.uiDialogTitlebar),

        uiDialogTitlebarFullText = $('<span></span>')
            .addClass(
                'ui-icon ' +
                'ui-icon-newwin'
            )
            .text(options.fullText);
    if(fitHeight) {
        self._setOptions({
            height : window.innerHeight - 10,
            width : oldWidth
        });
        self._position('center');
    }
    self._position('center');
}

function hideNextIfTrue(e) {
    var val = parseInt($(e).val());
    if (val) {
        $(e).parent('.margin-form').next('div').slideUp('fast');
    } else {
        $(e).parent('.margin-form').next('div').slideDown('fast');
    }
}
function showNextIfTrue(e) {
    var val = parseInt($(e).val());
    if (val) {
        showNext(e);
    } else {
        hideNext(e);
    }
}
function showNext(e) {
    $(e).parent('.margin-form').next('div').slideDown('fast');
}
function hideNext(e) {
    $(e).parent('.margin-form').next('div').slideUp('fast');
}
function showSpanIfChecked(e, idToShow) {
    var val = $(e).attr('checked');
    if (val) {
        $(idToShow).css('display', 'inline');
    } else {
        $(idToShow).hide();
    }
}
var dialogIframe;
function openDialogIframe(url,dialogWidth,dialogHeight,fitScreenHeight) {
    $('body').css('overflow','hidden');
    dialogIframe = $('<iframe class="dialogIFrame" frameborder="0" marginheight="0" marginwidth="0" src="'+url+'"></iframe>').dialog({
        bgiframe: true,
        modal: true,
        width:dialogWidth,
        height:dialogHeight,
        fitHeight:(typeof(fitScreenHeight)!='undefined' && fitScreenHeight ? true:false),
        close: function(event, ui) {$('body').css('overflow','');},
        open: function (event,ui) {$(this).css('width','97%');}
    });
}
function closeDialogIframe() {
    // Remove spinner as the iframe is not deleted, but hidden
    $('.dialogIFrame').contents().find(".as4-loader-bo").remove();
    $(dialogIframe).dialog("close");
}

var dialogInline;
function openDialogInline(contentId,dialogWidth,dialogHeight,fitScreenHeight) {
    $('body').css('overflow','hidden');
    dialogInline = $(contentId).dialog({
        modal: true,
        width:dialogWidth,
        height:dialogHeight,
        fitHeight:(typeof(fitScreenHeight)!='undefined' && fitScreenHeight ? true:false),
        close: function(event, ui) {$('body').css('overflow',''); },
        open: function (event,ui) {$(this).css('width','93%');}
    });
}

function closeDialogInline() {
    $(dialogInline).dialog("close");
}
function reloadPanel(idPanel) {
    var url = $('#'+idPanel).attr('rel');
    if(!url) show_info('Attribute rel is not set for panel '+idPanel);
    $('#'+idPanel).load(url);
}
function loadPanel(idPanel,url) {
    $('#'+idPanel).attr('rel',url);
    reloadPanel(idPanel);
}
function loadTabPanel(tabPanelId,li,ul) {
    var indexTab = $(li).index(ul);

    $(tabPanelId).tabs( "load" , indexTab, function(response, status, xhr) {
        if (status == "error") {
            //alert(msgAjaxError);
            return;
          }
    } );
}
function show_info(content) {
    $.jGrowl(content,{ themeState: 'ui-state-highlight' });
}
function show_error(content) {
    $.jGrowl(content,{ sticky: true, themeState: 'ui-state-error' });
}
function objectToarray (o,e) {
    a = new Array;
    for (var i=1; i<o.length; i++) {
        a.push(parseInt(o[i][e]));
    }
    return a;
}

$.fn.extend({
    pm_selectMenu: function() {
        $(this).chosen({ disable_search: true, max_selected_options: 1, inherit_select_classes: true });
        $(this).trigger('chosen:updated');
    },
    pm_ajaxScriptLoad: function(event) {
        if($(this).hasClass('pm_confirm') && !confirm($("<textarea />").html($(this).attr('title')).val())) {
            event.preventDefault();
            return false;
        }
        if($(this).next('.progressbar_wrapper').length) {
            var curLink = $(this);
            $(curLink).hide();
            var progressbar = $(this).next('.progressbar_wrapper').children('.progressbar');
            $(progressbar).progressbar({
                value: 0,
                complete: function(event, ui) { clearInterval(progressbarInterval);$(progressbar).text("");$(this).progressbar( "destroy" );$(curLink).show();}
            });
            var progressbarInterval = setInterval(function() {
                if($(progressbar).progressbar('value') == 99) $(progressbar).progressbar('value',0);
                $(progressbar).progressbar('value',$(progressbar).progressbar('value') + 9);
            }, 100);
        }
        var rel = $(this).attr('rel');
        if(rel) {
            var relSplit = rel.split(/_/g);
            if(relSplit[0] == 'tab') {
                var tabId = '#'+relSplit[1];
                var tabIndex = parseInt(relSplit[2]);
                $(tabId).unbind( "tabsload").bind( "tabsload", function(event, ui) {
                    $(window).scrollTo("#wrapConfigTab",1000);
                });
                $(tabId).tabs( "url" , tabIndex , $(this).attr('href') );
                $(tabId).tabs( "load" , tabIndex );
                event.preventDefault();
                return false;
            }
        }
        $.ajax( {
            type : "GET",
            url : $(this).attr('href'),
            dataType : "script",
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                //alert(msgAjaxError);
            }
        });
        event.preventDefault();
        return false;
    },
    pm_openOnDialogIframe: function(event) {
        var rel = $(this).attr('rel');
        var dialogWidth = 900;
        var dialogHeight = 600;
        var dialogFixHeight = false;
        if(rel) {
            var relSplit = rel.split(/_/g);
            if(typeof(relSplit[0]) != 'undefined' && typeof(relSplit[1]) != 'undefined') {
                dialogWidth = relSplit[0];
                dialogHeight = relSplit[1];
                dialogFixHeight = (typeof(relSplit[2])!='undefined' && relSplit[2] ? true:false);
            }
        }
        openDialogIframe($(this).attr('href'),dialogWidth,dialogHeight,dialogFixHeight);
        return false;
    },
    pm_ajaxLoadOnBlc: function(event) {
        var rel = $(this).attr('rel');
        if (rel) {
            loadPanel(rel,$(this).attr('href'));
        }
        return false;
    },
    pm_hideClassShowId: function(event) {
        var rel = $(this).attr('rel');
        if (typeof(rel) != 'undefined' && rel.length > 0) {
            var relSplit = rel.split(/@/g);
            if (typeof(relSplit[0]) != 'undefined' && typeof(relSplit[1]) != 'undefined') {
                class_to_hide = relSplit[0];
                id_to_show = relSplit[1];
            }
            // Hide other block
            $('.'+class_to_hide).slideUp('fast', function() {
                // Show selected block
                $('#'+id_to_show).slideDown('fast');
            });
        }
        return false;
    }
});

function loadAjaxLink() {
    $(document).off('click', '.ajax_script_load').on('click', '.ajax_script_load', function(e) {
        if ($(this).hasClass('disabledAction')) {
            e.preventDefault();
        } else {
            return $(this).pm_ajaxScriptLoad(e);
        }
    });
    $(document).off('click', '.open_on_dialog_iframe').on('click', '.open_on_dialog_iframe', function(e) {
        return $(this).pm_openOnDialogIframe(e);
    });
    $(document).off('click', '.ajax_load_on_blc').on('click', '.ajax_load_on_blc', function(e) {
        return $(this).pm_ajaxLoadOnBlc(e);
    });
    $(document).off('click', '.hide_class_show_id').on('click', '.hide_class_show_id', function(e) {
        return $(this).pm_hideClassShowId(e);
    });
}

function bindFillNextSize() {
    $('.fill_next_size').unbind('click').click(function() {
        $(this).nextAll('input.ui-input-pm-size[type=text]').val($(this).prev('input[type=text]').val());
    })
}
function initMakeGradient() {
    $('.makeGradient').unbind('click').click(function() {
        var e = $(this).parent('span').prev('span');
        if($(e).css('display') == 'inline')
            $(this).parent('span').prev('span').hide();
        else
            $(this).parent('span').prev('span').show();
    });
}
function checkChildrenCheckbox(e) {
    if($(e).children('input[type=checkbox]:checked').length)
        $(e).children('input[type=checkbox]').removeAttr('checked');
    else
        $(e).children('input[type=checkbox]').attr('checked','checked');
}
function unCheckAllChildrenCheckbox(e) {
    $(e).find('input[type=checkbox]').removeAttr('checked');
}
function display(message) {
    $.jGrowl(message, { sticky: true, themeState: 'ui-state-highlight'  });
}
function hide(message) {
    $.jGrowl('close');
}
function displayGroupBoxFromPermissions(id) {
    if($(id).val() == 2)
        $('#blc_groupBox').slideDown("fast");
    else $('#blc_groupBox').slideUp("fast");
}
function initTips(e) {
    $(document).ready(function() {
        $(e+"-tips").tipTip();
    });
}
$(document).ready(function() {
    // Add class if we are into an iframe context
    if (window != window.parent) {
        $('body').addClass('pm_iframe');
    }
    loadAjaxLink();
    $(".pm_tips").tipTip();
    $('link[href$="js/jquery/datepicker/datepicker.css"]').remove();
    $('div#addons-rating-container p.dismiss a').click(function() {
        $('div#addons-rating-container').hide(500);
        $.ajax({type : "GET", url : window.location+'&dismissRating=1' });
        return false;
    });
});