/**
 *
 * @author Presta-Module.com <support@presta-module.com>
 * @copyright Presta-Module
 *
 ****/

function deleteCriterionImg(id_criterion, id_search, id_lang) {
    $.ajax({
        method: "POST",
        url: baseConfigUrl + "&pm_load_function=processDeleteCriterionImg&id_criterion=" + id_criterion + "&id_search=" + id_search + "&id_lang=" + id_lang,
        dataType: "script",
        error : function (XMLHttpRequest, textStatus, errorThrown) {
            // alert("ERROR : " + errorThrown);
        }
    });
}

function displayHideBar(e, idSearch) {
    if ($(e).val() > 0) {
        $("#hide_after_" + idSearch).show("fast");
    } else {
        $("#hide_after_" + idSearch).hide("fast");
    }
    saveCriterionsGroupSorting(idSearch);
}

function saveCriterionsGroupSorting(id_search) {
    var order = $("#searchTabContainer-" +  id_search + " .connectedSortableIndex").sortable({
        items: "> li",
        axis: "y",
    }).sortable("toArray");

    saveOrder(order.join(","), "orderCriterionGroup", id_search);
}

function showRelatedOptions(e, groupType) {
    var itemType = $(e).val();
    var itemName = $(e).attr("name");
    var allowCombineCriterions = true;
    var isColorGroup = false;
    if ($('#display_type option[value=7]').size() > 0) {
        isColorGroup = true;
    }
    const booleanCriterionsGroupType = [
        'on_sale',
        'stock',
        'available_for_order',
        'online_only',
        'pack',
        'subscription',
        'new_products',
        'prices_drop',
    ];

    // Init items display status
    $('#display_type-menu li').show();
    $('.blc_range, .blc_range_nb, .blc_range_interval, .blc_range_sign, .multicrit, .combined_criterion, .max_display_container, .overflow_height_container, .blc_with_search_area, .all_label, .blc_category_tree_options').hide();
    if (typeof (window.supportsImageCriterionGroup) !== 'undefined' && !window.supportsImageCriterionGroup) {
        $('#display_type option[value=2]').prop('selected', false).prop('disabled', true);
    }
    if (groupType != 'attribute' && groupType != 'feature' && groupType != 'price' && groupType != 'depth' && groupType != 'height' && groupType != 'width' & groupType != 'weight') {
        $('#display_type option[value=5]').prop('selected', false).prop('disabled', true);
        $('#display_type option[value=8]').prop('selected', false).prop('disabled', true);
    }
    if (groupType == 'category' || groupType == 'subcategory' || groupType == 'manufacturer' || groupType == 'supplier') {
        if ($("#range_on:checked").length) {
            $("#range_off").prop('checked', true);
        }
        $(".multicrit, .combined_criterion").show();
    }
    if (groupType == 'price') {
        $('#display_type option[value=2]').prop('selected', false).prop('disabled', true);
        if (itemType != "5" && $("#range_off:checked").length) {
            $("#range_on").prop('checked', true);
        }
    }
    if (booleanCriterionsGroupType.includes(groupType)) {
        $('#display_type option[value=2]').prop('selected', false).prop('disabled', true);
    }

    switch (itemType) {
        // Select
        case '1':
            if (groupType == 'price') {
                $('.blc_range_interval, .max_display_container, .overflow_height_container').show();
            } else if (groupType == 'category' || groupType == 'subcategory' || groupType == 'manufacturer' || groupType == 'supplier' || groupType == 'condition' || booleanCriterionsGroupType.includes(groupType)) {
                $('.max_display_container, .overflow_height_container, .blc_with_search_area').show();
            } else {
                $('.blc_range, .blc_range_interval, .blc_range_sign, .max_display_container, .overflow_height_container, .blc_with_search_area, .all_label').show();
            }
            if ($('.blc_category_options').length) {
                $('.blc_category_options').show();
            }
            $('.sort_criteria_container, .custom_criteria_container').show();
            $(".multicrit, .combined_criterion").show();
            break;
        // Image
        case '2':
            if ($("#range_on:checked").length) {
                $("#range_off").prop('checked', true);
            }
            $(".multicrit, .max_display_container, .overflow_height_container, .combined_criterion").show();
            if ($('.blc_category_options').length) {
                $('.blc_category_options').show();
            }
            $('.sort_criteria_container, .custom_criteria_container').show();
            break;
        // Link
        case '3':
            $(".multicrit, .max_display_container, .overflow_height_container, .combined_criterion").show();
        // Checkbox
        case '4':
            $(".multicrit, .combined_criterion").show();
            if (groupType == 'price') {
                $('.blc_range_interval, .max_display_container, .overflow_height_container').show();
            } else if (groupType == 'category' || groupType == 'subcategory' || groupType == 'manufacturer' || groupType == 'supplier' || groupType == 'condition' || booleanCriterionsGroupType.includes(groupType)) {
                $('.max_display_container, .overflow_height_container').show();
            } else {
                $('.blc_range, .blc_range_interval, .blc_range_sign, .max_display_container, .overflow_height_container').show();
            }
            if ($('.blc_category_options').length) {
                $('.blc_category_options').show();
            }
            $('.sort_criteria_container, .custom_criteria_container').show();
            break;
        // Cursor, Slider
        case '5':
            $(".blc_range_nb").show();
            if (groupType != 'price') {
                $(".blc_range_sign").show();
            }
            if ($("#range_on:checked").length) {
                $("#range_off").prop('checked', true);
            }
            if ($('.blc_category_options').length) {
                $('.blc_category_options').show();
            }
            $('.sort_criteria_container, .custom_criteria_container').show();
            break;
        // Reserved
        case '6':
            break;
        // Color square
        case '7':
            $(".multicrit, .max_display_container, .overflow_height_container, .combined_criterion").show();
            $('.sort_criteria_container, .custom_criteria_container').show();
            break;
        // Ranges
        case '8':
            $(".blc_range_nb").show();
            if (groupType != 'price') $(".blc_range_sign").show();
            if ($("#range_on:checked").length) {
                $("#range_off").prop('checked', true);
            }
            if ($('.blc_category_options').length) {
                $('.blc_category_options').show();
            }
            $('.sort_criteria_container, .custom_criteria_container').show();
            break;
        // Level Depth
        case '9':
            $('.blc_category_tree_options').show();
            $('.blc_category_options').hide();
            $('.sort_criteria_container, .custom_criteria_container').hide();
            $('.multicrit').hide();
            $('.combined_criterion').hide();
            $("#show_all_depth_on").prop('checked', true);
            $("#show_all_depth_off").prop('checked', false);
            $("#is_multicriteria_off").prop('checked', true);
            $("#is_multicriteria_on").prop('checked', false);
            $("#is_combined_off").prop('checked', true);
            $("#is_combined_on").prop('checked', false);
            allowCombineCriterions = false;

            // @todo: better way for this
            reorderCriteria('position', 'ASC', $('input[name=id_criterion_group').val(), $('input[name=id_search').val(), itemType);
            break;
    }

    if (groupType == 'condition' || booleanCriterionsGroupType.includes(groupType)) {
        $(".combined_criterion").hide();
        allowCombineCriterions = false;
    }
    if (allowCombineCriterions && $("#is_multicriteria_on:checked").length) {
        $(".combined_criterion").show();
    } else {
        $(".combined_criterion").hide();
        $("#is_combined_off").prop('checked', true);
        $("#is_combined_on").prop('checked', false);
    }
    if (itemType == 1) {
        $('.max_display_container, .overflow_height_container').hide();
        if ($("#is_multicriteria_on:checked").length) {
            $(".blc_with_search_area").hide();
            $(".all_label").hide();
        } else {
            $(".blc_with_search_area").show();
            $(".all_label").show();
        }
    }

    // Reset change items
    if ($("#range_on:checked").length) {
        if (groupType != 'price') {
            $(".blc_range_interval, .blc_range_sign").show();
        } else {
            $(".blc_range_interval").show();
            $(".blc_range_sign").hide();
        }
    } else {
        if (itemType != 5 && itemType != 8) {
            $(".blc_range_interval, .blc_range_sign").hide();
        }
    }

    if (isColorGroup) {
        if ($("#range_on:checked").length) {
            $("#range_off").prop('checked', true);
        }
        $('.blc_range, .blc_range_nb, .blc_range_interval, .blc_range_sign').hide();
        $('#display_type option[value=5]').prop('selected', false).prop('disabled', true);
        $('#display_type option[value=8]').prop('selected', false).prop('disabled', true);
    }
}
function displayRangeOptions(e, groupType) {
    var valRange = parseInt($(e).val());
    if (valRange) {
        $(".blc_range_interval, .blc_range_sign").slideDown("fast");
        $('#display_type-menu li').show();
        $('#display_type-menu li.display_type-5').hide();
        if ($('#display_type').val() == 5) {
            $('#display_type').val(1);
        }
        $('#display_type').trigger('click');
    } else {
        $(".blc_range_interval, .blc_range_sign").slideUp("fast");
        $('#display_type-menu li.display_type-5').show();
    }
}

