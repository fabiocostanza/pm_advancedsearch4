{if !$returnAsScript}
    <script type="text/javascript">
        var oTable{$idDataTable|escape:'htmlall':'UTF-8'} = undefined;
        {literal}
        $(document).ready(function() {
        {/literal}
{/if}
            oTable{$idDataTable|escape:'htmlall':'UTF-8'} = $('#{$idDataTable|escape:'htmlall':'UTF-8'}').dataTable({
                "sDom": 'R<"H"lfr>t<"F"ip<',
                "bJQueryUI": true,
                "bStateSave": true,
                "sPaginationType": "full_numbers",
                "bDestory": true,
                "oLanguage": {
                    "sLengthMenu": "{l s='Display' mod='pm_advancedsearch4'} _MENU_ {l s='records per page' mod='pm_advancedsearch4'}",
                    "sZeroRecords": "{l s='Nothing found - sorry' mod='pm_advancedsearch4'}",
                    "sInfo": "{l s='Showing' mod='pm_advancedsearch4'} _START_ {l s='to' mod='pm_advancedsearch4'} _END_ {l s='of' mod='pm_advancedsearch4'} _TOTAL_ {l s='records' mod='pm_advancedsearch4'}",
                    "sInfoEmpty": "{l s='Showing' mod='pm_advancedsearch4'} 0 {l s='to' mod='pm_advancedsearch4'} 0 {l s='of' mod='pm_advancedsearch4'} 0 {l s='records' mod='pm_advancedsearch4'}",
                    "sInfoFiltered": "({l s='filtered from' mod='pm_advancedsearch4'} _MAX_ {l s='total records' mod='pm_advancedsearch4'})",
                    "sPageNext": "{l s='Next' mod='pm_advancedsearch4'}",
                    "sPagePrevious": "{l s='Previous' mod='pm_advancedsearch4'}",
                    "sPageLast": "{l s='Last' mod='pm_advancedsearch4'}",
                    "sPageFirst": "{l s='First' mod='pm_advancedsearch4'}",
                    "sSearch": "{l s='Search' mod='pm_advancedsearch4'}",
                    oPaginate: {
                        "sFirst":"{l s='First' mod='pm_advancedsearch4'}",
                        "sPrevious": "{l s='Previous' mod='pm_advancedsearch4'}",
                        "sNext": "{l s='Next' mod='pm_advancedsearch4'}",
                        "sLast": "{l s='Last' mod='pm_advancedsearch4'}"
{literal}
                    }
                }
            });
{/literal}
{if !$returnAsScript}
        {literal}
        });
        {/literal}
    </script>
{/if}