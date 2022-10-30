</form>
{if $has_jquerytools}
	<script type="text/javascript">
	    $("#{$form_id|escape:'htmlall':'UTF-8'}").validator({
	        lang: "{$current_iso_lang|escape:'htmlall':'UTF-8'}",
	        messageClass: "formValidationError",
	        errorClass: "elementErrorAssignedClass",
	        position: "center bottom"
	    })
	    {if !empty($jquerytools_validator_function)}
	    	.submit({$jquerytools_validator_function|as4_nofilter});
		{/if}
	</script>
{/if}