function convertToPointDecimal(e) {
    var valRange = $(e).val();
    valRange = valRange.replace(/,/g, ".");
    valRange = parseFloat(valRange);
    if (isNaN(valRange)) {
        valRange = 0;
    }
    $(e).val(valRange);
}

var original_search_results_selector = false;
function updateHookOptions(e, hookIds) {
    defaultSearchResultsSelector = $('#blc_search_results_selector').data('default-selector');
    if (!original_search_results_selector && $('#search_results_selector').val() != '#as_home_content_results' && $('#search_results_selector').val() != '#as_custom_content_results') {
        original_search_results_selector = $('#search_results_selector').val();
    } else if (!original_search_results_selector && ($('#search_results_selector').val() == '#as_home_content_results' || $('#search_results_selector').val() == '#as_custom_content_results')) {
        original_search_results_selector = defaultSearchResultsSelector;
    }
    var current_search_results_selector = $('#search_results_selector').val();
    var selectedHook = $(e).val();
    var selectedHookLabel = typeof(hookIds[selectedHook]) != 'undefined' ? hookIds[selectedHook] : selectedHook;
    $('.hookOptions').slideUp('fast');
    //Hide content selector if hook home
    if (selectedHookLabel == 'displayhome') {
        $('#blc_search_results_selector').hide();
        $('#search_results_selector').val('#as_home_content_results');
        $('.hookOption-' + selectedHook).slideDown('fast');
    } else if (selectedHook < 0) {
        $("#custom_content_area_results").show();
        $('.hookOption' + selectedHook).slideDown('fast');
        displayRelatedSmartyVarOptions();
    } else {
        if (selectedHook >= 0 || !parseInt($("input[name=insert_in_center_column]").val())) {
            $('#blc_search_results_selector').show();
        }
        if (original_search_results_selector == defaultSearchResultsSelector || current_search_results_selector == '#as_home_content_results' || current_search_results_selector == '#as_custom_content_results') {
            $('#search_results_selector').val(original_search_results_selector);
        }
        if (selectedHookLabel == 'Advanced Top Menu') {
            selectedHookLabel = 'ATM';
            $('.fieldsetAssociations').hide();
        } else {
            $('.fieldsetAssociations').show();
        }
        $('.hookOption-' + selectedHook).slideDown('fast');
    }
}

