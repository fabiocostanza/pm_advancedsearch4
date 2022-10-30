<div class="form-group">
    <label class="control-label col-lg-4 col-sm-4">{$options.label|escape:'htmlall':'UTF-8'}</label>
    <div class="col-lg-8 col-sm-8">
        <div class="form-group">
            <div class="input-group">
                <input size="30" type="color" name="{$options.key|escape:'htmlall':'UTF-8'}" id="{$options.id|escape:'htmlall':'UTF-8'}" data-hex="true" class="mColorPicker color ui-corner-all ui-input-pm" value="{$current_value|as4_nofilter}" style="width:{$options.size|escape:'htmlall':'UTF-8'}" />
            </div>
            {include file='./tips.tpl' options=$options}
            {include file='../clear.tpl'}
        </div>
    </div>
</div>
