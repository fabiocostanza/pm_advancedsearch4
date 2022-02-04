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

/* Events than can be implemented for custom integration or interaction (with Google Analytics Events for example) */
/*
$(document).on('as4-Before-Init-Search-Block', function(e, idSearch, searchMethod, stepSearch) {});
$(document).on('as4-After-Init-Search-Block', function(e, idSearch, searchMethod, stepSearch) {});
$(document).on('as4-Before-Init-Search-Results', function(e, idSearch, searchMethod, stepSearch) {});
$(document).on('as4-After-Init-Search-Results', function(e, idSearch, searchMethod, stepSearch) {});
$(document).on('as4-Criterion-Change', function(e, idSearch, idCriterionGroup, idCriterion, criterionName, groupType) {});
$(document).on('as4-Before-Response-Callback', function(e) {});
$(document).on('as4-After-Response-Callback', function(e) {});
$(document).on('as4-Before-Set-Results-Contents', function(e, idSearch, context) {});
$(document).on('as4-After-Set-Results-Contents', function(e, idSearch, context) {});
$(document).on('as4-Search-Reset', function(e, idSearch) {});
$(document).on('as4-Criterion-Group-Reset', function(e, idSearch, idCriterionGroup) {});
$(document).on('as4-Criterion-Group-Skip', function(e, idSearch, idCriterionGroup, searchMethod) {});
*/

/*!
 * hoverIntent v1.8.0 // 2014.06.29 // jQuery v1.9.1+
 * http://cherne.net/brian/resources/jquery.hoverIntent.html
 *
 * You may use hoverIntent under the terms of the MIT license. Basically that
 * means you are free to use hoverIntent as long as this header is left intact.
 * Copyright 2007, 2014 Brian Cherne
 */
(function($){$.fn.hoverIntent=function(handlerIn,handlerOut,selector){var cfg={interval:100,sensitivity:6,timeout:0};if(typeof handlerIn==="object"){cfg=$.extend(cfg,handlerIn)}else{if($.isFunction(handlerOut)){cfg=$.extend(cfg,{over:handlerIn,out:handlerOut,selector:selector})}else{cfg=$.extend(cfg,{over:handlerIn,out:handlerIn,selector:handlerOut})}}var cX,cY,pX,pY;var track=function(ev){cX=ev.pageX;cY=ev.pageY};var compare=function(ev,ob){ob.hoverIntent_t=clearTimeout(ob.hoverIntent_t);if(Math.sqrt((pX-cX)*(pX-cX)+(pY-cY)*(pY-cY))<cfg.sensitivity){$(ob).off("mousemove.hoverIntent",track);ob.hoverIntent_s=true;return cfg.over.apply(ob,[ev])}else{pX=cX;pY=cY;ob.hoverIntent_t=setTimeout(function(){compare(ev,ob)},cfg.interval)}};var delay=function(ev,ob){ob.hoverIntent_t=clearTimeout(ob.hoverIntent_t);ob.hoverIntent_s=false;return cfg.out.apply(ob,[ev])};var handleHover=function(e){var ev=$.extend({},e);var ob=this;if(ob.hoverIntent_t){ob.hoverIntent_t=clearTimeout(ob.hoverIntent_t)}if(e.type==="mouseenter"){pX=ev.pageX;pY=ev.pageY;$(ob).on("mousemove.hoverIntent",track);if(!ob.hoverIntent_s){ob.hoverIntent_t=setTimeout(function(){compare(ev,ob)},cfg.interval)}}else{$(ob).off("mousemove.hoverIntent",track);if(ob.hoverIntent_s){ob.hoverIntent_t=setTimeout(function(){delay(ev,ob)},cfg.timeout)}}};return this.on({"mouseenter.hoverIntent":handleHover,"mouseleave.hoverIntent":handleHover},cfg.selector)}})(jQuery);

/**
 * https://github.com/SaneMethod/jquery-ajax-localstorage-cache
 */
