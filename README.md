# Domain registrar module for WHMCS

## Introduction

WHMCS is a popular web hosting and automation solution, which includes billing and support options.

If you are using WHMCS for your hosting activities, you may interface ClouDNS.net with WHMCS domain functionality to easily order and manage domains from within WHMCS.

ClouDNS.net maintains a registrar module for WHMCS, which offers a great load of features to interface ClouDNS.net seamlessly from within WHMCS.

The following registrar core functionality is provided:

* register domains
* perform domain renewals
* allow viewing and changing of nameservers
* allow viewing and changing of glue records
* allow viewing and changing of WHOIS information of domains
* transfer domains
* obtain domain transfer code
* resend RAA verification email

Additionally, also these features are provided:

* sync of expiration date
* managing whois privacy protection
* managing domain registrar lock


### Installation

1. [Download](https://github.com/ClouDNS/whmcs-domain-registrar-module/archive/master.zip) ClouDNS module archive.
2. Unzip content of **modules/registrars/cloudns/** to **_<whmcs_root>_/modules/registrars/cloudns/**

### Configuration

1. Go to **Setup → Products/Services → Domain Registrars** section of the WHMCS admin area and activate **ClouDNS** module.
2. Configure ClouDNS module with your **User** ID and **Password**, set *Is sub user* if your user account is sub-account.
3. Press **Save Changes**.
