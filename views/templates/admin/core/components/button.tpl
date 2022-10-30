<a href="{$options.href|as4_nofilter}" title="{$options.title|escape:'html':'UTF-8'}" class="pmAsButton btn {if !preg_match('/btn-.*/i', $options.class)}btn-default{/if} {if $options.class}{$options.class|escape:'html':'UTF-8'}{/if}" id="{$currentId|escape:'htmlall':'UTF-8'}" {if $options.rel}rel="{$options.rel|escape:'htmlall':'UTF-8'}" {/if}{if $options.target}target="{$options.target|escape:'htmlall':'UTF-8'}" {/if}>
    {if $options.icon_class}
        <i class="material-icons">{$options.icon_class|escape:'html':'UTF-8'}</i>
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
