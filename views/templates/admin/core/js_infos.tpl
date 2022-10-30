{if isset($js_infos) && $js_infos|is_array && $js_infos|sizeof}
	{if $include_script_tag}
		<script type="text/javascript">
	{/if}
    {foreach from=$js_infos item=js_info}
	    parent.parent.show_info({$js_info|json_encode});
    {/foreach}
    parent.parent.removeIframeAnimations();
	{if $include_script_tag}
		</script>
	{/if}
{/if}