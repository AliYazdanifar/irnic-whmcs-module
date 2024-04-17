<?php

namespace WHMCS\Module\Registrar\irnic;

use Exception;
use WHMCS\Database\Capsule;

/**
 * Sample Registrar Module Simple API Client.
 *
 * A simple API Client for communicating with an external API endpoint.
 */
class ApiClient
{
    use XmlParser, Helpers, Contact, Domain;

    private function curlRequest($xml)
    {

        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $headers = [
                'Authorization: Bearer ' . $this->authToken,

            ];
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//            curl_setopt($ch, CURLOPT_SSLCERT, "$this->certificate");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_USERAGENT, "IRNIC_EPP_Client_Sample");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_URL, "$this->serverURL");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);

            $response = curl_exec($ch);

            if (curl_errno($ch) !== 0) {
                $response = '
					<epp xmlns="urn:ietf:params:xml:ns:epp-1.0" >
						<response>
							<result code="-500" >
								<msg>Connection error: ' . curl_error($ch) . '</msg>
							</result>
						</response>
					</epp>
				';
            }

            curl_close($ch);
            return $response;

        } catch (Exception $e) {
            $msg = 'catch Error curlRequest : ' . $e->getMessage();
            $this->addLog($msg);
            $response = '<epp xmlns="urn:ietf:params:xml:ns:epp-1.0" >
						<response>
							<result code="-500" >
								<msg>' . $msg . '</msg>
							</result>
						</response>
					</epp>
				';

            return $response;
        }
    }
}