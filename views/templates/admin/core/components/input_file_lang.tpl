<div class="form-group">
    <label class="control-label col-lg-4 col-sm-4">{$options.label|escape:'htmlall':'UTF-8'}</label>
    <div class="col-lg-8 col-sm-8">
        {foreach from=$languages item=language}
            <div id="lang{$options.key|escape:'htmlall':'UTF-8'}_{$language.id_lang|intval}" class="pmFlag pmFlagLang_{$language.id_lang|intval}" style="display: {if $language.id_lang == $default_language}block{else}none{/if}; float: left;">
                <input type="hidden" name="{$options.key|escape:'htmlall':'UTF-8'}_{$language.id_lang|intval}_temp_file_lang_destination_lang" id="{$options.key|escape:'htmlall':'UTF-8'}_{$language.id_lang|intval}_destination_lang" value="{$options.destination|escape:'htmlall':'UTF-8'}" />
                <input type="hidden" name="{$options.key|escape:'htmlall':'UTF-8'}_{$language.id_lang|intval}_temp_file_lang" id="{$options.key|escape:'htmlall':'UTF-8'}_{$language.id_lang|intval}" value="" />
                <div id="{$options.key|escape:'htmlall':'UTF-8'}_{$language.id_lang|intval}_container">
                    <button id="{$options.key|escape:'htmlall':'UTF-8'}_{$language.id_lang|intval}_pickfiles" class="btn btn-default pmAsImagePickerButton">{l s='Choose a file' mod='pm_advancedsearch4'}</button>
                </div>
                <div id="{$options.key|escape:'htmlall':'UTF-8'}_{$language.id_lang|intval}_filelist"></div>
            </div>
        {/foreach}

        {$pm_flags|as4_nofilter}

        {include file='./tips.tpl' options=$options nofloat=true}

        {if !empty($options.plupload)}
            {include file='../clear.tpl'}
            {foreach from=$languages item=language}
                {assign var=image_lang value=$options.obj->{$options.key|escape:'htmlall':'UTF-8'}}
                {assign var=file_location value="`$file_location_dir``$image_lang[$language.id_lang]`"}
                <div id="wrapper_preview-{$options.key|escape:'htmlall':'UTF-8'}_{$language.id_lang|intval}" class="wrapper_preview-{$options.key|escape:'htmlall':'UTF-8'}">
                    <div id="preview-{$options.key|escape:'htmlall':'UTF-8'}_{$language.id_lang|intval}" class="alert alert-info pm_preview_upload pm_preview_upload-{$options.key|escape:'htmlall':'UTF-8'}" style="
                    {if empty($options.obj) || empty($image_lang[$language.id_lang])}display:none;{/if}">
                        {* Check if have file and is exists *}
                        {if $options.obj && !empty($image_lang[$language.id_lang]) && Tools::file_exists_cache($file_location)}
                            {if !empty($is_image)}
                                <img src="{Tools::substr($module_path, 0, - 1)|as4_nofilter}{$options.destination|as4_nofilter}{$image_lang[$language.id_lang]|as4_nofilter}" id="{$options.key|escape:'htmlall':'UTF-8'}_{$language.id_lang|intval}_file" />
                            {else}
                                <a href="{Tools::substr($module_path, 0, - 1)|as4_nofilter}{$options.destination|as4_nofilter}{$image_lang[$language.id_lang]|as4_nofilter}" target="_blank" class="pm_view_file_upload_link" id="{$options.key|escape:'htmlall':'UTF-8'}_{$language.id_lang|intval}_file">{l s='View file' mod='pm_advancedsearch4'}</a>
                            {/if}
                        {/if}
                        <br />
                        <span>{l s='Delete this file' mod='pm_advancedsearch4'}</span>
                        <input type="checkbox" name="{$options.key|escape:'htmlall':'UTF-8'}_{$language.id_lang|intval}_unlink_lang" value="1" onclick="$('#preview-{$options.key|escape:'htmlall':'UTF-8'}_{$language.id_lang|intval}').slideUp('fast')" />
                        <input type="hidden" name="{$options.key|escape:'htmlall':'UTF-8'}_{$language.id_lang|intval}_old_file_lang" id="{$options.key|escape:'htmlall':'UTF-8'}_{$language.id_lang|intval}_old_file" value="{if $options.obj && !empty($image_lang[$language.id_lang])}{$image_lang[$language.id_lang]|escape:'htmlall':'UTF-8'}{/if}" />
                        {if !empty($options.extend)}
                            <div class="PM_CORE_CP">
                                <small>{l s='Apply to all languages without picture' mod='pm_advancedsearch4'}</small>&nbsp;
                                <input type="checkbox" value=1 name="{$options.key|escape:'htmlall':'UTF-8'}_all_lang">
                            </div>
                        {/if}
                    </div>
                </div>
                <script type="text/javascript">
                var pm_viewFileLabel = {{l s='View file' mod='pm_advancedsearch4'}|json_encode};
                $(document).ready(function() {
                    initUploader("{$options.key|escape:'htmlall':'UTF-8'}_{$language.id_lang|intval}", "{$base_config_url|as4_nofilter}&uploadTempFile=1", "{$options.filetype|escape:'htmlall':'UTF-8'}", {$is_image|intval});
                });
                </script>
            {/foreach}
            <script type="text/javascript">
                $("#{$flag_key}").bind("change",function() {
                    var currentIdLang = $("#{$flag_key|escape:'htmlall':'UTF-8'} option:selected").val();
                    $(".wrapper_preview-{$options.key|escape:'htmlall':'UTF-8'}").hide();
                    $("#wrapper_preview-{$options.key|escape:'htmlall':'UTF-8'}_" + currentIdLang).show();
                    if (currentIdLang == {$default_language}) {
                        $(".PM_CORE_CP").show("medium");
                    } else {
                        $(".PM_CORE_CP").hide("medium");
                    }
                });
                $("#{$flag_key|escape:'htmlall':'UTF-8'}").trigger("change");
            </script>
        {/if}

        {include file='../clear.tpl'}
    </div>
</div>
