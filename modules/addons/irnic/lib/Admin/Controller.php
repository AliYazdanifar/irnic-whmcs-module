<?php

namespace WHMCS\Module\Addon\irnic\Admin;

use WHMCS\Database\Capsule;
use WHMCS\Module\Registrar\irnic\ApiClient;

/**
 * Sample Admin Area Controller
 */
class Controller
{
    private $perPage = 70;

    public function index($vars)
    {
        if (isset($_GET['delete']))
            $this->deleteLog($_GET['delete']);

        $page = 1;
        if (isset($_GET['page']))
            $page = $_GET['page'];

        $op = 'index';
        if (isset($_GET['action']))
            $op = $_GET['action'];

        $smarty = new \Smarty();
        $smarty->assign('page', $page);
        $smarty->assign('perPage', $this->perPage);
        $smarty->assign('logs', $this->getIrnicLogs($page, $this->perPage));
        $smarty->assign('vars', $vars);
        $smarty->assign('op', $op);
        $smarty->assign('layoutTemplate', $this->getLayoutTemplateAbsolutePath());
        $smarty->caching = false;
        $smarty->compile_dir = $GLOBALS['templates_compiledir'];
        $smarty->display(dirname(__FILE__, 3) . '/templates/admin/index.tpl');

    }

    private function getLayoutTemplateAbsolutePath()
    {

        return dirname(__DIR__, 2) . '/templates/admin/layout.tpl';
    }


    public function logs($vars)
    {

        if (isset($_GET['clearFilter']) && $_GET['clearFilter'] == 1) {
            $_SESSION['nic_log_sort'] = "DESC";
            $_SESSION['nic_log_search_query']='';
        }

        if (isset($_POST['sort'])) {
            $_SESSION['nic_log_sort'] = $_POST['sort'];
        }

        $page = 1;
        if (isset($_GET['page']))
            $page = $_GET['page'];

        $op = 'index';
        if (isset($_GET['action']))
            $op = $_GET['action'];

        if (isset($_POST['search'])) {
            $_SESSION['nic_log_search_query'] = $_POST['q'];
        }

        $logs = $this->getLogs($page, $this->perPage);

        $smarty = new \Smarty();
        $smarty->assign('page', $page);
        $smarty->assign('perPage', $this->perPage);
        $smarty->assign('logs', $logs);
        $smarty->assign('vars', $vars);
        $smarty->assign('op', $op);
        $smarty->assign('session', $_SESSION);
        $smarty->assign('layoutTemplate', $this->getLayoutTemplateAbsolutePath());
        $smarty->assign('url', (explode('&', $this->getProjectURL()))[0]);
        $smarty->caching = false;
        $smarty->compile_dir = $GLOBALS['templates_compiledir'];
        $smarty->display(dirname(__FILE__, 3) . '/templates/admin/logs.tpl');

    }

    public function whoisNic($vars)
    {
        $op = 'index';
        if (isset($_GET['action']))
            $op = $_GET['action'];


        $smarty = new \Smarty();
        $smarty->assign('logs', $this->getLogs($page, $this->perPage));
        $smarty->assign('vars', $vars);
        $smarty->assign('op', $op);
        $req = false;
        if (isset($_POST['nichandle'])) {
            $api = new ApiClient($vars);
            $nicHandle = $_POST['nichandle'];
            $contactInfo = $api->contactInfoRequest($nicHandle);
            $smarty->assign('info', $api->parseContactInfo($contactInfo));
            $smarty->assign('msg', $api->parseResMessage($contactInfo));
            $smarty->assign('code', $api->parseResCode($contactInfo));
            $req = true;
        }
        $smarty->assign('req', $req);
        $smarty->assign('layoutTemplate', $this->getLayoutTemplateAbsolutePath());
        $smarty->caching = false;
        $smarty->compile_dir = $GLOBALS['templates_compiledir'];
        $smarty->display(dirname(__FILE__, 3) . '/templates/admin/whois_nic.tpl');

    }

