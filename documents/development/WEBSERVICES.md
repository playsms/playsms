# WEBSERVICES

This document explains about playSMS webservices protocol.

Minimum playSMS version **1.0-rc5**


## Access

Webservices URL:
`http://[playSMS_domain_or_url]/index.php?app=ws`

Example:
`https://playsms.org/trial/index.php?app=ws`


## Parameters

Listed in the below table are webservices parameters for individual tasks.

Name           | Description
-------------- | --------------
h              | webservices token, configured by user from Preferences menu
u              | username
p              | password, supplied for op=get_token
op             | operation or type of action
format	       | output format selection
from	       | SMS sender ID (for op=pv)
to             | destination numbers, @username or #groupcode, may use commas
footer	       | SMS footer (for op=pv)
nofooter       | remove SMS footer
msg            | message (+ or %20 for spaces, urlencode for non ascii chars)
schedule       | schedule message delivery, format: YYYY-MM-DD hh:mm:ss
type           | message type (flash or text)
unicode        | whether message unicode or not (1=unicode, 0=not unicode)
queue          | queue code
src            | sender number or ID
dst            | destination number (single number)
dt             | send SMS date/time
smslog_id      | SMS Log ID
last           | last SMS log ID (this number not included on result)
c              | number of delivery status that will be retrieved
kwd            | keyword
login_key      | login key sets by admin through webservices call op=loginkeyset
query          | query server for useful information

Listed in the below table are webservices parameters for admin tasks.

Name           | Description
-------------- | --------------
recvnum        | receiver number (for op=inject)
smsc           | SMSC (for op=inject)
data_*	       | admin tasks related supplied data

Please note that all values should be URL encoded.


## Return Codes

Below table listed return coded after unsuccessful call to a webservices operation. Successful operation will returns an OK data in the response message.

Please note that by default the response message is a JSON encoded message.

Error code | Description
---------- | -----------
ERR 100    | authentication failed
ERR 101    | type of action is invalid or unknown
ERR 102    | one or more field empty
ERR 103    | not enough credit for this operation
ERR 104    | webservice token is not available
ERR 105    | webservice token not enable for this user
ERR 106    | webservice token not allowed from this IP address
ERR 200    | send message failed
ERR 201    | destination number or message is empty
ERR 400    | no delivery status available
ERR 401    | no delivery status retrieved and SMS still in queue
ERR 402    | no delivery status retrieved and SMS has been processed from queue
ERR 501    | no data returned or result is empty
ERR 600    | admin level authentication failed
ERR 601    | inject message failed
ERR 602    | sender id or message is empty
ERR 603    | account addition failed due to missing data
ERR 604    | fail to add account
ERR 605    | account removal failed due to unknown username
ERR 606    | fail to remove account
ERR 607    | set parent failed due to unknown username
ERR 608    | fail to set parent
ERR 609    | get parent failed due to unknown username
ERR 610    | fail to get parent
ERR 611    | account ban failed due to unknown username
ERR 612    | fail to ban account
ERR 613    | account unban failed due to unknown username
ERR 614    | fail to unban account
ERR 615    | editing account preferences failed due to missing data
ERR 616    | fail to edit account preferences
ERR 617    | editing account configuration failed due to missing data
ERR 618    | fail to edit account configuration
ERR 619    | viewing credit failed due to missing data
ERR 620    | fail to view credit
ERR 621    | adding credit failed due to missing data
ERR 622    | fail to add credit
ERR 623    | deducting credit failed due to missing data
ERR 624    | fail to deduct credit
ERR 625    | setting login key failed due to missing data
ERR 626    | fail to set login key


There might appear new error codes in the future, you should be aware that new codes might appear in this syntax:

Error code | Description
---------- | -----------
ERR 1xx    | authentication or parameter erorrs
ERR 2xx    | specific pv errors
ERR 4xx    | delivery status errors
ERR 5xx    | others
ERR 6xx    | administrative tasks


## Protocol for non-admin tasks


### Send message

Send message to a single or multiple mobile numbers, @username or #groupcode

Parameters | Name or description
---------- | --------------------
Operation  | `pv`
Mandatory  | `u` `h` `to` `msg`
Optional   | `type` `unicode` `from` `footer` `nofooter` `format`
Returns    | return codes

Parameter `to` can be international formatted mobile number, #groupcode or @username, or a mix of them. Separate by commas for multiple value.


### Outgoing SMS and delivery status

List outgoing SMS and delivery status.

Parameters | Name or description
---------- | --------------------
Operation  | `ds`
Mandatory  | `u` `h`
Optional   | `queue` `src` `dst` `dt` `smslog_id` `c` `last` `format`
Returns    | data or return codes

Parameter `c` will retrieve as many as `c` value, `last` will retrieves data from last SMS log ID.


