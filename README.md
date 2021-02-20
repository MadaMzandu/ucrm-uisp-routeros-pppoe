# UISP/UCRM webhook API for Mikrotik PPPoE

This software is intended to integrate UISP/UCRM billing software with Mikrotik
RouterOS devices to provision PPPoE based services.

It is recommended to use the latest UISP/UCRM version available

# Features:

1\. Supports multiple routeros gateways

2\. Provides real time account creation

3\. Provides time suspending / unsuspending of accounts

4\. Provides real time migration between profiles

5\. Allows real time migration of accounts between devices

6\. Has self-managed IP address pool allowing persistent ip addressing versus the
dynamic pool on the routeros devices

# Installation Instructions

## On Web Server

1\. Install files into a path or a virtualhost on web server

2\. Configure mikrotik username and password in config.php

3\. Configure site to ip address mappings in ‘json/gateways.json’

4\. Clear the ip address pool of test addresses - command:

\# \> json/ipaddr.json

5\. Generate new ip addresses - command:

\# ./ipgen 10.85.1

run command as many times to append /24's to the address pool

6\. Remember to secure url with access list permitting UISP host address

## On Mikrotik Device/s

1\. Create ppp profiles matching the names of UCRM service plans including spaces
if any

2\. Create profile named ‘disabled’ according to your disabling policy.

3\. Create api username and password that was configured in config.php.

4\. Limit api account access to ip address of your webserver for security

## On UISP

1\. In CRM Settings \>\> Webhook create an endpoint with the url to the above
webserver path

2\. Make sure endpoint url has ending “/” e.g. http://127.0.0.1:8080/api/ to
avoid redirection.

3\. Disable ssl checking if using self signed certificateor http

4\. Test the webhook by clicking the test button. Response should acknowledge the
hook.

5\. In CRM Settings \>\> Other create 3 x text Custom Attributes of service type
as follows:

PPPoE Username

PPPoE Password

PPPoE Site Name

7\. I recommend disabling the client visibility of the PPPoE attributes

6\. If you prefer to name these attributes differently, you must edit the
corresponding config.php entries.

# Notes on Usage

1\. At this point you should be able to add a service and provision the pppoe
account at the bottom.

2\. If you forget to provision the PPPoE account during service creation the
webhook will have no pppoe credentials and will fail.

3\. Review the webhook review log until you are confident of your setup and usage
