<?php

use WHMCS\Database\Capsule;

if (!defined("WHMCS"))
    die("This file cannot be accessed directly");


add_hook('ShoppingCartValidateDomainsConfig', 1, function ($vars) {

    $api = new \WHMCS\Module\Registrar\irnic\ApiClient();

    $nicHandles = 0;
    foreach ($vars['domainfield'] as $key => $fields) {
        $nicHandles[$key] = array_filter($fields);

        foreach ($fields as $customField => $contact) {
            if ($customField == "nichandle") {
                $nicHandles++;
                $connection = $api->checkApiConnection();
                if (!$connection['ok']) {
                    $api->insertTodo('خطا هنگام بررسی شناسه ی کاربر ', 'nic msg = ' . $connection['msg'] . ' response code = ' . $connection['code'] . 'user id = ' . $_SESSION['uid']);
                    return ['error' => 'در حال حاضر سرور nic.ir در دسترس نیست لطفا بعدا تلاش کنید.'];

                }
                $contactChk = $api->contactInfoRequest($contact);
                $code = $api->parseResCode($contactChk);

                if ($code != 1000) {
                    if ($code == 2102) {
                        return ['error' => 'اطلاعات ارسالی اشتباه است.'];
                    }
                    if ($code == 2303) {
                        return [
                            "error" => 'ایمیل یا شناسه ' . $contact . ' در ایرنیک ثبت نشده است.<br> لطفا جهت ایجاد از <a href="#">این</a> آموزش استفاده کنید.',
                        ];
                    }

                    $msg = $api->parseResMessage($contactChk);
                    $api->addLog('in hook error code = ' . $code . ' msg = ' . $msg);

                    return ['error' => 'error = ' . $msg . ' code = ' . $code];
                }
            }
        }


    }
    if ($nicHandles > 4) {
    sleep(1);
        return ['error' => 'تعداد دامنه های ir برای یک درخواست نباید بیشتر از 4 عدد باشد.'];
    }

    $chk = chk($vars['domainfield']);
    if (!$chk['state'])
        return ['error' => $chk['msg']];

    return [];
});

function chk($domainFields)
{
    $api = new \WHMCS\Module\Registrar\irnic\ApiClient();
    foreach ($domainFields as $fields) {
        foreach ($fields as $key => $contact) {
            if ($key == 'nichandle') {
                $permission = $api->checkContactPositions($contact);
                if (!$permission['status'])
                    return ['state' => false, 'msg' => $permission['persian_message']];
            }
        }
    }

    return ['state' => true, 'msg' => 'OK'];

}
