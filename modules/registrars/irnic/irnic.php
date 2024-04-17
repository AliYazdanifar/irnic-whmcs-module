<?php
/**
 * WHMCS SDK Sample Registrar Module
 *
 * Registrar Modules allow you to create modules that allow for domain
 * registration, management, transfers, and other functionality within
 * WHMCS.
 *
 * This sample file demonstrates how a registrar module for WHMCS should
 * be structured and exercises supported functionality.
 *
 * Registrar Modules are stored in a unique directory within the
 * modules/registrars/ directory that matches the module's unique name.
 * This name should be all lowercase, containing only letters and numbers,
 * and always start with a letter.
 *
 * Within the module itself, all functions must be prefixed with the module
 * filename, followed by an underscore, and then the function name. For
 * example this file, the filename is "irnic.php" and therefore all
 * function begin "irnic_".
 *
 * If your module or third party API does not support a given function, you
 * should not define the function within your module. WHMCS recommends that
 * all registrar modules implement Register, Transfer, Renew, GetNameservers,
 * SaveNameservers, GetContactDetails & SaveContactDetails.
 *
 * For more information, please refer to the online documentation.
 *
 * @see https://developers.whmcs.com/domain-registrars/
 *
 * @copyright Copyright (c) WHMCS Limited 2017
 * @license https://www.whmcs.com/license/ WHMCS Eula
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

//include(dirname(__FILE__) . '/functions.php');

use WHMCS\Database\Capsule;
use WHMCS\Domains\DomainLookup\ResultsList;
use WHMCS\Domains\DomainLookup\SearchResult;
use WHMCS\Module\Registrar\irnic\ApiClient;
use WHMCS\Module\Registrar\irnic\Poll;

// Require any libraries needed for the module to function.
// require_once __DIR__ . '/path/to/library/loader.php';
//
// Also, perform any initialization required by the service's library.

/**
 * Define module related metadata
 *
 * Provide some module information including the display name and API Version to
 * determine the method of decoding the input values.
 *
 * @return array
 */
function irnic_MetaData()
{

    return array(
        'DisplayName' => 'irnic module',
        'APIVersion' => '1.0',
    );
}


/**
 * Define registrar configuration options.
 *
 * The values you return here define what configuration options
 * we store for the module. These values are made available to
 * each module function.
 *
 * You can store an unlimited number of configuration settings.
 * The following field types are supported:
 *  * Text
 *  * Password
 *  * Yes/No Checkboxes
 *  * Dropdown Menus
 *  * Radio Buttons
 *  * Text Areas
 *
 * @return array
 */

function irnic_getConfigArray()
{
    return array(
        "deposit_code" => array("Type" => "text", "Description" => "IRNIC Deposit Code",),
        "auth_token" => array("Type" => "text", "Description" => "IRNIC Authentication Token (without Bearer)",),
        "trid" => array("Type" => "text", "Description" => "Your Company Name For Any Request eg 'XYZ-1848'"),
        "admin_contact" => array("Type" => "text", "Description" => "IRNIC Admin Contact ID",),
        "technical_contact" => array("Type" => "text", "Description" => "IRNIC Technical Contact ID",),
        "billing_contact" => array("Type" => "text", "Description" => "IRNIC Billing Contact ID",),
        "ns_1" => array("Type" => "text", "Description" => "NameServer1",),
        "ns_2" => array("Type" => "text", "Description" => "NameServer2",),
        "ns_3" => array("Type" => "text", "Description" => "NameServer3",),
        "ns_4" => array("Type" => "text", "Description" => "NameServer4",),
        "ns_5" => array("Type" => "text", "Description" => "NameServer5",),

    );
}


/**
 * Fetch current nameservers.
 *
 * This function should return an array of nameservers for a given domain.
 *
 * @param array $params common module parameters
 *
 * @return array
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 *
 */

function irnic_GetNameservers($params)
{

    $api = new ApiClient($params);

//
    $connection = $api->checkApiConnection();
    if (!$connection['ok']) {
        return ['error' => 'خطا در برقراری ارتباط با سرور nic لطفا لیست Log را بررسی کنید.'];
    }

    $domainName = $api->generateDomain($params);
    $response = $api->domainInfoRequest($domainName);
    $resCode = $api->checkResponseCode($response);
    if ($resCode['code'] != 1000) {
        $error = $api->parseResMessage($response);
        $api->addLog('there are error in response code irnic func get name server error is : ' . $error);
        $values = ['there are error in response code irnic func get name server error is : ' . $error];
        return $values;
    }
    $hosts = $response['epp']['_c']['response']['_c']['resData']['_c']['domain:infData']['_c']['domain:ns']['_c']['domain:hostAttr'];

    $values = [];
    foreach ($hosts as $key => $host) {
        $values['ns' . ($key + 1)] = $host['_c']['domain:hostName']['_v'];
    }
    return $values;

}

