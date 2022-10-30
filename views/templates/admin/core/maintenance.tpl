<div class="form-group">
    <label class="control-label col-lg-4 col-sm-4">{l s='Maintenance mode' mod='pm_advancedsearch4'}</label>
    <div class="col-lg-8 col-sm-8">
        <span class="switch prestashop-switch fixed-width-lg">
            <input type="radio" name="maintenanceMode" id="maintenanceMode_on" value="1" {if !empty($config['maintenanceMode'])}checked{/if}>
            <label for="maintenanceMode_on" class="radioCheck">{l s='Yes' d='Admin.Global'}</label>
            <input type="radio" name="maintenanceMode" id="maintenanceMode_off" value="0" {if empty($config['maintenanceMode'])}checked{/if}>
            <label for="maintenanceMode_off" class="radioCheck">{l s='No' d='Admin.Global'}</label>
            <a class="slide-button btn"></a>
        </span>
        <p class="help-block">{l s='You can allow IPs in your' mod='pm_advancedsearch4'} <a href='{$pmAdminMaintenanceLink|escape:'htmlall':'UTF-8'}' style='text-decoration:underline;'>{l s='Preferences Panel.' mod='pm_advancedsearch4'}</a></p>
    </div>
</div>
