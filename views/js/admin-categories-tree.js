if (typeof(treeViewSetting) == 'undefined') {
    var treeViewSetting = new Array();
}

function formatCategoryIdTreeView(category_id) {
    if (typeof(category_id) != 'undefined' && category_id.indexOf("-") != -1)
        category_id = category_id.substring(0, category_id.indexOf("-"));
    return parseInt(category_id);
}

function loadTreeView(inputName, categoryRootId, selectedCategories, selectedLabel, home, use_radio) {
    treeViewSetting[inputName] = new Array();
    treeViewSetting[inputName]['categoryRootId'] = categoryRootId;
    treeViewSetting[inputName]['selectedCategories'] = selectedCategories;
    treeViewSetting[inputName]['selectedLabel'] = selectedLabel;
    treeViewSetting[inputName]['readyToExpand'] = true;
    treeViewSetting[inputName]['categoryBoxName'] = 'categoryBox[]';
    treeViewSetting[inputName]['needCheckAll'] = false;
    treeViewSetting[inputName]['needUncheckAll'] = false;
    treeViewSetting[inputName]['arrayCatToExpand'] = new Array();
    treeViewSetting[inputName]['id'] = 0;
    treeViewSetting[inputName]['interval'] = null;
    treeViewSetting[inputName]['home'] = home;
    treeViewSetting[inputName]['use_radio'] = use_radio;
    treeViewSetting[inputName]['selector'] = '#categories-treeview-'+inputName;
    treeViewSetting[inputName]['selector'] = treeViewSetting[inputName]['selector'].replace('[', '').replace(']', '');

    treeViewSetting[inputName]['inputNameSelector'] = inputName.replace('[', '').replace(']', '');

    $(document).ready(function() {
        $(treeViewSetting[inputName]['selector']).treeview({
            inputNameValue: inputName,
            inputNameSelector: inputName.replace('[', '').replace(']', ''),
            categoryRootId: categoryRootId,
            checkAllChildrenLabel: (typeof(checkAllChildrenLabel) != 'undefined' ? checkAllChildrenLabel : 'Check all children'),
            selectedLabel: selectedLabel,
            use_radio: use_radio,
            url : baseConfigUrl+'&getPanel=getChildrenCategories',
            toggle: function () { callbackToggle($(this), inputName); },
            ajax : {
                type: 'POST',
                async: true,
                data: { selectedCat: selectedCategories }
            }
        });

        $(treeViewSetting[inputName]['selector'] + ' li#'+categoryRootId+'-'+treeViewSetting[inputName]['inputNameSelector']+' span').trigger('click');
        $(treeViewSetting[inputName]['selector'] + ' li#'+categoryRootId+'-'+treeViewSetting[inputName]['inputNameSelector']).children('div').remove();
        $(treeViewSetting[inputName]['selector'] + ' li#'+categoryRootId+'-'+treeViewSetting[inputName]['inputNameSelector']).
            removeClass('collapsable lastCollapsable').
            addClass('last static');

        $('#expand_all-'+treeViewSetting[inputName]['inputNameSelector']).click( function () {
            if ($(this).attr('rel') != '') treeViewSetting[inputName]['categoryBoxName'] = $(this).attr('rel');
            expandAllCategories(inputName);
            return false;
        });

        $('#collapse_all-'+treeViewSetting[inputName]['inputNameSelector']).click( function () {
            if ($(this).attr('rel') != '') treeViewSetting[inputName]['categoryBoxName'] = $(this).attr('rel');
            collapseAllCategories(inputName);
            return false;
        });

        $('#check_all-'+treeViewSetting[inputName]['inputNameSelector']).click( function () {
            if ($(this).attr('rel') != '') treeViewSetting[inputName]['categoryBoxName'] = $(this).attr('rel');
            treeViewSetting[inputName]['needCheckAll'] = true;
            checkAllCategories(inputName);
            return false;
        });

        $('#uncheck_all-'+treeViewSetting[inputName]['inputNameSelector']).click( function () {
            if ($(this).attr('rel') != '') treeViewSetting[inputName]['categoryBoxName'] = $(this).attr('rel');
            treeViewSetting[inputName]['needUncheckAll'] = true;
            uncheckAllCategories(inputName);
            return false;
        });
    });
}

