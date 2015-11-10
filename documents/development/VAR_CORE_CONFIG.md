# CORE CONFIG

Example of `$core_config` on playSMS version 1.3.1:

```
Array
(
    [db] => Array
        (
            [type] => mysql
            [host] => localhost
            [port] => 3306
            [user] => root
            [pass] => password
            [name] => playsms
            [pref] => playsms
        )

    [smtp] => Array
        (
            [relm] => 
            [user] => 
            [pass] => 
            [host] => localhost
            [port] => 25
        )

    [apps_path] => Array
        (
            [logs] => /var/log/playsms
            [base] => /var/www/html/playsms
            [libs] => /var/www/html/playsms/lib
            [incs] => /var/www/html/playsms/inc
            [plug] => /var/www/html/playsms/plugin
            [themes] => /var/www/html/playsms/plugin/themes
            [tpl] => /var/www/html/playsms/plugin/themes/common/templates
            [storage] => /var/www/html/playsms/storage
        )

    [logstate] => 3
    [logfile] => playsms.log
    [logaudit] => 1
    [logauditfile] => audit.log
    [ishttps] => 
    [isdlrd] => 1
    [dlrd_limit] => 1000
    [isrecvsmsd] => 1
    [recvsmsd_limit] => 1000
    [issendsmsd] => 1
    [sendsmsd_queue] => 10
    [sendsmsd_chunk] => 20
    [sendsmsd_chunk_size] => 100
    [webservices_username] => 1
    [daemon_process] => 
    [http_path] => Array
        (
            [base] => http://localhost/playsms
            [libs] => http://localhost/playsms/lib
            [incs] => http://localhost/playsms/inc
            [plug] => http://localhost/playsms/plugin
            [themes] => http://localhost/playsms/plugin/themes
            [tpl] => http://localhost/playsms/plugin/themes/common/templates
            [storage] => http://localhost/playsms/storage
        )

    [corelist] => Array
        (
            [0] => acl
            [1] => auth
            [2] => blacklist
            [3] => country
            [4] => dba
            [5] => gateway
            [6] => logger
            [7] => main_config
            [8] => notif
            [9] => recvsms
            [10] => registry
            [11] => sender_id
            [12] => sendmail
            [13] => sendsms
            [14] => site
            [15] => themes
            [16] => tpl
            [17] => user
            [18] => welcome
        )

    [main] => Array
        (
            [web_title] => playSMS
            [email_service] => noreply@playsms.org
            [email_footer] => Powered by playSMS
            [main_website_name] => playSMS
            [main_website_url] => http://www.playsms.org
            [gateway_number] => 1234
            [gateway_timezone] => +0700
            [default_rate] => 0
            [gateway_module] => dev
            [themes_module] => default
            [language_module] => en_US
            [sms_max_count] => 3
            [default_credit] => 0
            [enable_register] => 0
            [enable_forgot] => 1
            [allow_custom_sender] => 0
            [allow_custom_footer] => 0
            [default_user_status] => 3
            [enable_logo] => 1
            [logo_url] => plugin/themes/common/images/playSMS_logo_full.png
            [logo_replace_title] => 1
            [layout_footer] => Application footer here. Go to main configuration or manage site to edit this footer.
            [buy_credit_page_title] => Buy credit
            [buy_credit_page_content] => Go to main configuration or manage site to edit this page
            [information_title] => Information
            [information_content] => Go to main configuration or manage site to edit this page
            [per_sms_length] => 153
            [per_sms_length_unicode] => 67
            [max_sms_length] => 459
            [max_sms_length_unicode] => 201
        )

    [datetime] => Array
        (
            [format] => Y-m-d H:i:s
            [now_stamp] => 20151110122647
        )

    [plugins_category] => Array
        (
            [0] => feature
            [1] => gateway
            [2] => themes
            [3] => language
        )

    [reserved_keywords] => Array
        (
            [0] => BC
        )

    [sendsmsd_limit] => 1000
    [menutab] => Array
        (
            [home] => Home
            [my_account] => My account
            [reports] => Reports
            [features] => Features
            [settings] => Settings
        )

    [featurelist] => Array
        (
            [0] => credit
            [1] => firewall
            [2] => inboxgroup
            [3] => incoming
            [4] => mailsms
            [5] => msgtemplate
            [6] => outgoing
            [7] => phonebook
            [8] => playsmslog
            [9] => pluginmanager
            [10] => queuelog
            [11] => report
            [12] => schedule
            [13] => sendfromfile
            [14] => simplebilling
            [15] => simplerate
            [16] => sms_autoreply
            [17] => sms_autorespond
            [18] => sms_board
            [19] => sms_command
            [20] => sms_custom
            [21] => sms_poll
            [22] => sms_quiz
            [23] => sms_subscribe
            [24] => sms_sync
            [25] => stoplist
        )

    [gatewaylist] => Array
        (
            [0] => blocked
            [1] => bulksms
            [2] => clickatell
            [3] => dev
            [4] => gammu
            [5] => generic
            [6] => infobip
            [7] => jasmin
            [8] => kannel
            [9] => nexmo
            [10] => openvox
            [11] => playnet
            [12] => smstools
            [13] => telerivet
            [14] => twilio
            [15] => uplink
        )

    [themeslist] => Array
        (
            [0] => default
            [1] => flatly
            [2] => ubuntu
        )

    [languagelist] => Array
        (
            [0] => ar_AR
            [1] => ar_SA
            [2] => ca_ES
            [3] => da_DK
            [4] => de_DE
            [5] => en_US
            [6] => es_VE
            [7] => fr_FR
            [8] => id_ID
            [9] => nb_NO
            [10] => pt_BR
            [11] => pt_PT
            [12] => ru_RU
            [13] => zh_CN
        )

)
```