    public function whoisDomain($vars)
    {

        $op = 'index';
        if (isset($_GET['action']))
            $op = $_GET['action'];


        $smarty = new \Smarty();
        $smarty->assign('logs', $this->getLogs($page, $this->perPage));
        $smarty->assign('vars', $vars);
        $smarty->assign('op', $op);
        $req = false;
        if (isset($_POST['domain'])) {
            $api = new ApiClient($vars);
            $domain = $_POST['domain'];
            $domainInfo = $api->domainInfoRequest($domain);
            $smarty->assign('info', $api->parseDomainInfo($domainInfo));
            $smarty->assign('msg', $api->parseResMessage($domainInfo));
            $smarty->assign('code', $api->parseResCode($domainInfo));
            $req = true;
        }
        $smarty->assign('req', $req);
        $smarty->assign('layoutTemplate', $this->getLayoutTemplateAbsolutePath());
        $smarty->caching = false;
        $smarty->compile_dir = $GLOBALS['templates_compiledir'];
        $smarty->display(dirname(__FILE__, 3) . '/templates/admin/whois_domain.tpl');

    }

    private function deleteLog($id)
    {
        $pdo = Capsule::connection()->getPdo();
        $query = "DELETE FROM `irnic_poll_logs` WHERE id = :id";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['id' => $id]);
    }

    private function getIrnicLogs($page = 1, $limit = 50)
    {
        if ($page < 1) {
            $page = 1;
        }
        $start = ($page - 1) * $limit;
        $pdo = Capsule::connection()->getPdo();


        $query = "SELECT count(*) as qty FROM `irnic_poll_logs`";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $result['qty'] = ($stmt->fetch(\PDO::FETCH_ASSOC)['qty']) / $this->perPage;


        $query = "SELECT * FROM `irnic_poll_logs` ORDER BY `id` DESC LIMIT $start,$limit";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $result['logs'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $result;
    }

    private function getLogs($page = 1, $limit = 50)
    {
        if ($page < 1) {
            $page = 1;
        }

        $start = ($page - 1) * $limit;
        $pdo = Capsule::connection()->getPdo();

//        $query = "SELECT count(*) as qty FROM `irnic_logs`";
//        $stmt = $pdo->prepare($query);
//        $stmt->execute();

        $query = "SELECT count(*) as c FROM `irnic_logs` ";

        if (isset($_SESSION['nic_log_search_query']) && $_SESSION['nic_log_search_query'] != '') {
            $q = $_SESSION['nic_log_search_query'];
            $query .= " where (`log` like '%$q%' OR `created_at` like '%$q%') ";
        }

        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $forCountPagination = $stmt->fetch(\PDO::FETCH_ASSOC);
        $result['qty'] = $forCountPagination['c']/$this->perPage;

        $query = "SELECT * FROM `irnic_logs` ";

        if (isset($_SESSION['nic_log_search_query']) && $_SESSION['nic_log_search_query'] != '') {
            $q = $_SESSION['nic_log_search_query'];
            $query .= " where (`log` like '%$q%' OR `created_at` like '%$q%') ";
        }

        if (!isset($_SESSION['nic_log_sort']))
            $_SESSION['nic_log_sort'] = "DESC";

        $query .= " ORDER BY `id` " . $_SESSION['nic_log_sort'] . " LIMIT $start,$limit";
//        $this->dd($query);
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $result['logs'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $result;
    }

    private function dd($data)
    {
        echo "<pre>";
        print_r($data);
        echo "</pre>";
        die();
    }

    private function getProjectURL()
    {
//        http://trtest.ir/whmadmin/addonmodules.php?module=performance
        $q = $_SERVER['HTTP_REFERER'];
        $protocol = $_SERVER['REQUEST_SCHEME'];
        $domain = $_SERVER['HTTP_HOST'];
        $url = $_SERVER['REQUEST_URI'];
        $text = $protocol . '://' . $domain . $url;
        return $text;
    }

}