function setCriterionGroupActions(key_criterions_group, show) {
    $('#' + key_criterions_group).append(
        '<div class="blocCriterionGroupActions">' +
        '<a title="' + editTranlate + '" ' + (typeof(show) == 'undefined' ? 'style="display:none;"' : '') + ' class="getCriterionGroupActions" id="action-' + key_criterions_group + '"><i class="material-icons">settings</i></a>' +
        '<a title="' + deleteTranlate + '" ' + (typeof (show) == 'undefined' ? 'style="display:none;"' : '') + ' class="getCriterionGroupActions" id="delete-' + key_criterions_group + '"><i class="material-icons">delete</i></a>' +
        '</div>'
    );
    if (typeof(show) == 'undefined') {
        $("#action-" + key_criterions_group).fadeIn("fast");
        $("#delete-" + key_criterions_group).fadeIn("fast");
    }
    $("#delete-" + key_criterions_group).click(function () {
        deleteCriterion($('li#' + key_criterions_group));
    });
    $("#action-" + key_criterions_group).click(function () {
        var id_criterion_group = $('#' + key_criterions_group).attr('rel');
        var id_search = $('#' + key_criterions_group).children('input[name=id_search]').val();
        openDialogIframe(baseConfigUrl + "&id_search=" + id_search + "&pm_load_function=displayCriterionGroupForm&class=Models\\CriterionGroup&id_criterion_group=" + id_criterion_group, 980, 540, 1);
    });
}
function getCriterionGroupActions(key_criterions_group, refresh) {
    if ((typeof(refresh) == 'undefined') && $('#' + key_criterions_group + ' .blocCriterionGroupActions div').length) {
        if ($('#' + key_criterions_group + ' .blocCriterionGroupActions:visible').length) {
            $('#' + key_criterions_group + ' .blocCriterionGroupActions').slideUp('slow');
        } else {
            $('#' + key_criterions_group + ' .blocCriterionGroupActions').slideDown('slow');
        }
    }
    return;
}
function saveOrder(order, actionType, curId_search) {
    let autoHide = $('input#auto_hide_' + curId_search + '_on');

    $.post(baseConfigUrl, {
        action: actionType,
        order: order,
        id_search: curId_search,
        auto_hide: autoHide.prop('checked') || false
    }, function (data) {
        parent.show_success(data);
    });
}