/**
 * Save nameserver changes.
 *
 * This function should submit a change of nameservers request to the
 * domain registrar.
 *
 * @param array $params common module parameters
 *
 * @return array
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 *
 */

function dd($data)
{
    echo "<pre>";
    print_r($data);
    echo "</pre>";
    die();
}

function irnic_SaveNameservers($params)
{
    global $_LANG;

    $api = new ApiClient($params);
    $connection = $api->checkApiConnection();
    if (!$connection['ok']) {
        return ['error' => 'خطا در برقراری ارتباط با سرور nic لطفا لیست Log را بررسی کنید.'];
    }

    //get domain info to get old nameservers

    $addNameServers = [
        'ns1' => $params['ns1'],
        'ns2' => $params['ns2'],
        'ns3' => $params['ns3'],
        'ns4' => $params['ns4'],
        'ns5' => $params['ns5'],
    ];

    $addNameServers = array_filter($addNameServers);

    $domainName = $api->generateDomain($params);

    $result = $api->domainInfoRequest($domainName);

    $result = $api->parseDomainInfo($result);

    //send domain update dns if code =2102 return darkhast eshtebah ast

    $technicalContact = $params["technicalContact"];

    $nicTech = $params['additionalfields']['nictech'];

    if ($nicTech == "") {
        $irnicTech = $technicalContact;
    } else {
        $irnicTech = $nicTech;
    }
    $saveNs = $api->domainUpdateRequest($domainName, $addNameServers, $result['nameServers'], $irnicTech);

    $resCode = $api->parseResCode($saveNs);
    if ($resCode != 1000) {
        $error = $api->parseResMessage($saveNs);

        $api->addLog('there are error in response irnic func saveNameServer error is : ' . $error . ' res code = ' . $resCode);
        $values['error'] = 'there are error in response code irnic func saveNameServer error is : ' . $error . 'res code = ' . $resCode;
        return $values;
    }

    $values['info'] = "تغییرات نیم سرور با موفقیت انجام شد";

    return $values;

}


/**
 * Register a domain.
 *
 * Attempt to register a domain with the domain registrar.
 *
 * This is triggered when the following events occur:
 * * Payment received for a domain registration order
 * * When a pending domain registration order is accepted
 * * Upon manual request by an admin user
 *
 * @param array $params common module parameters
 *
 * @return array
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 *
 */

function irnic_RegisterDomain($params)
{

    $api = new ApiClient($params);

    $domain = $api->generateDomain($params);
    $connection = $api->checkApiConnection();
    if (!$connection['ok']) {
        return ['error' => 'خطا در برقراری ارتباط با سرور nic لطفا لیست Log را بررسی کنید.'];

    }

    $period = $params['regperiod']; // 1 OR 5
    if ($period != 5 && $period != 1) {
        $api->addLog('register period not equal 1 or 5 user id = ' . $params['userid'] . ' domain = ' . $domain);

        return ['error' => 'register period not equal 1 or 5'];
    }

    $period = $period * 12;
    $nameServers = $api->generateNameServers($params);
    dd($nameServers);
    $nicIds = $api->generateNic($params);

    $permission = $api->checkContactPositions($nicIds['nicHandle']);

    if (!$permission['status']) {

        return ['error' => $permission['message']];

    }

    if ($params['tld'] != 'ir') {
        $api->addLog('domain is not ir  user id = ' . $params['userid'] . ' domain = ' . $domain);
        return ['error' => 'domain is not ir'];
    }

//    if nichandle = email then must change to nichandle to register
    if (filter_var($nicIds['nicHandle'], FILTER_VALIDATE_EMAIL)) {
        $result = $api->contactInfoRequest($nicIds['nicHandle']);
        $code = $api->parseResCode($result);
        if ($code != 1000)
            return ['error' => $api->parseResMessage($result) . ' code = ' . $code];

        $result = $api->parseContactInfo($result);

        if (isset($result['roid']))
            $nicIds['nicHandle'] = $result['roid'];
    }
    //end

    $result = $api->domainCreateRequest($domain, $period, $nameServers, $nicIds);

    $code = $api->parseResCode($result);

    if ($code != 1000) {
        $msg = $api->parseResMessage($result);
        $api->addLog('when register domain error= ' . $msg);
        $values['error'] = 'there are error in domain create code = ' . $code . ' error = ' . $msg;

        return $values;

    }

    $result = $api->parseDomainCreate($result);

    $domainId = $params["domainid"];

    $pdo = Capsule::connection()->getPdo();

    $query = "UPDATE `tbldomains` SET`expirydate`=:exp,`status`=:st WHERE id=:id";

    $stmt = $pdo->prepare($query);

    $stmt->execute([
        ':exp' => $result['exDate'],
        ':st' => 'Active',
        ':id' => $domainId
    ]);

    $api->addLog('success register domain --- ' . 'user id = ' . $params['userid'] . ' domain = ' . $domain);

    return array(
        'success' => true,
    );

}

