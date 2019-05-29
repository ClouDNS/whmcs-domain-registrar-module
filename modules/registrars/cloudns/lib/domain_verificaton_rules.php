<?php

function cloudns_get_tlds() {
    return  [
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
}
