# PLUGIN CONFIG

Example of `$plugin_config` on playSMS version 1.3.1:

```
Array
(
    [site] => Array
        (
            [default_config] => Array
                (
                    [enable_register] => 0
                    [enable_forgot] => 1
                    [enable_logo] => 0
                    [logo_replace_title] => 0
                    [logo_url] => 
                )

            [site_config] => Array
                (
                )

        )

    [ar_SA] => Array
        (
            [title] => Arabic (Saudi Arabia)
        )

    [ca_ES] => Array
        (
            [title] => Catalan (Spain)
        )

    [da_DK] => Array
        (
            [title] => Dansk (Danmark)
        )

    [de_DE] => Array
        (
            [title] => Deutsch (Deutschland)
        )

    [en_US] => Array
        (
            [title] => English (United States)
        )

    [es_VE] => Array
        (
            [title] => Spanish (Venezuela)
        )

    [fr_FR] => Array
        (
            [title] => French (France)
        )

    [id_ID] => Array
        (
            [title] => Indonesian (Indonesia)
        )

    [nb_NO] => Array
        (
            [title] => Norwegian Bokmål (Norway)
        )

    [pt_BR] => Array
        (
            [title] => Portuguese (Brazil)
        )

    [pt_PT] => Array
        (
            [title] => Portuguese (Portugal)
        )

    [ru_RU] => Array
        (
            [title] => Russian (Russia)
        )

    [zh_CN] => Array
        (
            [title] => Chinese (China)
        )

    [blocked] => Array
        (
            [name] => blocked
            [_smsc_config_] => Array
                (
                )

        )

    [bulksms] => Array
        (
            [name] => bulksms
            [username] => playsms
            [password] => playsms
            [module_sender] => PlaySMS
            [send_url] => http://bulksms.vsms.net:5567/eapi
            [additional_param] => routing_group=1&repliable=0
            [datetime_timezone] => 
            [_smsc_config_] => Array
                (
                )

        )

    [clickatell] => Array
        (
            [name] => clickatell
            [api_id] => 123456
            [username] => playsms
            [password] => playsms
            [module_sender] => PlaySMS
            [send_url] => https://api.clickatell.com/http
            [additional_param] => deliv_ack=1&callback=3
            [datetime_timezone] => 
            [_smsc_config_] => Array
                (
                    [api_id] => API ID
                    [username] => Username
                    [password] => Password
                    [send_url] => Clickatell API URL
                    [additional_param] => Additional URL parameter
                    [module_sender] => Module sender ID
                    [datetime_timezone] => Module timezone
                )

        )

    [dev] => Array
        (
            [name] => dev
            [enable_incoming] => 1
            [enable_outgoing] => 1
            [_smsc_config_] => Array
                (
                )

        )

    [gammu] => Array
        (
            [name] => gammu
            [sms_receiver] => 
            [path] => /var/spool/gammu
            [dlr] => 1
            [_smsc_config_] => Array
                (
                    [sms_receiver] => Receiver number
                    [path] => Spool folder
                )

        )

    [generic] => Array
        (
            [name] => generic
            [default_url] => http://example.api.url/handler.php?user={GENERIC_API_USERNAME}&pwd={GENERIC_API_PASSWORD}&sender={GENERIC_SENDER}&msisdn={GENERIC_TO}&message={GENERIC_MESSAGE}
            [default_callback_url] => http://localhost/playsms/plugin/gateway/generic/callback.php
            [url] => http://example.api.url/handler.php?user={GENERIC_API_USERNAME}&pwd={GENERIC_API_PASSWORD}&sender={GENERIC_SENDER}&msisdn={GENERIC_TO}&message={GENERIC_MESSAGE}
            [callback_url] => http://localhost/playsms/plugin/gateway/generic/callback.php
            [_smsc_config_] => Array
                (
                    [url] => Generic send SMS URL
                    [callback_url] => Callback URL
                    [api_username] => API username
                    [api_password] => API password
                    [module_sender] => Module sender ID
                    [datetime_timezone] => Module timezone
                )

        )

    [infobip] => Array
        (
            [name] => infobip
            [username] => 
            [password] => 
            [module_sender] => 
            [send_url] => http://api.infobip.com/api/v3
            [additional_param] => 
            [datetime_timezone] => 
            [dlr_nopush] => 1
            [_smsc_config_] => Array
                (
                )

        )

    [jasmin] => Array
        (
            [name] => jasmin
            [default_url] => https://127.0.0.1:1401/send
            [default_callback_url] => http://localhost/playsms/plugin/gateway/jasmin/callback.php
            [url] => https://127.0.0.1:1401/send
            [callback_url] => http://localhost/playsms/plugin/gateway/jasmin/callback.php
            [_smsc_config_] => Array
                (
                    [url] => Jasmin send SMS URL
                    [callback_url] => Callback URL
                    [api_username] => API username
                    [api_password] => API password
                    [module_sender] => Module sender ID
                    [datetime_timezone] => Module timezone
                )

        )

    [kannel] => Array
        (
            [name] => kannel
            [bearerbox_host] => localhost
            [sendsms_host] => localhost
            [sendsms_port] => 13131
            [dlr_mask] => 27
            [playsms_web] => http://localhost/playsms
            [admin_host] => localhost
            [admin_port] => 13000
            [local_time] => 0
            [_smsc_config_] => Array
                (
                    [username] => Username
                    [password] => Password
                    [module_sender] => Module sender ID
                    [module_timezone] => Module timezone
                    [bearerbox_host] => Bearerbox hostname or IP
                    [sendsms_host] => Send SMS hostname or IP
                    [sendsms_port] => Send SMS port
                    [dlr_mask] => DLR mask
                    [additional_param] => Additional URL parameter
                    [playsms_web] => playSMS web URL
                )

        )

    [nexmo] => Array
        (
            [name] => nexmo
            [url] => https://rest.nexmo.com/sms/json
            [api_key] => 12345678
            [api_secret] => 87654321
            [module_sender] => playSMS
            [datetime_timezone] => 
            [_smsc_config_] => Array
                (
                    [api_key] => API key
                    [api_secret] => API secret
                    [module_sender] => Module sender ID
                    [datetime_timezone] => Module timezone
                )

        )

    [openvox] => Array
        (
            [name] => openvox
            [gateway_port] => 80
            [_smsc_config_] => Array
                (
                    [gateway_host] => Gateway host
                    [gateway_port] => Gateway port
                    [username] => Username
                    [password] => Password
                )

        )

    [playnet] => Array
        (
            [name] => playnet
            [poll_interval] => 2
            [poll_limit] => 400
            [_smsc_config_] => Array
                (
                    [local_playnet_username] => Local playnet username
                    [local_playnet_password] => Local playnet password
                    [remote_on] => Remote is on
                    [remote_playsms_url] => Remote playSMS URL
                    [remote_playnet_smsc] => Remote playnet SMSC name
                    [remote_playnet_username] => Remote playnet username
                    [remote_playnet_password] => Remote playnet password
                    [sendsms_username] => Send SMS from remote using local username
                    [module_sender] => Module sender ID
                    [module_timezone] => Module timezone
                )

        )

    [smstools] => Array
        (
            [name] => smstools
            [default_queue] => /var/spool/sms
            [_smsc_config_] => Array
                (
                    [sms_receiver] => Receiver number
                    [queue] => Queue directory
                )

        )

    [telerivet] => Array
        (
            [name] => telerivet
            [url] => https://api.telerivet.com/
            [api_key] => 12345678
            [project_id] => abc123cde456
            [status_url] => https://localhost/plugin/gateway/telerivet/callback.php
            [status_secret] => myS3cr3t
            [_smsc_config_] => Array
                (
                )

        )

    [twilio] => Array
        (
            [name] => twilio
            [url] => https://api.twilio.com
            [callback_url] => http://localhost/playsms/plugin/gateway/twilio/callback.php
            [account_sid] => 12345678
            [auth_token] => 87654321
            [module_sender] => +10000000000
            [datetime_timezone] => 
            [_smsc_config_] => Array
                (
                    [account_sid] => Account SID
                    [auth_token] => Auth Token
                    [module_sender] => Module sender ID
                    [datetime_timezone] => Module timezone
                )

        )

    [uplink] => Array
        (
            [name] => uplink
            [master] => http://playsms.master.url
            [username] => 
            [token] => 
            [module_sender] => 
            [path] => /var/spool/playsms
            [additional_param] => 
            [datetime_timezone] => 
            [try_disable_footer] => 0
            [_smsc_config_] => Array
                (
                    [master] => Master URL
                    [username] => Webservice username
                    [token] => Webservice token
                    [additional_param] => Additional URL parameter
                    [module_sender] => Module sender ID
                    [datetime_timezone] => Module timezone
                )

        )

    [credit] => Array
        (
            [db_table] => playsms_featureCredit
        )

    [firewall] => Array
        (
            [login_attempt_limit] => 3
        )

    [playsmslog] => Array
        (
            [playsmsd] => Array
                (
                    [bin] => /usr/local/bin/playsmsd
                    [conf] => /etc/playsmsd.conf
                )

        )

    [schedule] => Array
        (
            [rules] => Array
                (
                    [Once] => 0
                    [Annually] => 1
                    [Monthly] => 2
                    [Weekly] => 3
                    [Daily] => 4
                )

            [rules_display] => Array
                (
                    [0] => Once
                    [1] => Annually
                    [2] => Monthly
                    [3] => Weekly
                    [4] => Daily
                )

            [import_row_limit] => 1000
            [export_row_limit] => 1000
        )

    [sms_command] => Array
        (
            [bin] => /var/lib/playsms/sms_command
            [allow_user_access] => 
        )

    [sms_subscribe] => Array
        (
            [durations] => Array
                (
                    [Unlimited] => 0
                    [1 Day] => 1001
                    [2 Days] => 1002
                    [1 Week] => 101
                    [2 Weeks] => 102
                    [1 Month] => 1
                    [6 Months] => 6
                )

        )

)
```
