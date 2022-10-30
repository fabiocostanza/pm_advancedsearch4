<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{$current_iso_lang|escape:'htmlall':'UTF-8'}" lang="{$current_iso_lang|escape:'htmlall':'UTF-8'}">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>PrestaShop</title>
        {$inline|as4_nofilter}
        {foreach from=$assets.css item=css}
            <link href="{$css.uri}" rel="stylesheet" type="text/css" media="{$css.media}" />
        {/foreach}
        {foreach from=$assets.js item=js}
            <script type="text/javascript" src="{$js.uri}"></script>
        {/foreach}
    </head>
    <body style="background:#ffffff;" class="pm_bo_ps_{$ps_version|escape:'htmlall':'UTF-8'} pm_bo_ps_{$ps_version_full|escape:'htmlall':'UTF-8'}">
