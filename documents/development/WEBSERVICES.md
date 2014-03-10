# WEBSERVICES

This document explains about playSMS webservices protocol.

Minimum playSMS version **1.0.0**


## Access

Webservices URL:
`http://[playSMS_domain_or_url]/index.php?app=ws`

Example:
`https://playsms.org/trial/index.php?app=ws`


## Parameters

Below table listed playSMS webservices paramaters.

Name           | Description
-------------- | --------------
h              | webservices token, configured by user from Preferences menu
u              | username
p              | password, supplied for op=get_token
op             | operation or type of action
format	       | output format selection
from	       | SMS sender ID (for op=pv or op=bc)
to             | destination numbers or group codes (international format)
footer	       | SMS footer (for op=pv pr op=bc)
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
ERR 200    | send private failed
ERR 201    | destination number or message is empty
ERR 300    | send broadcast failed
ERR 301    | destination group or message is empty
ERR 400    | no delivery status available
ERR 401    | no delivery status retrieved and SMS still in queue
ERR 402    | no delivery status retrieved and SMS has been processed from queue
ERR 501    | no data returned or result is empty

There might appear new error codes in the future, you should be aware that new
codes might appear with this syntax

Error code | Description
---------- | -----------
ERR 1xx    | authentication or parameter erorrs
ERR 2xx    | specific pv errors
ERR 3xx    | specific bc errors
ERR 4xx    | delivery status errors
ERR 5xx    | others


## Protocol

### Send SMS

Send SMS to a single or multiple mobile numbers.

Parameters | Name or description
---------- | --------------------
Operation  | `pv`
Mandatory  | `u` `h` `to` `msg`
Optional   | `type` `unicode` `from` `footer` `nofooter` `format`
Returns    | return codes

Parameter `to` is an international formatted mobile number. Separate by commas for multiple value.

### Send broadcast SMS

Send broadcast or bulk SMS to single or multiple phonebook group codes.

Parameters | Name or description
---------- | --------------------
Operation  | `bc`
Mandatory  | `u` `h` `to` `msg`
Optional   | `type` `unicode` `from` `footer` `nofooter` `format`
Returns    | return codes

Parameter `to` is a group code. Separate by commas for multiple value.

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

### Inbox SMS

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
{"status":"OK","error":"0","smslog_id":0,"queue":"afb5f34575e30ec4efe4471cf5d1bee4","to":"0987654321"}
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
