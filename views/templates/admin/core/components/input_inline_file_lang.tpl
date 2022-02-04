{if !empty($options.label)}
    <label>{$options.label|escape:'htmlall':'UTF-8'}</label>
{/if}
<div class="margin-form" id="blc_lang{$options.key|escape:'htmlall':'UTF-8'}">

    {foreach from=$languages item=language}
        <div id="lang{$options.key|escape:'htmlall':'UTF-8'}_{$language.id_lang|intval}" class="pmFlag pmFlagLang_{$language.id_lang|intval}" style="display: {if $language.id_lang == $default_language}block{else}none{/if}; float: left;">
            <div style="float:left;">
                <input type="hidden" name="{$options.key|escape:'htmlall':'UTF-8'}_{$language.id_lang|intval}_temp_file_lang_destination_lang" id="{$options.key|escape:'htmlall':'UTF-8'}_{$language.id_lang|intval}_destination_lang" value="{$options.destination|escape:'htmlall':'UTF-8'}" />
                <input type="hidden" name="{$options.key|escape:'htmlall':'UTF-8'}_{$language.id_lang|intval}_temp_file_lang" id="{$options.key|escape:'htmlall':'UTF-8'}_{$language.id_lang|intval}" value="" />
                <div id="{$options.key|escape:'htmlall':'UTF-8'}_{$language.id_lang|intval}_container">
                    <button id="{$options.key|escape:'htmlall':'UTF-8'}_{$language.id_lang|intval}_pickfiles" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only">{l s='Choose a file' mod='pm_advancedsearch4'}</button>
                </div>
                <div id="{$options.key|escape:'htmlall':'UTF-8'}_{$language.id_lang|intval}_filelist"></div>
            </div>
        </div>
    {/foreach}

    {$pm_flags|as4_nofilter}

    {include file='./tips.tpl' options=$options}

    {if !empty($options.plupload)}
        {foreach from=$languages item=language}
            {assign var=image_lang value=$options.obj->{$options.key_db|escape:'htmlall':'UTF-8'}}
            {assign var=file_location value="`$file_location_dir``$image_lang[$language.id_lang]`"}
            <div id="wrapper_preview-{$options.key|escape:'htmlall':'UTF-8'}_{$language.id_lang|intval}" class="wrapper_preview-inline wrapper_preview-{$options.key|escape:'htmlall':'UTF-8'}" style="{if empty($options.obj) || empty($image_lang[$language.id_lang]) || $language.id_lang != $default_language}display:none;{/if}">
                <div id="preview-{$options.key|escape:'htmlall':'UTF-8'}_{$language.id_lang|intval}" class="pm_preview_upload pm_preview_upload_2 pm_preview_upload-{$options.key|escape:'htmlall':'UTF-8'}" style="{if !empty($options.obj) && !empty($image_lang[$language.id_lang])}{else}display:none;{/if}">
                    <span>
                        <input type="checkbox" name="{$options.key_db|escape:'htmlall':'UTF-8'}_{$language.id_lang|intval}_unlink_lang" value="1" onclick="deleteCriterionImg({$options.obj->id|intval}, {$options.obj->id_search|intval}, {$language.id_lang|intval}); $('#preview-{$options.key|escape:'htmlall':'UTF-8'}_{$language.id_lang|intval}').slideUp('fast');" />
                        <input type="hidden" name="{$options.key|escape:'htmlall':'UTF-8'}_{$language.id_lang|intval}_old_file_lang" id="{$options.key|escape:'htmlall':'UTF-8'}_{$language.id_lang|intval}_old_file" value="{if $options.obj && !empty($image_lang[$language.id_lang])}{$image_lang[$language.id_lang]|escape:'htmlall':'UTF-8'}{/if}" />
                        {l s='Delete this file' mod='pm_advancedsearch4'}
                        &nbsp;-&nbsp;
                    </span>
                    {* Check if have file and is exists *}
                    <span>
                    {if $options.obj && !empty($image_lang[$language.id_lang]) && Tools::file_exists_cache($file_location)}
                        {if !empty($is_image)}
                            <img src="{Tools::substr($module_path, 0, - 1)|as4_nofilter}{$options.destination|as4_nofilter}{$image_lang[$language.id_lang]|as4_nofilter}" id="{$options.key|escape:'htmlall':'UTF-8'}_{$language.id_lang|intval}_file" />
                        {else}
                            <a href="{Tools::substr($module_path, 0, - 1)|as4_nofilter}{$options.destination|as4_nofilter}{$image_lang[$language.id_lang]|as4_nofilter}" target="_blank" class="pm_view_file_upload_link" id="{$options.key|escape:'htmlall':'UTF-8'}_{$language.id_lang|intval}_file">{l s='View file' mod='pm_advancedsearch4'}</a>
                        {/if}
                    {/if}
                    </span>
                </div>
            </div>
            <script type="text/javascript">
            var pm_viewFileLabel = {{l s='View file' mod='pm_advancedsearch4'}|json_encode};
            $(document).ready(function() {
                initUploader("{$options.key|escape:'htmlall':'UTF-8'}_{$language.id_lang|intval}", "{$base_config_url|as4_nofilter}&uploadTempFile=1", "{$options.filetype|escape:'htmlall':'UTF-8'}", {$is_image|intval}, function(file_name) {
                    $.ajax( {
                        type : "GET",
                        url : _base_config_url+"&pm_load_function=processSaveCriterionImg&file_name="+file_name+"&id_criterion={$options.obj->id|intval}&id_search={$options.obj->id_search|intval}&id_lang={$language.id_lang|intval}",
                        error: function(XMLHttpRequest, textStatus, errorThrown) {
                            alert(errorThrown);
                        },
                        complete: function(XMLHttpRequest, textStatus, errorThrown) {
                            if (XMLHttpRequest.responseText == 'ok') {
                                $(".wrapper_preview-{$options.key|escape:'htmlall':'UTF-8'}").hide();
                                $("#wrapper_preview-{$options.key|escape:'htmlall':'UTF-8'}_{$language.id_lang|intval}").show();
                            }
                        },
                    });
                });
            });
            </script>
        {/foreach}
        <script type="text/javascript">
            $("#blc_lang{$options.key|escape:'htmlall':'UTF-8'} .pmSelectFlag").bind("change",function() {
                var current_id_language = $(this).val();
                $(".wrapper_preview-{$options.key|escape:'htmlall':'UTF-8'}").hide();
                $("#wrapper_preview-{$options.key|escape:'htmlall':'UTF-8'}_"+current_id_language).show();
            });
        </script>
    {/if}

    {include file='../clear.tpl'}
</div>