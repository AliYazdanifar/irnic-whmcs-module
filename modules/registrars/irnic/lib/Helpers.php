<?php


namespace WHMCS\Module\Registrar\irnic;


trait Helpers
{
    public function generateDomain(array $params)
    {
        $sld = $params['sld'];
        $tld = $params['tld'];
        return strtolower($sld . '.' . $tld);
    }

    public function generateNic(array $params)
    {
        $nicHandle = $params['additionalfields']['nichandle'];
        $nicAdmin = $params['additionalfields']['nicadmin'];
        $nicTech = $params['additionalfields']['nictech'];
        $nicBill = $params['additionalfields']['nicbill'];

        if ($nicAdmin == "") {
            $nicAdmin = $params["adminContact"];
        }
        if ($nicTech == "") {
            $nicTech = $params["technicalContact"];
        }
        if ($nicBill == "") {
            $nicBill = $params["billingContact"];
        }

        return [
            'nicHandle' => $nicHandle,
            'nicAdmin' => $nicAdmin,
            'nicTech' => $nicTech,
            'nicBill' => $nicBill,
        ];

    }

    public function checkResponseCode(array $response)
    {
        $res_code = $this->parseResCode($response);

        $this->addLog('res code = ' . $res_code);

        $msg = $this->parseResMessage($response);
        return ['code' => $res_code, 'message' => $msg];
    }

    public function getNicHandle($domain)
    {
        $domainInfo = $this->domainInfoRequest($domain);

        return $domainInfo['epp']['_c']['response']['_c']['resData']['_c']['domain:infData']['_c']['domain:contact'][0]['_v'];
    }

    public function parseResCode(array $irnicResultInArray)
    {
        return $irnicResultInArray['epp']['_c']['response']['_c']['result']['_a']['code'];
    }

    public function parseResMessage(array $irnicResultInArray)
    {
        $err = $irnicResultInArray['epp']['_c']['response']['_c']['result']['_c']['msg']['_v'];

        if ($exterr = $irnicResultInArray['epp']['_c']['response']['_c']['result']['_c']['extValue']['_c']['reason']['_v']) {
            $val = '';
            foreach ($irnicResultInArray['epp']['_c']['response']['_c']['result']['_c']['extValue']['_c']['value']['_c'] as $child) {
                $val = $child['_v'];

                if (!empty($val)) {
                    $err .= ' - ' . $val . ', ' . $exterr;
                    continue;
                }
            }

            if (empty($val)) {
                $err .= ' - ' . $exterr;
            }
        }

        $this->addLog('response message = ' . $err);

        return $err;
    }


    //poll helpers

    public function parseMsgQ(array $response)
    {
        $response = $response['epp']['_c']['response']['_c']['msgQ'];

        return [
            'msgId' => $response['_a']['id'],
            'qCount' => $response['_a']['count'],
            'qDate' => $response['_c']['qDate']['_v'],
            'msgIndex' => $response['_c']['msg']['_c']['index']['_v'],
            'msgNote' => $response['_c']['msg']['_c']['note']['_v'],
        ];
    }


}