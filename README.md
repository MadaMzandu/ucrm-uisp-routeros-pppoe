# UISP/UCRM REST API for Mikrotik PPPoE

This software is a REST PHP solution intended to integrate the UISP/UCRM billing
system with Mikrotik RouterOS devices for provisioning PPPoE service accounts.
Unlike other integration options this solution does not use the plugin
extensibility of UISP instead it uses the native webhook facility to allow real
time provisioning.

It is recommended to use the latest UISP/UCRM version available.

# Features:

1.  Supports multiple RouterOS devices as gateways

2.  Provides real time creation of service accounts

3.  Provides real time suspending / unsuspending (this should be an English
    word) of service accounts

4.  Provides real time migration of service accounts between profiles

5.  Allows real time migration of accounts between gateway devices

6.  Has self-managed IP address pool allowing persistent IP address assignment
    which is more practical for monitoring client devices than using the dynamic
    IP pool on the RouterOS devices

# Installation Instructions

## On Web Server

1.  Install files into a path or a virtualhost on PHP enabled web server

2.  Configure RouterOs username and password in config.php

3.  Make the json directory and contents writeable by your www user.

4.  Map site names to IP addresses in ‘json/gateways.json’

5.  Clean the IP address pool of default IP addresses - command:

\# \> json/ipaddr.json

6.  Generate new IP addresses using provided “ipgen” script - command:

\# ./ipgen 10.85.1

>   *This command will generate 10.85.1.0/24 into the pool.*

>   *Run command as many times to append /24's to the address pool*

>   *This gift horse command will only consume an ipv4 /24 in x.x.x notation so
>   be warned.*

6.  Remember to secure API url with access list especially if running on a
    publicly accessible webserver

## On Mikrotik RouterOs Device/s

1.  Create PPP profiles matching the names of UCRM service plans (including
    spaces if any)

2.  Create a profile named ‘disabled’ according to your disabling policy.

3.  Create API username and password that was configured on the webserver in
    config.php.

4.  Remember to limit API account access to IP address of your webserver. Can’t
    be too secure.

## On UISP

1.  In CRM Settings \>\> Webhook create an endpoint with the url to the above
    webserver path

2.  Specify only the service related webhook event types for this endpoint.

3.  Make sure endpoint url has ending “/” e.g. http://127.0.0.1:8080/api/ to
    avoid Apache/Nginx redirection.

4.  Disable SSL checking of endpoint if using self signed certificate or pure
    http

5.  Test the webhook by clicking the test button. Response should return a json
    response acknowledging the hook.

6.  In CRM Settings \>\> Other create three text Custom Attributes of service
    type as follows:

    PPPoE Username

    PPPoE Password

    PPPoE Site Name

7.  Disabling the client visibility of the PPPoE attributes is a good
    recommendation

8.  If one prefers to name these custom attributes differently, the
    corresponding config.php entries must be updated to reflect the new labels.
    Review the attribute key property that is sent in the webhook event.

# Usage

1.  At this point you should be able to add a service and provision the pppoe
    account details at the bottom of the service account

2.  PPPoE secrets are provisioned with a comment which helps to track the CRM
    assigned service id. This is because the webhook does not send the previous
    state of custom attributes. Do not edit these comments to avoid orphaned
    accounts.

3.  Review the webhook request logs until you are confident of your setup and
    usage

4.  Some webhook requests such as archiving are not applicable to the setup and
    will fail. This is normal behaviour.

5.  Webhooks will fail if you run out of IP addresses in the pool with relevant
    message

6.  You can resend webhooks that fail to provision the first time e.g. Web
    server was down or IP addresses were depleted when account was provisioned

# Commercial Assistance

Commercial remote installation assistance is available.

Requirements – Ubuntu 18.04 or 20.04 with Public IP address for remote
installation only, Apache with modphp or Nginx with php-fpm.

# Credits

This software uses or depends on the following software by these developers with
the greatest gratitude.

Ben Menking – RouterOS API

<https://github.com/BenMenking/routeros-api>

Ubiquiti - UISP/UCRM/UNMS

<https://ubnt.com>

Mikrotik - RouterOS

<https://mikrotik.com>