/**
 * Initiate domain transfer.
 *
 * Attempt to create a domain transfer request for a given domain.
 *
 * This is triggered when the following events occur:
 * * Payment received for a domain transfer order
 * * When a pending domain transfer order is accepted
 * * Upon manual request by an admin user
 *
 * @param array $params common module parameters
 *
 * @return array
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 *
 */
function irnic_TransferDomain($params)
{
    $api = new ApiClient($params);
    $domain = $api->generateDomain($params);
    $result = $api->domainInfoRequest($domain);

    $code = $api->parseResCode($result);
    if ($code != 1000) {
        $api->addLog('IRNIC error When the user intends to transfer the domain. nic ErrorMsg = ' . $api->parseResMessage($result) . 'code = ' . $code);

        return ['error' => 'IRNIC Error when transfer the domain. nic ErrorMsg = ' . $api->parseResMessage($result) . 'code = ' . $code];
    }

    $result = $api->parseDomainInfo($result);

    foreach ($result['contact'] as $contact) {
        $type = $contact['_a']['type'];
        $nicHandle = $contact['_v'];
        if ($type != 'holder' && $nicHandle != 'pe134-irnic') {
            $api->addLog('رابط holder  روی pe134-irnic ست نشده و کاربر نمی تواند انتقال دامنه را انجام دهد' . ' -- userId = ' . $params['userid'] . ' --domain = ' . $domain);
            $api->insertTodo('خطا هنگام انتقال دامنه', 'رابط holder  روی pe134-irnic ست نشده و کاربر نمی تواند انتقال دامنه را انجام دهد' . ' -- userId = ' . $params['userid'] . ' --domain = ' . $domain);
            return ['error' => "$type is not pe134-irnic"];
        }
    }

    return irnic_RenewDomain($params);
}

/**
 * Renew a domain.
 *
 * Attempt to renew/extend a domain for a given number of years.
 *
 * This is triggered when the following events occur:
 * * Payment received for a domain renewal order
 * * When a pending domain renewal order is accepted
 * * Upon manual request by an admin user
 *
 * @param array $params common module parameters
 *
 * @return array
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 *
 */

