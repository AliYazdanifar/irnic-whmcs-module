<style>
    .rtl {
        direction: rtl;
    }
</style>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12 text-center">
            <a href="{$vars['modulelink']}&action=logs"
               class="{if $op=='logs'}btn btn-primary{else}btn btn-light bordered{/if}">logs</a>

            <a href="{$vars['modulelink']}&action=whoisNic"
               class="{if $op=='whoisNic'}btn btn-primary{else}btn btn-light bordered{/if}">whoisNic</a>

            <a href="{$vars['modulelink']}&action=whoisDomain"
               class="{if $op=='whoisDomain'}btn btn-primary{else}btn btn-light bordered{/if}">whoisDomain</a>


            <a href="{$vars['modulelink']}&action=index"
               class="{if $op=='index'}btn btn-primary{else}btn btn-light bordered{/if}">poll messages</a>
        </div>
    </div>

    <div class="row text-center">
        {if isset($session['alert'])}
            <div class="col-12">
                <div class="alert alert-success" style="margin-top: 15px">
                    <b>{$session['alert']}</b>

                </div>
            </div>
        {/if}
        {if isset($session['error'] )}
            <div class="col-12">
                <div class="alert alert-danger" style="margin-top: 15px">
                    <b>{$session['error']}</b>
                </div>
            </div>
        {/if}

    </div>
    <hr>
    {block name="content"}default{/block}

</div>

{literal}
    <script>

        $("#domain").select2();

    </script>
{/literal}
