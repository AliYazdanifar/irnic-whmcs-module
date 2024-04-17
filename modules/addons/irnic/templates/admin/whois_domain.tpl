{extends file="{$layoutTemplate}"}
{block name="content"}
    <div class="row" style="margin-top: 17px;">
        <div class="col-md-12 text-center">
            <form action="{$vars['modulelink']}&action=whoisDomain" method="post">
                <h1><b>IRNIC Domain Details</b></h1>
                <div class="col-md-12">
                    <label for="domain"></label>
                    <input required min="3" id="domain" placeholder="domain" class="form-control"
                           style="width: 35%!important;padding: 16px !important;display: inline-block" name="domain"
                           type="text">
                    <button class="btn btn-primary">check</button>

                </div>
            </form>
        </div>
    </div>
    {if ($req)}
        <div class="row" style="margin-top: 17px;">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body ">


                        {if ($code != 1000 )}
                            <h2 class="alert alert-danger text-center">
                                {if ($code == 2102)}
                                    اطلاعات ارسالی اشتباه است
                                {elseif ($code == 2303)}
                                    این دامنه در ایرنیک وجود ندارد
                                {else}
                                    {$msg} code = {$code}
                                {/if}
                            </h2>
                        {else}
                            <div class="alert alert-success">{$msg} code = {$code}</div>
                            <div class="row">
                                <div class="col-md-12">
                                    <table class="table table-bordered">
                                        <thead>
                                        <tr>
                                            <th>name</th>
                                            <th>expDate</th>
                                            <th>status</th>
                                            <th>contact</th>
                                            <th>nameServers</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <td>{$info['name']}</td>
                                            <td>{$info['expDate']}</td>
                                            <td>
                                                <ul>
                                                    {foreach from=$info['status'] item=foo}
                                                        <li>{$foo['_a']['s']}</li>
                                                    {/foreach}
                                                </ul>

                                            </td>
                                            <td>
                                                <ul>
                                                    {foreach from=$info['contact'] item=foo}
                                                        <li>{$foo['_a']['type']} : {$foo['_v']}</li>
                                                    {/foreach}
                                                </ul>

                                            </td>
                                            <td>
                                                <ul>
                                                    {foreach from=$info['nameServers']  key=k item=foo}
                                                        <li> {$k} : {$foo}</li>
                                                    {/foreach}
                                                </ul>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        {/if}

                    </div>
                </div>
            </div>
        </div>
    {/if}
{/block}



