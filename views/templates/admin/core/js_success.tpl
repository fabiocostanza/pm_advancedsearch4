{if isset($js_successes) && $js_successes|is_array && $js_successes|sizeof}
	{if $include_script_tag}
		<script type="text/javascript">
	{/if}
    {foreach from=$js_successes item=js_success}
	    parent.parent.show_success({$js_success|json_encode});
    {/foreach}
    parent.parent.removeIframeAnimations();
	{if $include_script_tag}
		</script>
	{/if}
{/if}