### Incoming SMS

List incoming SMS.

Parameters | Name or description
---------- | --------------------
Operation  | `in`
Mandatory  | `u` `h`
Optional   | `queue` `src` `dst` `dt` `smslog_id` `c` `last` `format`
Returns    | data or return codes

Parameter `c` will retrieve as many as `c` value, `last` will retrieves data from last SMS log ID.


### Inbox

List SMS on user's inbox.

Parameters | Name or description
---------- | --------------------
Operation  | `ix`
Mandatory  | `u` `h`
Optional   | `queue` `src` `dst` `dt` `smslog_id` `c` `last` `format`
Returns    | data or return codes

Parameter `c` will retrieve as many as `c` value, `last` will retrieves data from last SMS log ID.


### Sandbox

List unhandled incoming SMS.

Parameters | Name or description
---------- | --------------------
Operation  | `sx`
Mandatory  | `u` `h`
Optional   | `queue` `src` `dst` `dt` `smslog_id` `c` `last` `format`
Returns    | data or return codes

Parameter `c` will retrieve as many as `c` value, `last` will retrieves data from last SMS log ID.


### User credit

Get user's credit information.

Parameters | Name or description
---------- | --------------------
Operation  | `cr`
Mandatory  | `u` `h`
Optional   | `format`
Returns    | user's credit or return codes


### Get token

Get user's webservices token. This can be used as a login mechanism.

Parameters | Name or description
---------- | --------------------
Operation  | `get_token`
Mandatory  | `u` `p`
Optional   | `format`
Returns    | webservices token or return codes


### Set token

Set user's webservices token. This can be used as a change password mechanism.

Parameters | Name or description
---------- | --------------------
Operation  | `set_token`
Mandatory  | `u` `h`
Optional   | `format`
Returns    | new webservices token or return codes


### Get contact list

Get contact list by name, mobile or email

Parameters | Name or description
---------- | --------------------
Operation  | `get_contact`
Mandatory  | `u` `h` `kwd`
Optional   | `c` `format`
Returns    | list of contacts similar or the same as `kwd` or return codes


### Get group contact list

Get group contact list by name or code

Parameters | Name or description
---------- | --------------------
Operation  | `get_contact_group`
Mandatory  | `u` `h` `kwd`
Optional   | `c` `format`
Returns    | list of contact groups similar or the same as `kwd` or return codes


### Webservices login

Authenticate user via webservices and redirect to index.php upon authentication, successful or failed or invalid

Parameters | Name or description
---------- | ---------------------
Operation  | `ws_login`
Mandatory  | `u` `login_key`
Optional   | none
Returns    | none, web redirect to index.php

Parameter `login_key` is set by admin accounts through webservices call using operation `loginkeyset`


### Query

Query server for useful information such as user's data, user's credit, last smslog_id for inbox, incoming and outgoing SMS

Parameters | Name or description
---------- | ---------------------
Operation  | `query`
Mandatory  | `u` `h`
Optional   | `format`
Returns    | server's information


## Protocol for admin tasks


### Inject message

Inject message to the system

Parameters | Name or description
---------- | --------------------
Operation  | `inject`
Mandatory  | `u` `h` `from` `msg` `recvnum` `smsc`
Optional   | `format`
Returns    | return codes

Injected message will be treated as a valid incoming SMS.


### Add account

Add an account

Parameters | Name or description
---------- | --------------------
Operation  | `accountadd`
Mandatory  | `u` `h` `data_status` `data_username` `data_password` `data_name` `data_email`
Optional   | `format` `data_parent` `data_mobile` `data_datetime_timezone` `data_language_module`
Returns    | return codes and info

Notes:
* most mandatory and optional query parameters are prefixed with `data_`
* data_status 2 is for admin level account
* data_status 3 is for user level account
* data_status 4 is for subuser level account
* data_parent may need to be set when adding subuser level account


### Remove account

Remove an account

Parameters | Name or description
---------- | --------------------
Operation  | `accountremove`
Mandatory  | `u` `h` `data_username`
Optional   | `format`
Returns    | return codes and info


### Set parent for subuser

Set parent for subuser level account

Parameters | Name or description
---------- | --------------------
Operation  | `parentset`
Mandatory  | `u` `h` `data_username` `data_parent`
Optional   | `format`
Returns    | return codes


### Get parent from subuser

Get parent from subuser level account

Parameters | Name or description
---------- | --------------------
Operation  | `parentget`
Mandatory  | `u` `h` `data_username`
Optional   | `format`
Returns    | return codes and data parent


### Ban an account

Ban an account

Parameters | Name or description
---------- | --------------------
Operation  | `accountban`
Mandatory  | `u` `h` `data_username`
Optional   | `format`
Returns    | return codes


### Unban an account

