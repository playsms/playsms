# MENU CONFIG

Example of `$menu_config` on playSMS version 1.3.1:

```
Array
(
    [My account] => Array
        (
            [0] => Array
                (
                    [0] => index.php?app=main&inc=core_sendsms&op=sendsms
                    [1] => Compose message
                    [2] => 1
                )

            [1] => Array
                (
                    [0] => #
                    [1] => -
                    [2] => 99
                )

            [2] => Array
                (
                    [0] => index.php?app=main&inc=core_user&route=user_config&op=user_config
                    [1] => User configuration
                    [2] => 99
                )

            [3] => Array
                (
                    [0] => index.php?app=main&inc=core_user&route=user_pref&op=user_pref
                    [1] => Preferences
                    [2] => 99
                )

            [4] => Array
                (
                    [0] => index.php?app=main&inc=feature_mailsms&route=mailsms_user&op=mailsms_user
                    [1] => My email to SMS
                )

            [5] => Array
                (
                    [0] => index.php?app=main&inc=feature_msgtemplate&op=list
                    [1] => Message template
                )

            [6] => Array
                (
                    [0] => index.php?app=main&inc=feature_phonebook&op=phonebook_list
                    [1] => Phonebook
                    [2] => 2
                )

            [7] => Array
                (
                    [0] => index.php?app=main&inc=feature_report&route=user_inbox&op=user_inbox
                    [1] => Inbox
                    [2] => 1
                )

            [8] => Array
                (
                    [0] => index.php?app=main&inc=feature_schedule&op=list
                    [1] => Schedule messages
                    [2] => 1
                )

            [9] => Array
                (
                    [0] => index.php?app=main&inc=feature_sendfromfile&op=list
                    [1] => Send from file
                    [2] => 1
                )

        )

    [Settings] => Array
        (
            [0] => Array
                (
                    [0] => index.php?app=main&inc=core_user&route=user_mgmnt&op=user_list
                    [1] => Manage account
                    [2] => 3
                )

            [1] => Array
                (
                    [0] => index.php?app=main&inc=core_acl&op=acl_list
                    [1] => Manage ACL
                    [2] => 3
                )

            [2] => Array
                (
                    [0] => index.php?app=main&inc=core_user&route=subuser_mgmnt&op=subuser_list
                    [1] => Manage subuser
                    [2] => 3
                )

            [3] => Array
                (
                    [0] => index.php?app=main&inc=core_sender_id&op=sender_id_list
                    [1] => Manage sender ID
                    [2] => 3
                )

            [4] => Array
                (
                    [0] => index.php?app=main&inc=core_main_config&op=main_config
                    [1] => Main configuration
                    [2] => 3
                )

            [5] => Array
                (
                    [0] => index.php?app=main&inc=core_gateway&op=gateway_list
                    [1] => Manage gateway and SMSC
                    [2] => 3
                )

            [6] => Array
                (
                    [0] => index.php?app=main&inc=gateway_dev&route=simulate&op=simulate
                    [1] => Simulate incoming SMS
                )

            [7] => Array
                (
                    [0] => index.php?app=main&inc=feature_credit&op=credit_list
                    [1] => Manage credit
                )

            [8] => Array
                (
                    [0] => index.php?app=main&inc=feature_firewall&op=firewall_list
                    [1] => Manage firewall
                    [2] => 3
                )

            [9] => Array
                (
                    [0] => index.php?app=main&inc=feature_incoming&op=incoming
                    [1] => Route incoming SMS
                    [2] => 1
                )

            [10] => Array
                (
                    [0] => index.php?app=main&inc=feature_mailsms&op=mailsms
                    [1] => Manage email to SMS
                )

            [11] => Array
                (
                    [0] => index.php?app=main&inc=feature_outgoing&op=outgoing_list
                    [1] => Route outgoing SMS
                    [2] => 2
                )

            [12] => Array
                (
                    [0] => index.php?app=main&inc=feature_pluginmanager&op=pluginmanager_list
                    [1] => Manage plugin
                )

            [13] => Array
                (
                    [0] => index.php?app=main&inc=feature_simplerate&op=simplerate_list
                    [1] => Manage SMS rate
                )

            [14] => Array
                (
                    [0] => index.php?app=main&inc=feature_stoplist&op=stoplist_list
                    [1] => Manage stoplist
                    [2] => 3
                )

        )

    [Features] => Array
        (
            [0] => Array
                (
                    [0] => index.php?app=main&inc=feature_inboxgroup&op=list
                    [1] => Group inbox
                )

            [1] => Array
                (
                    [0] => index.php?app=main&inc=feature_sms_autoreply&op=sms_autoreply_list
                    [1] => Manage autoreply
                )

            [2] => Array
                (
                    [0] => index.php?app=main&inc=feature_sms_autorespond&op=sms_autorespond_list
                    [1] => Manage autorespond
                )

            [3] => Array
                (
                    [0] => index.php?app=main&inc=feature_sms_board&op=sms_board_list
                    [1] => Manage board
                )

            [4] => Array
                (
                    [0] => index.php?app=main&inc=feature_sms_command&op=sms_command_list
                    [1] => Manage command
                )

            [5] => Array
                (
                    [0] => index.php?app=main&inc=feature_sms_custom&op=sms_custom_list
                    [1] => Manage custom
                )

            [6] => Array
                (
                    [0] => index.php?app=main&inc=feature_sms_poll&op=sms_poll_list
                    [1] => Manage poll
                )

            [7] => Array
                (
                    [0] => index.php?app=main&inc=feature_sms_quiz&op=sms_quiz_list
                    [1] => Manage quiz
                )

            [8] => Array
                (
                    [0] => index.php?app=main&inc=feature_sms_subscribe&op=sms_subscribe_list
                    [1] => Manage subscribe
                )

            [9] => Array
                (
                    [0] => index.php?app=main&inc=feature_sms_sync&op=sms_sync_list
                    [1] => Manage sync
                )

        )

    [Reports] => Array
        (
            [0] => Array
                (
                    [0] => index.php?app=main&inc=feature_playsmslog&op=playsmslog_list
                    [1] => View log
                )

            [1] => Array
                (
                    [0] => index.php?app=main&inc=feature_queuelog&op=queuelog_list
                    [1] => View SMS queue
                )

            [2] => Array
                (
                    [0] => index.php?app=main&inc=feature_report&route=all_inbox&op=all_inbox
                    [1] => All inbox
                    [2] => 3
                )

            [3] => Array
                (
                    [0] => index.php?app=main&inc=feature_report&route=all_incoming&op=all_incoming
                    [1] => All feature messages
                    [2] => 4
                )

            [4] => Array
                (
                    [0] => index.php?app=main&inc=feature_report&route=all_outgoing&op=all_outgoing
                    [1] => All sent messages
                    [2] => 4
                )

            [5] => Array
                (
                    [0] => index.php?app=main&inc=feature_report&route=sandbox&op=sandbox
                    [1] => Sandbox
                    [2] => 5
                )

            [6] => Array
                (
                    [0] => index.php?app=main&inc=feature_report&route=admin
                    [1] => Report all users
                    [2] => 10
                )

            [7] => Array
                (
                    [0] => index.php?app=main&inc=feature_report&route=online
                    [1] => Report whose online
                    [2] => 10
                )

            [8] => Array
                (
                    [0] => index.php?app=main&inc=feature_report&route=banned
                    [1] => Report banned users
                    [2] => 10
                )

            [9] => Array
                (
                    [0] => index.php?app=main&inc=feature_report&route=user_incoming&op=user_incoming
                    [1] => My feature messages
                    [2] => 1
                )

            [10] => Array
                (
                    [0] => index.php?app=main&inc=feature_report&route=user_outgoing&op=user_outgoing
                    [1] => My sent messages
                    [2] => 1
                )

            [11] => Array
                (
                    [0] => index.php?app=main&inc=feature_report&route=user
                    [1] => My report
                    [2] => 2
                )

            [12] => Array
                (
                    [0] => index.php?app=main&inc=feature_report&route=credit&op=credit_list
                    [1] => My credit transactions
                    [2] => 2
                )

        )

)
```
