{extends file="{$layoutTemplate}"}
{block name="content"}
    <div class="row" style="margin-top: 17px;">
        <div class="col-md-12 text-center">
            <form action="{$vars['modulelink']}&action=whoisNic" method="post">
                <h1><b>IRNIC Contact Details</b></h1>
                <div class="col-md-12">
                    <label for="nichandle"></label>
                    <input required min="3" id="nichandle" placeholder="nicHandle" class="form-control"
                           style="width: 35%!important;padding: 16px !important;display: inline-block" name="nichandle"
                           type="text">
                    <button class="btn btn-success">check</button>
                </div>
            </form>
        </div>
    </div>
    {if ($req)}
        <div class="row" style="margin-top: 17px;">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">

                        {if ($code != 1000 )}
                            <h2 class="alert alert-danger text-center">
                              
                                {if ($code == 2102)}
                                    اطلاعات ارسالی اشتباه است
                                {elseif ($code == 2303)}
                                    این شناسه در ایرنیک وجود ندارد
                                {else}
                                    {$msg} code = {$code}
                                {/if}

                            </h2>
                        {else}
                            <div class="alert alert-success">{$msg} code = {$code}</div>
                            <table class="table table-bordered text-center">
                                <thead>
                                <tr>
                                    <th class="text-center">nichandle</th>
                                    <th class="text-center">{$info['position'][0]['_a']['type']}</th>
                                    <th class="text-center">{$info['position'][1]['_a']['type']}</th>
                                    <th class="text-center">{$info['position'][2]['_a']['type']}</th>
                                    <th class="text-center">{$info['position'][3]['_a']['type']}</th>
                                    <th class="text-center">email</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td>{$info['roid']}</td>
                                    <td class="alert {if ($info['position'][0]['_a']['allowed'] == 1)} alert-success {else} alert-danger {/if}">{if ($info['position'][0]['_a']['allowed'] == 1)} مجاز {else} غیر مجاز {/if}</td>
                                    <td class="alert {if ($info['position'][1]['_a']['allowed'] == 1)} alert-success {else} alert-danger {/if}">{if ($info['position'][1]['_a']['allowed'] == 1)} مجاز {else} غیر مجاز {/if}</td>
                                    <td class="alert {if ($info['position'][2]['_a']['allowed'] == 1)} alert-success {else} alert-danger {/if}">{if ($info['position'][2]['_a']['allowed'] == 1)} مجاز {else} غیر مجاز {/if}</td>
                                    <td class="alert {if ($info['position'][3]['_a']['allowed'] == 1)} alert-success {else} alert-danger {/if}">{if ($info['position'][3]['_a']['allowed'] == 1)} مجاز {else} غیر مجاز {/if}</td>
                                    <td>{$info['email']}</td>
                                </tr>
                                </tbody>
                            </table>
                        {/if}

                    </div>
                </div>
            </div>
        </div>
    {/if}
{/block}



