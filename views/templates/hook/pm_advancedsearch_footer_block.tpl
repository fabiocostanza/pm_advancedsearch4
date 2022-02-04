{if $hookName eq 'leftcolumn' || $hookName eq 'rightcolumn'}
		</div>
	</div>
{if empty($ajaxMode)}
</div>
{/if}
{else}
		</div>
	</div>
{if empty($ajaxMode)}
</div>
{/if}
{if $hookName eq 'home' && empty($ajaxMode)}
	<div class="clear"></div>
	<div id="as_home_content_results">
	</div>
{/if}
{/if}