<?php

namespace WHMCS\Module\Addon\irnic\Client;

/**
 * Sample Client Area Controller
 */
class Controller
{
    /**
     * Index action.
     *
     * @param array $vars Module configuration parameters
     *
     * @return array
     */
    public function index($vars)
    {
        // Get common module parameters
        $modulelink = $vars['modulelink']; // eg. index.php?module=irnic
        $version = $vars['version']; // eg. 1.0
        $LANG = $vars['_lang']; // an array of the currently loaded language variables

        $data = array();

        return $this->view('ایرنیک', 'client/index.tpl', $data);

    }

    public function showError($vars)
    {
        $errors = [
            1 => 'تاریخ دامنه باید 1 یا 5 سال باشد',
            -1 => 'ایرنیک بین ساعت های 11 تا 12:30 غیرفعال است.',
            10 => 'خطا در برقراری ارتباط با irnic',
            501 => 'تعداد شناسه ها بیشتر از حد مجاز است',
            502 => 'شناسه ' . $_GET['c'] . ' در ایرنیک ثبت نشده است.',
            401 => 'دوره ی تمدید دامنه باید یا 1 و یا 5 ساله باشد',
            402 => 'دامنه ی شما قفل است و نمی توانید آن را تمدید کنید.',
            2102 => 'Unimplemented option',
            3010 => 'عملیات ثبت دامنه با خطا مواجه شده است.لطفا با پشتیبانی در ارتباط باشید.',
            3020 => 'عملیات تمدید دامنه با خطا مواجه شد. لطفا با پشتیبانی در ارتباط باشید.',
            3030 => 'عملیات ثبت دامنه با خطا مواجه شد. لطفا با پشتیبانی در ارتباط باشید.',
        ];

        $data = array(
            'error' => $errors[$_GET['ec']],
        );
        return $this->view('ارور ایرنیک', 'client/irnic_error.tpl', $data);
    }

    private function dd($data)
    {
        echo "<pre>";
        print_r($data);
        echo "</pre>";
        die();
    }

    private function view($title, $templateFile, $vars = [])
    {
        return array(
            'pagetitle' => $title,
            'breadcrumb' => array(
                'index.php?m=irnic' => 'Home',
            ),
            'templatefile' => $templateFile,
            'requirelogin' => true, // Set true to restrict access to authenticated client users
            'forcessl' => false, // Deprecated as of Version 7.0. Requests will always use SSL if available.
            'vars' => $vars,
        );
    }

}