function receiveCriteria(item) {
    var curAction = $(item).parent("ul").parent("div").attr("id");
    if (curAction == "DesindexCriterionsGroup") {
        $(item).children(".blocCriterionGroupActions").remove();
    }
    if (curAction == "DesindexCriterionsGroup" && $(item).data('id-criterion-group-type') == 'category') {
        $(item).hide();
    }
    $(item).append("<div class='loadingOnConnectList'><img src='" + modulePath + "views/img/snake_transparent.gif' /></div>");
    $.ajax({
        method: "POST",
        url: baseConfigUrl + "&pm_load_function=process" + curAction + "&key_criterions_group=" + $(item).attr("id"),
        dataType: "script",
        complete: function (data, textStatus, errorThrown) {
            addDeleteInProgress = false;
            if (curAction == "DesindexCriterionsGroup" && $(item).data('id-criterion-group-type') == 'category') {
                $(item).remove();
            }
            $('ul.connectedSortable li.ui-state-disabled').toggleClass('ui-state-disabled');
        }
    });
}
var addDeleteInProgress = false;
function addCriterion(item) {
    if (!addDeleteInProgress) {
        addDeleteInProgress = true;
        parentTab = '#' + $(item).parents('.ui-tabs-panel').attr('id');
        removeAfter = true;
        if ($(item).data('id-criterion-group-type') == 'category') {
            removeAfter = false;
        }
        $('.availableCriterionGroups ul li').toggleClass('ui-state-disabled');
        $(item).animateAppendTo($(parentTab + ' #IndexCriterionsGroup ul'), 600, removeAfter, function(originalItem, newItem) {
            receiveCriteria(newItem);
        });
    }
}
function deleteCriterion(item) {
    if (!addDeleteInProgress) {
        if (confirm(alertDeleteCriterionGroup)) {
            addDeleteInProgress = true;
            parentTab = '#' + $(item).parents('.ui-tabs-panel').attr('id');
            $('.indexedCriterionGroups ul li').toggleClass('ui-state-disabled');
            $(item).animateAppendTo($(parentTab + ' ul.availableCriterionGroups-' + $(item).data('id-criterion-group-unit')), 600, true, function(originalItem, newItem) {
                receiveCriteria(newItem);
            });
        }
    }
}
function loadTabPanel(tabPanelId, li) {
    var indexTab = $(li).index();
    $(li + ' a').trigger('click');
    $(tabPanelId).tabs("load", indexTab);
}
function updateSearchNameIntoTab(tabPanelId, newName) {
    $(tabPanelId + ' a').html(newName);
}
function updateCriterionGroupName(criterionGroupId, newName) {
    $('ul.connectedSortable li[rel="' + criterionGroupId + '"] .as4-criterion-group-name').html(newName);
}
function addTabPanel(tabPanelId, label, id_search, load_after) {
    $("#msgNoResults").hide();
    if (typeof(load_after) != 'undefined' && load_after == true) {
        $(tabPanelId).unbind("tabsadd").bind("tabsadd", function (event, ui) {
            $(tabPanelId).tabs('select', '#' + ui.panel.id);
        });
    }

    $(tabPanelId + ' > ul').append('<li id="TabSearchAdminPanel' + id_search + '" class="pmAsSearchEngineTab"><a href="' + baseConfigUrl + '&pm_load_function=displaySearchAdminPanel&id_search=' + id_search + '">' + label + '</a></li>');
    $(tabPanelId).append('<div id="TabSearchAdminPanel' + id_search + '"></div>');
    $(tabPanelId).tabs('refresh');
}
function removeTabPanel(tabPanelId, li, ul) {
    var indexTab = $(li).index();

    $(li).remove();
    $(tabPanelId + ' div#ui-tabs-' + indexTab).remove();
    $(tabPanelId).tabs('refresh');
}

var defaultValueSubmit = false;
function showRequest(formData, jqForm, options) {
    var btn_submit = $(jqForm).find('input[type=submit]');
    defaultValueSubmit = $(btn_submit).attr('value');
    $(btn_submit).prop('disabled', true);
    $(btn_submit).attr('value', msgWait);
    return true;
}
// post-submit callback
function showResponse(responseText, statusText, xhr, $form) {
    var btn_submit = $form.find('input[type=submit]');
    if (defaultValueSubmit) {
        $(btn_submit).prop('disabled', false);
        $(btn_submit).attr('value', defaultValueSubmit);
        defaultValueSubmit = false;
    }
}
function removeSelectedSeoCriterion(e) {
    var curId = $(e).parent('li').attr('rel').replace(/(~)/g, "\\$1");
    $('#' + curId).fadeIn('fast');
    $('#bis' + curId).remove();
    seoSearchCriteriaUpdate();
}
function seoSearchCriteriaUpdate() {
    var order = $("#seoSearchPanelCriteriaSelected ul").sortable("toArray");
    $("#posted_id_currency").val($("#id_currency").val());
    $("#seoSearchCriteriaInput").val(order);
    checkSeoCriteriaCombination();
}
var id_currency = 0;
function massSeoSearchCriteriaGroupUpdate() {
    var order = $("#seoMassSearchPanelCriteriaGroupsTabs ul").sortable("toArray");
    $("#posted_id_currency").val($("#id_currency").val());
    $("#massSeoSearchCriterionGroupsInput").val(order);
    id_currency = $("#id_currency").val();
}
function fillSeoFields() {
    var criteria = $("#seoSearchPanelCriteriaSelected ul").sortable("toArray");
    if (criteria == '') {
        show_error(msgNoSeoCriterion);
        return;
    }
    $.ajax({
        method: "POST",
        url: baseConfigUrl + "&pm_load_function=processFillSeoFields&criteria=" + $("#seoSearchCriteriaInput").val() + "&id_search=" + $("#id_search").val() + "&id_currency=" + id_currency,
        dataType: "script",
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            // alert("ERROR : " + errorThrown);
        }
    });
}

