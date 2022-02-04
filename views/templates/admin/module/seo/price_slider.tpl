$("#PM_ASSeoPriceRange").slider({
    range: true,
    min: {$price_range.min_price|intval},
    max: {$price_range.max_price|intval},
    values: [ {$price_range.min_price|intval}, {$price_range.max_price|intval} ],
    slide: function(event, ui) {
        $("#PM_ASPriceRangeValue").html(ui.values[0] + "{$currency->getSign('right')|escape:'htmlall':'UTF-8'}" + " - " + ui.values[1] + "{$currency->getSign('right')|escape:'htmlall':'UTF-8'}");
        $(".seoSearchCriterionPriceSortable").attr("id", "criterion_{$id_criterion_group|intval}_" + ui.values[0] + "~" + ui.values[1]);
        $(".seoSearchCriterionPriceSortable").attr("title", "{l s='From' mod='pm_advancedsearch4'} " + ui.values[0] + "{$currency->getSign('right')|escape:'htmlall':'UTF-8'}" + " {l s='to' mod='pm_advancedsearch4'} " + ui.values[1] + "{$currency->getSign('right')|escape:'htmlall':'UTF-8'}");
    }
});
$("#PM_ASPriceRangeValue").html("{displayPrice price=$price_range.min_price|intval currency=$currency} - {displayPrice price=$price_range.max_price|intval currency=$currency}");
$(".seoSearchCriterionPriceSortable").attr("id", "criterion_{$id_criterion_group|intval}_{$price_range.min_price|intval}~{$price_range.max_price|intval}");
$(".seoSearchCriterionPriceSortable").attr("title", "{l s='From' mod='pm_advancedsearch4'} {displayPrice price=$price_range.min_price|intval currency=$currency} {l s='to' mod='pm_advancedsearch4'} {displayPrice price=$price_range.max_price|intval currency=$currency}");
var id_currency = {$currency->id|json_encode};