function irnic_RenewDomain($params)
{
    global $_LANG;

    $api = new ApiClient($params);
    $domainName = $api->generateDomain($params);
    $connection = $api->checkApiConnection();
    if (!$connection['ok']) {
        return ['error' => 'خطا در برقراری ارتباط با سرور nic لطفا لیست error log را بررسی کنید.'];

    }

    $period = $params['regperiod'] * 12;

    $domainInfo = $api->domainInfoRequest($domainName);

    $code = $api->parseResCode($domainInfo);

    if ($code != 1000) {
        $api->addLog('there are error in renew response code error is : ' . $res['message']);
        $values['error'] = 'there are error in renew response code error is : ' . $res['message'];
        return $values;
    }

    $domainInfo = $api->parseDomainInfo($domainInfo);

    $nichandle = $domainInfo['contact'][0]['_v'];

    foreach ($domainInfo['contact'] as $contact) {
        if ($contact['_a']['type'] == 'holder') {
            $nichandle = $contact['_v'];
        }
    }

    $permission = $api->checkContactPositions($nichandle);

    if (!$permission['status']) {

//        if (isset($params['userid'])) {
//            header('location:index.php?m=irnic&action=showError&ec=3020');
//        }

        return ['error' => $permission['message']];

    }

    if ($domainInfo['expDate'] == $api->EXP_DATE_NOT_SET)
        return ['error' => 'دسترسی به تاریخ انقضاء این دامنه وجود ندارد.شما مجاز به تمدید این دامنه نیستید.'];


    if ($period != 12 && $period != 60) {
        $error = 'The renewal period should be 1 or 5 years. ';
        $api->addLog('The renewal period should be 1 or 5 years.  user id = ' . $params['userid'] . ' domain = ' . $domainName . ' period in renew is : ' . $period);

        $values['error'] = $error . ' Your renewal period is : ' . ($period / 12) . ' years.';
        return $values;
    }

    $domainDate = explode('T', $domainInfo['expDate']);

    $nowDate = date('Y-m-d');

    $currentExpDate = $domainDate[0];

    $limitDays = 2189; // (5years , 364 days) in days

    $date1 = new DateTime($nowDate);
    $date2 = new DateTime($currentExpDate);

    $interval = $date1->diff($date2);
    $diffY = $interval->y;
    $diffM = $interval->m;
    $diffD = $interval->d;

    $days = $interval->days;

    $renInday = $period * 30;
    $sum = $renInday + $days;
    if ($sum >= $limitDays) {
        $api->addLog('cannot renew the domain for more than 5 years and 364 days. user id = ' . $params['userid'] . ' domain = ' . $domainName);

        return ['error' => 'شما نمیتوانید بیشتر از 5 سال و 364 روز دامنه را تمدید کنید.'];
    }
    foreach ($domainInfo['status'] as $status) {
        if (strtolower($status['_a']['s']) == 'serverrenewprohibited') {
            $values['error'] = 'Currently, it is not possible to renew this domain in Irnic user id = ' . $params['userid'] . ' (' . $domainInfo['name'] . ' )' . ' irnic status is : ' . $status['_a']['s'];
            $api->addLog($values['error']);

            return $values;
        }

        if (!isset($params['unlockAndRenew']) && strtolower($status['_a']['s']) == 'irniclocked') {
            $values['error'] = 'domain is lock in user id = ' . $params['userid'] . ' renew : ' . $domainInfo['name'] . ' irnic status is : ' . $status['_a']['s'];
            $api->addLog($values['error']);

            return $values;
        }

        if (strtolower($status['_a']['s']) == 'irnicrenewaliswithdrawn') {
            $values['error'] = 'درخواست تمدید دامنه توسط رابط یا درخواست دهنده ی آن لغو شده است. :  user id = ' . $params['userid'] . '' . $domainInfo['name'] . ' irnic status is : ' . $status['_a']['s'];

            $api->addLog($values['error']);

            return $values;
        }

        if (strtolower($status['_a']['s']) == 'pendingrenew') {
            $api->addLog(' در انتظار تمدید دامنه  user id = ' . $params['userid'] . ' ' . $domainInfo['name'] . ' irnic status is : ' . $status['_a']['s']);
            $values['error'] = 'در انتظار تمدید دامنه ' . $domainInfo['name'] . ' irnic status is : ' . $status['_a']['s'];

            return $values;
        }

    }

//        2025-09-08T23:26:19
    $exDate = explode('T', $domainInfo['expDate']);

    $domainRenew = $api->domainRenewRequest($domainName, $period, $exDate[0]);

    $code = $api->parseResCode($domainRenew);

    if ($code != 1001) {
        if ($code == 2304) {
            $error = " دامنه مجاز برای تمدید نیست. لطفا وضعیت دامنه را بررسی کنید.";
        } else {
            $error = $api->parseResMessage($domainRenew);
        }
        $api->addLog($error);

        return ['error' => $error];
    }

    $api->addLog('success renew' . 'user id = ' . $params['userid'] . ' domain = ' . $domainName);
    return ['success' => true];

}


/**
 * Get the current WHOIS Contact Information.
 *
 * Should return a multi-level array of the contacts and name/address
 * fields that be modified.
 *
 * @param array $params common module parameters
 *
 * @return array
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 *
 */
function irnic_GetContactDetails($params)
{
    $api = new ApiClient($params);

    $api->addLog('irnic_GetContactDetails');

    $domainName = $api->generateDomain($params);

    $connection = $api->checkApiConnection();
    if (!$connection['ok']) {
        return ['error' => 'خطا در برقراری ارتباط با سرور nic لطفا لیست Log را بررسی کنید.'];

    }

    $nichandle = $api->generateNic($params)['nichandle'];

    $resp = $api->domainInfoRequest($domainName);
    if ($nichandle == '')
        $nichandle = $resp['epp']['_c']['response']['_c']['resData']['_c']['domain:infData']['_c']['domain:contact'][0]['_v'];
    $admindet = $resp['epp']['_c']['response']['_c']['resData']['_c']['domain:infData']['_c']['domain:contact'][1]['_v'];
    $techdet = $resp['epp']['_c']['response']['_c']['resData']['_c']['domain:infData']['_c']['domain:contact'][2]['_v'];
    $billdet = $resp['epp']['_c']['response']['_c']['resData']['_c']['domain:infData']['_c']['domain:contact'][3]['_v'];

    $values['Handles']['Holder Handle'] = $nichandle;
    $values['Handles']['Admin Handle'] = $admindet;
    $values['Handles']['Technical Handle'] = $techdet;
    $values['Handles']['Billing Handle'] = $billdet;

    return $values;
}

