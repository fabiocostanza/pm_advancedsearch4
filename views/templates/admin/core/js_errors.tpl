{if isset($js_errors) && $js_errors|is_array && $js_errors|sizeof}
	{if $include_script_tag}
		<script type="text/javascript">
	{/if}
    {foreach from=$js_errors item=js_error}
	    parent.parent.show_error({$js_error|json_encode});
    {/foreach}
    parent.parent.removeIframeAnimations();
	{if $include_script_tag}
		</script>
	{/if}
{/if}