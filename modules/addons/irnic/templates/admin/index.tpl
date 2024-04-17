{extends file="{$layoutTemplate}"}
{block name="content"}
    <style>
        .rtl {
            direction: rtl;
        }
    </style>
    <div class="card rtl">
        <div class="card-body table-responsive">
            <table class="table table-striped table-responsive table-bordered">
                <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">msg ID</th>
                    <th scope="col">Result Code</th>
                    <th scope="col">Q count</th>
                    <th scope="col">msg Index</th>
                    <th scope="col">msg Note</th>
                    <th scope="col">XML Resp</th>
                    <th scope="col">Resp Date</th>
                    <th scope="col">created At</th>
                    <th scope="col">op</th>
                </tr>
                </thead>
                <tbody>
                {foreach from=$logs['logs'] item=log}
                    <tr>
                        <th scope="row">{$log['id']}</th>
                        <td>{$log['msg_id']}</td>
                        <td>{$log['res_code']}</td>
                        <td>{$log['qcount']}</td>
                        <td>{$log['msg_index']}</td>
                        <td>{$log['msg_note']}</td>
                        <td><textarea style="direction: ltr;text-align: left">{$log['response_xml']}</textarea></td>
                        <td>{$log['res_date']}</td>
                        <td>{$log['created_at']}</td>
                        <td><a class="btn btn-danger" href="{$vars['modulelink']}&action=index&delete={$log['id']}">delete</a></td>
                    </tr>
                {/foreach}
                </tbody>
            </table>

            <nav aria-label="Page navigation example ltr"
                 style="display: flex;justify-content: center;text-align: left;direction: ltr!important;">
                <ul class="pagination">
                    <li class="page-item">
                        <a class="page-link" href="addonmodules.php?module=irnic&action=index&page={$page-1}"
                           aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                            <span class="sr-only">Previous</span>
                        </a>
                    </li>
                    {for $i=1 to $logs['qty']}
                        <li class="page-item">
                            {if $i > 10}
                                <span> ... </span>
                                {break}
                            {else}
                                <a class="page-link {if $i eq $page}bg-primary text-light{/if}"
                                   href="addonmodules.php?module=irnic&action=index&page={$i}">{$i}</a>
                            {/if}
                        </li>
                    {/for}

                    <li class="page-item">
                        <a class="page-link" href="addonmodules.php?module=irnic&action=index&page={$page+1}"
                           aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                            <span class="sr-only">Next</span>
                        </a>
                    </li>
                </ul>
            </nav>

        </div>
    </div>
{/block}