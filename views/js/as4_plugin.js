/**
 *
 * Advanced Search 4
 *
 * @author Presta-Module.com <support@presta-module.com>
 * @copyright Presta-Module
 *
 *           ____     __  __
 *          |  _ \   |  \/  |
 *          | |_) |  | |\/| |
 *          |  __/   | |  | |
 *          |_|      |_|  |_|
 *
 ****/

var as4Plugin = {

    // Attributes
    locationName: false,
    lastIdSearch: false,
    // Set to false in order to disable localStorage cache for AJAX queries
    localCache: false,
    localCacheKey: '',
    waitingLayers: new Array(),
    params: new Array(),
    extraParams: new Array(),
    persistentParams: new Array(),
    visibleCriterionsGroupsHash: '',
    fromBackForwardEvent: false,
    localStorageAvailable: null,
    blurEffect: true,
    previousOnPopState: null,

    // Init
    initDone: false,

    // Get search results selector
    getSearchResultsSelector: function(idSearch) {
        return (as4Plugin.getParamValue(idSearch, 'search_results_selector') != '' ? as4Plugin.getParamValue(idSearch, 'search_results_selector') : '#center_column');
    },

    // Get object value from key
    getObjectValueByKey: function(obj, key) {
        if (obj.length) {
            for (var k in obj) {
                if (obj[k].name == key) {
                    return obj[k].value;
                }
            }
        }
        return null;
    },

    // Get params var
    getParamValue: function(idSearch, varName) {
        if (typeof(as4Plugin.params[idSearch][varName]) != 'undefined') {
            return as4Plugin.params[idSearch][varName];
        }
        return false;
    },

    // Get persistent params var
    getPersistentParamValue: function(idSearch, varName) {
        if (typeof(as4Plugin.persistentParams[idSearch]) == 'undefined') {
            as4Plugin.persistentParams[idSearch] = new Array();
        }
        if (typeof(as4Plugin.persistentParams[idSearch][varName]) != 'undefined') {
            return as4Plugin.persistentParams[idSearch][varName];
        }
        return false;
    },

    // Set persistent params var
    setPersistentParamValue: function(idSearch, varName, varValue) {
        if (typeof(as4Plugin.persistentParams[idSearch]) == 'undefined') {
            as4Plugin.persistentParams[idSearch] = new Array();
        }
        as4Plugin.persistentParams[idSearch][varName] = varValue;
    },

    // Prevent some action to be done if search is triggered from back/forward event
    getASFormOptionsCompleteCallBack: function(arg1) {
        as4Plugin.fromBackForwardEvent = false;
    },

    // Get Ajax dynamic parameters
    getASFormOptions: function(idSearch) {
        return {
            beforeSubmit: as4Plugin.showAsRequest,
            success: as4Plugin.showAsResponse,
            complete: as4Plugin.getASFormOptionsCompleteCallBack,
            localCache: as4Plugin.localCache,
            localCacheKey: as4Plugin.localCacheKey,
            cacheTTL: 2,
            dataType: 'json',
            data: {
                ajaxMode: 1,
                productFilterListData: as4Plugin.getParamValue(idSearch, 'as4_productFilterListData'),
                productFilterListSource: as4Plugin.getParamValue(idSearch, 'as4_productFilterListSource'),
                with_product: 1
            },
            type: "GET"
        };
    },

    // Get Ajax dynamic parameters
    getASFormDynamicCriterionOptions: function(idSearch) {
        return {
            beforeSubmit: as4Plugin.showAsRequest,
            success: as4Plugin.showAsResponse,
            localCache: as4Plugin.localCache,
            localCacheKey: as4Plugin.localCacheKey,
            cacheTTL: 2,
            dataType: 'json',
            mode: 'abort',
            port: 'asSearch',
            data: {
                with_product: 0,
                ajaxMode: 1,
                productFilterListData: as4Plugin.getParamValue(idSearch, 'as4_productFilterListData'),
                productFilterListSource: as4Plugin.getParamValue(idSearch, 'as4_productFilterListSource')
            },
            type: "GET"
        };
    },

    // Add extra parameters to AJAX data and for History API
    setExtraParameters: function(ajaxData, nextExtraParams) {
        if (nextExtraParams == null) {
            return;
        }
        for (var i = 0; i < nextExtraParams.length; i++) {
            tmpParameter = nextExtraParams[i].substring(1).split('=');
            extraParameterKey = tmpParameter.shift();
            if (extraParameterKey) {
                extraParameterValue = tmpParameter.join('=');
                ajaxData.push({
                    name: extraParameterKey,
                    value: extraParameterValue,
                });
                as4Plugin.extraParams.push({
                    name: extraParameterKey,
                    value: extraParameterValue,
                });
            }
        }
    },

    // Pre-submit callback
    showAsRequest: function(formData, jqForm, options) {
        var idSearch = $(jqForm).find('input[name=id_search]').val();
        if (typeof(idSearch) == 'undefined' && typeof(history.state) != 'undefined' && history.state != null && typeof(history.state.id_search) != 'undefined' && !isNaN(history.state.id_search)) {
            idSearch = history.state.id_search;
        }
        if (isNaN(idSearch) && as4Plugin.lastIdSearch != false && !isNaN(as4Plugin.lastIdSearch)) {
            // Retrieve latest known idSearch
            idSearch = as4Plugin.lastIdSearch;
        }
        if (isNaN(idSearch)) {
            // Retrieve idSearch from ajax call data
            idSearch = parseInt(as4Plugin.getObjectValueByKey(formData, 'id_search'));
        }
        if (!isNaN(idSearch)) {
            // With product ?
            withProduct = parseInt(as4Plugin.getObjectValueByKey(formData, 'with_product'));
            as4Plugin.lastIdSearch = idSearch;
            as4Plugin.setLayer('#PM_ASBlockOutput_' + idSearch);
            if (withProduct) {
                as4Plugin.setLayer(as4Plugin.getSearchResultsSelector(idSearch));
                // Add CSS classes to center column
                var centerColumnCssClasses = as4Plugin.getParamValue(idSearch, 'centerColumnCssClasses');
                if (typeof(centerColumnCssClasses) != 'undefined' && centerColumnCssClasses != null && centerColumnCssClasses.length > 0) {
                    $(as4Plugin.getSearchResultsSelector(idSearch)).addClass(centerColumnCssClasses);
                }
            }
        }
        return true;
    },

    scrollTop: function(idSearch, context) {
        if (as4Plugin.getParamValue(idSearch, 'scrollTopActive') == true) {
            if (as4Plugin.getParamValue(idSearch, 'stepSearch') == 1) {
                var pm_scrollTopSelector = $('#PM_ASForm_' + idSearch + ' .PM_ASCriterionsGroupTitle:visible:last');
                if (as4Plugin.visibleCriterionsGroupsHash == as4Plugin.getVisibleCriterionsGroupsHash(idSearch) || typeof(pm_scrollTopSelector) == 'undefined' || context == 'pagination' || context == 'order_by') {
                    pm_scrollTopSelector = as4Plugin.getSearchResultsSelector(idSearch);
                }
            } else {
                pm_scrollTopSelector = as4Plugin.getSearchResultsSelector(idSearch);
            }

            if (typeof($(pm_scrollTopSelector)) != 'undefined' && $(pm_scrollTopSelector).size() > 0) {
                $('html, body').animate({
                    scrollTop: $(pm_scrollTopSelector).offset().top
                }, 500);
                as4Plugin.visibleCriterionsGroupsHash = as4Plugin.getVisibleCriterionsGroupsHash(idSearch);
            }
        }
    },

    getVisibleCriterionsGroupsHash: function(idSearch) {
        var pm_getVisibleCriterionsGroupsHashReturn = '';
        if ($('#PM_ASForm_' + idSearch + ' .PM_ASCriterionsGroupTitle:visible') != 'undefined' && $('#PM_ASForm_' + idSearch + ' .PM_ASCriterionsGroupTitle:visible').size() > 0) {
            $('#PM_ASForm_' + idSearch + ' .PM_ASCriterionsGroupTitle:visible').each(function() {
                pm_getVisibleCriterionsGroupsHashReturn += '-' + $(this).attr('id');
            });
            return pm_getVisibleCriterionsGroupsHashReturn;
        }
        return pm_getVisibleCriterionsGroupsHashReturn;
    },

    setResultsContents: function(id_search, htmlResults, context) {
        $(document).trigger('as4-Before-Set-Results-Contents', [id_search, context]);
        var keepCategoryInformation = as4Plugin.getParamValue(id_search, 'keep_category_information');
        var searchResultsSelector = as4Plugin.getSearchResultsSelector(id_search);
        var insertInCenterColumn = parseInt(as4Plugin.getParamValue(id_search, 'insert_in_center_column'));

        // Init sliders
        as4Plugin.initSliders();

        // Remove any previous search results (SEO pages case)
        $('#PM_ASearchResultsInner, #PM_ASearchResults').remove();
        if (keepCategoryInformation) {
            $('span.heading-counter, #productsSortForm, #pagination, .content_sortPagiBar, .pagination, ' + searchResultsSelector + ' form, ' + searchResultsSelector + ' script, #product_list, .product_list, .listorgridswitch, .listorgridcanvas').remove();
            $(searchResultsSelector).css('height', 'auto');
        }

        if (searchResultsSelector != '' && (searchResultsSelector == '#as_home_content_results' || insertInCenterColumn == 1)) {
            if (searchResultsSelector == '#as_custom_content_results' && keepCategoryInformation) {
                $('#PM_ASBlockOutput_' + id_search).parent('div').find('*:not(#PM_ASBlockOutput_' + id_search + ', #PM_ASBlockOutput_' + id_search + ' *, ' + searchResultsSelector + ', .content_scene_cat, .page-heading)').remove();
            } else {
                $('#PM_ASBlockOutput_' + id_search).parent('div').find('*:not(#PM_ASBlockOutput_' + id_search + ', #PM_ASBlockOutput_' + id_search + ' *, ' + searchResultsSelector + ')').remove();
            }
        }
        var destinationElement = $('body ' + searchResultsSelector);
        if ($(destinationElement).size() > 0) {
            // Animation complete.
            $(searchResultsSelector).css('height', 'auto');
            if (keepCategoryInformation) {
                if ($('#PM_ASearchSeoCrossLinks').size() > 0) {
                    $(htmlResults).insertBefore('#PM_ASearchSeoCrossLinks');
                } else {
                    $(searchResultsSelector).append(htmlResults);
                }
            } else {
                $(searchResultsSelector).html(htmlResults);
            }
            as4Plugin.scrollTop(id_search, context);
        }
        as4Plugin.removeLayer();
        $(document).trigger('as4-After-Set-Results-Contents', [id_search, context]);
    },

    showAsResponse: function(responseText, statusText, xhr, $form) {
        if (typeof(responseText.redirect_to_url) != 'undefined' && responseText.redirect_to_url != '') {
            window.location = responseText.redirect_to_url;
            return;
        }
        if (typeof($form) == 'undefined') {
            $form = $('#PM_ASForm_' + history.state.id_search);
        }
        if (typeof(responseText.url) != 'undefined' && responseText.url != '') {
            as4Plugin.pushStateNewURL(responseText.url);
        }

        var id_search = $form.find('input[name=id_search]').val();
        var step_search = as4Plugin.getParamValue(id_search, 'stepSearch');
        var hookName = as4Plugin.getParamValue(id_search, 'hookName');

        if (typeof(responseText.html_block) != 'undefined' && responseText.html_block != '' && responseText.html_block != null) {
            var htmlBlock = responseText.html_block;
            step_search = false;
        } else if (step_search == 1) {
            var next_id_criterion_group = $form.find('input[name="next_id_criterion_group"]').val();
            var htmlBlock = responseText.html_criteria_block;
            as4Plugin.setNextIdCriterionGroup(id_search, responseText.next_id_criterion_group);
        }
        var htmlResults = responseText.html_products;
        if (htmlBlock) {
            if (hookName == 'top') {
                if (step_search == 1) {
                    var htmlBlockSelection = responseText.html_selection_block;
                    if (htmlBlockSelection) {
                        $('#PM_ASBlock_' + id_search + ' .PM_ASSelectionsBlock').html(htmlBlockSelection);
                    }
                    $('#PM_ASCriterionsGroup_' + id_search + '_' + next_id_criterion_group).html(htmlBlock);
                } else {
                    $('#PM_ASBlockOutput_' + id_search).html(htmlBlock);
                }
            } else {
                // Animation complete.
                if (step_search == 1) {
                    var htmlBlockSelection = responseText.html_selection_block;
                    if (htmlBlockSelection) {
                        $('#PM_ASBlock_' + id_search + ' .PM_ASSelectionsBlock').html(htmlBlockSelection);
                    }
                    $('#PM_ASCriterionsGroup_' + id_search + '_' + next_id_criterion_group).html(htmlBlock);
                } else {
                    $('#PM_ASBlockOutput_' + id_search).html(htmlBlock);
                }
            }
        }
        if (htmlResults) {
            as4Plugin.setResultsContents(id_search, htmlResults, 'showAsResponse');
        } else {
            as4Plugin.removeLayer();
        }
    },

    runSearch: function(id_search, search_method) {
        if (search_method == 1) {
            setTimeout(function() {
                $('#PM_ASForm_' + id_search).ajaxSubmit(as4Plugin.getASFormOptions(id_search));
            }, 1);
        } else if (search_method == 2) {
            setTimeout(function() {
                $('#PM_ASForm_' + id_search).ajaxSubmit(as4Plugin.getASFormDynamicCriterionOptions(id_search));
            }, 1);
        }
    },

    nextStep: function(id_search, search_method) {
        setTimeout(function() {
            if (search_method == 2) {
                $('#PM_ASForm_' + id_search).ajaxSubmit(as4Plugin.getASFormDynamicCriterionOptions(id_search));
            } else {
                $('#PM_ASForm_' + id_search).ajaxSubmit(as4Plugin.getASFormOptions(id_search));
            }
        }, 1);
    },

    // Get AS URL because it may be incorrectly formatted
    getAsAjaxUrl: function(curUrl) {
        var destUrl = curUrl;
        var asPathReg = new RegExp("(" + ASPath + ")", "g");
        if (!destUrl.match(asPathReg)) {
            var asQuery = curUrl.substring(curUrl.indexOf("?", 0));
            if (ASSearchUrl.indexOf("?", 0) != -1 && asQuery.indexOf("?", 0) == 0) {
                destUrl = ASSearchUrl + '&' + asQuery.substring(1, asQuery.length);
            } else {
                if (typeof(asQuery[0]) != 'undefined' && asQuery[0] == '?') {
                    if (asQuery.indexOf("?", 1) != -1) {
                        // Second ?, fix URL
                        asQuery = asQuery.substring(0, asQuery.indexOf("?", 1)) + '&' + asQuery.substring(asQuery.indexOf("?", 1) + 1, asQuery.length);
                    }
                }
                destUrl = ASSearchUrl + asQuery;
            }
        }
        return destUrl;
    },

    getFormSerialized: function(id_search) {
        return $('#PM_ASForm_' + id_search).serialize();
    },

    getFormSerializedArray: function(id_search) {
        return $('#PM_ASForm_' + id_search).serializeArray();
    },

    addBestSalesOptions: function(id_search) {
        if (as4Plugin.getParamValue(id_search, 'addBestSalesOption') == true) {
            // Add best sales option
            $(document).ready(function() {
                $('#selectPrductSort, #selectProductSort, .selectPrductSort').each(function() {
                    $('option[value^="sales:"]', this).remove();
                    if ($('option[value^="sales:"]', this).size() == 0) {
                        if (as4Plugin.getParamValue(id_search, 'orderBy') == 'sales') {
                            $('option:selected', this).removeAttr('selected').prop('selected', false);
                        }
                        // Add new items
                        if (as4Plugin.getParamValue(id_search, 'orderBy') == 'sales' && as4Plugin.getParamValue(id_search, 'orderWay') == 'asc') {
                            $(this).append('<option value="sales:asc" selected="selected">' + as4_orderBySalesAsc + '</option>');
                        } else {
                            $(this).append('<option value="sales:asc">' + as4_orderBySalesAsc + '</option>');
                        }
                        if (as4Plugin.getParamValue(id_search, 'orderBy') == 'sales' && as4Plugin.getParamValue(id_search, 'orderWay') == 'desc') {
                            $(this).append('<option value="sales:desc" selected="selected">' + as4_orderBySalesDesc + '</option>');
                        } else {
                            $(this).append('<option value="sales:desc">' + as4_orderBySalesDesc + '</option>');
                        }
                    }
                });
            });
        }
    },

    getIdSearchFromItem: function(item) {
        if ($(item).parents('.PM_ASBlockOutput').size() > 0) {
            return $(item).parents('.PM_ASBlockOutput').data('id-search');
        } else if ($(item).parents('#PM_ASearchResults').size() > 0) {
            return $(item).parents('#PM_ASearchResults').data('id-search');
        }
        return false;
    },

    initSearchEngine: function() {
        // Init is already done...
        if (as4Plugin.initDone) {
            return;
        }
        as4Plugin.initDone = true;

        $(document).on('click', '.PM_ASBlockOutput .PM_ASResetSearch', function(e) {
            e.preventDefault();
            id_search = as4Plugin.getIdSearchFromItem(this);
            $(document).trigger('as4-Search-Reset', [id_search]);
            location.href = as4Plugin.getParamValue(id_search, 'resetURL');
        });

        $(document).on('click', '.PM_ASSelectionsBlock .PM_ASSelectionsDropDownShowLink', function(e) {
            e.preventDefault();
            $(this).next('.PM_ASSelectionsDropDownMenu').slideToggle('fast');
        });

        $(document).on('click', '.PM_ASBlockOutput .PM_ASLabelCheckbox', function(e) {
            e.preventDefault();
            $('input#' + $(this).attr('for')).trigger('click');
        });

        $(document).on('click', '.PM_ASBlockOutput .PM_ASCriterionEnable .PM_ASCriterionLink', function(e) {
            e.preventDefault();

            if ($(this).parents('li').hasClass('PM_ASCriterionDisable')) {
                return;
            }
            id_search = as4Plugin.getIdSearchFromItem(this);
            id_criterion_group = $(this).data('id-criterion-group');
            if (typeof(id_criterion_group) != 'undefined' && as4Plugin.getParamValue(id_search, 'seo_criterion_groups') != '' && as4Plugin.getParamValue(id_search, 'seo_criterion_groups').length > 0) {
                if ($.inArray(id_criterion_group, as4Plugin.getParamValue(id_search, 'seo_criterion_groups').split(',')) != -1) {
                    return;
                }
            }

            if (!$(this).hasClass('PM_ASCriterionLinkSelected')) {
                $(this).next('input').removeAttr('disabled');
                $(this).addClass('PM_ASCriterionLinkSelected');
            } else {
                $(this).next('input').attr('disabled', 'disabled');
                $(this).removeClass('PM_ASCriterionLinkSelected');
            }

            $(document).trigger('as4-Criterion-Change', [id_search, id_criterion_group, $(this).next('input').val(), $.trim($(this).text() == '' ? $(this).attr('title') : $(this).text()), 'link']);
        });

        $(document).on('click', '.PM_ASBlockOutput .PM_ASCriterionStepEnable .PM_ASCriterionLink', function(e) {
            e.preventDefault();

            if ($(this).parents('li').hasClass('PM_ASCriterionDisable')) {
                return;
            }

            id_search = as4Plugin.getIdSearchFromItem(this);
            id_criterion_group = $(this).data('id-criterion-group');
            if (typeof(id_criterion_group) != 'undefined' && as4Plugin.getParamValue(id_search, 'seo_criterion_groups') != '' && as4Plugin.getParamValue(id_search, 'seo_criterion_groups').length > 0) {
                if ($.inArray(id_criterion_group, as4Plugin.getParamValue(id_search, 'seo_criterion_groups').split(',')) != -1) {
                    return;
                }
            }

            if (!$(this).hasClass('PM_ASCriterionLinkSelected')) {
                $(this).next('input').removeAttr('disabled');
                $(this).addClass('PM_ASCriterionLinkSelected');
            } else {
                $(this).next('input').attr('disabled', 'disabled');
                $(this).removeClass('PM_ASCriterionLinkSelected');
            }

            $(document).trigger('as4-Criterion-Change', [id_search, id_criterion_group, $(this).next('input').val(), $.trim($(this).text() == '' ? $(this).attr('title') : $(this).text()), 'link']);
        });

        $(document).on('change', '.PM_ASBlockOutput .PM_ASCriterionGroupSelect', function(e) {
            e.preventDefault();
            if ($(this).is('select')) {
                id_search = as4Plugin.getIdSearchFromItem(this);
                id_criterion_group = $(this).data('id-criterion-group');

                $(document).trigger('as4-Criterion-Change', [id_search, id_criterion_group, $(this).val(), $.trim($(this).find('option:selected').text()), 'select']);
            }
        });

        $(document).on('click', '.PM_ASBlockOutput .PM_ASCriterionCheckbox', function(e) {
            id_search = as4Plugin.getIdSearchFromItem(this);
            id_criterion_group = $(this).data('id-criterion-group');

            $(document).trigger('as4-Criterion-Change', [id_search, id_criterion_group, $(this).val(), $.trim($('label[for="as4c_' + $(this).attr('data-id-criterion-group') + '_' + $(this).val() + '"]').text()), 'checkbox']);
        });

        // Numeric range
        $(document).on('change', '.PM_ASCriterionsGroup input[type=number]', function(e) {
            e.preventDefault();

            id_search = as4Plugin.getIdSearchFromItem(this);
            id_criterion_group = $(this).data('id-criterion-group');
            search_method = as4Plugin.getParamValue(id_search, 'searchMethod')
            step_search = as4Plugin.getParamValue(id_search, 'stepSearch');

            min = parseFloat($('#PM_ASInputCritRange' + id_search + '_' + id_criterion_group + '_min').val());
            max = parseFloat($('#PM_ASInputCritRange' + id_search + '_' + id_criterion_group + '_max').val());

            if (min == "NaN" || max == "NaN") {
                return false;
            }

            newInputValue = (min <= max ? min : max) + "~" + (max >= min ? max : min);
            $('#PM_ASInputCritRange' + id_search + '_' + id_criterion_group).val(newInputValue);

            if (step_search == 1) {
                as4Plugin.nextStep(id_search, $('#PM_ASInputCritRange' + id_search + '_' + id_criterion_group), null, search_method);
            } else {
                if (search_method == 1) {
                    $('#PM_ASForm_' + id_search + '').ajaxSubmit(as4Plugin.getASFormOptions(id_search));
                }
                if (search_method == 2) {
                    $('#PM_ASForm_' + id_search + '').ajaxSubmit(as4Plugin.getASFormDynamicCriterionOptions(id_search));
                }
            }

            $(document).trigger('as4-Criterion-Change', [id_search, id_criterion_group, null, null, 'range']);
        });

        // Reset criterions group
        $(document).on('click', '.PM_ASBlockOutput .PM_ASResetGroup', function(e) {
            e.preventDefault();

            id_search = as4Plugin.getIdSearchFromItem(this);
            id_criterion_group = $(this).attr('rel');
            search_method = as4Plugin.getParamValue(id_search, 'searchMethod');

            $(document).trigger('as4-Criterion-Group-Reset', [id_search, id_criterion_group]);
            $('#PM_ASForm_' + id_search + ' input[name=reset_group]').val(id_criterion_group);

            // In case we are in "Last criterion selected" mode, the reset button must re-execute the step, not the search
            if (search_method == 3) {
                as4Plugin.nextStep(id_search, search_method);
            } else {
                as4Plugin.runSearch(id_search, search_method);
            }
        });

        // Skip criterions group (step search)
        $(document).on('click', '.PM_ASBlockOutput .PM_ASSkipGroup', function(e) {
            e.preventDefault();

            id_search = as4Plugin.getIdSearchFromItem(this);
            id_criterion_group = $(this).attr('rel');
            search_method = as4Plugin.getParamValue(id_search, 'searchMethod');

            $('#PM_ASForm_' + id_search + ' [name="as4c[' + id_criterion_group + '][]"]').prop('disabled', true);
            $('#PM_ASForm_' + id_search + ' [name="as4c[' + id_criterion_group + '][]"][value="-1"]').prop('disabled', false);
            $(document).trigger('as4-Criterion-Group-Skip', [id_search, id_criterion_group, search_method]);
            as4Plugin.nextStep(id_search, search_method);
        });

        // Show advanced Search
        $(document).on('click', '.PM_ASBlockOutput .PM_ASShowCriterionsGroupHidden a', function(e) {
            e.preventDefault();

            var id_search = as4Plugin.getIdSearchFromItem(this);
            var e = $(this);
            var hideState = $(e).parent('.PM_ASShowCriterionsGroupHidden').next('.PM_ASCriterionsGroupHidden:hidden').size();
            $.ajax({
                type: "GET",
                url: ASSearchUrl,
                cache: false,
                data: ('setHideCriterionStatus=1&id_search=' + id_search + '&state=' + hideState + '&productFilterListData=' + as4Plugin.getParamValue(id_search, 'as4_productFilterListData') + '&productFilterListSource=' + as4Plugin.getParamValue(id_search, 'as4_productFilterListSource')),
                success: function(responseText) {
                    if (hideState == 0) {
                        $(e).parent().removeClass('PM_ASShowCriterionsGroupHiddenOpen');
                    } else {
                        $(e).parent().addClass('PM_ASShowCriterionsGroupHiddenOpen');
                    }
                    $(e).parent('.PM_ASShowCriterionsGroupHidden').nextAll('.PM_ASCriterionsGroupHidden').slideToggle('fast');
                    as4Plugin.searchResponseCallback(id_search);
                }
            });
        });

        // From initSearch
        $(document).on('click', '#PM_ASearchResults .pagination a', function(e) {
            e.preventDefault();

            var id_search = as4Plugin.getIdSearchFromItem(this);

            // Pagination change
            var finalDestUrl = ASSearchUrl;
            var destUrl = as4Plugin.getAsAjaxUrl($(this).attr('href'));
            var asExtraParamsReg = new RegExp("&p=[0-9]+|&orderby=[a-z]+|&orderway=[a-z]+|&n=[0-9]+|\\?p=[0-9]+|\\?orderby=[a-z]+|\\?orderway=[a-z]+|\\?n=[0-9]+", "g");
            var nextExtraParams = destUrl.match(asExtraParamsReg);
            finalDestUrl = as4Plugin.cleanAjaxDuplicateParams(finalDestUrl, (as4Plugin.getFormSerialized(id_search) + '&only_products=1&ajaxMode=1'));

            ajaxData = as4Plugin.getFormSerializedArray(id_search);
            ajaxData.push({
                name: 'only_products',
                value: 1
            });
            ajaxData.push({
                name: 'ajaxMode',
                value: 1
            });
            ajaxData.push({
                name: 'productFilterListData',
                value: as4Plugin.getParamValue(id_search, 'as4_productFilterListData')
            });
            ajaxData.push({
                name: 'productFilterListSource',
                value: as4Plugin.getParamValue(id_search, 'as4_productFilterListSource')
            });

            // Add extra parameters to AJAX data and for History API
            as4Plugin.setExtraParameters(ajaxData, nextExtraParams);

            $.ajax({
                type: "GET",
                url: finalDestUrl,
                cache: false,
                data: ajaxData,
                mode: 'abort',
                dataType: 'json',
                port: 'asSearch',
                beforeSend: function() {
                    as4Plugin.setLayer(as4Plugin.getSearchResultsSelector(id_search));
                },
                success: function(responseText) {
                    var htmlResults = responseText.html_products;
                    if (typeof(responseText.url) != 'undefined' && responseText.url != '') {
                        as4Plugin.pushStateNewURL(responseText.url);
                    }
                    as4Plugin.setNextIdCriterionGroup(id_search, responseText.next_id_criterion_group);
                    as4Plugin.setResultsContents(id_search, htmlResults, 'pagination');
                }
            });
            return;
        });

        // Product sort
        $(document).on('change', '#PM_ASearchResults form#productsSortForm select, #PM_ASearchResults form.productsSortForm select', function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            var id_search = as4Plugin.getIdSearchFromItem(this);

            var finalDestUrl = ASSearchUrl;
            var asRegCheckSortMethod = new RegExp("name:|price:|quantity:|reference:|sales:", "g");
            var isNewSortMethod = $(this).val().match(asRegCheckSortMethod);
            if (!isNewSortMethod) {
                var destUrl = as4Plugin.getAsAjaxUrl($(this).val());
            } else {
                var destBaseUrl = as4Plugin.getAsAjaxUrl($('#PM_ASearchResults form#productsSortForm, #PM_ASearchResults form.productsSortForm').attr('action'));
                var splitData = $(this).val().split(':');
                var destUrl = destBaseUrl + ((destBaseUrl.indexOf('?') < 0) ? '?' : '&') + 'orderby=' + splitData[0] + '&orderway=' + splitData[1];
            }
            // Set order by for next search
            var regOrderBy = new RegExp("&orderby=[a-z]+|\\?orderby=[a-z]+", "g");
            var orderby = regOrderBy.exec(destUrl);
            if (orderby) {
                orderby = orderby.toString().substring(9);
                $('#PM_ASBlockOutput_' + id_search + ' input[name=orderby]').val(orderby).attr('disabled', '').removeAttr('disabled');
            }
            // Set order way for next search
            var regOrderWay = new RegExp("&orderway=[a-z]+|\\?orderway=[a-z]+", "g");
            var orderway = regOrderWay.exec(destUrl);
            if (orderway) {
                orderway = orderway.toString().substring(10);
                $('#PM_ASBlockOutput_' + id_search + ' input[name=orderway]').val(orderway).attr('disabled', '').removeAttr('disabled');
            }
            var asExtraParamsReg = new RegExp("&orderby=[a-z]+|&orderway=[a-z]+|&n=[0-9]+|\\?orderby=[a-z]+|\\?orderway=[a-z]+|\\?n=[0-9]+", "g");
            var nextExtraParams = destUrl.match(asExtraParamsReg);
            finalDestUrl = as4Plugin.cleanAjaxDuplicateParams(finalDestUrl, (as4Plugin.getFormSerialized(id_search) + '&only_products=1&ajaxMode=1'));

            ajaxData = as4Plugin.getFormSerializedArray(id_search);
            ajaxData.push({
                name: 'only_products',
                value: 1
            });
            ajaxData.push({
                name: 'ajaxMode',
                value: 1
            });
            ajaxData.push({
                name: 'productFilterListData',
                value: as4Plugin.getParamValue(id_search, 'as4_productFilterListData')
            });
            ajaxData.push({
                name: 'productFilterListSource',
                value: as4Plugin.getParamValue(id_search, 'as4_productFilterListSource')
            });

            // Add extra parameters to AJAX data and for History API
            as4Plugin.setExtraParameters(ajaxData, nextExtraParams);

            $.ajax({
                type: "GET",
                url: finalDestUrl,
                cache: false,
                data: ajaxData,
                mode: 'abort',
                dataType: 'json',
                port: 'asSearch',
                beforeSend: function() {
                    as4Plugin.setLayer(as4Plugin.getSearchResultsSelector(id_search));
                },
                success: function(responseText) {
                    var htmlResults = responseText.html_products;
                    if (typeof(responseText.url) != 'undefined' && responseText.url != '') {
                        as4Plugin.pushStateNewURL(responseText.url);
                    }
                    as4Plugin.setNextIdCriterionGroup(id_search, responseText.next_id_criterion_group);
                    as4Plugin.setResultsContents(id_search, htmlResults, 'order_by');
                }
            });
            return;
        });


        $(document).on('change', '#PM_ASearchResults form#productsSortForm select, #PM_ASearchResults form.productsSortForm select, #PM_ASearchResults form.nbrItemPage select', function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            if (typeof($(this).form) != 'undefined') {
                $($(this).form).trigger('submit');
            } else if (typeof($(this).parents('form:first')) != 'undefined') {
                $(this).parents('form:first').trigger('submit');
            } else if (typeof($(this).closest('form')) != 'undefined') {
                $(this).closest('form').trigger('submit');
            }
        });

        $(document).on('submit', '#PM_ASearchResults form.pagination, #PM_ASearchResults form.showall, #PM_ASearchResults form.nbrItemPage', function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            var id_search = as4Plugin.getIdSearchFromItem(this);

            var asExtraParamsReg = new RegExp("&orderby=[a-z]+|&orderway=[a-z]+|\\?orderby=[a-z]+|\\?orderway=[a-z]+", "g");
            var asSerializeDatas = as4Plugin.getFormSerialized(id_search);
            var asMatchIdSearchReg = new RegExp("id_search", "g");

            if ($(this).find('#nb_page_items').size() > 0) {
                var curN = $(this).find('#nb_page_items').val();
                var nextExtraParams = $('#nb_page_items').parents('form').serialize().match(asExtraParamsReg);
            } else {
                if ($(this).find('#nb_item').size() > 0) {
                    var curN = $(this).find('#nb_item').val();
                    var nextExtraParams = $('#nb_item').parents('form').serialize().match(asExtraParamsReg);
                } else if ($(this).find('#nb_item_bottom').size() > 0) {
                    var curN = $(this).find('#nb_item_bottom').val();
                    var nextExtraParams = $('#nb_item_bottom').parents('form').serialize().match(asExtraParamsReg);
                }
            }

            if (!asSerializeDatas.match(asMatchIdSearchReg)) {
                asSerializeDatas = $(this).serialize();
            }

            // Set N for next search
            if (curN) {
                $('#PM_ASBlockOutput_' + id_search + ' input[name=n]').val(curN).attr('disabled', '').removeAttr('disabled');
            }

            $.ajax({
                type: "GET",
                url: ASSearchUrl,
                cache: false,
                data: (asSerializeDatas + '&only_products=1&ajaxMode=1&n=' + curN + '&productFilterListData=' + as4Plugin.getParamValue(id_search, 'as4_productFilterListData') + '&productFilterListSource=' + as4Plugin.getParamValue(id_search, 'as4_productFilterListSource')),
                mode: 'abort',
                dataType: 'json',
                port: 'asSearch',
                beforeSend: function() {
                    as4Plugin.setLayer(as4Plugin.getSearchResultsSelector(id_search));
                },
                success: function(responseText) {
                    var htmlResults = responseText.html_products;
                    if (typeof(responseText.url) != 'undefined' && responseText.url != '') {
                        as4Plugin.pushStateNewURL(responseText.url);
                    }
                    as4Plugin.setNextIdCriterionGroup(id_search, responseText.next_id_criterion_group);
                    as4Plugin.setResultsContents(id_search, htmlResults, 'pagination');
                }
            });
            return;
        });
        // /From initSearch

        // From initNotMulticriteriaElements
        $(document).on('mousedown', '.PM_ASNotMulticriteria', function(e) {
            e.preventDefault();

            if ($(this).parents('li').hasClass('PM_ASCriterionDisable')) {
                return;
            }
            // For checkbox
            if ($(this).attr('type') == 'checkbox') {
                if (!$(this).attr('checked')) {
                    var curIndex = $(this).parent('li').index();
                    $(this).parent('li').parent('ul').find('li:not(:eq(' + curIndex + ')) > input[type=checkbox]').removeAttr('checked');
                }
            } else {
                if (!$(this).hasClass('PM_ASCriterionLinkSelected')) {
                    var curIndex = $(this).parent('li').index();
                    $(this).parent('li').parent('ul').find('li:eq(' + curIndex + ') > input[type=hidden]').attr('disabled', '');
                    $(this).parent('li').parent('ul').find('li:not(:eq(' + curIndex + ')) > input[type=hidden]').attr('disabled', 'disabled');
                    $(this).parent('li').parent('ul').find('li > a').removeClass('PM_ASCriterionLinkSelected');
                }
            }
        });
        // /From initNotMulticriteriaElements

        // From initFormSearchBlocLink
        $(document).on('click', '.PM_ASSelectionsRemoveLink', function(e) {
            e.preventDefault();
            var id_search = as4Plugin.getIdSearchFromItem(this);

            $(this).next('input').attr('disabled', 'disabled');
            $(this).parents('form').ajaxSubmit(as4Plugin.getASFormOptions(id_search));
        });

        $(document).on('click', '.PM_ASBlockOutput .PM_ASCriterionHideToggleClick a', function(e) {
            e.preventDefault();
            $(this).parents('.PM_ASCriterions').find('.PM_ASCriterionHide').slideToggle('fast');
            $(this).parents('.PM_ASCriterions').find('.PM_ASCriterionGroupColor.color_to_pick_list li.PM_ASCriterionHide, .PM_ASCriterionGroupImage li.PM_ASCriterionHide').css('display', 'inline-block');
            $(this).children('.PM_ASHide, .PM_ASShow').toggle();
        });
        // /From initFormSearchBlocLink

        // From initFormSearchBlockLevelDepth
        /* Level Depth */
        $(document).on('click', '.PM_ASBlockOutput .PM_ASCriterionOpenClose', function(e) {
            e.preventDefault();
            id_category = $(this).data('id-category');
            id_search = as4Plugin.getIdSearchFromItem(this);
            event_type = 'open';

            if ($(this).hasClass('PM_ASCriterionClose')) {
                event_type = 'open';
                $(this).removeClass('PM_ASCriterionClose').addClass('PM_ASCriterionOpen');
            } else if ($(this).hasClass('PM_ASCriterionOpen')) {
                event_type = 'close';
                $(this).removeClass('PM_ASCriterionOpen').addClass('PM_ASCriterionClose');
            }

            as4Plugin.closeNestedCategories(event_type, id_category, true);
        });
        $(document).on('click', '.PM_ASBlockOutput .PM_ASCriterionLevelChoose', function(e) {
            e.preventDefault();

            id_criterion = $(this).data('id-criterion');
            id_criterion_group = $(this).data('id-criterion-group');
            id_search = as4Plugin.getIdSearchFromItem(this);
            currentCategoryLevelItem = $('#PM_ASCriterionGroupSelect_' + id_search + '_' + id_criterion_group);
            currentSelectedCategory = $('option[value="' + id_criterion + '"]', currentCategoryLevelItem);

            if (currentSelectedCategory.size() > 0 && currentSelectedCategory.prop('selected') == true) {
                // Category is already selected, we must unselect it
                $('option:selected', currentCategoryLevelItem).prop('selected', false);
                currentCategoryLevelItem.trigger('change');
            } else {
                $('option:selected', currentCategoryLevelItem).prop('selected', false);
                currentSelectedCategory.prop('selected', true);
                currentCategoryLevelItem.trigger('change');
            }
        });
        /* /Level Depth */
        // /From initFormSearchBlockLevelDepth

        as4Plugin.removeOldEvents();
    },

    closeNestedCategories: function(eventType, idCategory, onlyShowNextLevel) {
        if (!isNaN(idCategory)) {
            $('#PM_ASBlock_' + id_search + ' .PM_ASCriterionLevel[data-id-parent="' + idCategory + '"]').each(function() {
                if (eventType == 'close') {
                    $('.PM_ASCriterionOpenClose', this).removeClass('PM_ASCriterionOpen').addClass('PM_ASCriterionClose');
                    $(this).slideUp();
                } else {
                    if (onlyShowNextLevel) {
                        $(this).slideDown();
                    }
                }

                nestedCategoryId = $('.PM_ASCriterionOpenClose', this).data('id-category');
                as4Plugin.closeNestedCategories(event_type, nestedCategoryId, false);
            });
        }
    },

    removeOldEvents: function() {
        $('#selectPrductSort, #selectProductSort, .selectPrductSort').unbind('change').removeAttr('onchange');
        $(document).off('change', '.selectProductSort');
        $(document).off('change', 'select[name="n"]');
        $('#PM_ASearchResults form#productsSortForm select, #PM_ASearchResults form.productsSortForm select').unbind('change').removeAttr('onchange');
    },

    initSearchFromResults: function(id_search, search_method, step_search) {
        $(document).trigger('as4-Before-Init-Search-Results', [id_search, search_method, step_search]);

        as4Plugin.removeOldEvents();

        $(document).trigger('as4-After-Init-Search-Results', [id_search, search_method, step_search]);

        as4Plugin.searchResponseCallback(id_search);
    },

    initSliders: function() {
        $('.PM_ASCritRange').each(function() {
            sliderItem = $(this);
            id_search = as4Plugin.getIdSearchFromItem(sliderItem);

            sliderItem.slider({
                range: true,
                min: $(this).data('min'),
                max: $(this).data('max'),
                step: $(this).data('step'),
                values: $(this).data('values'),
                disabled: $(this).data('disabled'),
                slide: function(event, ui) {
                    id_search = $(this).data('id-search');

                    as4Plugin.assignRangeValues($(this), id_search, ui);
                },
                stop: function(event, ui) {
                    id_search = $(this).data('id-search');
                    search_method = as4Plugin.getParamValue(id_search, 'searchMethod')
                    step_search = as4Plugin.getParamValue(id_search, 'stepSearch');

                    as4Plugin.assignRangeValues($(this), id_search, ui);

                    if (step_search == 1) {
                        as4Plugin.nextStep(id_search, $('#PM_ASInputCritRange' + id_search + '_' + $(this).data('id-criterion-group')), null, search_method);
                    } else {
                        if (search_method == 1) {
                            $('#PM_ASForm_' + id_search).ajaxSubmit(as4Plugin.getASFormOptions(id_search));
                        }
                        if (search_method == 2) {
                            $('#PM_ASForm_' + id_search).ajaxSubmit(as4Plugin.getASFormDynamicCriterionOptions(id_search));
                        }
                    }
                }
            });
        });
    },

    assignRangeValues: function(slider, id_search, ui) {
        $('#PM_ASCritRangeValue' + id_search + '_' + slider.data('id-criterion-group')).html(slider.data('left-range-sign') + Math.round(ui.values[0]*100)/100 + slider.data('right-range-sign') + ' - ' + slider.data('left-range-sign') + Math.round(ui.values[1]*100)/100 + slider.data('right-range-sign'));
        $('#PM_ASInputCritRange' + id_search + '_' + slider.data('id-criterion-group')).val('' + Math.round(ui.values[0]*100)/100 + '~' + Math.round(ui.values[1]*100)/100);
    },

    initSearchBlock: function(id_search, search_method, step_search) {
        $(document).trigger('as4-Before-Init-Search-Block', [id_search, search_method, step_search]);

        // Init sliders
        as4Plugin.initSliders();

        $('.PM_ASCriterionHideToggleLink').click(function(e) {
            e.preventDefault();
            return;
        });

        $('.PM_ASBlockOutput .PM_ASCriterionsToggleHover').hoverIntent(function(e) {
            if (typeof(e.toElement) != 'undefined' && ($(e.toElement).is('.PM_ASResetGroup') || $(e.toElement).is('.PM_ASSkipGroup'))) {
                e.preventDefault();
                return;
            }
            $(this).addClass('PM_ASCriterionGroupToggleHover');
            $(this).find('.PM_ASCriterionHide').stop().slideDown('fast');
            $(this).find('.PM_ASCriterionGroupColor.color_to_pick_list li.PM_ASCriterionHide').css('display', 'inline-block');
            $(this).find('.PM_ASCriterionGroupImage li.PM_ASCriterionHide').css('display', 'inline-block');
        }, function() {
            $(this).removeClass('PM_ASCriterionGroupToggleHover');
            $(this).find('.PM_ASCriterionHide').stop().slideUp('fast', function() {
                $(this).parents('.PM_ASCriterions').removeClass('PM_ASCriterionGroupToggleHover');
            });
            $(this).find('.PM_ASCriterionGroupColor.color_to_pick_list li.PM_ASCriterionHide').css('display', 'none');
            $(this).find('.PM_ASCriterionGroupImage li.PM_ASCriterionHide').css('display', 'none');
        });
        as4Plugin.removeOldEvents();

        // Submit search
        if (search_method == 2) {
            $('#PM_ASForm_' + id_search).ajaxForm(as4Plugin.getASFormOptions(id_search));
        }
        $(document).trigger('as4-After-Init-Search-Block', [id_search, search_method, step_search]);
        as4Plugin.searchResponseCallback(id_search);
    },

    // Set Next Id Criterion Group when step_search is on
    setNextIdCriterionGroup: function(id_search, next_id_criterion_group) {
        var input_next_id_criterion_group = $('#PM_ASBlock_' + id_search).find('input[name="next_id_criterion_group"]');
        if (next_id_criterion_group != 0) {
            $(input_next_id_criterion_group).val(next_id_criterion_group);
        } else  {
            $(input_next_id_criterion_group).val('');
        }
    },

    // Clean duplicate parameters
    cleanAjaxDuplicateParams: function(destUrl, params) {
        var hasDuplicateValues = true;
        var paramsSplit = params.split('&');
        var destUrlSplit = destUrl.split('&');
        var i = 0;
        while (hasDuplicateValues) {
            hasDuplicateValues = false;
            var paramsListDestUrl = new Array();
            $.each(destUrlSplit, function(index, value) {
                if (typeof(value) != 'undefined') {
                    if ($.inArray(value, paramsSplit) != -1 || $.inArray(value, paramsListDestUrl) != -1) {
                        destUrlSplit.splice(index, 1);
                        hasDuplicateValues = true;
                    } else {
                        paramsListDestUrl.push(value);
                    }
                }
            });
            i++;
            if (i == 10) break;
        }
        return destUrlSplit.join('&');
    },

    moveFormContainerForSEOPages: function() {
        if (typeof($('div#PM_ASFormContainerHidden')) != 'undefined' && $('div#PM_ASFormContainerHidden').size() > 0) {
            var element_parent = $('div#PM_ASFormContainerHidden').parent().parent();
            if (typeof(element_parent) != 'undefined' && $(element).size() > 0) {
                var element = $('div#PM_ASFormContainerHidden').detach();
                $(element_parent).append(element);
            }
        }
    },

    searchResponseCallback: function(id_search) {
        as4Plugin.removeOldEvents();

        $(document).trigger('as4-Before-Response-Callback');
        //Override button add to cart from results
        if ($('#PM_ASearchResults').size() > 0) {
            if (typeof initAp4CartLink == 'function') {
                initAp4CartLink();
            }
            if (typeof(ajaxCart) != 'undefined') {
                ajaxCart.overrideButtonsInThePage();
            }
            if (typeof(modalAjaxCart) != 'undefined') {
                modalAjaxCart.overrideButtonsInThePage();
            }
            // Init PS 1.6 theme default behaviour
            if (typeof(display) != 'undefined' && display instanceof Function) {
                // Set default display to grid view
                var view = 'grid';
                if ($.totalStorage instanceof Function) {
                    viewFromLocalStorage = $.totalStorage('display');
                    if (typeof(viewFromLocalStorage) != 'undefined' && viewFromLocalStorage) {
                        // Get display mode from local storage
                        view = viewFromLocalStorage;
                    }
                }
                try {
                    // Apply display mode if different than grid (default mode)
                    if (view && view != 'grid') {
                        display(view);
                    }
                } catch (e) { }

                if (typeof(blockHover) != 'undefined' && blockHover instanceof Function) {
                    blockHover();
                }
                $('#grid').click(function(e) {
                    e.preventDefault();
                    try {
                        display('grid');
                    } catch (e) { }
                });
                $('#list').click(function(e) {
                    e.preventDefault();
                    try {
                        display('list');
                    } catch (e) { }
                });
            }
            // /Init PS 1.6 theme default behaviour
        }

        // Add best sales options
        as4Plugin.addBestSalesOptions(id_search);

        $(document).ready(function() {
            // Init selectize items (select with filters)
            $(".PM_ASBlockOutput select.chosen:visible, .PM_ASBlockOutput select.as4-select:visible").each(function() {
                selectizePlugins = [];
                if ($(this).prop('multiple')) {
                    selectizePlugins = ['remove_button'];
                }
                $(this).selectize({
                    plugins: selectizePlugins,
                    hideSelected: true,
                    copyClassesToDropdown: false,
                    closeAfterSelect: true,
                    allowEmptyOption: true,
                    triggerChangeEvent: false,
                    onChange: function(value, event) {
                        if (typeof event == 'object' && value != null) {
                            if (event.type == 'keydown' && !value.length) {
                                if (event.keyCode == 8) {
                                    this.open();
                                    return;
                                }
                            }
                        }

                        // Restore native triggering process
                        this.$input.trigger('change');
                    },
                });
            });
            if (typeof($.uniform) != 'undefined') {
                $(document).on('click', '.PM_ASBlockOutput .title_block', function(e) {
                    $.uniform.update(".PM_ASBlockOutput select.form-control");
                });
                if ($('.PM_ASBlockOutput').size() > 0) {
                    // Init PS 1.6 theme default behaviour
                    $("select.form-control,input[type='checkbox']:not(.comparator), input[type='radio']").not(".not_uniform").uniform();
                    // /Init PS 1.6 theme default behaviour
                }
            }
            // Product comparison
            if (typeof(reloadProductComparison) != 'undefined') {
                reloadProductComparison();
            }
            if (typeof(compareButtonsStatusRefresh) != 'undefined' && typeof(comparedProductsIds) != 'undefined') {
                compareButtonsStatusRefresh();
            }
            if (typeof(totalCompareButtons) != 'undefined') {
                totalCompareButtons();
            }
            // /Product comparison

            // IQIT Lazy Load
            if(typeof(iqit_lazy_load) != "undefined" && iqit_lazy_load !== null && iqit_lazy_load) {
                $("ul.product_list img.lazy").lazyload({
                    threshold : 200,
                    skip_invisible : false
                });
            }
            // /IQIT Lazy Load
        });
        $(document).trigger('as4-After-Response-Callback');
    },

    pushNewState: function(idSearch, fromInit) {
        if (fromInit == true && as4Plugin.getPersistentParamValue(idSearch, 'pushInitStateDone') == false) {
            as4Plugin.setPersistentParamValue(idSearch, 'pushInitStateDone', true);
        } else if (fromInit == true && as4Plugin.getPersistentParamValue(idSearch, 'pushInitStateDone')) {
            return;
        }

        formOptionsObject = as4Plugin.getASFormOptions(idSearch);
        for (paramKey in as4Plugin.extraParams) {
            formOptionsObject.data[as4Plugin.extraParams[paramKey].name] = as4Plugin.extraParams[paramKey].value;
        }
        // Reset extra parameters
        as4Plugin.extraParams = new Array();

        history.replaceState({
            id_search: idSearch,
            formOptionsData: formOptionsObject.data,
            formSerializedArray: as4Plugin.getFormSerializedArray(idSearch),
        }, null, null);
    },

    pushStateNewURL: function(url) {
        if (document.location != url) {
            history.pushState(null, null, url);
        }
    },

    // Add layer and spinner
    setLayer: function(pmAjaxSpinnerTarget) {
        // Create the spinner here
        if (as4Plugin.blurEffect) {
            $(pmAjaxSpinnerTarget).addClass('as4-loader-blur');
        }
        $(pmAjaxSpinnerTarget).append('<div class="as4-loader"></div>');
        $(pmAjaxSpinnerTarget).find('.as4-loader').each(function() {
            $(this).css('top', -$(pmAjaxSpinnerTarget).outerHeight() / 2);
        });
    },

    // Remove layer and spinner
    removeLayer: function(pmAjaxSpinnerTarget) {
        // Remove layer and spinner
        $('.as4-loader-blur').removeClass('as4-loader-blur');
        $('.as4-loader').remove();
    },

    // Send event to Google Analytics
    sendGAEvent: function(eventCategory, eventAction, eventLabel) {
        if (typeof ga !== 'undefined') {
            ga('send', 'event', {
                eventCategory: eventCategory,
                eventAction: eventAction,
                eventLabel: eventLabel
            });
        }
    },

    // Test if LocalStorage is available
    isLocalStorageAvailable: function() {
        if (as4Plugin.localStorageAvailable == null) {
            var vTest = 'as4Test';
            try {
                localStorage.setItem(vTest, vTest);
                localStorage.removeItem(vTest);
                as4Plugin.localStorageAvailable = true;
                // Clear expired cache
                as4Plugin.clearExpiredLocalStorage();
            } catch (e) {
                as4Plugin.localStorageAvailable = false;
            }
        }
        return as4Plugin.localStorageAvailable;
    },

    // Clear expired cache
    clearExpiredLocalStorage: function() {
        for (var i = 0; i < localStorage.length; i++){
            cacheKey = localStorage.key(i);
            if (cacheKey.includes('advancedsearch4') && !cacheKey.includes('cachettl')) {
                ttl = localStorage.getItem(cacheKey + 'cachettl');
                if (ttl && ttl < +new Date()) {
                    localStorage.removeItem(cacheKey);
                    localStorage.removeItem(cacheKey + 'cachettl');
                }
            }
        }
    }

}