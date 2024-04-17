<?php


namespace WHMCS\Module\Registrar\irnic;


trait Domain
{

    public $EXP_DATE_NOT_SET = 'notSet';

    public function parseDomainAvails(array $domainCheckResponse)
    {
        $domains = $domainCheckResponse['epp']['_c']['response']['_c']['resData']['_c']['domain:chkData']['_c']['domain:cd'];
        $domainAvails = [];
        foreach ($domains as $domain) {
            $domainName = $domain['_c']['domain:name']['_v'];
            $avail = $domain['_c']['domain:name']['_a']['avail'];
            $domainAvails[$domainName]['avail'] = $avail;
            if ($avail != 1 && isset($domain['_c']['domain:reson'])) {
                $domainAvails[$domainName]['reson'] = $domain['_c']['domain:reson']['_v'];
            }

        }

        return $domainAvails;
    }

    public function parseDomainInfo(array $domainInfoResponse)
    {

        $info = $domainInfoResponse['epp']['_c']['response']['_c']['resData']['_c']['domain:infData']['_c'];

        $expDate = $this->EXP_DATE_NOT_SET;
        if (isset($info['domain:exDate']['_v']))
            $expDate = $info['domain:exDate']['_v'];


        $nameServers = [];
        foreach ($info['domain:ns']['_c']['domain:hostAttr'] as $key=>$ns) {
            $nameServers['ns'.($key+1)]= $ns['_c']['domain:hostName']['_v'];
        }

        return [
            'message' => $this->parseResMessage($domainInfoResponse),
            'name' => $info['domain:name']['_v'],
            'roid' => $info['domain:roid']['_v'],
            'expDate' => $expDate,
            'crDate' => $info['domain:crDate']['_v'],
            'status' => $info['domain:status'],
            'contact' => $info['domain:contact'],
            'nameServers' => $nameServers,
            'upDate' => $info['domain:upDate']['_v'],
        ];

    }

    public function parseDomainCreate(array $domainCreateResponse)
    {
        $response = $domainCreateResponse['epp']['_c']['response']['_c']['resData']['_c']['domain:creData']['_c'];

        return [

            'message' => $this->parseResMessage($domainCreateResponse),
            'domain' => $response['domain:name']['_v'],
            'crDate' => $response['domain:crDate']['_v'],
            'exDate' => $response['domain:exDate']['_v'],
        ];
    }

    public function parseDomainRenew(array $domainRenewResponse)
    {
        $response = $domainRenewResponse['epp']['_c']['response']['_c'];
        $clTrId = $response['trID']['_c']['clTRID']['_v'];
        $svTrId = $response['trID']['_c']['svTRID']['_v'];
        $domainName = $response['resData']['_c']['domain:renData']['_c']['domain:name']['_v'];

        return [
            'message' => $this->parseResMessage($domainRenewResponse),
            'clTrid' => $clTrId,
            'svTrid' => $svTrId,
            'domain' => $domainName,
        ];

    }

    public function parseDomainTransfer(array $domainTransferResponse)
    {
        $response = $domainTransferResponse['epp']['_c']['response']['_c'];
        $clTrId = $response['trID']['_c']['clTRID']['_v'];
        $svTrId = $response['trID']['_c']['svTRID']['_v'];

        return [
            'message' => $this->parseResMessage($domainTransferResponse),
            'clTrid' => $clTrId,
            'svTrid' => $svTrId,
        ];
    }

    public function checkNumberOfDomains(array $domains)
    {
        return count($domains) <= 10;
    }


}