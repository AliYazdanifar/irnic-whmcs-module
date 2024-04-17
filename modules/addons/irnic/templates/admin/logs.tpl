{extends file="{$layoutTemplate}"}
{block name="content"}
    <style>
        .rtl {
            direction: rtl;
        }
    </style>
    <div class="card">
        <div class="card-body">

            <a href="{$vars['modulelink']}&action=logs&clearFilter=1">حذف فیلترها</a>
            <div class="row ltr">

                <form action="{$vars['modulelink']}&action=logs" method="post">
                    <div class="col-md-4">
                        <input value="{$session['nic_log_search_query']}" placeholder="search log" type="search" class="form-control" name="q">
                    </div>
                    <div class="col-md-4">
                        <button name="search" class="btn btn-success">search</button>
                    </div>
                </form>

                <form action="{$vars['modulelink']}&action=logs" id="sort-form" method="post">
                    <div class="col-md-4">
                        <label>sort by
                            <select name="sort" onchange="sendSortForm()">
                                <option {if (isset($session['nic_log_sort']) && $session['nic_log_sort'] == "ASC")}selected{/if} value="ASC">ASC</option>
                                <option {if (isset($session['nic_log_sort']) && $session['nic_log_sort'] == "DESC")}selected{/if} value="DESC">DESC</option>
                            </select>
                        </label>
                    </div>
                    <script>
                        function sendSortForm() {
                            $('#sort-form').submit();
                        }
                    </script>
                </form>
            </div>
            <hr>

            <table class="table table-striped table-bordered">
                <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">log</th>
                    <th scope="col">created_at</th>

                </tr>
                </thead>
                <tbody>
                {foreach from=$logs['logs'] item=log}
                    <tr>
                        <th scope="row">{$log['id']}</th>
                        <td><textarea>{$log['log']}</textarea></td>
                        <td>{$log['created_at']}</td>
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
                    {$logs['qty']}
                    {for $i=1 to $logs['qty']}
                        <li class="page-item">
                            {if $i > 10}
                                <span> ... </span>
                                {break}
                            {else}
                                <a class="page-link {if $i eq $page}bg-primary text-light{/if}"
                                   href="addonmodules.php?module=irnic&action=logs&page={$i}">{$i}</a>
                            {/if}
                        </li>
                    {/for}

                    <li class="page-item">
                        <a class="page-link" href="addonmodules.php?module=irnic&action=logs&page={$page+1}"
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



