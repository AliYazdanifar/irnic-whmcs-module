<?php


namespace WHMCS\Module\Registrar\irnic;


use PDO;
use WHMCS\Database\Capsule;


class Poll extends ApiClient
{
    use XmlParser;

    public function sendRequestForGetMessage()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
		<epp xmlns="urn:ietf:params:xml:ns:epp-1.0">
		 <command>
		  <poll op="req"/>
		  <clTRID>' . $this->trid . '</clTRID>
		 </command>
		</epp>';


        $res = '<?xml version="1.0" encoding="UTF-8"?>
<epp xmlns="urn:ietf:params:xml:ns:epp-1.0" xmlns:contact="http://epp.nic.ir/ns/contact-1.0">
<response xmlns:contact="http://epp.nic.ir/ns/contact-1.0">
    <result code="1301">
        <msg>Command completed successfully</msg>
    </result>
    <msgQ count="3" id="14">
        <qDate>2009-12-08T19:27:01</qDate>
        <msg>
            <index>DomainUpdateStatus</index>
        </msg>
    </msgQ>
    <resData xmlns:contact="http://epp.nic.ir/ns/contact-1.0">
        <domain:polData xmlns:domain="http://epp.nic.ir/ns/contact-1.0">
            <domain:id>ex61-irnic</domain:id>
            <domain:roid>ex61-irnic</domain:roid>
            <domain:status s="irnicQueued"/>
            <domain:status s="Inactive"/>
            <domain:postalInfo type="int">
                <domain:firstname>ali</domain:firstname>
                <domain:lastname>yazdanifar</domain:lastname>
                <domain:addr>
                    <domain:street>aaa</domain:street>
                    <domain:city>bbb</domain:city>
                    <domain:sp>ccccc</domain:sp>
                    <domain:pc>55653</domain:pc>
                    <domain:cc>IR</domain:cc>
                </domain:addr>
            </domain:postalInfo>
            <domain:voice>123456789</domain:voice>
            <domain:fax>afax</domain:fax>
        </domain:polData>
    </resData>
    <trID>
        <clTRID>TEST-12345</clTRID>
        <svTRID>IRNIC_2009-12-10T12:14:17+03:30_zkr</svTRID>
    </trID>
</response>
</epp>';

//        return $res;


