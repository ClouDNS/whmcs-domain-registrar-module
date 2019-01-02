<?php

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

require_once __DIR__ . '/lib/cloudns-php-sdk/ClouDNS_SDK.php';


function cloudns_MetaData() {
    return [
        'DisplayName' => 'ClouDNS',
        'APIVersion' => '1.1',
    ];
}

function cloudns_getConfigArray() {
    return [
        'FriendlyName' => [
            'Type' => 'System',
            'Value' => 'ClouDNS',
        ],
        'User' => [
            'Type' => 'text',
            'Size' => '25',
            'Default' => '',
            'Description' => 'API user ID or sub user ID',
        ],
        'Password' => [
            'Type' => 'password',
            'Size' => '25',
            'Default' => '',
            'Description' => 'The password for user ID or sub user ID',
        ],
        'Is sub user' => [
            'Type' => 'yesno',
            'Description' => 'Auth ID is sub user id',
        ],
    ];
}

function cloudns_RegisterDomain($params) {
    try {
        $tlds = [

            '^(fr|re|pm|tf|wf|yt)$' => function($domain_params, $generic_register_params) {
                $register_params = $generic_register_params;

                # registrant_type
                $register_params['registrant_type'] = strtoupper($domain_params['additionalfields']['Legal Type']);
                # birth_date
                $register_params['birth_date'] = $domain_params['additionalfields']['Birthdate'];
                # birth_cc
                $register_params['birth_cc'] = $domain_params['additionalfields']['Birthplace Country'];
                # birth_city
                $register_params['birth_city'] = $domain_params['additionalfields']['Birthplace City'];
                # birth_zip
                $register_params['birth_zip'] = $domain_params['additionalfields']['Birthplace Postcode'];
                # publication
                $register_params['publication'] = 1;
                # vat
                $register_params['vat'] = $domain_params['additionalfields']['VAT Number'];
                # siren
                $register_params['siren'] = $domain_params['additionalfields']['SIRET Number'];
                # duns
                $register_params['duns'] = $domain_params['additionalfields']['DUNS Number'];
                # trademark
                $register_params['trademark'] = $domain_params['additionalfields']['Trademark Number'];

                return $register_params;
            },

            '^it$' => function($domain_params, $generic_register_params) {
                $register_params = $generic_register_params;

                # registrant_type
                $type = array_search(
                    $domain_params['additionalfields']['Legal Type'],
                    [
                        "Italian and foreign natural persons",
                        "Companies/one man companies",
                        "Freelance workers/professionals",
                        "non-profit organizations",
                        "public organizations",
                        "other subjects",
                        "non natural foreigners",
                    ]
                );
                $register_params['registrant_type'] = $type + 1;
                # code
                $register_params['code'] = $domain_params['additionalfields']['Tax ID'];
                # publicity
                $register_params['publicity'] = ($domain_params['additionalfields']['Publish Personal Data'] ? 1 : 0 );
                # birth_cc
                $register_params['birth_cc'] = $domain_params['countrycode'];

                return $register_params;
            },

            '^ru$' => function($domain_params, $generic_register_params) {
                $register_params = $generic_register_params;

                # registrant_type
                $register_params['registrant_type'] = ($domain_params['additionalfields']['Registrant Type'] == 'ORG' ? 'ORG' : 'PRS');
                # birth_date
                $date = date_parse($domain_params['additionalfields']['Individuals Birthday']);
                $register_params['birth_date'] = "{$date['day']}.{$date['month']}.{$date['year']}";
                # code
                $register_params['code'] = $domain_params['additionalfields']['Russian Organizations Taxpayer Number 1'];
                # kpp
                $register_params['kpp'] = $domain_params['additionalfields']['Russian Organizations Territory-Linked Taxpayer Number 2'];
                # passport_number
                $register_params['passport_number'] = $domain_params['additionalfields']['Individuals Passport Number'];
                # passport_issued_by
                $register_params['passport_issued_by'] = $domain_params['additionalfields']['Individuals Passport Issuer'];
                # passport_issued_on
                $date = date_parse($domain_params['additionalfields']['Individuals Passport Issue Date']);
                $register_params['passport_issued_on'] = "{$date['day']}.{$date['month']}.{$date['year']}";

                return $register_params;
            },

            '^ca$' => function($domain_params, $generic_register_params) {
                $register_params = $generic_register_params;

                $types = [
                    'Corporation' => 'CCO',
                    'Canadian Citizen' => 'CCT',
                    'Permanent Resident of Canada' => 'RES',
                    'Government' => 'GOV',
                    'Canadian Educational Institution' => 'EDU',
                    'Canadian Unincorporated Association' => 'ASS',
                    'Canadian Hospital' => 'HOP',
                    'Partnership Registered in Canada' => 'PRT',
                    'Trade-mark registered in Canada' => 'TDM',
                    'Canadian Trade Union' => 'TRD',
                    'Canadian Political Party' => 'PLT',
                    'Canadian Library Archive or Museum' => 'LAM',
                    'Trust established in Canada' => 'TRS',
                    'Aboriginal Peoples' => 'ABO',
                    'Legal Representative of a Canadian Citizen' => 'LGR',
                    'Official mark registered in Canada' => 'OMK',
                ];
                $register_params['registrant_type'] = $types[$domain_params['additionalfields']['Legal Type']];

                return $register_params;
            },

            '^es$' => function($domain_params, $generic_register_params) {
                $register_params = $generic_register_params;

                # registrant_type
                $register_params['registrant_type'] = $domain_params['additionalfields']['Legal Form'];
                # registrant_type_id
                $types = [
                    'Other Identification' => 0,
                    'Tax Identification Number' => 1,
                    'Tax Identification Code' => 1,
                    'Foreigner Identification Number' => 3,
                ];
                $register_params['registrant_type_id'] = $types[$domain_params['additionalfields']['ID Form Type']];
                # code
                $register_params['code'] = $domain_params['additionalfields']['ID Form Number'];

                return $register_params;
            },

            '^(com|net).au$' => function($domain_params, $generic_register_params) {
                $register_params = $generic_register_params;

                # registrant_type
                $register_params['registrant_type'] = $domain_params['additionalfields']['Registrant ID Type'];
                # registrant_type_id
                $register_params['registrant_type_id'] = $domain_params['additionalfields']['Registrant ID'];
                # registrant_policy
                $register_params['registrant_policy'] = ($domain_params['additionalfields']['Eligibility Reason'] == "Domain name is an Exact Match Abbreviation or Acronym of your Entity or Trading Name." ? 1 : 2);
                # trademark
                $register_params['trademark'] = $domain_params['additionalfields']['Eligibility Type'];

                return $register_params;
            },

            '(^|\.)br$' => function($domain_params, $generic_register_params) {
                $register_params = $generic_register_params;

                # Not implemented by WHMCS:
                # registrant_type
                # code

                return $register_params;
            },

            '(^|\.)cn$' => function($domain_params, $generic_register_params) {
                $register_params = $generic_register_params;

                # registrant_type
                $register_params['registrant_type'] = ($domain_params['additionalfields']['cnhosting'] ? 'cnhosting' : 'nocnhosting');

                # Not implemented by WHMCS:
                # code

                return $register_params;
            },

            '(^|\.)ro$' => function($domain_params, $generic_register_params) {
                $register_params = $generic_register_params;

                # registrant_type
                $register_params['registrant_type'] = $domain_params['additionalfields']['Registrant Type'];
                # registrant_type_id
                $register_params['registrant_type_id'] = $domain_params['additionalfields']['Registration Number'];
                # code
                $register_params['code'] = $domain_params['additionalfields']['CNPFiscalCode'];

                return $register_params;
            },
        ];

        $ns_fields = [$params['ns1'], $params['ns2'], $params['ns3'], $params['ns4'], $params['ns5']];
        $nameservers = array_filter(array_map('trim', $ns_fields));
        $address = implode(', ', array_filter(array_map('trim', [$params['address1'], $params['address2']])));

        # The order should match params order from domainsRegisterNewDomain()
        $generic_register_params = [
            'domain_name'           => $params['sld'],
            'tld'                   => $params['tld'],
            'period'                => $params['regperiod'],
            'mail'                  => $params['email'],
            'name'                  => $params['fullname'],
            'company'               => $params['companyname'],
            'address'               => $address,
            'city'                  => $params['city'],
            'state'                 => $params['state'],
            'zip'                   => $params['postcode'],
            'country'               => $params['countrycode'],
            'telnocc'               => $params['phonecc'],
            'telno'                 => $params['phonenumber'],
            'faxnocc'               => false,
            'faxno'                 => false,
            'ns'                    => $nameservers,
            'registrant_type'       => false,
            'registrant_type_id'    => false,
            'registrant_policy'     => false,
            'birth_date'            => false,
            'birth_cc'              => false,
            'birth_city'            => false,
            'birth_zip'             => false,
            'publication'           => false,
            'vat'                   => false,
            'siren'                 => false,
            'duns'                  => false,
            'trademark'             => false,
            'waldec'                => false,
            'registrant_type_other' => false,
            'privacy_protection'    => false,
            'code'                  => false,
            'publicity'             => false,
            'kpp'                   => false,
            'passport_number'       => false,
            'passport_issued_by'    => false,
            'passport_issued_on'    => false,
        ];

        foreach ($tlds as $tld_re => $register_params_generator) {
            if (preg_match("/$tld_re/", $params['tld'])) {
                if (is_callable($register_params_generator)) {
                    $register_params = $register_params_generator($params, $generic_register_params);
                    break;
                }
                throw new Exception("Function \$register_params_generator() does not exist for {$params['tld']} TLD!");
            }
        }

        if (!$register_params) {
            $register_params = $generic_register_params;
        }

        $api = new ClouDNS_SDK($params['User'], $params['Password'], (bool)$params['Is sub user']);
        $res = call_user_func_array([$api, 'domainsRegisterNewDomain'], $register_params);
        logModuleCall($params['registrar'], __FUNCTION__, $register_params, $res);

        if ($res['status'] != 'Success') {
            throw new Exception($res['statusDescription']);
        }

    }
    catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

function cloudns_RenewDomain($params) {
    try {
        $api = new ClouDNS_SDK($params['User'], $params['Password'], (bool)$params['Is sub user']);
        $res = $api->domainsRenewDomain($params['domainname'], $params['regperiod']);
        logModuleCall($params['registrar'], __FUNCTION__, [$params['domainname'], $params['regperiod']], $res);

        if ($res['status'] != 'Success') {
            throw new Exception($res['statusDescription']);
        }
    }
    catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

function cloudns_GetNameservers($params) {
    try {

        $api = new ClouDNS_SDK($params['User'], $params['Password'], (bool)$params['Is sub user']);
        $res = $api->domainsListNameServers($params['domainname']);
        logModuleCall($params['registrar'], __FUNCTION__, [$params['domainname']], $res);

        if (isset($res['status'])) {
            throw new Exception($res['statusDescription']);
        }

        foreach ($res as $ns_num => $ns) {
            $response['ns' . $ns_num] = $ns;
        }
        return $response;
    }
    catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

function cloudns_SaveNameservers($params) {
    try {
        for ($i=1; $i <= 5; $i++) {
            if ($params['ns' . $i]) {
                $new_ns[] = $params['ns' . $i];
            }
        }
        $api = new ClouDNS_SDK($params['User'], $params['Password'], (bool)$params['Is sub user']);
        $res = $api->domainsModifyNameServers($params['domainname'], $new_ns);
        logModuleCall($params['registrar'], __FUNCTION__, [$params['domainname'], $new_ns], $res);

        if ($res['status'] != 'Success') {
            throw new Exception($res['statusDescription']);
        }

        return ['success' => true];

    }
    catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

function cloudns_GetContactDetails($params) {
    try {
        $api = new ClouDNS_SDK($params['User'], $params['Password'], (bool)$params['Is sub user']);
        $roles = $api->domainsGetContacts($params['domainname']);
        logModuleCall($params['registrar'], __FUNCTION__, $params['domainname'], $roles);

        $contacts = [];
        foreach ($roles as $role) {
            $contacts[strtoupper($role['type'])] = [
                'Name' => $role['name'],
                'Company' => $role['company'],
                'Email' => $role['mail'],
                'Address 1' => $role['address1'],
                'Address 2' => $role['address2'],
                'Address 3' => $role['address3'],
                'City' => $role['city'],
                'State' => $role['state'],
                'Zip' => $role['zip'],
                'Country' => $role['country'],
                'Phone Number' => $role['telnocc'] . $role['telno'],
                'Fax Number' => $role['faxnocc'] . $role['faxno'],
            ];
        }

        return $contacts;

    }
    catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

function cloudns_SaveContactDetails($params) {
    try {
        $api = new ClouDNS_SDK($params['User'], $params['Password'], (bool)$params['Is sub user']);

        foreach ($params['contactdetails'] as $role => $c) {

            $address = implode(', ', array_filter(array_map('trim', [$c['Address 1'], $c['Address 2'], $c['Address 3']])));

            if (preg_match('/^\+(\d+)\.(\d+)$/', $c['Phone Number'], $phone_matches)) {
                $telnocc = $phone_matches[1];
                $telno = $phone_matches[2];
            }

            if (preg_match('/^\+(\d+)\.(\d+)$/', $c['Fax Number'], $fax_matches)) {
                $faxnocc = $fax_matches[1];
                $faxno = $fax_matches[2];
            }

            $args = [
                $params['domainname'],
                strtolower($role),
                $c['Email'],
                $c['Name'],
                $c['Company'],
                $c['Address 1'],
                $c['City'],
                $c['State'],
                $c['Zip'],
                $c['Country'],
                $telnocc,
                $telno,
                $c['Address 2'],
                $c['Address 3'],
                $faxnocc,
                $faxno,
            ];

            $res = call_user_func_array([$api, 'domainsModifyContacts'], $args);
            logModuleCall($params['registrar'], __FUNCTION__, $args, $res);

            if ($res['status'] != 'Success') {
                throw new Exception($res['statusDescription']);
            }
        }

    }
    catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

function cloudns_RegisterNameserver($params) {
    try {
        $api = new ClouDNS_SDK($params['User'], $params['Password'], (bool)$params['Is sub user']);
        $ns = str_replace('.' . $params['domainname'],"", $params['nameserver']);
        $res = $api->domainsAddChildNameServers($params['domainname'], $ns, $params['ipaddress']);
        logModuleCall($params['registrar'], __FUNCTION__, [$params['domainname'], $ns, $params['ipaddress']], $res);

        if ($res['status'] != 'Success') {
            throw new Exception($res['statusDescription']);
        }
    }
    catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

function cloudns_ModifyNameserver($params) {
    try {
        $api = new ClouDNS_SDK($params['User'], $params['Password'], (bool)$params['Is sub user']);
        $ns = str_replace('.' . $params['domainname'],"", $params['nameserver']);
        $res = $api->domainsModifyChildNameServers($params['domainname'], $ns, $params['currentipaddress'], $params['newipaddress']);
        logModuleCall($params['registrar'], __FUNCTION__, [$params['domainname'], $ns, $params['currentipaddress'], $params['newipaddress']], $res);

        if ($res['status'] != 'Success') {
            throw new Exception($res['statusDescription']);
        }
    }
    catch (\Exception $e) {
        return ['error' => 'Error: ' . $e->getMessage()];
    }
}

function cloudns_DeleteNameserver($params) {
    try {
        $api = new ClouDNS_SDK($params['User'], $params['Password'], (bool)$params['Is sub user']);
        $child_ns = $api->domainsGetChildNameServers($params['domainname']);
        logModuleCall($params['registrar'], __FUNCTION__, $params['domainname'], $child_ns);
        $ns_to_remove = str_replace('.' . $params['domainname'],"", $params['nameserver']);

        foreach ($child_ns as $ns) {
            if ($ns['host'] == $params['nameserver']) {
                $res = $api->domainsDeleteChildNameServers($params['domainname'], $ns_to_remove, $ns['ip']);
                logModuleCall($params['registrar'], __FUNCTION__, [$params['domainname'], $ns_to_remove, $ns['ip']], $res);

                if ($res['status'] != 'Success') {
                    throw new Exception($res['statusDescription']);
                }
            }
        }
    }
    catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

function cloudns_CustomGetChildNameservers($params) {
    try {
        $api = new ClouDNS_SDK($params['User'], $params['Password'], (bool)$params['Is sub user']);
        $child_ns = $api->domainsGetChildNameServers($params['domainname']);
        logModuleCall($params['registrar'], __FUNCTION__, $params['domainname'], $child_ns);

        if ($child_ns['status']) {
            return ['error' => "<br /><h4>List of child nameservers:</h4><ul><li>{$child_ns['statusDescription']}</li></ul>"];
        }

        foreach ($child_ns as $ns) {
            $result .= "<li>{$ns['host']} ({$ns['ip']})\n";
        }

        return ['error' => "<br /><h4>List of child nameservers:</h4><ul> $result </ul>"];

    }
    catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

function cloudns_Sync($params) {
    try {
        $api = new ClouDNS_SDK($params['User'], $params['Password'], (bool)$params['Is sub user']);
        $domain = $api->domainsDomainInfo($params['domainname']);
        $response = [
            'expirydate' => date("Y-m-d", $domain['expire_on']),
            'active' => (bool) $domain['status'],
            'expired' => (bool) ($domain['expire_on'] < time()),
        ];
        return $response;
    }
    catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

function cloudns_AdminCustomButtonArray($params) {
    return [
        // "Show Child NS" => "CustomGetChildNameservers",
        "Sync" => "Sync",
    ];
}

function cloudns_ClientArea($params) {
    return cloudns_CustomGetChildNameservers($params)['error'];
}

function cloudns_GetRegistrarLock($params) {
    try {
        $api = new ClouDNS_SDK($params['User'], $params['Password'], (bool)$params['Is sub user']);
        $domain = $api->domainsDomainInfo($params['domainname']);
        logModuleCall($params['registrar'], __FUNCTION__, $params['domainname'], $domain);
        return ($domain['transfer_lock_status'] ? 'locked' : 'unlocked');
    }
    catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

function cloudns_SaveRegistrarLock($params) {
    try {
        $api = new ClouDNS_SDK($params['User'], $params['Password'], (bool)$params['Is sub user']);
        $domain = $api->domainsDomainInfo($params['domainname']);
        logModuleCall($params['registrar'], __FUNCTION__, $params['domainname'], $domain);

        if ($domain['transfer_lock_available']) {
            $new_status = ($params['lockenabled'] == 'unlocked' ? 0 : 1);
            $res = $api->domainsModifyTransferLock($params['domainname'], $new_status);
            logModuleCall($params['registrar'], __FUNCTION__, [$params['domainname'], $new_status], $res);
            if ($res['status'] != 'Success') {
                throw new Exception($res['statusDescription']);
            }
        }
    }
    catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

function cloudns_IDProtectToggle($params) {
    try {
        $api = new ClouDNS_SDK($params['User'], $params['Password'], (bool)$params['Is sub user']);
        $domain = $api->domainsDomainInfo($params['domainname']);
        logModuleCall($params['registrar'], __FUNCTION__, $params['domainname'], $domain);

        if ($domain['privacy_protection_available']) {
            $new_status = ($params['protectenable'] ? 1 : 0);
            $res = $api->domainsModifyPrivacyProtection($params['domainname'], $new_status);
            logModuleCall($params['registrar'], __FUNCTION__, [$params['domainname'], $new_status], $res);
            if ($res['status'] != 'Success') {
                throw new Exception($res['statusDescription']);
            }
        }
    }
    catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}
