<label>{$options.label|escape:'htmlall':'UTF-8'}</label>
<div class="margin-form">
    <div id="slider-{$options.key|escape:'htmlall':'UTF-8'}" style="width:{$options.size|escape:'htmlall':'UTF-8'};float:left;"></div>
    <span id="slide_value_{$options.key|escape:'htmlall':'UTF-8'}" style="float:left;padding-left:10px">{$current_value|escape:'htmlall':'UTF-8'} {$options.suffix|escape:'htmlall':'UTF-8'}</span>
    <input size="20" type="hidden" name="{$options.key|escape:'htmlall':'UTF-8'}" id="{$options.key|escape:'htmlall':'UTF-8'}" class="sliderPicker" value="{$current_value|intval}" size="20" />

    {include file='./tips.tpl' options=$options}
    {include file='../clear.tpl'}
    </div>
    <script type="text/javascript">
    $(document).ready(function() {
        $("#slider-{$options.key|escape:'htmlall':'UTF-8'}").slider({
            range: "min",
            value: {$current_value|intval},
            min: {$options.minvalue|intval},
            max: {$options.maxvalue|intval},
            slide: function(event, ui) {
                $("input[name={$options.key|escape:'htmlall':'UTF-8'}]").val(ui.value);
                $("#slide_value_{$options.key|escape:'htmlall':'UTF-8'}").html(ui.value + " {$options.suffix|escape:'htmlall':'UTF-8'}");
            }
        });
    });
    </script>
</div>