        return $this->call($xml);

    }

    public function domainUpdateStatusProcess(array $responseArr, $responseXml, $code = 1301)
    {
        $msg = $this->parseMsgQ($responseArr);

        $polData = $responseArr['epp']['_c']['response']['_c']['resData']['_c']['domain:polData']['_c'];
        $domainName = $polData['domain:name']['_v'];
        $domainRoid = $polData['domain:roid']['_v'];
        $statuses = $polData['domain:status'];

        $note = $domainName . ' - ';
        $domainStatuses = [];
        foreach ($statuses as $domainStatus) {
            array_push($domainStatuses, $domainStatus["_a"]["s"]);
        }

        if (in_array("serverHold", $domainStatuses) || in_array("irnicReserved", $domainStatuses)) {
            $note .= " دامنه رزرو گردیده است. ";
            $query = "UPDATE tbldomains SET status = 'Active' WHERE `domain` = '$domainName' ";
            $this->doQuery($query);
        }

        if (in_array("serverRenewProhibited", $domainStatuses)) {
            $note .= " امکان تمدید دامنه وجود ندارد. ";
        }

        if (in_array("serverDeleteProhibited", $domainStatuses)) {
            $note .= " امکان حذف دامنه وجود ندارد. ";
        }

        if (in_array("irnicRegistrationApproved", $domainStatuses)) {
            $note .= " درخواست ثبت دامنه تایید شده است. ";
        }

        if (in_array("irnicRegistrationRejected", $domainStatuses)) {
            $note .= " درخواست ثبت دامنه رد شده است. ";
            $this->insertTodo('ثبت دامنه رد شده است.', $domainName . $note);
        }

        if (in_array("Inactive", $domainStatuses)) {
            $note .= " دامنه قفل یا منقضی است (فعال نیست). ";
            $query = "UPDATE tbldomains SET status = 'Expired' WHERE `domain` = '$domainName' ";
            $this->doQuery($query);
        }

        if (in_array("irnicRegistrationPendingHolderCheck", $domainStatuses)) {
            $note .= " در انتظار بررسی شناسه ی صاحب امتیاز دامنه توسط ایرنیک در فرایند ثبت دامنه. ";
        }

        if (in_array("irnicRegistrationDocRequired", $domainStatuses)) {
            $note .= " برای تکمیل درخواست ثبت دامنه، مستندات نیاز است . ";
            $this->insertTodo('خطا در ثبت دامنه', $note);
        }

        if (in_array("irnicRenewalDocRequired", $domainStatuses)) {
            $note .= " برای تکمیل درخواست تمدید دامنه، مستندات نیاز است . ";
            $this->insertTodo('خطا در تمدید دامنه', $note);
        }

        if (in_array("irnicRegistrationIsWithdraw", $domainStatuses)) {
            $note .= " درخواست ثبت دامنه توسط رابط یا درخواست دهنده ی آن لغو شده . ";
            $this->insertTodo(' ثبت دامنه لغو شد ', $note);
        }

        if (in_array("irnicRenewalRejected", $domainStatuses)) {
            $note .= " درخواست تمدید دامنه رد شده . ";
            $this->insertTodo(' درخواست تمدید دامنه رد شده ', $note);
        }

        if (in_array("irnicHolderChangeRejected", $domainStatuses)) {
            $note .= " درخواست تغییر صاحب امتیاز دامنه رد شده . ";
            $this->insertTodo(' درخواست تغییر صاحب امتیاز دامنه رد شده ', $note);
        }

        if (in_array("irnicRegistrationPendingDomainCheck", $domainStatuses)) {
            $note .= " در انتظار بررسی درخواست صبت دامنه. ";
        }

        if (in_array("serverUpdateProhibited", $domainStatuses)) {
            $note .= " امکان تغییر مشخصات دامنه وجود ندارد. ";
        }

        if (in_array("pendingDelete", $domainStatuses)) {
            $note .= " در انتظار لغو دامنه. ";
        }

        if (in_array("pendingRenew", $domainStatuses)) {
            $note .= " در انتظار تمدید دامنه. ";
        }

        if (in_array("pendingUpdate", $domainStatuses)) {
            $note .= " در انتظار بروزرسانی دامنه. ";
        }

        if (in_array("irnicLocked", $domainStatuses)) {
            $note .= " دامنه قفل شده است. ";
            $query = "UPDATE tbldomains SET status = 'Locked' WHERE `domain` = '$domainName' ";
            $this->doQuery($query);
        }

        if (in_array("irnicExpired", $domainStatuses)) {
            $note .= " دامنه منقضی شده است. ";
            $query = "UPDATE tbldomains SET status = 'expired' WHERE `domain` = '$domainName' ";
            $this->doQuery($query);
        }


        if (in_array("irnicExpired", $domainStatuses)) {
            $note .= " دامنه منقضی شده است. ";
            $query = "UPDATE tbldomains SET status = 'expired' WHERE `domain` = '$domainName' ";
            $this->doQuery($query);
        }


         if (in_array("irnicHolderChangeIsWithdrawn", $domainStatuses)
            || in_array("irnicRegistrationRejected", $domainStatuses)
            || in_array("irnicRenewalRejected", $domainStatuses)
            || in_array("irnicHolderChangeRejected", $domainStatuses)
            || in_array("irnicSuspended", $domainStatuses)
            || in_array("Inactive", $domainStatuses)
            || in_array("irnicRenewalIsWithdrawn", $domainStatuses)
            || in_array("irnicRegistrationIsWithdrawn", $domainStatuses)) {
            $query = "SELECT * FROM tbldomains WHERE (status = 'Pending Transfer' OR status = 'Active' OR status = 'Expired') AND domain = '$domainName' LIMIT 1";
            $domain = $this->selectQuery($query, false);
            $domainId = $domain['id'];

            $query = "UPDATE tbldomains SET status = 'Cancelled' WHERE id = '$domainId' ";
            $this->doQuery($query);

            $now_Todo = date("Y-m-d", time());
            $description = 'IRNIC Domain : ' . $domainName . ' Rejected';
            $query = "INSERT INTO tbltodolist(`date`, `title`, `description`, `admin`, `status`, `duedate`) values('$now_Todo','IRNIC_Rejected_Domain_Status','$description',0,'Pending','$now_Todo') ";
            if ($this->doQuery($query)) {
                $note = 'وضعیت حالت های استردادی به لغو شده تغییر یافت';
            } else {
                $note = 'وضعیت حالت های استردادی به لغو شده تغییر یافت اما اطلاعات در جدول صف کارها افزوده نشد';
            }

        } else if (in_array("irnicHolderChangeApproved", $domainStatuses)) {
            $query = "SELECT * FROM tbldomains WHERE `status` = 'Pending Transfer' AND `domain` = '$domainName' LIMIT 1";
            $domain = $this->selectQuery($query, false);
            $domainId = $domain['id'];
            $query = "UPDATE tbldomains SET `status` = 'Active' WHERE `id` = '$domainId'";
            if ($this->doQuery($query)) {

                $domainInfoXml = $this->domainInfoRequest($domainName, false);
                $domainInfoArr = $this->xml2array($domainInfoXml);

                $code = $this->parseResCode($domainInfoArr);
                if ($code == 1000) {
                    $holderNicHandle = $this->parseDomainInfo($domainInfoArr)['contact'][0]['_v'];
                    if ($holderNicHandle) {
                        $query = "UPDATE tbldomainsadditionalfields SET `value` = '$holderNicHandle' WHERE `domainid` = '$domainId' AND name = 'nichandle'";
                        $this->doQuery($query);
                        $note.= 'اطلاعات دامنه در پایگاه داده بروزرسانی شد';
                    } else {
                        $note.= 'اطلاعات دامنه در پایگاه داده بروزرسانی نشددددد';
                    }

                } else {
                    $note .= 'خطایی در دریافت اطلاعات دامنه بوجود آمده است';

                }
            } else {
                $note .= 'خطای 2048 پیش آمده لطفا به برنامه نویس اطلاع دهید.';
            }
            $this->insertIntoIrnicTbl($msg['msgId'], $code, $msg['qCount'], $msg['msgIndex'], $note, $responseXml, $msg['qDate']);

        } else {
            $note .= 'حالت های HolderChangeApproved و Rejected وجود ندارند';

            $now_Todo = date("Y-m-d", time());
            $title = 'IRNIC Domain : ' . $domainName;

            $query = "INSERT INTO tbltodolist(`date`, `title`, `description`, `admin`, `status`, `duedate`) values('$now_Todo','$title','$note',0,'Pending','$now_Todo') ";
            if (!$this->doQuery($query))
                $note .= 'خطای 1024 پیش آمده با برنامه نویس در ارتباط باشید';
        }
        $this->insertIntoIrnicTbl($msg['msgId'], $code, $msg['qCount'], $msg['msgIndex'], $note, $responseXml, $msg['qDate']);

        return $note;
    }

    public function selectQuery($query, $fetchAll = true)
    {
        $pdo = Capsule::connection()->getPdo();
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        if ($fetchAll)
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        else
            return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function doQuery($query)
    {
        $pdo = Capsule::connection()->getPdo();
        $stmt = $pdo->prepare($query);
        return $stmt->execute();
    }

    public function insertIntoIrnicTbl($msgId, $resultCode, $qMsgCount, $msgIndex, $msgNote, $responseXml, $resultDate)
    {
        $pdo = Capsule::connection()->getPdo();
        $query = "INSERT INTO `irnic_poll_logs`(`msg_id`, `res_code`, `qcount`, `msg_index`, `msg_note`, `response_xml`, `res_date`) 
VALUES (:mi,:rc,:qc,:mindex,:mnote,:xml,:resdate)";

        $stmt = $pdo->prepare($query);
        $stmt->execute([
            ':mi' => $msgId,
            ':rc' => $resultCode,
            ':qc' => $qMsgCount,
            ':mindex' => $msgIndex,
            ':mnote' => $msgNote,
            ':xml' => $responseXml,
            ':resdate' => $resultDate,
        ]);

    }

    public function deleteOldPollMessages()
    {
        $pdo = Capsule::connection()->getPdo();

        $query = "DELETE FROM `irnic_poll_logs` WHERE created_at < NOW() - INTERVAL 90 DAY;";

        $stmt = $pdo->prepare($query);
        $stmt->execute([]);

    }

    public function contactUpdateStatusProcess(array $responseArr, $responseXml, $code = 1301)
    {
        $msg = $this->parseMsgQ($responseArr);

        $polData = $responseArr['epp']['_c']['response']['_c']['resData']['_c']['contact:polData']['_c'];
        $contactId = $polData['contact:id']['_v'];
        $contactRoid = $polData['contact:roid']['_v'];
        $statuses = $polData['contact:status'];

        $contactStatuses = [];
        foreach ($statuses as $contactStatus) {
            array_push($contactStatuses, $contactStatus["_a"]["s"]);
        }

        if ((in_array("irnicHolderChangeIsWithdrawn", $contactStatuses)
                || in_array("irnicRegistrationRejected", $contactStatuses)
                || in_array("irnicRenewalRejected", $contactStatuses)
                || in_array("irnicHolderChangeRejected", $contactStatuses)
            ) && (in_array("irnicHolderChangeApproved", $contactStatuses))) {

            $note = 'حالت های Rejected و HolderChangeApproved همزمان باز گردانیده شده اند !';

        } else if (in_array("irnicHolderChangeIsWithdrawn", $contactStatuses)
            || in_array("irnicRegistrationRejected", $contactStatuses)
            || in_array("irnicRenewalRejected", $contactStatuses)
            || in_array("irnicHolderChangeRejected", $contactStatuses)
            || in_array("irnicSuspended", $contactStatuses)
            || in_array("Inactive", $contactStatuses)
            || in_array("irnicRenewalIsWithdrawn", $contactStatuses)
            || in_array("irnicRegistrationIsWithdrawn", $contactStatuses)) {
            $note = 'وضعیت حالت های استردادی به لغو شده تغییر یافت';

        } else if (in_array("irnicHolderChangeApproved", $contactStatuses)) {
            $note = 'irnicHolderChangeApproved';
        } else {
            $note = 'حالت های HolderChangeApproved و Rejected وجود ندارند';
        }
        $this->insertIntoIrnicTbl($msg['msgId'], $code, $msg['qCount'], $msg['msgIndex'], $note, $responseXml, $msg['qDate']);

        return $note;
    }

    public function sendAckRequest($msgId)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<epp xmlns="urn:ietf:params:xml:ns:epp-1.0">
    <command>
        <poll op="ack" msgID="' . $msgId . '"/>
        <clTRID>' . $this->trid . '</clTRID>

    </command>
</epp>';

        $this->call($xml);
    }

    public function dd($data)
    {
        echo "<pre>";
        print_r($data);
        echo "</pre>";
        die();
    }

}