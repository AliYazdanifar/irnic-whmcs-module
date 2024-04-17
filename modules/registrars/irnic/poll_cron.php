<?php

die('d');
include dirname(__DIR__, 3) . "/init.php";

use WHMCS\Module\Registrar\irnic\Poll;


$poll = new Poll();

$poll->deleteOldPollMessages();

$res = $poll->sendRequestForGetMessage();

$responseXml = $poll->sendRequestForGetMessage();
$responseArr = $poll->xml2array($responseXml);

$msg = $poll->parseMsgQ($responseArr);

$code = $poll->parseResCode($responseArr);

if ($code == 1300) {
    $responseDate = $responseArr['epp']['_c']['response']['_c']['trID']['_c']['svTRID']['_v'];
    $poll->insertIntoIrnicTbl(0, $code, 0, 'No Index', 'هیچ پیامی وجود ندارد', $responseXml, $responseDate);
    echo 'command complete successfully. No message -- code = ' . $code;
    die();
}
$note = 'هیچ کاری انجام نشده است';

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

        echo $note . PHP_EOL;

        $ack = $poll->sendAckRequest($msg['msgId']);

        if ($ack)
            echo 'send ack success!' . PHP_EOL;

        $responseXml = $poll->sendRequestForGetMessage();
        $responseArr = $poll->xml2array($responseXml);
        $msg = $poll->parseMsgQ($responseArr);
//dd(explode(' ', $msg['msgIndex']));

        $code = $poll->parseResCode($responseArr);

        $note = 'هیچ کاری انجام نشده است';
        if ($code == 1300) {
            $responseDate = $responseArr['epp']['_c']['response']['_c']['trID']['_c']['svTRID']['_v'];
            $poll->insertIntoIrnicTbl(0, $code, 0, '', 'هیچ پیامی وجود ندارد', $responseXml, $responseDate);
            echo 'command complete successfully. No message -- code = ' . $code;
            die();
        }
        $messageCount = $msg['qCount'];

    }

} else {
    $note = 'کد وجود یا عدم وجود پیام دریافت نشد !';

    $poll->insertIntoIrnicTbl('0', '0', 0, '', $note, $responseXml, '');
    echo $note;
    die();
}

function dd($data)
{
    echo "<pre>";
    print_r($data);
    echo "</pre>";
    die();
}

