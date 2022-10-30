/*
 * Async Treeview 0.1 - Lazy-loading extension for Treeview
 *
 * http://bassistance.de/jquery-plugins/jquery-plugin-treeview/
 *
 * Copyright (c) 2007 Jörn Zaefferer
 *
 * Dual licensed under the MIT and GPL licenses:
 *   http://www.opensource.org/licenses/mit-license.php
 *   http://www.gnu.org/licenses/gpl.html
 *
 * Revision: $Id: jquery.treeview.async.js 8040 2011-08-11 15:21:09Z aFolletete $
 *
 */

;(function($) {

function load(settings, root, child, container) {
    root = formatCategoryIdTreeView(root);

    function createNode(parent) {
        var id_category = this.id_category;
        var checked = false;
        $('input[name="'+settings.inputNameValue+'"][type=hidden]').each( function () {
            if ($(this).attr('value') == id_category)
            {
                checked = true;
                $(this).remove();
            }
        });
        var categoryHtmlItem = " <input type=\"" + (!settings.use_radio ? 'checkbox' : 'radio') + "\" value=\"" + this.id_category + "\"/ name=\"" + settings.inputNameValue + "\" " + (checked ? 'checked' : '') + " onclick=\"clickOnCategoryBox($(this), '" + settings.inputNameValue + "');\"/>";
        categoryHtmlItem += " <span class=\"category_label\">" + this.name + "</span>";
        categoryHtmlItem += (this.has_children > 0 && !settings.use_radio ? " <input type=\"checkbox\" class=\"check_all_children\" onclick=\"checkChildrenCategory(this," + this.id_category + ", '" + settings.inputNameValue + "')\"  /> <small>" + settings.checkAllChildrenLabel + "</small> " : '');
        categoryHtmlItem += " <span class=\"category_level\" style=\"display: none;\">" + this.level_depth + "</span>";
        categoryHtmlItem += " <span class=\"nb_sub_cat_selected\" style=\"font-weight: bold;" + (this.nbSelectedSubCat == 0 ? 'display: none;' : '') + "\">(<span class=\"nb_sub_cat_selected_value\">" + this.nbSelectedSubCat + "</span> " + settings.selectedLabel + ")</span>";
        var current = $("<li/>")
            .addClass(settings.inputNameSelector)
            .attr("id", (this.id_category + '-' + settings.inputNameSelector) || "")
            .html(categoryHtmlItem)
            .appendTo(parent);
        if (this.classes) {
            current.children("span").addClass(this.classes);
        }
        if (this.has_children > 0) {
            var branch = $("<ul/>").hide().appendTo(current);
            current.addClass("hasChildren");
            createNode.call({
                classes: "placeholder",
                name: "&nbsp;",
                children:[],
                nbSelectedSubCat: 0
            }, branch);
            branch.children().children('.nb_sub_cat_selected').remove();
        }
    }
    $.ajax($.extend(true, {
        url: settings.url,
        dataType: "json",
        data: {
            id_category_parent: root
        },
        success: function(response) {
            child.empty();
            $.each(response, function(index, value) {
                createNode.call(value, child);
            });
            $(container).treeview({
                add: child
            });
            treeViewSetting[settings.inputNameValue]['readyToExpand'] = true;
        }
    }, settings.ajax));
}

var proxied = $.fn.treeview;
$.fn.treeview = function(settings) {
    if (!settings.url) {
        return proxied.apply(this, arguments);
    }
    var container = this;
    if (!container.children().size())
        load(settings, "source", this, container);
    var userToggle = settings.toggle;
    return proxied.call(this, $.extend({}, settings, {
        collapsed: true,
        toggle: function() {
            var $this = $(this);
            if ($this.hasClass("hasChildren")) {
                var childList = $this.removeClass("hasChildren").find("ul");
                load(settings, this.id, childList, container);
            }
            if (userToggle) {
                userToggle.apply(this, arguments);
            }
        }
    }));
};

})($);
