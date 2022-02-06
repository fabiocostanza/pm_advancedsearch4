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
        return (as4Plugin.getParamValue(idSearch, 'search_results_selector') != '' ? as4Plugin.getParamValue(idSearch, 'search_results_selector') : '#content-wrapper');
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

    // Set params var
    setParamValue: function(idSearch, varName, varValue) {
        as4Plugin.params[idSearch][varName] = varValue;
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

    scrollTop: function(idSearch, context, forceScroll) {
        if (as4Plugin.getParamValue(idSearch, 'scrollTopActive') == true || forceScroll === true) {
            pm_scrollTopSelector = as4Plugin.getSearchResultsSelector(idSearch);

            if (typeof($(pm_scrollTopSelector)) != 'undefined' && $(pm_scrollTopSelector).length > 0) {
                $('html, body').animate({
                    scrollTop: $(pm_scrollTopSelector).offset().top
                }, 500);
            }
        }
    },

    setResultsContents: function(id_search, htmlResults, context, withProduct) {
        $(document).trigger('as4-Before-Set-Results-Contents', [id_search, context]);
        var keepCategoryInformation = as4Plugin.getParamValue(id_search, 'keep_category_information');
        var searchResultsSelector = as4Plugin.getSearchResultsSelector(id_search);

        // Init sliders
        as4Plugin.initSliders();

        if (!keepCategoryInformation) {
            $('#main .block-category').remove();
        }

        var destinationElement = $('body ' + searchResultsSelector);
        if ($(destinationElement).length > 0) {
            // Animation complete.
            $(searchResultsSelector).css('height', 'auto');
        }
        as4Plugin.removeLayer();
        $(document).trigger('as4-After-Set-Results-Contents', [id_search, context]);
    },

    showAsResponse: function(responseText, statusText, xhr, $form) {
        if (typeof(responseText.redirect_to_url) != 'undefined' && responseText.redirect_to_url != '') {
            window.location = responseText.redirect_to_url;
            return;
        }

        // Allow to "not" replace rendered products in case we only want facets to be updated
        if (responseText.rendered_products_top == null) {
            responseText.rendered_products_top = function() { return $(this) };
        }
        var withProduct = true;
        if (responseText.rendered_products == null) {
            withProduct = false;
            responseText.without_products = true;
            responseText.rendered_products = function() { return $(this) };
        } else {
            responseText.without_products = false;
        }
        if (responseText.rendered_products_bottom == null) {
            responseText.rendered_products_bottom = function() { return $(this) };
        }
        if (typeof($form) == 'undefined') {
            $form = $('#PM_ASForm_' + history.state.id_search);
        }
        var id_search = $form.find('input[name=id_search]').val();
        var step_search = as4Plugin.getParamValue(id_search, 'stepSearch');
        var hookName = as4Plugin.getParamValue(id_search, 'hookName');
        var searchResultsSelector = as4Plugin.getSearchResultsSelector(id_search);

        if ($('#js-product-list').length == 0 && withProduct) {
            if (hookName == 'home') {
                // Remove any extra content from homepage
                $('#PM_ASBlockOutput_' + responseText.id_search).parent().find('*:not([id="PM_ASBlockOutput_' + responseText.id_search + '"])').remove();
                $('#PM_ASBlockOutput_' + responseText.id_search).after('<section id="products"><div id="js-product-list-top"></div><div id="js-product-list"></div><div id="js-product-list-bottom"></div></section>');
            } else {
                $(searchResultsSelector).find('*:not([id="PM_ASBlockOutput_' + responseText.id_search + '"])').remove();
                $(searchResultsSelector).prepend('<section id="products"><div id="js-product-list-top"></div><div id="js-product-list"></div><div id="js-product-list-bottom"></div></section>');
            }
        }

        if ((typeof(responseText.remind_selection) != 'undefined' && (responseText.remind_selection == 3 || responseText.remind_selection == 1))) {
            // Check if #js-active-search-filters exists
            if (withProduct && $('#js-active-search-filters').length == 0 && $('#js-product-list-top').length > 0) {
                // Add this missing div first
                $('#js-product-list-top').parent().prepend('<div id="js-active-search-filters"></div>');
            }
        } else {
            $('#js-active-search-filters').remove();
        }

        // Prevent scroll to the top from the default theme's updateProductList handler when we apply a new criterion
        var currentTop = $(window).scrollTop();
        prestashop.once('updateProductList', function () {
            $(document).scrollTop(currentTop);
        });

        prestashop.emit('updateProductList', responseText);

        // Update search block (facets)
        $('#PM_ASBlockOutput_' + responseText.id_search).replaceWith(responseText.rendered_facets);

        // Mobile styles
        as4Plugin.initMobileStyles(responseText.id_search);

        // Hide selection reminder if empty
        $('.PM_ASSelectionsDropDown').each(function() {
            if ($('li.PM_ASSelectionsSelectedCriterion', $(this)).length == 0) {
                $(this).hide();
            }
        });

        if (typeof(responseText.current_url) != 'undefined' && responseText.current_url != '') {
            as4Plugin.pushStateNewURL(responseText.current_url);
        }


        if (typeof(responseText.html_block) != 'undefined' && responseText.html_block != '' && responseText.html_block != null) {
            var htmlBlock = responseText.html_block;
            step_search = false;
        } else if (step_search == 1) {
            var next_id_criterion_group = $form.find('input[name="next_id_criterion_group"]').val();
            var htmlBlock = responseText.html_criteria_block;
            as4Plugin.setNextIdCriterionGroup(id_search, responseText.next_id_criterion_group);
        }
        // var htmlResults = responseText.html_products;
        if (htmlBlock) {
            if (hookName == 'top' || hookName == 'displayNavFullWidth') {
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
        // as4Plugin.setResultsContents(id_search, htmlResults, 'showAsResponse');
        as4Plugin.setResultsContents(id_search, '', 'showAsResponse', withProduct);
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
                    if ($('option[value^="sales:"]', this).length == 0) {
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
        if ($(item).parents('.PM_ASBlockOutput').length > 0) {
            return $(item).parents('.PM_ASBlockOutput').data('id-search');
        } else if ($(item).parents('#PM_ASearchResults').length > 0) {
            return $(item).parents('#PM_ASearchResults').data('id-search');
        } else if ($(item).parents('[data-id-search]').length > 0) {
            return $(item).parents('[data-id-search]').data('id-search');
        }
        return false;
    },

    initMobileStyles: function(idSearch) {
        if (typeof(prestashop) == 'object' && prestashop.responsive.mobile == true) {
            $('.PM_ASBlockOutput').addClass('PM_ASMobileMode');
            if (!isNaN(idSearch)) {
                $('#PM_ASBlockOutput_' + idSearch).addClass('PM_ASMobileVisible');
            }
        } else if (typeof(prestashop) == 'object' && prestashop.responsive.mobile == false) {
            $('.PM_ASBlockOutput').removeClass('PM_ASMobileMode');
        }
    },

    initSearchEngine: function() {
        // Init is already done...
        if (as4Plugin.initDone) {
            return;
        }
        as4Plugin.initDone = true;

        $(document).on('click', '.PM_ASBlockOutput .card-header', function(e) {
            e.preventDefault();
            if (typeof(prestashop) == 'object' && prestashop.responsive.mobile == true) {
                $(this).parents('.PM_ASBlockOutput').toggleClass('PM_ASMobileVisible');
            }
        });

        $(document).on('click', '.PM_ASBlockOutput .PM_ASResetSearch', function(e) {
            e.preventDefault();
            id_search = as4Plugin.getIdSearchFromItem(this);
            $(document).trigger('as4-Search-Reset', [id_search]);
            location.href = as4Plugin.getParamValue(id_search, 'resetURL');
        });

        $(document).on('click', '.PM_ASSelectionsBlock .PM_ASSelectionsDropDownShowLink', function(e) {
            e.preventDefault();
            $(this).toggleClass('PM_ASSelectionsBlockOpened');
            $(this).next('.PM_ASSelectionsDropDownMenu').slideToggle('fast');
        });
        // Hide selection reminder if empty (on load)
        $('.PM_ASSelectionsDropDown').each(function() {
            if ($('li.PM_ASSelectionsSelectedCriterion', $(this)).length == 0) {
                $(this).hide();
            }
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

        $('body').on('change', '#search_filters select, .PM_ASBlockOutput .PM_ASCriterionGroupSelect', function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            id_search = as4Plugin.getIdSearchFromItem(this);
            id_criterion_group = $(this).data('id-criterion-group');

            $(document).trigger('as4-Criterion-Change', [id_search, id_criterion_group, $(this).val(), $.trim($(this).find('option:selected').text()), 'select']);
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
            search_method = as4Plugin.getParamValue(id_search, 'searchMethod');
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
            var hideState = $(e).parent('.PM_ASShowCriterionsGroupHidden').next('.PM_ASCriterionsGroupHidden:hidden').length;
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

            if (currentSelectedCategory.length > 0 && currentSelectedCategory.prop('selected') == true) {
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

        $(document).on('click', '.PM_ASSubmitSearch', function(e) {
            e.preventDefault();
            var id_search = as4Plugin.getIdSearchFromItem(this);
            $(this).parents('form').ajaxSubmit(as4Plugin.getASFormOptions(id_search));
        });

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

    handleFilterButtonEvent: function(id_search) {
        // Open search engine filters when native "Filter" button is pressed
        setTimeout(function () {
            $('body').off('click', '#search_filter_toggler');
            if (typeof (id_search) == 'undefined') {
                $('body').on('click', '#search_filter_toggler', function () {
                    if ($('.PM_ASBlockOutput:not(.PM_ASMobileVisible) .card-header').size() > 0) {
                        searchBlock = $('.PM_ASBlockOutput:not(.PM_ASMobileVisible) .card-header');
                        searchBlock.get(0).click();
                        $('html, body').animate({
                            scrollTop: searchBlock.offset().top
                        }, 500);
                    }
                });
            } else {
                $('body').on('click', '#search_filter_toggler', function () {
                    $('#PM_ASBlockOutput_' + id_search + ':not(.PM_ASMobileVisible) .card-header').click();
                    $('html, body').animate({
                        scrollTop: $('#PM_ASBlockOutput_' + id_search + ' .card-header').offset().top
                    }, 500);
                });
            }
        }, 200);
    },

    removeOldEvents: function(id_search) {
        as4Plugin.handleFilterButtonEvent(id_search);
        $('body').off('change', '#search_filters select');
        $(document).off('change', '#search_filters select');
    },

    initSearchFromResults: function(id_search, search_method, step_search) {
        $(document).trigger('as4-Before-Init-Search-Results', [id_search, search_method, step_search]);

        as4Plugin.removeOldEvents(id_search);

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
                    search_method = as4Plugin.getParamValue(id_search, 'searchMethod');
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
        slideMinValue = Math.round(ui.values[0]*100)/100;
        slideMaxValue = Math.round(ui.values[1]*100)/100;

        if (typeof(slider.data('currency-format')) != 'undefined' && slider.data('currency-format') != null && slider.data('currency-format').length > 0) {
            // Price slider
            $('#PM_ASCritRangeValue' + id_search + '_' + slider.data('id-criterion-group')).html(as4Plugin.formatCurrency(slider.data('currency-format'), slideMinValue) + ' - ' + as4Plugin.formatCurrency(slider.data('currency-format'), slideMaxValue));
        } else {
            $('#PM_ASCritRangeValue' + id_search + '_' + slider.data('id-criterion-group')).html(slider.data('left-range-sign') + slideMinValue + slider.data('right-range-sign') + ' - ' + slider.data('left-range-sign') + slideMaxValue + slider.data('right-range-sign'));
        }
        $('#PM_ASInputCritRange' + id_search + '_' + slider.data('id-criterion-group')).val('' + slideMinValue + '~' + slideMaxValue);
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
        as4Plugin.removeOldEvents(id_search);

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
        if (typeof($('div#PM_ASFormContainerHidden')) != 'undefined' && $('div#PM_ASFormContainerHidden').length > 0) {
            var element_parent = $('div#PM_ASFormContainerHidden').parent().parent();
            if (typeof(element_parent) != 'undefined' && $(element).length > 0) {
                var element = $('div#PM_ASFormContainerHidden').detach();
                $(element_parent).append(element);
            }
        }
    },

    searchResponseCallback: function(id_search) {
        as4Plugin.removeOldEvents(id_search);

        $(document).trigger('as4-Before-Response-Callback');
        //Override button add to cart from results
        if ($('#PM_ASearchResults').length > 0) {
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
            // Init chosen items (select with filters)
            // On mobile, we don't specify the "visible" because we hide the filters on page load
            $(".PM_ASBlockOutput select.chosen:visible, .PM_ASBlockOutput select.as4-select:visible, .PM_ASBlockOutput.PM_ASMobileMode select.as4-select").each(function() {
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
            stateFromInit: fromInit,
        }, null, null);
    },

    pushStateNewURL: function(url) {
        if (document.location != url) {
            history.pushState(null, null, url);
        }
    },

    // Check if current browser is Safari
    isSafari: function() {
        safariRegexp = new RegExp('^(?!.*(?:Chrome|Edge)).*Safari');
        return (safariRegexp.test(navigator.userAgent) == true);
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
    },

    // Format currency regarding format
    formatCurrency: function(mask, value) {
        // From: http://mottie.github.io/javascript-number-formatter/src/format.js
        if ( !mask || isNaN( +value ) ) {
            return value; // return as it is.
        }

        var isNegative, result, decimal, group, posLeadZero, posTrailZero, posSeparator,
            part, szSep, integer,

            // find prefix/suffix
            len = mask.length,
            start = mask.search( /[0-9\-\+#]/ ),
            prefix = start > 0 ? mask.substring( 0, start ) : '',
            // reverse string: not an ideal method if there are surrogate pairs
            str = mask.split( '' ).reverse().join( '' ),
            end = str.search( /[0-9\-\+#]/ ),
            offset = len - end,
            substr = mask.substring( offset, offset + 1 ),
            indx = offset + ( ( substr === '.' || ( substr === ',' )) ? 1 : 0 ),
            suffix = end > 0 ? mask.substring( indx, len ) : '';

        // mask with prefix & suffix removed
        mask = mask.substring( start, indx );

        // convert any string to number according to formation sign.
        value = mask.charAt( 0 ) === '-' ? -value : +value;
        isNegative = value < 0 ? value = -value : 0; // process only abs(), and turn on flag.

        // search for separator for grp & decimal, anything not digit, not +/- sign, not #.
        result = mask.match( /[^\d\-\+#]/g );
        decimal = ( result && result[ result.length - 1 ] ) || '.'; // treat the right most symbol as decimal
        group = ( result && result[ 1 ] && result[ 0 ] ) || ',';  // treat the left most symbol as group separator

        // split the decimal for the format string if any.
        mask = mask.split( decimal );
        // Fix the decimal first, toFixed will auto fill trailing zero.
        value = value.toFixed( mask[ 1 ] && mask[ 1 ].length );
        value = +( value ) + ''; // convert number to string to trim off *all* trailing decimal zero(es)

        // fill back any trailing zero according to format
        posTrailZero = mask[ 1 ] && mask[ 1 ].lastIndexOf( '0' ); // look for last zero in format
        part = value.split( '.' );
        // integer will get !part[1]
        if ( !part[ 1 ] || ( part[ 1 ] && part[ 1 ].length <= posTrailZero ) ) {
            value = ( +value ).toFixed( posTrailZero + 1 );
        }
        szSep = mask[ 0 ].split( group ); // look for separator
        mask[ 0 ] = szSep.join( '' ); // join back without separator for counting the pos of any leading 0.

        posLeadZero = mask[ 0 ] && mask[ 0 ].indexOf( '0' );
        if ( posLeadZero > -1 ) {
            while ( part[ 0 ].length < ( mask[ 0 ].length - posLeadZero ) ) {
                part[ 0 ] = '0' + part[ 0 ];
            }
        } else if ( +part[ 0 ] === 0 ) {
            part[ 0 ] = '';
        }

        value = value.split( '.' );
        value[ 0 ] = part[ 0 ];

        // process the first group separator from decimal (.) only, the rest ignore.
        // get the length of the last slice of split result.
        posSeparator = ( szSep[ 1 ] && szSep[ szSep.length - 1 ].length );
        if ( posSeparator ) {
            integer = value[ 0 ];
            str = '';
            offset = integer.length % posSeparator;
            len = integer.length;
            for ( indx = 0; indx < len; indx++ ) {
                str += integer.charAt( indx ); // ie6 only support charAt for sz.
                // -posSeparator so that won't trail separator on full length
                /*jshint -W018 */
                if ( !( ( indx - offset + 1 ) % posSeparator ) && indx < len - posSeparator ) {
                    str += group;
                }
            }
            value[ 0 ] = str;
        }
        value[ 1 ] = ( mask[ 1 ] && value[ 1 ] ) ? decimal + value[ 1 ] : '';

        // remove negative sign if result is zero
        result = value.join( '' );
        if ( result === '0' || result === '' ) {
            // remove negative sign if result is zero
            isNegative = false;
        }

        // put back any negation, combine integer and fraction, and add back prefix & suffix
        return prefix + ( ( isNegative ? '-' : '' ) + result ) + suffix;
    }

}