;
(function($, window) {
    /**
     * Generate the cache key under which to store the local data - either the cache key supplied,
     * or one generated from the url, the type and, if present, the data.
     */
    var genCacheKey = function(options) {
        var url = options.url.replace(/jQuery.*/, '');

        // Strip _={timestamp}, if cache is set to false
        if (options.cache === false) {
            url = url.replace(/([?&])_=[^&]*/, '');
        }
        if (options.data) {
            return (as4Plugin.localCacheKey || '') + (options.cacheKey || url + '?' + options.data + options.type);
        } else {
            return (as4Plugin.localCacheKey || '') + (options.cacheKey || url + options.type + (options.data || ''));
        }
    };
    /**
     * Prefilter for caching ajax calls.
     * See also $.ajaxTransport for the elements that make this compatible with jQuery Deferred.
     * New parameters available on the ajax call:
     * localCache   : true // required - either a boolean (in which case localStorage is used), or an object
     * implementing the Storage interface, in which case that object is used instead.
     * cacheTTL     : 5,           // optional - cache time in hours, default is 5.
     * cacheKey     : 'post',      // optional - key under which cached string will be stored
     * isCacheValid : function  // optional - return true for valid, false for invalid
     * @method $.ajaxPrefilter
     * @param options {Object} Options for the ajax call, modified with ajax standard settings
     */
    $.ajaxPrefilter(function(options) {
        var storage = (options.localCache === true) ? window.localStorage : options.localCache,
            hourstl = options.cacheTTL || 5,
            cacheKey = genCacheKey(options),
            cacheValid = options.isCacheValid,
            ttl,
            value;

        // Check if localStorage is available
        if (!storage || (storage && !as4Plugin.isLocalStorageAvailable())) {
            return;
        }
        ttl = storage.getItem(cacheKey + 'cachettl');

        if (cacheValid && typeof cacheValid === 'function' && !cacheValid()) {
            storage.removeItem(cacheKey);
        }

        if (ttl && ttl < +new Date()) {
            storage.removeItem(cacheKey);
            storage.removeItem(cacheKey + 'cachettl');
            ttl = 0;
        }

        value = storage.getItem(cacheKey);
        if (!value) {
            // If it not in the cache, we store the data, add success callback - normal callback will proceed
            if (options.success) {
                options.realsuccess = options.success;
            }
            options.success = function(data) {
                var strdata = data;
                if (this.dataType.toLowerCase().indexOf('json') === 0) strdata = JSON.stringify(data);

                // Save the data to storage catching exceptions (possibly QUOTA_EXCEEDED_ERR)
                try {
                    storage.setItem(cacheKey, strdata);
                } catch (e) {
                    // Remove any incomplete data that may have been saved before the exception was caught
                    storage.removeItem(cacheKey);
                    storage.removeItem(cacheKey + 'cachettl');
                }

                if (options.realsuccess) options.realsuccess(data);
            };

            // store timestamp
            if (!ttl) {
                storage.setItem(cacheKey + 'cachettl', +new Date() + 1000 * 60 * 60 * hourstl);
            }
        }
    });

    /**
     * This function performs the fetch from cache portion of the functionality needed to cache ajax
     * calls and still fulfill the jqXHR Deferred Promise interface.
     * See also $.ajaxPrefilter
     * @method $.ajaxTransport
     * @params options {Object} Options for the ajax call, modified with ajax standard settings
     */
    $.ajaxTransport("+*", function(options) {
        if (options.localCache) {
            var cacheKey = genCacheKey(options),
                storage = (options.localCache === true) ? window.localStorage : options.localCache,
                value = (storage) ? storage.getItem(cacheKey) : false;

            if (value) {
                // In the cache? Get it, parse it to json if the dataType is JSON,
                // and call the completeCallback with the fetched value.
                if (options.dataType.toLowerCase().indexOf('json') === 0) value = JSON.parse(value);
                return {
                    send: function(headers, completeCallback) {
                        var response = {};
                        response[options.dataType] = value;
                        completeCallback(200, 'success', response, '');
                    },
                    abort: function() {
                        console.log("Aborted ajax transport for json cache.");
                    }
                };
            }
        }
    });
})(jQuery, window);

window.onload = function () {
    // Fix Safari issue that is firing onpopstate on page load (instead of all other browsers)
    setTimeout(function() {
        window.onpopstate = function(event) {
            if (typeof(event.state) != 'undefined' && event.state != null && typeof(event.state.id_search) != 'undefined' && !isNaN(event.state.id_search)) {
                if (typeof(event.state.stateFromInit) != 'undefined' && event.state.stateFromInit && as4Plugin.isSafari()) {
                    if (!isNaN(as4Plugin.previousOnPopState) && as4Plugin.previousOnPopState != null && as4Plugin.previousOnPopState > 0) {
                        // Previous page was generated by AS4, refresh this one
                        as4Plugin.previousOnPopState = null;
                        // Reload current page
                        location.reload();
                    } else {
                        as4Plugin.previousOnPopState = null;
                    }
                    return;
                }
                // Set event source flag
                as4Plugin.fromBackForwardEvent = true;
                formOptionsObject = as4Plugin.getASFormOptions(event.state.id_search);
                // Replace data from the old one
                formOptionsObject.data = $.param(event.state.formSerializedArray) + '&' + $.param(event.state.formOptionsData);
                formOptionsObject.url = $('#PM_ASForm_' + event.state.id_search).attr('action');
                formOptionsObject.form = $('#PM_ASForm_' + event.state.id_search);
                // Submit new search query (data from history)
                $.ajax(formOptionsObject);
                as4Plugin.previousOnPopState = event.state.id_search;
            } else {
                if (typeof(prestashop) != 'object') {
                    if (!isNaN(as4Plugin.previousOnPopState) && as4Plugin.previousOnPopState != null && as4Plugin.previousOnPopState > 0) {
                        // Previous page was generated by AS4, refresh this one
                        as4Plugin.previousOnPopState = null;
                        // Reload current page
                        location.reload();
                    } else {
                        as4Plugin.previousOnPopState = null;
                    }
                }
            }
        };
    }, 500);
};