/**
 * Update the WHOIS Contact Information for a given domain.
 *
 * Called when a change of WHOIS Information is requested within WHMCS.
 * Receives an array matching the format provided via the `GetContactDetails`
 * method with the values from the users input.
 *
 * @param array $params common module parameters
 *
 * @return array
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 *
 */

function irnic_SaveContactDetails($params)
{
    global $_LANG;
    $api = new ApiClient($params);
    $api->addLog('irnic_SaveContactDetails');

    $adminContact = $params["adminContact"];
    $technicalContact = $params["technicalContact"];
    $billingContact = $params["billingContact"];

    $domainName = $api->generateDomain($params);

    $connection = $api->checkApiConnection();
    if (!$connection['ok']) {
        return ['error' => 'خطا در برقراری ارتباط با سرور nic لطفا لیست Log را بررسی کنید.'];
    }

    # Data is returned as specified in the GetContactDetails() function
    $nicadmin = $params["contactdetails"]["Handles"]["Admin Handle"];
    $nictech = $params["contactdetails"]["Handles"]["Technical Handle"];
    $nicbill = $params["contactdetails"]["Handles"]["Billing Handle"];

    if ($nicadmin == "") {
        $nicadmin = $adminContact;
    }

    if ($nictech == "") {
        $nictech = $technicalContact;
    }

    if ($nicbill == "") {
        $nicbill = $billingContact;
    }

    $response = $api->domainContactUpdateRequest($domainName, $nicadmin, $nictech, $nicbill);

    $code = $api->parseResCode($response);
    if ($code == -1) {
        $values['error'] = $api->parseResMessage($response);

        return $values;
    }

    $res_code = $api->parseResCode($response);

    if ($res_code != 1000) {
        $error = $api->parseResMessage($response);
        $values['error'] = $error;

        if ($res_code == 2306) {
            $values['error'] = "شما امکان تغییر رابط ادمین را ندارید";
        }

        if ($res_code == 2303) {
            $values['error'] = "دامنه برای تغییرات رابط موجود نیست";
        }
        $api->addLog($values['error']);

        return $values;
    }

    return ['info' => 'success'];
}

/**
 * Check Domain Availability.
 *
 * Determine if a domain or group of domains are available for
 * registration or transfer.
 *
 * @param array $params common module parameters
 * @return \WHMCS\Domains\DomainLookup\ResultsList An ArrayObject based collection of \WHMCS\Domains\DomainLookup\SearchResult results
 * @throws Exception Upon domain availability check failure.
 *
 * @see \WHMCS\Domains\DomainLookup\ResultsList
 *
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 *
 * @see \WHMCS\Domains\DomainLookup\SearchResult
 */
function irnic_CheckAvailability($params)
{
    $api = new ApiClient($params);
    $api->addLog('irnic_CheckAvailability');
    return ['error', 'no action'];

}

/**
 * Domain Suggestion Settings.
 *
 * Defines the settings relating to domain suggestions (optional).
 * It follows the same convention as `getConfigArray`.
 *
 * @see https://developers.whmcs.com/domain-registrars/check-availability/
 *
 * @return array of Configuration Options
 */
function irnic_DomainSuggestionOptions()
{
    return array(
        'includeCCTlds' => array(
            'FriendlyName' => 'Include Country Level TLDs',
            'Type' => 'yesno',
            'Description' => 'Tick to enable',
        ),
    );
}

/**
 * Get Domain Suggestions.
 *
 * Provide domain suggestions based on the domain lookup term provided.
 *
 * @param array $params common module parameters
 * @return \WHMCS\Domains\DomainLookup\ResultsList An ArrayObject based collection of \WHMCS\Domains\DomainLookup\SearchResult results
 * @throws Exception Upon domain suggestions check failure.
 *
 * @see \WHMCS\Domains\DomainLookup\ResultsList
 *
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 *
 * @see \WHMCS\Domains\DomainLookup\SearchResult
 */
function irnic_GetDomainSuggestions($params)
{
    $api = new ApiClient($params);
    $api->addLog('irnic_GetDomainSuggestions');
    return ['info'];
    // user defined configuration values

}

