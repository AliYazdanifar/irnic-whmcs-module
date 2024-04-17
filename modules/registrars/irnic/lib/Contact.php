<?php


namespace WHMCS\Module\Registrar\irnic;


trait Contact
{

    public function parseContactsAvail(array $contactCheckResponse)
    {

        $contacts = $contactCheckResponse['epp']['_c']['response']['_c']['resData']['_c']['contact:chkData']['_c']['contact:cd'];
        $avilContacts = [];
        foreach ($contacts as $contact) {
            $avail = $contact['contact:id']['_a']['avail'];
            $contactId = $contact['contact:id']['_v'];

            $avilContacts[$contactId] = [
                'avail' => $avail
            ];

            if ($avail == 1) {//If avail ==1, it means that this contact ID exists in Irnic

                $positions = $contact['contact:position'];

                foreach ($positions as $position) {

                    $type = $position['_a']['type'];
                    $allowed = $position['_a']['allowed'];
                    $avilContacts[$contactId]['positions'][$type] = $allowed;

                }

            }

        }

        return $avilContacts;
    }

    public function parseContactsAvailGroup(array $contactCheckResponse)
    {

        $contacts = $contactCheckResponse['epp']['_c']['response']['_c']['resData']['_c']['contact:chkData']['_c']['contact:cd'];
        $avilContacts = [];
        foreach ($contacts as $contact) {
            $avail = $contact['_c']['contact:id']['_a']['avail'];
            $contactId = $contact['_c']['contact:id']['_v'];

            $avilContacts[$contactId] = [
                'avail' => $avail
            ];

            if ($avail == 1) {//If avail ==1, it means that this contact ID exists in Irnic

                $positions = $contact['_c']['contact:position'];

                foreach ($positions as $position) {

                    $type = $position['_a']['type'];
                    $allowed = $position['_a']['allowed'];
                    $avilContacts[$contactId]['positions'][$type] = $allowed;

                }

            }

        }

        return $avilContacts;
    }

}

