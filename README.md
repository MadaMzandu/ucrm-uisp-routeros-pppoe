# UISP/UCRM REST API for Mikrotik PPPoE + Static DHCP

Many thanks to all the people that are testing and providing feeback. Because
of your input we are making progress. This branch includes some of your
valuable input.

# New Features

1. Now supports static DHCP as well as PPPoE.

2. Now has a backend for IP Management and future capabilities

3. Now understands ipv4 cidr prefixes so pools can be defined in x.x.x.x/xx notation.

# Introdution

This is a REST PHP script aimed at integrating the Ubiquiti UISP/UCRM billing
system with Mikrotik RouterOS devices for PPPoE services. Unlike other 
integration options this solution does not use the plugin extensibility of 
UISP but instead it uses the native webhook facility to provide real time 
account provisioning.

It is recommended to use the latest UISP/UCRM version available.

# Features:

1.  Supports multiple RouterOS devices as gateways

2.  Provides real time creation of service accounts

3.  Provides real time suspending / unsuspending (this should be an English
    word) of service accounts.

4.  Provides real time migration of service accounts between service plans.

5.  Allows real time migration of accounts between gateway devices.

6.  Has self-managed IP address pool allowing persistent IP address assignment
    which is more practical for monitoring client devices than using the dynamic
    IP pool on the RouterOS devices

# Installation Instructions

## Backup Old Configuration

If upgrading from the legacy version backup the json directory so that you can
roll back if something goes wrong.

## PHP Dependancies

The following php dependancies are required, on ubuntu/debian:

\# sudo apt install php-sqlite3 php-curl

## On Web Server

1.  Install files into a path or a virtualhost on PHP enabled web server

2.  Configure RouterOs username and password in config.php

3.  Make the the data directory and contents writeable by your www user.

4.  Map device names to IP addresses in ‘json/devices.json’ and

5.  Add dhcp address ranges for each device. You can add any number of comma 
    separated x.x.x.x/xx prefixes. PPPoE address pool is in 
    json/pppoe_pool.json. You can also add any number of x.x.x.x/xx prefixes
    for pppoe.

6.  Remember to secure API url with access list especially if running on a
    publicly accessible webserver

## On Mikrotik RouterOs Device/s

1.  Create PPP profiles matching the names of UCRM service plans (including
    spaces if any)

2.  Create a PPP profile named ‘disabled’ according to your disabling policy. 
    You can also edit config.php to change the name of the disabled profile.

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

>    *PPPoE Username*

>    *PPPoE Password*

>    *Device Name*

7.  For dhcp also add:

>     *DHCP MAC Address*

8.  Disabling the client visibility of the PPPoE attributes is a good
    recommendation

9.  If one prefers to name these custom attributes differently, the
    corresponding config.php entries must be updated to reflect the new labels.
    Review the attribute key property that is sent in the webhook event. Recommend 
    to skip this until setup is confirmed to be working.

# Usage

1.  At this point you should be able to add a service and provision the pppoe
    account or DHCP leaese details at the bottom of the service account page.

2.  Accounts are provisioned with a comment which helps to track the CRM
    assigned service id. This is because in some cases the webhook does not send the 
    previous state of custom attributes. Do not edit these comments to avoid 
    orphaned accounts.

3.  Review the webhook request logs until you are confident of your setup and
    usage

4.  Some webhook requests are not applicable to the setup and will fail. This 
    is normal behaviour.

5.  Webhooks will fail with relevant message if you run out of IP addresses in 
    the pool.

6.  You can resend webhooks that fail to provision the first time e.g. Web
    server was down or IP addresses were depleted when account was provisioned

# Commercial Installation Assistance

Commercial remote installation assistance is available.

<https://columbus-inet-services.company.site/UISP-UCRM-Mikrotik-PPPoE-Integration-p300849115>

Requirements – Ubuntu 18.04/20.04 + remote access for installation
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