/**
 * Get registrar lock status.
 *
 * Also known as Domain Lock or Transfer Lock status.
 *
 * @param array $params common module parameters
 *
 * @return string|array Lock status or error message
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 *
 */
function irnic_GetRegistrarLock($params)
{

    $api = new ApiClient($params);
    $api->addLog('irnic_GetRegistrarLock');

    return "locked";

}

/**
 * Set registrar lock status.
 *
 * @param array $params common module parameters
 *
 * @return array
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 *
 */
function irnic_SaveRegistrarLock($params)
{
    global $_LANG;
    $api = new ApiClient($params);
    $api->addLog('irnic_SaveRegistrarLock');

    $domain_name = $api->generateDomain($params);

    if ($params["lockenabled"] == "locked") {
        $response = $api->domainLockRequest($domain_name);

        $res_code = $api->parseResCode($response);
        if ($res_code != 1000) {
            return ['error' => $api->parseResMessage($response)];
        }
    } else {
        $resp = $api->domainReqRequest($domain_name);

        $resp_code = $api->parseResCode($resp);
        if ($resp_code != 1000) {
            if ($resp_code == 2303) {
                return ['error' => "دامنه برای انتقال وجود ندارد"];
            }

            if ($resp_code == 2306) {
                return ['error' => "نماینده دامنه امکان انتقال دامنه را ندارد"];
            }

            if ($resp_code == 2304) {
                return ['error' => "وضعیت دامنه برای انتقال مجاز نیست"];
            }
            return ['error' => "خطایی در ارسال درخواست رخ داده است"];

        } else {
            return ['info' => "درخواست بازگشایی قفل با موفقیت انجام شد"];
        }
    }

    return ['error' => "خطایی در ارسال درخواست رخ داده است"];
}

function irnic_LockDomain($params)
{
    global $_LANG;

    $api = new ApiClient($params);
    $api->addLog('irnic_LockDomain');

    return ['error' => 'no action'];

    $nictoken = $params["nictoken"];
    $trid = $params["trid"];
    $test = $params["test"];
    $adminContact = $params["adminContact"];
    $technicalContact = $params["technicalContact"];
    $billingContact = $params["billingContact"];

    if ($test == 'on') {
        $trid = 'TEST';
    }
    $tld = $params['tld'];
    $sld = $params['sld'];
    $domainName = strtolower($sld . '.' . $tld);

    $api = new ApiClient($params);

    $request = $api->domainLockRequest($domainName);

    if ($request == "TimeError") {
        $values['error'] = $_LANG['irnictimeerror'];
        return $values;
    }

    $respons = $api->xml2array($request);
    $res_code = $api->parseResCode($respons);
    if ($res_code != 1000) {
        $error = $api->parseResMessage($respons);
        $values['error'] = $error;
    } else {
        $success = "درخواست قفل دامنه با موفقیت انجام شد";
        $values['info'] = $success;
    }

    return $values;
}

function irnic_AdminCustomButtonArray()
{
    return array('Polling' => 'Polling', 'Lock Domain' => 'LockDomain', 'Unlock & Renew' => 'unlockAndRenew', 'Sync Domain' => 'syncDomainStatusButtonHandle');
}

