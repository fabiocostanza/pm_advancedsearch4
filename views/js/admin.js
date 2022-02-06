/**
 *
 * @author Presta-Module.com <support@presta-module.com>
 * @copyright Presta-Module
 *
 ****/

function deleteCriterionImg(id_criterion, id_search, id_lang) {
	$.ajax({
		type : "GET",
		url : _base_config_url + "&pm_load_function=processDeleteCriterionImg&id_criterion=" + id_criterion + "&id_search=" + id_search + "&id_lang=" + id_lang,
		dataType : "script",
		error : function (XMLHttpRequest, textStatus, errorThrown) {
			// alert("ERROR : " + errorThrown);
		}
	});
}

function displayHideBar(e) {
	var itemType = $(e).val();
	var itemName = $(e).attr("name");
	if ($(e).is(":checked")) {
		$("#hide_after_" + itemType).show("fast");
	} else {
		$("#hide_after_" + itemType).hide("fast");
	}
	saveCriterionsGroupSorting(itemType);
}

function saveCriterionsGroupSorting(id_search) {
	var order = $("#searchTabContainer-" +  id_search + " .connectedSortableIndex").sortable({
	    items: "> li",
		axis: "y",
	}).sortable("toArray");
	var auto_hide = $("#searchTabContainer-" +  id_search + " input[name=auto_hide]").is(":checked");

	saveOrder(order.join(","), "orderCriterionGroup", id_search, auto_hide);
}