function checkChildrenCheckbox(e) {
    if (fromMassAction) {
        let allCriterionEnable = true;;
        if ($(e).data('checkboxStateToggle') === undefined) {
            $(e).data('checkboxStateToggle', true);
        } else {
            $(e).data('checkboxStateToggle', !$(e).data('checkboxStateToggle'));
        }
        $(e).children('input[type=checkbox]').prop('checked', $(e).data('checkboxStateToggle'));
    } else {
        if ($(e).children('input[type=checkbox]:checked').length) {
            $(e).children('input[type=checkbox]').prop('checked', false);
        } else {
            $(e).children('input[type=checkbox]').prop('checked', true);
        }
    }
}
function unCheckAllChildrenCheckbox(e) {
    $(e).find('input[type=checkbox]').prop('checked', false);
}

var allCriterionEnable = false;
var fromMassAction = false;
function enableAllCriterion4MassSeo(e) {
    allCriterionEnable = !allCriterionEnable;
    var parentDiv = $(e).parent('div');
    var id_criterion_group = $(parentDiv).children('input[name=id_criterion_group]').val();
    if (!$('#criterion_group_' + id_criterion_group + ':visible').length && $('.seoSearchCriterionGroupSortable:visible').length >= msgMaxCriteriaGroupCountForMass) {
        unCheckAllChildrenCheckbox(parentDiv);
        alert(msgMaxCriteriaForMass);
        return false;
    }
    fromMassAction = true;
    $(parentDiv).find('li.massSeoSearchCriterion').trigger('click');
    fromMassAction = false;
}
function enableCriterion4MassSeo(e) {
    checkChildrenCheckbox(e, true);
    var parentDiv = $(e).parent('ul').parent('div');
    var id_criterion_group = $(parentDiv).children('input[name=id_criterion_group]').val();

    if ($(parentDiv).find('input[type=checkbox]:checked').length) {
        if ($(e).children('input[type=checkbox]:checked').length) {
            if (!$('#criterion_group_' + id_criterion_group + ':visible').length) {
                if ($('.seoSearchCriterionGroupSortable:visible').length >= msgMaxCriteriaGroupCountForMass) {
                    unCheckAllChildrenCheckbox(parentDiv);
                    alert(msgMaxCriteriaForMass);
                    return false;
                }
                $('#criterion_group_' + id_criterion_group).removeClass('ui-state-disabled').fadeIn('fast');
                $('#seoMassSearchPanelCriteriaGroupsTabs ul').sortable('refresh');
                massSeoSearchCriteriaGroupUpdate();
            }

        }
    } else {
        $('#criterion_group_' + id_criterion_group).addClass('ui-state-disabled').fadeOut('fast');
        $('#seoMassSearchPanelCriteriaGroupsTabs ul').sortable('refresh');
        massSeoSearchCriteriaGroupUpdate();
    }
}
function checkSeoCriteriaCombination() {
    $.ajax({
        method: "POST",
        url: baseConfigUrl + "&pm_load_function=checkSeoCriteriaCombination&criteria=" + $("#seoSearchCriteriaInput").val() + "&id_search=" + $("#id_search").val() + "&id_currency=" + $("#posted_id_currency").val(),
        dataType: "script",
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            // alert("ERROR : " + errorThrown);
        }
    });
}
function ASStr2url(e) {
    $(e).val(str2url($(e).val(), 'UTF-8'));
    return true;
}
function displayRelatedFilterByEmplacementOptions() {
    if (parseInt($('select[name="filter_by_emplacement"]').val())) {
        $('div.id_category_root_container').show();
    } else {
        $('div.id_category_root_container').hide();
    }
}
function displayRelatedSmartyVarOptions() {
    defaultSearchResultsSelector = $('#blc_search_results_selector').data('default-selector');
    if (parseInt($("input[name=insert_in_center_column]:checked").val())) {
        $("#custom_content_area_results").show();
        $("#blc_search_results_selector").hide();
        $("#search_results_selector").val("#as_custom_content_results");
    } else {
        $("#custom_content_area_results").hide();
        $("#blc_search_results_selector").show();
        if ($("#search_results_selector").val() == '' || $("#search_results_selector").val() == '#as_home_content_results' || $("#search_results_selector").val() == '#as_custom_content_results') {
            $("#search_results_selector").val(defaultSearchResultsSelector);
        }
    }
    updateSmartyVarNamePicker();
}
function updateSmartyVarNamePicker() {
    if ($("#smarty_var_name").size() > 0) {
        var smartyVarName = $("#smarty_var_name").val();
        $("#smarty_var_name_picker").html(
            '{* Advanced Search 5 Pro - Start of custom search variable *}'
            + "\n" + '{if isset($' + smartyVarName + ')}' + '{$' + smartyVarName + '}'
            + ($('input[name="insert_in_center_column"]:checked').val() == 1 ? '&lt;div id="as_custom_content_results"&gt;&lt;/div&gt;' : '')
            + '{/if}'
            + "\n" + '{* /Advanced Search 5 Pro - End of custom search variable *}'
        );
    }
}

