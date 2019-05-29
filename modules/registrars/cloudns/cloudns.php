<?php

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

require_once __DIR__ . '/lib/ClouDNS_SDK.php';
require_once __DIR__ . '/lib/domain_verificaton_rules.php';


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

        $tlds = cloudns_get_tlds();

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
        // "Sync" => "Sync",
        // "Resend RAA verification" => "ResendIRTPVerificationEmail",
        // "Get RAA status" => "GetRAAstatus",
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

function cloudns_GetEPPCode($params) {
    try {
        if ($params['Is sub user']) {
            return ['error' => 'This function is not available for sub users.'];
        }

        $api = new ClouDNS_SDK($params['User'], $params['Password'], (bool)$params['Is sub user']);
        $res = $api->domainsGetTransferCode($params['domainname']);

        logModuleCall($params['registrar'], __FUNCTION__, $params['domainname'], $res);

        if ($res['transfer_code']) {
            // If EPP Code is returned, return it for display to the end user
            return ['eppcode' => $res['transfer_code']];
        } else {
            // If EPP Code is not returned
            return ['error' => 'Can\'t obtain domain transfer code. Please contact support.'];
        }
    }
    catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

function cloudns_TransferDomain($params) {
    try {

        if ($params['Is sub user']) {
            return ['error' => 'This function is not available for sub users.'];
        }

        $ns_fields = [$params['ns1'], $params['ns2'], $params['ns3'], $params['ns4'], $params['ns5']];
        $nameservers = array_filter(array_map('trim', $ns_fields));
        $address = implode(', ', array_filter(array_map('trim', [$params['address1'], $params['address2']])));

        $generic_transfer_params = [
            'domain_name'           => $params['sld'],
            'tld'                   => $params['tld'],
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
            'transfer_code'         => $params['eppcode'],
            'registrant_type'       => false,
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
            'registrant_type_id'    => false,
            'publicity'             => false,
            'ns'                    => $nameservers,
            'kpp'                   => false,
            'passport_number'       => false,
            'passport_issued_by'    => false,
            'passport_issued_on'    => false,
        ];

        $tlds = cloudns_get_tlds();

        foreach ($tlds as $tld_re => $transfer_params_generator) {
            if (preg_match("/$tld_re/", $params['tld'])) {
                if (is_callable($transfer_params_generator)) {
                    $transfer_params = $transfer_params_generator($params, $generic_transfer_params);
                    break;
                }
                throw new Exception("Function \$transfer_params_generator() does not exist for {$params['tld']} TLD!");
            }
        }

        if (!$transfer_params) {
            $transfer_params = $generic_transfer_params;
        }

        $api = new ClouDNS_SDK($params['User'], $params['Password'], (bool)$params['Is sub user']);
        $res = call_user_func_array([$api, 'domainsTransferDomain'], $transfer_params);
        logModuleCall($params['registrar'], __FUNCTION__, $transfer_params, $res);

        if ($res['status'] != 'Success') {
            throw new Exception($res['statusDescription']);
        }
    }
    catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

function cloudns_ResendIRTPVerificationEmail($params) {
    try {

        if ($params['Is sub user']) {
            return ['error' => 'This function is not available for sub users.'];
        }

        $api = new ClouDNS_SDK($params['User'], $params['Password'], (bool)$params['Is sub user']);
        $res = $api->domainsResendRAAVerification($params['domainname']);
        logModuleCall($params['registrar'], __FUNCTION__, $params['domainname'], $res);

        if ($res['status'] != 'Success') {
            throw new Exception($res['statusDescription']);
        }
        else {
            return ['success' => true];
        }
    }
    catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

function cloudns_GetRAAstatus($params) {
    try {

        if ($params['Is sub user']) {
            return ['error' => 'This function is not available for sub users.'];
        }

        $api = new ClouDNS_SDK($params['User'], $params['Password'], (bool)$params['Is sub user']);
        $res = $api->domainsGetRAAStatus($params['domainname']);
        logModuleCall($params['registrar'], __FUNCTION__, $params['domainname'], $res);

    }
    catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}