$(document).on('as4-After-Init-Search-Block', function(e, idSearch, searchMethod, stepSearch) {
    // Let's save form state after search engine init, only if it's not coming from a back/forward browser event
    if (typeof(prestashop) == 'object') {
        if (!as4Plugin.fromBackForwardEvent) {
            as4Plugin.pushNewState(idSearch, true);
        }
    }
});

$(document).on('as4-Before-Set-Results-Contents', function(e, idSearch, context) {
    // Let's save form state just before result is updated, only if it's not coming from a back/forward browser event
    if (!as4Plugin.fromBackForwardEvent) {
        as4Plugin.pushNewState(idSearch);
    }

    // Let's prepare GA event to announce a that search results is available
    eventLabel = [];
    availableCriterionsGroups = as4Plugin.getParamValue(idSearch, 'availableCriterionsGroups');
    $.each(as4Plugin.getParamValue(idSearch, 'selectedCriterions'), function(idCriterionGroup, selectedCriterions) {
        $.each(selectedCriterions, function(index2, selectedCriterion) {
            eventLabel.push(availableCriterionsGroups[idCriterionGroup] + ': ' + selectedCriterion.value);
        });
    });
    // Send GA Event
    as4Plugin.sendGAEvent('Advanced Search', 'Show Results', eventLabel.join(', '))
});

// Do something when a new criterion is selected
$(document).on('as4-Criterion-Change', function(e, idSearch, idCriterionGroup, idCriterion, criterionName, groupType) {
    step_search = as4Plugin.getParamValue(idSearch, 'stepSearch');
    search_method = as4Plugin.getParamValue(idSearch, 'searchMethod');

    // Send GA Event
    availableCriterionsGroups = as4Plugin.getParamValue(idSearch, 'availableCriterionsGroups');
    as4Plugin.sendGAEvent('Advanced Search', 'Criterion Selected', availableCriterionsGroups[idCriterionGroup] + ': ' + criterionName)

    if (step_search == 1) {
        as4Plugin.nextStep(idSearch, search_method);
    } else {
        as4Plugin.runSearch(idSearch, search_method);
    }
});

// PrestaShop 1.7 specific
if (typeof(prestashop) == 'object') {
    prestashop.on('responsive update', function(event) {
        as4Plugin.initMobileStyles();
    });
    prestashop.on('updateProductList', function(event) {
        if (typeof(as4Plugin) == 'object' && typeof(event.id_search) != 'undefined' && !isNaN(event.id_search)) {
            scrollTopActive = as4Plugin.getParamValue(event.id_search, 'scrollTopActive');
            if (!scrollTopActive) {
                if (typeof(event.without_products) == 'undefined') {
                    // Native ajax call
                    as4Plugin.scrollTop(event.id_search, 'updateProductList', (scrollTopActive == false));
                }
            } else {
                if (typeof(event.without_products) == 'undefined') {
                    as4Plugin.scrollTop(event.id_search, 'updateProductList', (scrollTopActive == false));
                } else {
                    if (!event.without_products) {
                        as4Plugin.scrollTop(event.id_search, 'updateProductList', (event.without_products == false));
                    }
                }
            }
        }
        // Retrieve 'order' parameter from URL in case the order is changed by the user
        // and assign it to our 'orderby' hidden input
        if (typeof(as4Plugin) == 'object') {
            setTimeout(function() {
                var currentUrlParams = window.location.href.substring(window.location.href.indexOf('?', 0));
                var orderRegexp = new RegExp(/[?&]order=\w+\.\w+\.\w+/);

                // If we find a order parameter in the URL, we retrieve its value, and assign it to our input
                if (currentUrlParams.match(orderRegexp)) {
                    var matchedOrder = currentUrlParams.match(orderRegexp)[0];
                    var cleanedMatchedOrder = matchedOrder.substr(matchedOrder.indexOf('=', 0) + 1);
                    if (cleanedMatchedOrder.length > 0) {
                        $('.PM_ASForm input[name="orderby"]').prop('disabled', false);
                        $('.PM_ASForm input[name="orderby"]').val(cleanedMatchedOrder);
                    }
                }
            }, 100);
        }
    });
}