function handleSortChange(e) {
    let selectedValue;
    if (typeof (e) === 'undefined' || e === null) {
        selectedValue = document.querySelector('#products_order_by').value;
    } else {
        selectedValue = e[0].value;
    }

    // Enable back all options before continuing
    const orderWayOptions = document.querySelectorAll('#products_order_way option');
    orderWayOptions.forEach(function (element) {
        element.disabled = false;
    });
    // Disable all sorts that are not allowed in the JS config object
    const orderByOptions = document.querySelectorAll('#products_order_by option');
    orderByOptions.forEach(function (element) {
        if (typeof (window.pmAsAllowedSortOrders[element.value]) === 'undefined') {
            element.disabled = true;
        }
    });

    // If the selected sort is among the available list, we'll disable asc/desc based on the configuration
    if (typeof (window.pmAsAllowedSortOrders[selectedValue]) !== 'undefined') {
        const allowedOrderWays = window.pmAsAllowedSortOrders[selectedValue];
        if (allowedOrderWays.asc === false) {
            document.querySelector('#products_order_way option[value="0"]').disabled = true;
            document.querySelector('#products_order_way option[value="1"]').disabled = false;
            document.querySelector('#products_order_way option[value="1"]').selected = true;
        } else if (allowedOrderWays.desc === false) {
            document.querySelector('#products_order_way option[value="1"]').disabled = true;
            document.querySelector('#products_order_way option[value="0"]').disabled = false;
            document.querySelector('#products_order_way option[value="0"]').selected = true;
        }
    } else {
        document.querySelector('#products_order_by option[value="' + selectedValue + '"]').disabled = true;
    }

    if (document.querySelector('#products_order_by option[selected][disabled]') != null) {
        // Force to select another value than the disabled one, reset orderby & orderway to default values
        document.querySelector('#products_order_by option[selected][disabled]').selected = false;
        document.querySelector('#products_order_by option[value="0"]').selected = true;
        document.querySelector('#products_order_way option[selected]').selected = false;
        document.querySelector('#products_order_way option[value="0"]').selected = true;
    }
}

function selectText(element) {
    var doc = document;
    var text = doc.getElementById(element);
    if (doc.body.createTextRange) {
        var range = document.body.createTextRange();
        range.moveToElementText(text);
        range.select();
    } else if (window.getSelection) {
        var selection = window.getSelection();
        if (selection.setBaseAndExtent) {
            selection.setBaseAndExtent(text, 0, text, 1);
        } else {
            var range = document.createRange();
            range.selectNodeContents(text);
            selection.removeAllRanges();
            selection.addRange(range);
        }
    }
}
var checkAllState = true;
function checkAllSeoItems(id_search) {
    $('#dataTable' + id_search + ' input[name="seo_group_action[]"]').prop('checked', checkAllState);
    checkAllState = !checkAllState;
}
function deleteSeoItems(id_search) {
    $.ajax({
        method: "POST",
        url: baseConfigUrl + "&pm_load_function=processDeleteMassSeo&id_search=" + id_search + '&' + $('#dataTable' + id_search + ' input[name="seo_group_action[]"]:checked').serialize(),
        dataType: "script",
        error: function (XMLHttpRequest, textStatus, errorThrown) {}
    });
}

function reorderCriteria(sort_by, sort_way, id_criterion_group, id_search, itemType) {
    $('#sortCriteriaPanel').load(baseConfigUrl + "&pm_load_function=displaySortCriteriaPanel&id_criterion_group=" + id_criterion_group + '&sort_by=' + sort_by + '&sort_way=' + sort_way + '&id_search=' + id_search + '&display_type=' + itemType);
}

function display_cat_picker() {
    var val = parseInt($('input[name="bool_cat"]:checked').val());
    if (val) {
        $('#category_picker').show('medium');
    } else {
        $('#category_picker').hide('medium');
    }
}

function display_cat_prod_picker() {
    var val = parseInt($('input[name="bool_cat_prod"]:checked').val());
    if (val) {
        $('#category_product_picker').show('medium');
    } else {
        $('#category_product_picker').hide('medium');
    }
}

function display_prod_picker() {
    var val = parseInt($('input[name="bool_prod"]:checked').val());
    if (val) {
        $('#product_picker').show('medium');
    } else {
        $('#product_picker').hide('medium');
    }
}

function display_manu_picker() {
    var val = parseInt($('input[name="bool_manu"]:checked').val());
    if (val) {
        $('#manu_picker').show('medium');
    } else {
        $('#manu_picker').hide('medium');
    }
}

function display_supp_picker() {
    var val = parseInt($('input[name="bool_supp"]:checked').val());
    if (val) {
        $('#supp_picker').show('medium');
    } else {
        $('#supp_picker').hide('medium');
    }
}

function display_cms_picker() {
    var val = parseInt($('input[name="bool_cms"]:checked').val());
    if (val) {
        $('#cms_picker').show('medium');
    } else {
        $('#cms_picker').hide('medium');
    }
}

function display_spe_picker() {
    var val = parseInt($('input[name="bool_spe"]:checked').val());
    if (val) {
        $('#special_pages').show('medium');
    } else {
        $('#special_pages').hide('medium');
    }
}