function irnic_Polling($params)
{
    global $_LANG;

    $poll = new Poll();

    $poll->addLog('irnic_Polling');

    $responseXml = $poll->sendRequestForGetMessage();
    $responseArr = $poll->xml2array($responseXml);

    $msg = $poll->parseMsgQ($responseArr);

    $code = $poll->parseResCode($responseArr);

    $note = 'هیچ کاری انجام نشده است';
    if ($code == 1300) {
        $note = 'هیچ پیامی وجود ندارد';
        $poll->insertIntoIrnicTbl(0, $code, 0, '', 'هیچ پیامی وجود ندارد', $responseXml, '');

        $values['success'] = $note;
        return $values;

    }

    $messageCount = $msg['qCount'];
    $poll->insertIntoIrnicTbl($msg['msgId'], $code, $msg['qCount'], $msg['msgIndex'], $note, $responseXml, $msg['qDate']);


    if ($code == 1301) {
        while ($messageCount >= 1) {

            if (str_contains($msg['msgIndex'], 'DomainUpdateStatus')) {
//            if (str_contains($msg['msgIndex'], 'DomainNotice')) {}
                $note = $poll->domainUpdateStatusProcess($responseArr, $responseXml);

            } else if (str_contains($msg['msgIndex'], 'ContactUpdateStatus')) {
//            if (str_contains($msg['msgIndex'], 'ContactNotice')) {}
                $note = $poll->contactUpdateStatusProcess($responseArr, $responseXml);

            }


            $poll->insertIntoIrnicTbl($msg['msgId'], $code, $msg['qCount'], $msg['msgIndex'], $note, $responseXml, $msg['qDate']);

            $poll->sendAckRequest($msg['msgId']);

            $responseXml = $poll->sendRequestForGetMessage();
            $responseArr = $poll->xml2array($responseXml);
            $msg = $poll->parseMsgQ($responseArr);
//dd(explode(' ', $msg['msgIndex']));

            $code = $poll->parseResCode($responseArr);


            if ($code == 1300) {
                $values['info'] = 'هیچ پیامی وجود ندارد';
                $poll->insertIntoIrnicTbl(0, $code, 0, '', 'هیچ پیامی وجود ندارد', $responseXml, '');
                return $values;
            }
            $messageCount = $msg['qCount'];
            $poll->insertIntoIrnicTbl($msg['msgId'], $code, $msg['qCount'], $msg['msgIndex'], $msg['msgNote'], $responseXml, $msg['qDate']);

        }

        $values['success'] = 'انجام شد.';

        return $values;
    } else {
        $note = 'کد وجود یا عدم وجود پیام دریافت نشد !';

        $values['success'] = $note;

        $poll->insertIntoIrnicTbl('0', '0', 0, '', $note, $responseXml, '');
        return $values;
    }

}

function irnic_GetEPPCode($params)
{
    global $_LANG;
    $api = new ApiClient($params);
    $api->addLog('irnic_GetEPPCode');

    $domainName = $api->generateDomain($params);

    $response = $api->domainReqRequest($domainName);

    $res_code = $api->parseResCode($response);
    if ($res_code == -1) {
        $values['error'] = $api->parseResMessage($response);
        return $values;
    }

    if ($res_code != 1000) {
        if ($res_code == 2303) {
            $values['error'] = "دامنه برای انتقال وجود ندارد";
        } else {
            if ($res_code == 2306) {
                $values['error'] = "نماینده دامنه امکان انتقال دامنه را دارد";
            } else {
                if ($res_code == 2304) {
                    $values['error'] = " دامنه مجاز برای تمدید نیست. لطفا وضعیت دامنه را بررسی کنید.";

                } else {
                    $values['error'] = "خطایی در ارسال درخواست رخ داده است";
                }
            }
        }
    } else {
        $success = "درخواست با موفقیت انجام شد و کد انتقال دامنه به ایمیل شما ارسال شد";
        $values['info'] = $success;
    }

    return $values;

}

function irnic_AdminDomainsTabFields($params)
{
    global $_LANG;
    $api = new ApiClient($params);

    $domainName = $api->generateDomain($params);

    $resp = $api->domainInfoRequest($domainName);

    $resp_code = $api->parseResCode($resp);

    if ($resp_code != 1000) {
        $error = $api->parseResMessage($resp);
        $values['error'] = $error;
        return $values;
    }

    $domaincheck = $resp['epp']['_c']['response']['_c']['resData']['_c']['domain:infData']['_c']['domain:status'];
    $i = 0;
    foreach ($domaincheck as $key => $val) {
        $i = $i + 1;
        $domain_status = $val['_a']['s'];
        if ($domain_status) {
            $domain_all_status .= $i . ") " . $_LANG['IRNIC_DOMAIN_STATUS_' . $domain_status] . '<br>';
        } else {
            $domain_all_status = ' ';
        }
    }
    $fieldsarray = array('IRNIC Domain Status' => $domain_all_status);
    $resp = $api->parseDomainInfo($resp);
    $fieldsarray['expDate'] = $resp['expDate'];
    return $fieldsarray;

}