function callbackToggle(element, inputName) {
    if (!element.is('.expandable'))
        return false;

    if (element.children('ul').children('li.collapsable').length != 0)
        closeChildrenCategories(element, inputName);
}

function closeChildrenCategories(element, inputName) {
    var arrayLevel = new Array();

    if (element.children('ul').find('li.collapsable').length == 0) {
        return false;
    }

    element.children('ul').find('li.collapsable').each(function() {
        var level = $(this).children('span.category_level').html();
        if (arrayLevel[level] == undefined)
            arrayLevel[level] = new Array();

        arrayLevel[level].push(formatCategoryIdTreeView($(this).attr('id')));
    });

    for(i=arrayLevel.length-1;i!=0;i--)
        if (arrayLevel[i] != undefined)
            for(j=0;j<arrayLevel[i].length;j++)
            {
                $('li#'+arrayLevel[i][j]+'-'+treeViewSetting[inputName]['inputNameSelector']+'.collapsable').children('span.category_label').trigger('click');
                $('li#'+arrayLevel[i][j]+'-'+treeViewSetting[inputName]['inputNameSelector']+'.expandable').children('ul').hide();
            }
}

function setCategoryToExpand(inputName) {
    var ret = false;

    treeViewSetting[inputName]['id'] = 0;
    treeViewSetting[inputName]['arrayCatToExpand'] = new Array();
    $(treeViewSetting[inputName]['selector'] + ' li.expandable:visible').each(function() {
        treeViewSetting[inputName]['arrayCatToExpand'].push($(this).attr('id'));
        ret = true;
    });

    return ret;
}

function needExpandAllCategories(inputName) {
    return $(treeViewSetting[inputName]['selector'] + ' li').is('.expandable');
}

function expandAllCategories(inputName) {
    // if no category to expand, no action
    if (!needExpandAllCategories(inputName)) return;

    // force to open main category
    if ($('li#'+treeViewSetting[inputName]['categoryRootId']+'-'+treeViewSetting[inputName]['inputNameSelector']).is('.expandable'))
        $('li#'+treeViewSetting[inputName]['categoryRootId']+'-'+treeViewSetting[inputName]['inputNameSelector']).children('span.folder').trigger('click');
    treeViewSetting[inputName]['readyToExpand'] = true;
    if (setCategoryToExpand(inputName)) {
        treeViewSetting[inputName]['interval'] = setInterval('openCategory("'+inputName+'")', 10);
    }
}

function openCategory(inputName) {
    // Check readyToExpand in order to don't clearInterval if AJAX request is in progress
    // readyToExpand = category has been expanded, go to next ;)
    if (treeViewSetting[inputName]['id'] >= treeViewSetting[inputName]['arrayCatToExpand'].length && treeViewSetting[inputName]['readyToExpand']) {
        if (!setCategoryToExpand(inputName)) {
            clearInterval(treeViewSetting[inputName]['interval']);
            // delete interval value
            treeViewSetting[inputName]['interval'] = null;
            treeViewSetting[inputName]['readyToExpand'] = false;
            if (treeViewSetting[inputName]['needCheckAll']) {
                checkAllCategories(inputName);
                treeViewSetting[inputName]['needCheckAll'] = false;
            }
            else if (treeViewSetting[inputName]['needUncheckAll']) {
                uncheckAllCategories(inputName);
                treeViewSetting[inputName]['needUncheckAll'] = false;
            }
        }
        else
            treeViewSetting[inputName]['readyToExpand'] = true;
    }

    if (treeViewSetting[inputName]['readyToExpand']) {
        if ($('li#'+treeViewSetting[inputName]['arrayCatToExpand'][treeViewSetting[inputName]['id']]+'.hasChildren').length > 0)
            treeViewSetting[inputName]['readyToExpand'] = false;
        $('li#'+treeViewSetting[inputName]['arrayCatToExpand'][treeViewSetting[inputName]['id']]+'.expandable:visible span.category_label').trigger('click');
        treeViewSetting[inputName]['id']++;
    }
}