Unban an account

Parameters | Name or description
---------- | --------------------
Operation  | `accountunban`
Mandatory  | `u` `h` `data_username`
Optional   | `format`
Returns    | return codes


### Update account preferences

Update account preferences

Parameters | Name or description
---------- | --------------------
Operation  | `accountpref`
Mandatory  | `u` `h` `data_username`
Optional   | `format` `data_name` `data_email` `data_mobile` `data_address` `data_city` `data_state` `data_country` `data_zipcode` `data_password`
Returns    | return codes and info

Notes:
* most mandatory and optional query parameters are prefixed with `data_`
* this command may be used to update account's password


### Update account configuration

Update account configuration

Parameters | Name or description
---------- | --------------------
Operation  | `accountconf`
Mandatory  | `u` `h` `data_username`
Optional   | `format` `data_footer` `datetime_timezone` `data_language_module` `data_fwd_to_inbox` `data_fwd_to_email` `data_fwd_to_mobile` `data_local_length` `data_replace_zero` `data_sender` 
Returns    | return codes and info

Notes:
* most mandatory and optional query parameters are prefixed with `data_`
* this command may be used to update account's default sender ID
* only valid sender ID may be selected
* `data_fwd_to_inbox` `data_fwd_to_email` `data_fwd_to_mobile` are boolean variables, fill with 0 to disable and 1 to enable
* `data_local_length` used to detect local destination number by its length
* `data_replace_zero` is a numeric only prefix number to replace prefix 0


### View account credit

View an account credit or balance

Parameters | Name or description
---------- | --------------------
Operation  | `creditview`
Mandatory  | `u` `h` `data_username`
Optional   | `format`
Returns    | return codes and balance


### Add account credit

Add credit to an account

Parameters | Name or description
---------- | --------------------
Operation  | `creditadd`
Mandatory  | `u` `h` `data_username` `data_amount`
Optional   | `format`
Returns    | return codes, updated balance and amount


### Deduct account credit

Deduct credit to an account

Parameters | Name or description
---------- | --------------------
Operation  | `creditdeduct`
Mandatory  | `u` `h` `data_username` `data_amount`
Optional   | `format`
Returns    | return codes, updated balance and amount


### Set login key

Set login key for an account

Parameters | Name or description
---------- | --------------------
Operation  | `loginkeyset`
Mandatory  | `u` `h` `data_username`
Optional   | `format`
Returns    | return codes and login key


## Examples


### Send SMS

Example webservice URL:

```
http://x.dom/index.php?app=ws&u=anton&h=a45a02791b2fe2fedb078c39fd83637a&op=pv&to=0987654321&msg=test+only
```

Explanation:

playSMS webservices in x.dom with operation `op` pv (send SMS) was accessed by a user using username `u` and webservices token `h` with destination number `to` 0987654321, message `msg` 'test only' and expected output format is the default format, JSON format.

When succeeded playSMS will returns OK status message in JSON format:

```
{"data":[{"status":"OK","error":"0","smslog_id":"30","queue":"afb5f34575e30ec4efe4471cf5d1bee4","to":"0987654321"}],"error_string":null}
```
When error occurred playSMS will returns one of the return code, also in JSON format.


### List of incoming SMS

Example webservice URL:

```
http://x.com/index.php?app=ws&u=anton&h=482ac0069592c647289e52dfef88be68&op=in&kwd=IDOL&format=xml
```

Explanation:

playSMS webservices in x.com with operation `op` in (incoming SMS) was accessed by a user using username `u` and webservices token `h` with keyword `kwd` IDOL and expected output format is in XML format `format=xml`.

When succeeded playSMS will returns OK status message in XML format:

```
<response>
    <data>
        <item>
            <id>2</id>
            <src>+629876543210</src>
            <dst>1234</dst>
            <kwd>IDOL</kwd>
            <msg>A</msg>
            <dt>2013-05-20 12:40:38</dt>
            <status>1</status>
        </item>
    </data>
</response>
```

When error occurred playSMS will returns one of the return code, also in XML format.


### List of contacts on phonebook

Example webservice URL:

```
http://x.com/index.php?app=ws&u=anton&h=482ac0069592c647289e52dfef88be68&op=get_contact&kwd=anton
```

Explanation:

playSMS webservices in x.com with operation `op` get_contact was accessed by a user using username `u` and webservices token `h` with keyword `kwd` anton and expected output format is in JSON format.

When succeeded playSMS will returns OK status message in JSON format:

```
{"status":"OK","error":"0","data":[{"pid":"13674","gpid":"2","p_desc":"Anton Raharja","p_num":"08901230659","email":"","group_name":"Test Group","code":"TESTGROUP"}],"multi":true}
```

When error occurred playSMS will returns one of the return code, also in JSON format.