function toggleSearchEngineSettings(realChange) {
    var searchType = parseInt($('select[name="search_type"]').val());
    if (searchType == 0) {
        // Classic
        $('input[name="step_search"]').val(0);
        $('.enabled-option-step-search').hide('medium');
        // Only apply presets if value is changed from the select
        if (realChange == true) {
            $('select[name="filter_by_emplacement"]').val(1).trigger("change");
        }
        $('select[name="search_method"] option[value="3"]').prop('selected', false).prop('disabled', true).hide();
        $('select[name="search_method"]').trigger("change");
    } else if (searchType == 1) {
        // Global
        $('input[name="step_search"]').val(0);
        $('.enabled-option-step-search').hide('medium');
        // Only apply presets if value is changed from the select
        if (realChange == true) {
            $('select[name="filter_by_emplacement"]').val(0).trigger("change");
        }
        $('select[name="search_method"] option[value="3"]').prop('selected', false).prop('disabled', true).hide();
        $('select[name="search_method"]').trigger("change");
    } else if (searchType == 2) {
        // Step by step
        $('input[name="step_search"]').val(1);
        $('.enabled-option-step-search').show('medium');
        // Only apply presets if value is changed from the select
        if (realChange == true) {
            $('input[name="hide_empty_crit_group"][value=0]').prop('checked', true);
            $('input[name="hide_criterions_group_with_no_effect"][value=0]').prop('checked', true);
            $('input[name="display_empty_criteria"][value=0]').prop('checked', true);
            $('input[name="step_search_next_in_disabled"][value=0]').prop('checked', true);
            $('select[name="filter_by_emplacement"]').val(0).trigger("change");
        }
        $('select[name="search_method"] option[value=3]').prop('disabled', false).show();
        $('select[name="search_method"]').trigger("change");
    }
    var stepSearch = parseInt($('input[name="step_search"]').val());
    var displayEmptyCriterion = parseInt($('input[name="display_empty_criteria"]:checked').val());

    $('select[name="search_method"] option').prop('disabled', false);
    $('select[name="search_method"]').trigger('change');

    if (displayEmptyCriterion) {
        $('.hide-empty-criterion-group').hide('medium');
    } else {
        $('.hide-empty-criterion-group').show('medium');
    }
}

function display_search_method_options() {
    var val = parseInt($('select[name="search_method"]').val());
    if (val == 2 || val == 4) {
        $('.search_method_options_1').hide('medium');
        $('.search_method_options_2').show('medium');
    } else {
        $('.search_method_options_1').show('medium');
        $('.search_method_options_2').hide('medium');
    }
}

var currentCriteriaGroupIndex = 0;
var prevCriteriaGroupIndex = -1;
var reindexation_in_progress = false;
function reindexSearchCriterionGroups(e, criterionGroups, wrapperProgress, confirmationMessage) {
    if (reindexation_in_progress) {
        alert(reindexationInprogressMsg);
        return;
    }

    reindexation_in_progress = true;
    var nbCriteriaGroupsTotal = criterionGroups.length;
    $(e).addClass('disabled').prop('disabled', true);

    var reindexationInterval = setInterval(function () {
        $('.progressbarReindexSpecificSearch').css('display', 'inline-block');
        $(e).hide();
        if (typeof(criterionGroups[currentCriteriaGroupIndex]) != 'undefined' && currentCriteriaGroupIndex != prevCriteriaGroupIndex) {
            // Reindexation in progress
            prevCriteriaGroupIndex++;
            $(wrapperProgress).progressbar({
                value: Math.round((currentCriteriaGroupIndex * 100) / nbCriteriaGroupsTotal)
            });
            $(wrapperProgress).next('.progressbarpercent').html(reindexingCriteriaMsg + ' ' + currentCriteriaGroupIndex + ' ' + reindexingCriteriaOfMsg + ' ' + nbCriteriaGroupsTotal + ' (' + Math.round((currentCriteriaGroupIndex * 100) / nbCriteriaGroupsTotal) + '%)');
            reindexSearchCriteriaGroup(
                criterionGroups[currentCriteriaGroupIndex].id_criterion_group,
                criterionGroups[currentCriteriaGroupIndex].id_search,
            );
        } else if (typeof(criterionGroups[currentCriteriaGroupIndex]) == 'undefined') {
            // Reindexation done
            if (typeof (confirmationMessage) != 'undefined') {
                show_success(confirmationMessage);
            } else {
                show_success($(e).data('confirmation-message'));
            }
            $(wrapperProgress).progressbar({
                value: 100
            });
            clearInterval(reindexationInterval);
            $(e).removeClass('disabled').prop('disabled', false).show();
            $(wrapperProgress).next('.progressbarpercent').text("");
            $(wrapperProgress).progressbar("destroy");
            $('.progressbarReindexSpecificSearch').hide();
            currentCriteriaGroupIndex = 0;
            prevCriteriaGroupIndex = -1;
            reindexation_in_progress = false;
        }
    }, 500);
}
function reindexSearchCriteriaGroup(id_criterion_group, id_search) {
    $.ajax({
        method: "POST",
        url: baseConfigUrl + "&pm_load_function=reindexCriteriaGroup&id_criterion_group=" + id_criterion_group + "&id_search=" + id_search,
        dataType: "script",
        success: function (data) {
            currentCriteriaGroupIndex++;
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            alert("ERROR : " + errorThrown);
        }
    });
}