function showRelatedOptions(e, groupType) {
	var itemType = $(e).val();
	var itemName = $(e).attr("name");
	var allowCombineCriterions = true;
	var isColorGroup = false;
	if ($('#display_type option[value=7]').size() > 0) {
		isColorGroup = true;
	}
	
	// Init items display status
	$('#display_type-menu li').show();
	$('.blc_range, .blc_range_nb, .blc_range_interval, .blc_range_sign, .multicrit, .combined_criterion, .max_display_container, .overflow_height_container, .blc_with_search_area, .all_label, .blc_category_tree_options').hide();
	if (groupType != 'attribute' && groupType != 'feature' && groupType != 'price' && groupType != 'depth' && groupType != 'height' && groupType != 'width' & groupType != 'weight') {
		$('#display_type option[value=5]').removeAttr('selected').attr('disabled', 'disabled');
		$('#display_type option[value=8]').removeAttr('selected').attr('disabled', 'disabled');
		$('#display_type').pm_selectMenu();
	}
	if (groupType == 'category' || groupType == 'subcategory' || groupType == 'manufacturer' || groupType == 'supplier') {
		if ($("#range_on:checked").length) {
			$("#range_off").attr('checked', 'checked');
		}
		$(".multicrit, .combined_criterion").show();
	}
	if (groupType == 'price') {
		$('#display_type option[value=2]').removeAttr('selected').attr('disabled', 'disabled');
		$('#display_type').pm_selectMenu();
		if (itemType != "5" && $("#range_off:checked").length) {
			$("#range_on").attr('checked', 'checked');
		}
	}
	if (groupType == 'online_only' || groupType == 'pack' || groupType == 'on_sale' || groupType == 'available_for_order' || groupType == 'stock' || groupType == 'new_products' || groupType == 'prices_drop') {
		$('#display_type option[value=2]').removeAttr('selected').attr('disabled', 'disabled');
		$('#display_type').pm_selectMenu();
	}

	switch (itemType) {
		// Select
		case '1':
			if (groupType == 'price') {
				$('.blc_range_interval, .max_display_container, .overflow_height_container').show();
			} else if (groupType == 'category' || groupType == 'subcategory' || groupType == 'manufacturer' || groupType == 'supplier' || groupType == 'on_sale' || groupType == 'condition' || groupType == 'online_only' || groupType == 'available_for_order' || groupType == 'stock' || groupType == 'new_products' || groupType == 'prices_drop' || groupType == 'pack') {
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
				$("#range_off").attr('checked', 'checked');
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
			} else if (groupType == 'category' || groupType == 'subcategory' || groupType == 'manufacturer' || groupType == 'supplier' || groupType == 'on_sale' || groupType == 'condition' || groupType == 'online_only' || groupType == 'available_for_order' || groupType == 'stock' || groupType == 'new_products' || groupType == 'prices_drop' || groupType == 'pack') {
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
				$("#range_off").attr('checked', 'checked');
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
				$("#range_off").attr('checked', 'checked');
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
			$("#show_all_depth_on").attr('checked', 'checked');
			$("#show_all_depth_off").removeAttr('checked');
			$("#is_multicriteria_off").attr('checked', 'checked');
			$("#is_multicriteria_on").removeAttr('checked');
			$("#is_combined_off").attr('checked', 'checked');
			$("#is_combined_on").removeAttr('checked');
			allowCombineCriterions = false;

			// @todo: better way for this
			reorderCriteria('position', 'ASC', $('input[name=id_criterion_group').val(), $('input[name=id_search').val());
			break;
	}

	if (groupType == 'on_sale' || groupType == 'condition' || groupType == 'online_only' || groupType == 'available_for_order' || groupType == 'stock' || groupType == 'new_products' || groupType == 'prices_drop' || groupType == 'pack') {
		$(".combined_criterion").hide();
		allowCombineCriterions = false;
	}
	if (allowCombineCriterions && $("#is_multicriteria_on:checked").length) {
		$(".combined_criterion").show();
	} else {
		$(".combined_criterion").hide();
		$("#is_combined_off").attr('checked', 'checked');
		$("#is_combined_on").removeAttr('checked');
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
			$("#range_off").attr('checked', 'checked');
		}
		$('.blc_range, .blc_range_nb, .blc_range_interval, .blc_range_sign').hide();
		$('#display_type option[value=5]').removeAttr('selected').attr('disabled', 'disabled');
		$('#display_type option[value=8]').removeAttr('selected').attr('disabled', 'disabled');
		$('#display_type').pm_selectMenu();
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
		'<a title="' + editTranlate + '" ' + (typeof(show) == 'undefined' ? 'style="display:none;"' : '') + ' class="getCriterionGroupActions" id="action-' + key_criterions_group + '"><span class="ui-icon ui-icon-gear"></span></a>' +
		'<a title="' + deleteTranlate + '" ' + (typeof(show) == 'undefined' ? 'style="display:none;"' : '') + ' class="getCriterionGroupActions" id="delete-' + key_criterions_group + '"><span class="ui-icon ui-icon-trash"></span></a>' +
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
		openDialogIframe(_base_config_url + "&id_search=" + id_search + "&pm_load_function=displayCriterionGroupForm&class=AdvancedSearchCriterionGroupClass&id_criterion_group=" + id_criterion_group, 980, 540, 1);
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
function saveOrder(order, actionType, curId_search, auto_hide) {
	$.post(_base_config_url, {
		action : actionType,
		order : order,
		id_search : curId_search,
		auto_hide : auto_hide
	}, function (data) {
		parent.show_info(data);
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
	$(item).append("<div class='loadingOnConnectList'><img src='" + _modulePath + "views/img/snake_transparent.gif' /></div>");
	$.ajax({
		type : "GET",
		url : _base_config_url + "&pm_load_function=process" + curAction + "&id_employee=" + _id_employee + "&key_criterions_group=" + $(item).attr("id"),
		dataType : "script",
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
	
	$(tabPanelId + ' > ul').append('<li id="TabSearchAdminPanel' + id_search + '"><a href="' + _base_config_url + '&pm_load_function=displaySearchAdminPanel&id_search=' + id_search + '">' + label + '</a></li>');
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
	$(btn_submit).attr('disabled', 'disabled');
	$(btn_submit).attr('value', msgWait);
	return true;
}
// post-submit callback
function showResponse(responseText, statusText, xhr, $form) {
	var btn_submit = $form.find('input[type=submit]');
	if (defaultValueSubmit) {
		$(btn_submit).removeAttr('disabled');
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
		show_info(msgNoSeoCriterion);
		return;
	}
	$.ajax({
		type : "GET",
		url : _base_config_url + "&pm_load_function=processFillSeoFields&criteria=" + $("#seoSearchCriteriaInput").val() + "&id_search=" + $("#id_search").val() + "&id_currency=" + id_currency,
		dataType : "script",
		error : function (XMLHttpRequest, textStatus, errorThrown) {
			// alert("ERROR : " + errorThrown);
		}
	});
}

function checkChildrenCheckbox(e) {
	if (fromMassAction) {
		if ($(e).children('input[type=checkbox]:checked').length) {
			$(e).children('input[type=checkbox]').prop('checked', allCriterionEnable);
		} else {
			$(e).children('input[type=checkbox]').prop('checked', allCriterionEnable);
		}
	} else {
		if ($(e).children('input[type=checkbox]:checked').length) {
			$(e).children('input[type=checkbox]').prop('checked', false);
		} else {
			$(e).children('input[type=checkbox]').prop('checked', true);
		}
	}
}
function unCheckAllChildrenCheckbox(e) {
	$(e).find('input[type=checkbox]').removeAttr('checked');
}

var allCriterionEnable = false;
var fromMassAction = false;
function enableAllCriterion4MassSeo(e) {
	allCriterionEnable = !allCriterionEnable;
	var parentDiv = $(e).parent('div');
	var id_criterion_group = $(parentDiv).children('input[name=id_criterion_group]').val();
	if (!$('#criterion_group_' + id_criterion_group + ':visible').length && $('.seoSearchCriterionGroupSortable:visible').length >= 3) {
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
				if ($('.seoSearchCriterionGroupSortable:visible').length >= 3) {
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
		type : "GET",
		url : _base_config_url + "&pm_load_function=checkSeoCriteriaCombination&criteria=" + $("#seoSearchCriteriaInput").val() + "&id_search=" + $("#id_search").val() + "&id_currency=" + $("#posted_id_currency").val(),
		dataType : "script",
		error : function (XMLHttpRequest, textStatus, errorThrown) {
			// alert("ERROR : " + errorThrown);
		}
	});
}
function ASStr2url(e) {
	if (typeof str2url == 'function') {
		$(e).val(str2url($(e).val(), 'UTF-8'));
		return true;
	}
	str = $(e).val();

	// From admin.js - 1.6.1.0
	if (typeof(PS_ALLOW_ACCENTED_CHARS_URL) != 'undefined' && PS_ALLOW_ACCENTED_CHARS_URL) {
		str = str.replace(/[^a-z0-9\s\'\:\/\[\]-]\\u00A1-\\uFFFF/g,'');
	} else {
		/* Lowercase */
		str = str.replace(/[\u00E0\u00E1\u00E2\u00E3\u00E5\u0101\u0103\u0105\u0430]/g, 'a');
		str = str.replace(/[\u0431]/g, 'b');
		str = str.replace(/[\u00E7\u0107\u0109\u010D\u0446]/g, 'c');
		str = str.replace(/[\u010F\u0111\u0434]/g, 'd');
		str = str.replace(/[\u00E8\u00E9\u00EA\u00EB\u0113\u0115\u0117\u0119\u011B\u0435\u044D]/g, 'e');
		str = str.replace(/[\u0444]/g, 'f');
		str = str.replace(/[\u011F\u0121\u0123\u0433\u0491]/g, 'g');
		str = str.replace(/[\u0125\u0127]/g, 'h');
		str = str.replace(/[\u00EC\u00ED\u00EE\u00EF\u0129\u012B\u012D\u012F\u0131\u0438\u0456]/g, 'i');
		str = str.replace(/[\u0135\u0439]/g, 'j');
		str = str.replace(/[\u0137\u0138\u043A]/g, 'k');
		str = str.replace(/[\u013A\u013C\u013E\u0140\u0142\u043B]/g, 'l');
		str = str.replace(/[\u043C]/g, 'm');
		str = str.replace(/[\u00F1\u0144\u0146\u0148\u0149\u014B\u043D]/g, 'n');
		str = str.replace(/[\u00F2\u00F3\u00F4\u00F5\u00F8\u014D\u014F\u0151\u043E]/g, 'o');
		str = str.replace(/[\u043F]/g, 'p');
		str = str.replace(/[\u0155\u0157\u0159\u0440]/g, 'r');
		str = str.replace(/[\u015B\u015D\u015F\u0161\u0441]/g, 's');
		str = str.replace(/[\u00DF]/g, 'ss');
		str = str.replace(/[\u0163\u0165\u0167\u0442]/g, 't');
		str = str.replace(/[\u00F9\u00FA\u00FB\u0169\u016B\u016D\u016F\u0171\u0173\u0443]/g, 'u');
		str = str.replace(/[\u0432]/g, 'v');
		str = str.replace(/[\u0175]/g, 'w');
		str = str.replace(/[\u00FF\u0177\u00FD\u044B]/g, 'y');
		str = str.replace(/[\u017A\u017C\u017E\u0437]/g, 'z');
		str = str.replace(/[\u00E4\u00E6]/g, 'ae');
		str = str.replace(/[\u0447]/g, 'ch');
		str = str.replace(/[\u0445]/g, 'kh');
		str = str.replace(/[\u0153\u00F6]/g, 'oe');
		str = str.replace(/[\u00FC]/g, 'ue');
		str = str.replace(/[\u0448]/g, 'sh');
		str = str.replace(/[\u0449]/g, 'ssh');
		str = str.replace(/[\u044F]/g, 'ya');
		str = str.replace(/[\u0454]/g, 'ye');
		str = str.replace(/[\u0457]/g, 'yi');
		str = str.replace(/[\u0451]/g, 'yo');
		str = str.replace(/[\u044E]/g, 'yu');
		str = str.replace(/[\u0436]/g, 'zh');

		/* Uppercase */
		str = str.replace(/[\u0100\u0102\u0104\u00C0\u00C1\u00C2\u00C3\u00C4\u00C5\u0410]/g, 'A');
		str = str.replace(/[\u0411]/g, 'B');
		str = str.replace(/[\u00C7\u0106\u0108\u010A\u010C\u0426]/g, 'C');
		str = str.replace(/[\u010E\u0110\u0414]/g, 'D');
		str = str.replace(/[\u00C8\u00C9\u00CA\u00CB\u0112\u0114\u0116\u0118\u011A\u0415\u042D]/g, 'E');
		str = str.replace(/[\u0424]/g, 'F');
		str = str.replace(/[\u011C\u011E\u0120\u0122\u0413\u0490]/g, 'G');
		str = str.replace(/[\u0124\u0126]/g, 'H');
		str = str.replace(/[\u0128\u012A\u012C\u012E\u0130\u0418\u0406]/g, 'I');
		str = str.replace(/[\u0134\u0419]/g, 'J');
		str = str.replace(/[\u0136\u041A]/g, 'K');
		str = str.replace(/[\u0139\u013B\u013D\u0139\u0141\u041B]/g, 'L');
		str = str.replace(/[\u041C]/g, 'M');
		str = str.replace(/[\u00D1\u0143\u0145\u0147\u014A\u041D]/g, 'N');
		str = str.replace(/[\u00D3\u014C\u014E\u0150\u041E]/g, 'O');
		str = str.replace(/[\u041F]/g, 'P');
		str = str.replace(/[\u0154\u0156\u0158\u0420]/g, 'R');
		str = str.replace(/[\u015A\u015C\u015E\u0160\u0421]/g, 'S');
		str = str.replace(/[\u0162\u0164\u0166\u0422]/g, 'T');
		str = str.replace(/[\u00D9\u00DA\u00DB\u0168\u016A\u016C\u016E\u0170\u0172\u0423]/g, 'U');
		str = str.replace(/[\u0412]/g, 'V');
		str = str.replace(/[\u0174]/g, 'W');
		str = str.replace(/[\u0176\u042B]/g, 'Y');
		str = str.replace(/[\u0179\u017B\u017D\u0417]/g, 'Z');
		str = str.replace(/[\u00C4\u00C6]/g, 'AE');
		str = str.replace(/[\u0427]/g, 'CH');
		str = str.replace(/[\u0425]/g, 'KH');
		str = str.replace(/[\u0152\u00D6]/g, 'OE');
		str = str.replace(/[\u00DC]/g, 'UE');
		str = str.replace(/[\u0428]/g, 'SH');
		str = str.replace(/[\u0429]/g, 'SHH');
		str = str.replace(/[\u042F]/g, 'YA');
		str = str.replace(/[\u0404]/g, 'YE');
		str = str.replace(/[\u0407]/g, 'YI');
		str = str.replace(/[\u0401]/g, 'YO');
		str = str.replace(/[\u042E]/g, 'YU');
		str = str.replace(/[\u0416]/g, 'ZH');
		str = str.toLowerCase();
		str = str.replace(/[^a-z0-9\s\'\:\/\[\]-]/g,'');
	}
	str = str.replace(/[\u0028\u0029\u0021\u003F\u002E\u0026\u005E\u007E\u002B\u002A\u002F\u003A\u003B\u003C\u003D\u003E]/g, '');
	str = str.replace(/[\s\'\:\/\[\]-]+/g, ' ');

	// Add special char not used for url rewrite
	str = str.replace(/[ ]/g, '-');
	str = str.replace(/[\/\\"'|,;%]*/g, '');
	// From admin.js - 1.6.1.0

	$(e).val(str)

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
			'{* Advanced Search 4 - Start of custom search variable *}'
			+ "\n" + '{if isset($' + smartyVarName + ')}' + '{$' + smartyVarName + '}'
			+ ($('input[name="insert_in_center_column"]:checked').val() == 1 ? '&lt;div id="as_custom_content_results"&gt;&lt;/div&gt;' : '')
			+ '{/if}'
			+ "\n" + '{* /Advanced Search 4 - End of custom search variable *}'
		);
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
		type : "GET",
		url : _base_config_url + "&pm_load_function=processDeleteMassSeo&id_search=" + id_search + '&' + $('#dataTable' + id_search + ' input[name="seo_group_action[]"]:checked').serialize(),
		dataType : "script",
		error : function (XMLHttpRequest, textStatus, errorThrown) {}
	});
}

function reorderCriteria(sort_by, sort_way, id_criterion_group, id_search) {
	$('#sortCriteriaPanel').load(_base_config_url + "&pm_load_function=displaySortCriteriaPanel&id_criterion_group=" + id_criterion_group + '&sort_by=' + sort_by + '&sort_way=' + sort_way + '&id_search=' + id_search);
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
			$('select[name="filter_by_emplacement"]').val(1).trigger("change").trigger("chosen:updated");
		}
		$('select[name="search_method"] option[value="3"]').removeAttr('selected').attr('disabled', 'disabled').hide();
		$('select[name="search_method"]').trigger("change").trigger("chosen:updated");
	} else if (searchType == 1) {
		// Global
		$('input[name="step_search"]').val(0);
		$('.enabled-option-step-search').hide('medium');
		// Only apply presets if value is changed from the select
		if (realChange == true) {
			$('select[name="filter_by_emplacement"]').val(0).trigger("change").trigger("chosen:updated");
		}
		$('select[name="search_method"] option[value="3"]').removeAttr('selected').attr('disabled', 'disabled').hide();
		$('select[name="search_method"]').trigger("change").trigger("chosen:updated");
	} else if (searchType == 2) {
		// Step by step
		$('input[name="step_search"]').val(1);
		$('.enabled-option-step-search').show('medium');
		// Only apply presets if value is changed from the select
		if (realChange == true) {
			$('input[name="hide_empty_crit_group"][value=0]').attr('checked', 'checked');
			$('input[name="hide_criterions_group_with_no_effect"][value=0]').attr('checked', 'checked');
			$('input[name="display_empty_criteria"][value=0]').attr('checked', 'checked');
			$('input[name="step_search_next_in_disabled"][value=0]').attr('checked', 'checked');
			$('select[name="filter_by_emplacement"]').val(0).trigger("change").trigger("chosen:updated");
		}
		$('select[name="search_method"] option[value=3]').removeAttr('disabled').show();
		$('select[name="search_method"]').trigger("change").trigger("chosen:updated");
	}
	var stepSearch = parseInt($('input[name="step_search"]').val());
	var displayEmptyCriterion = parseInt($('input[name="display_empty_criteria"]:checked').val());
	
	$('select[name="search_method"] option').removeAttr('disabled');
	$('select[name="search_method"]').trigger('change').trigger("chosen:updated");
	
	if (displayEmptyCriterion) {
		$('.hide-empty-criterion-group').hide('medium');
	} else {
		$('.hide-empty-criterion-group').show('medium');
	}
}

function display_search_method_options() {
	var val = parseInt($('select[name="search_method"]').val());
	if (val == 2) {
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
function reindexSearchCriterionGroups(e, criterionGroups, wrapperProgress) {
	if (reindexation_in_progress) {
		alert(reindexationInprogressMsg);
		return;
	}
	reindexation_in_progress = true;
	var nbCriteriaGroupsTotal = criterionGroups.length;
	var nbCriteriaGroupsReindexed = 0;

	$('.progressbarReindexSpecificSearch').css('display', 'inline-block');
	$(e).hide();

	var reindexationInterval = setInterval(function () {
			if (typeof(criterionGroups[currentCriteriaGroupIndex]) != 'undefined' && currentCriteriaGroupIndex != prevCriteriaGroupIndex) {
				// Reindexation in progress
				prevCriteriaGroupIndex++;
				$(wrapperProgress).progressbar({
					value : Math.round((currentCriteriaGroupIndex * 100) / nbCriteriaGroupsTotal)
				});
				$(wrapperProgress).next('.progressbarpercent').html(reindexingCriteriaMsg + ' ' + currentCriteriaGroupIndex + ' ' + reindexingCriteriaOfMsg + ' ' + nbCriteriaGroupsTotal + ' (' + Math.round((currentCriteriaGroupIndex * 100) / nbCriteriaGroupsTotal) + '%)');
				reindexSearchCritriaGroup(criterionGroups[currentCriteriaGroupIndex].id_criterion_group, criterionGroups[currentCriteriaGroupIndex].id_search);
			} else if (typeof(criterionGroups[currentCriteriaGroupIndex]) == 'undefined') {
				// Reindexation done
				$(wrapperProgress).progressbar({
					value : 100
				});
				clearInterval(reindexationInterval);
				$(e).show();
				$(wrapperProgress).next('.progressbarpercent').text("");
				$(wrapperProgress).progressbar("destroy");
				$('.progressbarReindexSpecificSearch').hide();
				currentCriteriaGroupIndex = 0;
				prevCriteriaGroupIndex = -1;
				reindexation_in_progress = false;
			}
		}, 500);
}
function reindexSearchCritriaGroup(id_criterion_group, id_search) {
	$.ajax({
		type : "GET",
		url : _base_config_url + "&pm_load_function=reindexCriteriaGroup&id_criterion_group=" + id_criterion_group + "&id_search=" + id_search,
		dataType : "script",
		success : function (data) {
			currentCriteriaGroupIndex++;
		},
		error : function (XMLHttpRequest, textStatus, errorThrown) {
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
		url : _base_config_url + "&pm_load_function=processAddCustomCriterionToGroup&id_search="+ id_search,
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
			url : _base_config_url + "&pm_load_function=processAddCustomCriterion&id_criterion_group=" + idCriterionGroup + '&id_search='+ idSearch,
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
				url : _base_config_url + "&pm_load_function=processUpdateCustomCriterion&id_criterion=" + idCriterion + '&id_search='+ idSearch,
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
});