function irnic_unlockAndRenew($params)
{

    $api = new ApiClient($params);
    $api->addLog('irnic_unlockAndRenew');

    $domainName = $api->generateDomain($params);

    $domainInfo = $api->domainInfoRequest($domainName);
    $domainInfo = $api->parseDomainInfo($domainInfo);

    $allow = false;
    foreach ($domainInfo['status'] as $status) {
        $state = $status['_a']['s'];
        if ($state == 'inactive')
            $allow = true;
        if ($state == 'irnicLocked')
            $allow = true;
        if ($state == 'serverUpdateProhibited')
            $allow = true;
        if ($state == 'serverUpdateProhibited')
            $allow = true;
    }

    if (!$allow)
        return ['error' => 'این دامنه قفل نیست'];

    $params['unlockAndRenew'] = true;

    return irnic_RenewDomain($params);


//    $unlock = irnic_SaveRegistrarLock($params);
//    if ($unlock) {
//        $api->addLog('in unlock');
//
//        $params['regperiod'] = 1;
//        $renew = irnic_RenewDomain($params);
//        if ($renew) {
//            $api->addLog('info');
//            $values['info'] = "بازگشایی و تمدید دامنه با موفقیت انجام شد";
//        } else {
//            $api->addLog('errorororo in unlock');
//            $values['error'] = "تمدید دامنه انجام نشد";
//        }
//    } else {
//        $api->addLog('error23or in unlock');
//        $values['error'] = "بازگشایی و تمدید دامنه انجام نشد";
//    }
//
//    return $values;
}

function irnic_syncDomainStatusButtonHandle($params)
{
    $api = new ApiClient($params);
    $api->addLog('irnic_syncDomainStatusButtonHandle');
    $username = $params["Username"];
    $password = $params["Password"];
    $testmode = $params["TestMode"];
    $tld = $params["tld"];
    $sld = $params["sld"];
    # Data is returned as specified in the GetContactDetails() function
    $firstname = $params["contactdetails"]["Registrant"]["First Name"];
    $lastname = $params["contactdetails"]["Registrant"]["Last Name"];
    $adminfirstname = $params["contactdetails"]["Admin"]["First Name"];
    $adminlastname = $params["contactdetails"]["Admin"]["Last Name"];
    $techfirstname = $params["contactdetails"]["Tech"]["First Name"];
    $techlastname = $params["contactdetails"]["Tech"]["Last Name"];
    # Put your code to save new WHOIS data here

    try {
        $api = new ApiClient($params);

        $domain = $api->generateDomain($params);

        $domainInfo = $api->domainInfoRequest($domain);

        $code = $api->parseResCode($domainInfo);
        if ($code != 1000) {
            return ['error' => 'there are error in sync request res code = ' . $code . ' res msg = ' . $api->parseResMessage($domainInfo)];
        }

        $domainInfo = $api->parseDomainInfo($domainInfo);

        $pdo = Capsule::connection()->getPdo();

        $query = "UPDATE `tbldomains` SET `nextduedate`=:ndd, `registrationdate`=:rd, `expirydate`=:exp  WHERE id = :id";

        $stmt = $pdo->prepare($query);

        $dueDate = explode('T', $domainInfo['expDate']);
        return $stmt->execute([
            ':ndd' => $dueDate[0],
            ':rd' => $domainInfo['crDate'],
            ':exp' => $domainInfo['expDate'],
            ':id' => $params['domainid']
        ]);


    } catch (\Exception $e) {
        return array(
            'error' => $e->getMessage(),
        );
    }

}

function irnic_Sync($params)
{
    try {
        $api = new ApiClient($params);

        $domain = $api->generateDomain($params);

        $domainInfo = $api->domainInfoRequest($domain);

        $code = $api->parseResCode($domainInfo);
        if ($code != 1000) {
            return ['error' => 'there are error in sync request res code = ' . $code . ' res msg = ' . $api->parseResMessage($domainInfo)];
        }

        $domainInfo = $api->parseDomainInfo($domainInfo);
        $exp = explode('T', $domainInfo['expDate']);

        $active = false;
        foreach ($domainInfo['status'] as $status) {
            if ($status['_a']['s'] == 'irnicRegistered') {
                $active = true;
                break;
            }
        }

        $expired = false;
        foreach ($domainInfo['status'] as $status) {
            if ($status['_a']['s'] == 'irnicExpired' || $status['_a']['s'] == 'inactive') {
                $expired = true;
                break;
            }
        }


        return array(
            'expirydate' => $exp[0], // Format: YYYY-MM-DD
            'active' => $active, // Return true if the domain is active
            'expired' => $expired, // Return true if the domain has expired
            'transferredAway' => false, // Return true if the domain is transferred out
        );

    } catch (\Exception $e) {
        return array(
            'error' => $e->getMessage(),
        );
    }
}

/**
 * Client Area Output.
 *
 * This function renders output to the domain details interface within
 * the client area. The return should be the HTML to be output.
 *
 * @param array $params common module parameters
 *
 * @return string HTML Output
 * @see https://developers.whmcs.com/domain-registrars/module-parameters/
 *
 */
function irnic_ClientArea($params)
{
    return '
        <div class="alert alert-info">
            اطلاعات
        </div>
    ';
}