function processAddCustomCriterionToGroup(e, id_search, id_criterion_group) {
    var idCriterionListTmp = new Array;
    $('select[name^="custom_group_link_id_"]').each(function() {
        idCriterionListTmp.push($(this).attr('name').replace('custom_group_link_id_', '') + '-' + $(this).val());
    });

    $.ajax({
        type : "POST",
        url : baseConfigUrl + "&pm_load_function=processAddCustomCriterionToGroup&id_search="+ id_search,
        data : 'id_criterion_group=' + id_criterion_group + '&criterionsGroupList=' + idCriterionListTmp.join(','),
        dataType : "script",
        success : function (data) {},
        error : function (XMLHttpRequest, textStatus, errorThrown) {
            alert("ERROR : " + errorThrown);
        }
    });
}

$.fn.animateAppendTo = function(whereToAppend, duration, removeOld, callback) {
    var $this = this,
    newEle = $this.clone(true).appendTo(whereToAppend),
    newWidth = $this.width(),
    newHeight = $this.height(),
    newOffset = $this.position(),
    newPos = newEle.position();

    if (removeOld) {
        elementToAnimate = $this;
        newEle.css('visibility', 'hidden');
        newEle.removeClass('ui-state-disabled');
    } else {
        elementToAnimate = newEle;
    }
    elementToAnimate.removeClass('ui-state-disabled');

    elementToAnimate.width(newWidth);
    elementToAnimate.height(newHeight);
    elementToAnimate.css('left', newOffset.left);
    elementToAnimate.css('top', newOffset.top);
    elementToAnimate.css('position', 'absolute').animate(newPos, duration, function() {
        callback($this, newEle);
        if (removeOld) {
            newEle.css('visibility', 'visible');
            elementToAnimate.remove();
        } else {
            elementToAnimate.css('position', '');
            elementToAnimate.css('left', '');
            elementToAnimate.css('top', '');
            elementToAnimate.css('width', '');
            elementToAnimate.css('width', '');
        }
    });
    return newEle;
};

$(document).ready(function() {
    // Criterions groups
    $(document).on('click', '.availableCriterionGroups ul li', function() {
        addCriterion($(this));
    });
    // Use context for search
    $(document).on('change', 'select[name="filter_by_emplacement"]', function() {
        displayRelatedFilterByEmplacementOptions();
    });
    $(document).on('change', 'input[name=insert_in_center_column]', function() {
        displayRelatedSmartyVarOptions();
    });
    $(document).on('keyup', '#smarty_var_name', function() {
        updateSmartyVarNamePicker();
    });
    $(document).on('click', 'div#addCustomCriterionContainer input[name="submitAddCustomCriterionForm"]', function(e) {
        var idCriterionGroup = parseInt($(this).parent().parent().parent().data('id-criterion-group'));
        var idSearch = parseInt($(this).parent().parent().parent().data('id-search'));
        $.ajax({
            type : "POST",
            url : baseConfigUrl + "&pm_load_function=processAddCustomCriterion&id_criterion_group=" + idCriterionGroup + '&id_search='+ idSearch,
            data : $(this).parent().parent().parent().find('input').serialize(),
            dataType : "script",
            success : function (data) {},
            error : function (XMLHttpRequest, textStatus, errorThrown) {
                alert("ERROR : " + errorThrown);
            }
        });
    });
    $(document).on('click', 'table.criterionsList input[name="submitCustomCriterionForm"]', function(e) {
        if (typeof($(this).parent().parent().data('id-criterion')) != 'undefined') {
            var idCriterion = parseInt($(this).parent().parent().data('id-criterion'));
            var idSearch = parseInt($(this).parent().parent().data('id-search'));
            $.ajax({
                type : "POST",
                url : baseConfigUrl + "&pm_load_function=processUpdateCustomCriterion&id_criterion=" + idCriterion + '&id_search='+ idSearch,
                data : $(this).parent().parent().find('input').serialize(),
                dataType : "script",
                success : function (data) {},
                error : function (XMLHttpRequest, textStatus, errorThrown) {
                    alert("ERROR : " + errorThrown);
                    $('li#criterion_'+$(this).parent().parent().data('id-criterion')).removeClass('customCriterionEditState');
                }
            });
        }
        e.preventDefault();
    });
    $(document).on('click', 'input[name="submitSearch"], input[name="submitCriteriaGroupOptions"]', function(e) {
        // Add a small blur effect on the dialog's form, and display the loading animation
        $('body > form[target="dialogIframePostForm"]').css('filter', 'blur(2px)');
        $('body').append('<div class="as4-loader-bo"></div>');
    });
    $(document).on('click', 'a[href="#config-2"]', function() {
        // Fix possible hidden text by refreshing it right after
        editor.refresh();
    });
});
