<a href="{$options.href|as4_nofilter}" title="{$options.title|escape:'html':'UTF-8'}"  class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only {if $options.class}{$options.class|escape:'html':'UTF-8'}{/if}" id="{$currentId|escape:'htmlall':'UTF-8'}" {if $options.text}style="padding-right:5px;" {/if}{if $options.rel}rel="{$options.rel|escape:'htmlall':'UTF-8'}" {/if}{if $options.target}target="{$options.target|escape:'htmlall':'UTF-8'}" {/if}>
    {if $options.icon_class}
        <span class="{$options.icon_class|escape:'html':'UTF-8'}" style="float: left; margin-right: .3em;"></span>
    {/if}
    {$options.text|as4_nofilter}
</a>
{if $options.onclick}
    <script type="text/javascript">
        $(document).on("click", "#{$currentId|escape:'htmlall':'UTF-8'}", function(e) {
            e.preventDefault();
            {$options.onclick|as4_nofilter}
        });
    </script>
{/if}