function collapseAllCategories(inputName) {
    closeChildrenCategories($('li#'+treeViewSetting[inputName]['categoryRootId']+'-'+treeViewSetting[inputName]['inputNameSelector']), inputName);
}

function checkAllCategories(inputName) {
    $('input.check_all_children', $(treeViewSetting[inputName]['selector'])).prop('checked', true);

    if (needExpandAllCategories(inputName)) {
        expandAllCategories(inputName);
    } else {
        $('input[name="'+treeViewSetting[inputName]['categoryBoxName']+'"]').not(':checked').each(function () {
            $(this).prop('checked', true);
            clickOnCategoryBox($(this), inputName);
        });
    }
}

function uncheckAllCategories(inputName) {
    $('input.check_all_children', $(treeViewSetting[inputName]['selector'])).prop('checked', false);

    if (needExpandAllCategories(inputName))
        expandAllCategories(inputName);
    else
    {
        $('input[name="'+treeViewSetting[inputName]['categoryBoxName']+'"]:checked').each(function () {
            $(this).prop('checked', false);
            clickOnCategoryBox($(this), inputName);
        });
    }
}

function clickOnCategoryBox(category, inputName) {
    if (category.is(':checked')) {
        $('select#id_category_default').append('<option value="'+category.val()+'">'+(category.val() != treeViewSetting[inputName]['categoryRootId'] ? category.parent().find('span').html() : treeViewSetting[inputName]['home'])+'</option>');
        updateNbSubCategorySelected(category, inputName);
        if ($('select#id_category_default option').length > 0)
        {
            $('select#id_category_default').show();
            $('#no_default_category').hide();
        }
    } else {
        $('select#id_category_default option[value='+category.val()+']').remove();
        updateNbSubCategorySelected(category, inputName);
        if ($('select#id_category_default option').length == 0)
        {
            $('select#id_category_default').hide();
            $('#no_default_category').show();
        }
    }
}

function updateNbSubCategorySelected(category, inputName) {
    var currentSpan = category.parent().parent().parent().children('.nb_sub_cat_selected');
    var currentUl = category.parent().parent().parent().children('ul');
    var nbCurrentSelectedItems = currentUl.children('li').children('input:not([value="undefined"]):not(.check_all_children):checked').size();

    if (treeViewSetting[inputName]['use_radio']) {
        $('.nb_sub_cat_selected').hide();
        return false;
    }

    currentSpan.children('.nb_sub_cat_selected_value').html(nbCurrentSelectedItems);
    currentSpan.children('.nb_sub_cat_selected_word').html(treeViewSetting[inputName]['selectedLabel']);

    if (nbCurrentSelectedItems == 0) {
        currentSpan.hide();
    } else {
        currentSpan.show();
    }

    if (currentSpan.parent().children('.nb_sub_cat_selected').length != 0) {
        updateNbSubCategorySelected(currentSpan.parent().children('input'), inputName);
    }
}
function checkChildrenCategory(e, id_category, inputName) {
    if($(e).is(':checked')) {
        $('li#'+id_category+'-'+treeViewSetting[inputName]['inputNameSelector']+'.expandable:visible span.category_label').trigger('click');
        treeViewSetting[inputName]['interval'] = setInterval(function() {
            if($(e).parent('li').children('ul').children('li').children('input:not([value="undefined"]):not(.check_all_children)').length) {
                categories = $(e).parent('li').children('ul').children('li').children('input:not(.check_all_children)');
                categories.prop('checked', true);
                categories.each(function(index, category) {
                    updateNbSubCategorySelected($(category), inputName);
                });
                clearInterval(treeViewSetting[inputName]['interval']);
            }
        }, 200);
    } else {
        categories = $(e).parent('li').children('ul').children('li').children('input:not(.check_all_children)');
        categories.prop('checked', false);
        categories.each(function (index, category) {
            updateNbSubCategorySelected($(category), inputName);
        });
    }
}
