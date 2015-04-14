# FAQ

Frequently Asked Questions for playSMS version **1.0-rc4** and above.

1. Who wrote this FAQ?

   Answer:

   Mostly me, anton.

2. Why is playSMS licensed as GPL version 3?

   Answer:

   GPLv3 is one of the open source distribution license, choosing this will force
   anyone that distributing this software to contribute back when they modify it.

   I believe playSMS users, open source community in general, will gain benefit
   from applying this distribution license.


## Login

1.  How to login for the first time after installation?

    Answer:

    Browse playSMS URL and login with default username and password:

    Username | password
    -------- | --------
    admin    | admin

2.  What should I do next after I logged in as Administrator?

    Answer:

    Change your default password, add new users, setup main configuration,
    create group and add mobile phone number.

3.  What should I do for the first time as User?

    Answer:

    Change your default password given by Administrator, create group
    and add mobile phone number to phonebook.


## Send SMS

1.  How to send one SMS from web?

    Answer:

    Use Send SMS page.

2.  How to send broadcast SMS from web?

    Answer:

    Use Send SMS page.

    Groups in phonebook can be addressed with prefix # followed by group code.

3.  Is there any delivery status reported?

    Answer:

    Yes. Go to Outgoing SMS page.

    * Red dot represent status "Failed" for failed attempt
    * Yellow dot represent status "Pending" for queued SMS (on server)
    * Green dot represent status "Sent" for SMS sent (SMS sent to gateway)
    * Blue dot represent status "Delivered" for SMS delivered to phone

4.  Can we delete Outgoing SMS?

    Answer:

    Yes, but its not really deleted, its just permanently hidden from
    everyone's Outgoing SMS page (including from administrators).

5.  Can we retrieve deleted Outgoing SMS?

    Answer:

    No, not at the moment. Not from playSMS interface.

6.  If we delete Outgoing SMS, is it affecting SMS transmission?

    Answer:

    No, playSMS is already send the message.

7.  How do my sent SMS look like at the recipient mobile phones?

    Answer:

    If you set the SMS footer in your Preferences, then the SMS footer
    will be added to the end of your SMS, this is useful when using
    gateway module with no ability to set SMS sender ID.

    Gateway modules which have the ability to set SMS sender ID will
    set SMS sender ID to the SMS.

    The sender ID is set according to this order:

    1. Default sender ID on Main configuration (in case it exists)
    2. Module sender ID on active gateway configuration (in case it exists)
    3. SMS sender ID on User preferences

    Sender ID is managed from Manage sender ID menu and is required to be
    approved by administrator.


## Receive SMS

1.  Can I receive SMS?

    Answer:

    Yes. If it came to Inbox, it can also be forwarded to email or mobile.

2.  How do I receive SMS in my Inbox?

    Answer:

    Tell people to send SMS to your playSMS number with format:

    `@[your username] [their message]`

    or as long as the SMS contains `@[your username]` (since playSMS 0.9.8)

    Examples:

    * @devteam your application rocks!
    * @devteam I want to help but dunno wht todo, any idea?
    * @admin bro, please set me as admin too !!
    * Hi @admin, nice to know that now we have a free SMS service!

3.  Can I forward SMS from my mobile phone to my phonebook group?

    Answer:

    When you create a group in your phonebook you will be asked for
    a Group Code. Use that Group Code as parameter to forward SMS to
    group using special character # you can forward SMS to your group:

    `#[group code] [your message]`

    or as long as the SMS contains `#[group code]` (since playSMS 0.9.8)

    Examples:

    * #DEV thx for joining our crussade :)
    * #DEV its nice to meet you all
    * #Dev dont forget to read function.php
    * #dev testing only, sending this SMS to group code DEV
    * To all of you in #DEV please get this bug fixed asap!

    playSMS will use Mobile number sets in user's Preferences menu to
    authenticate this feature

    In current release group in phonebook can also be set to allows forwards from
    senders other than the group owner.

4.  What happened to the received/incoming SMS without keyword?

    Answer:

    They will only be shown in Sandbox menu, only administrators can view them.

    In current release these messages may be routed to several users or a feature.


## Webservices

1.  Can I use my own application or 3rd party application to send SMS
    using playSMS?

    Answer:

    Yes, more information how to use this feature can be read in `WEBSERVICES.md`
    located in `documents/development/`.

    Also available here: http://bit.ly/playsmsws

2.  Can I retrieve delivery status remotely so I can process it from
    other application?

    Answer:

    Yes, delivery statuses are retrieved in JSON, XML and some other formats.

    More information about this feature can be read in: http://bit.ly/playsmsws


## SMS Board

1.  What is SMS board?

    Answer:

    Administrator can add new SMS board with keyword other than PV and
    BC. Incoming SMS with the specified keyword will be handled by SMS
    board and be forwarded to a unique web page served by playSMS.

    Incoming SMS will also be forwarded to an email specified in each
    SMS board keywords.

2.  If I add SMS board, howto access web page for a keyword?

    Answer:
    For example you've added SMS board with keyword INFO. The web page
    for keyword INFO (referred as SMS board INFO) would be accessible
    through this url:

    `http://playsms/index.php?app=webservices&ta=sms_board&keyword=INFO`

    Other parameter you can use to refine the display:

    * line : show x line number of rows
    * bodybgcolor : set body background color
    * oddbgcolor : set odd row background color
    * evenbgcolor	: set even row background color

    Example:

    ```
    http://playsms.org/trial/index.php?app=webservices&ta=sms_board&keyword=INFO&line=15
    ```

3.  What output formats available for SMS board webservices?

    Answer:

    PHP serialize, json, xml, rss feed (0.91, 1.0, 2.0, ATOM) and html.

    Please see menu view in SMS board.

4.  How to put that web page on my main website?

    Answer:

    Use IFRAME html tag. Customize your IFRAME to match your main
    website look and feel.

    Example:

    ```
    <IFRAME
    src="http://playsms/index.php?app=webservices&ta=sms_board&keyword=INFO">
    </IFRAME>
    ```

    Other solution may be available.


## SMS Command

1.  Can I command server todo something?

    Answer:

    Yes. Login as admin user and configure SMS command feature.

2.  How to setup SMS command?

    Answer:

    Add new SMS command and associate a command that will be executed
    on incoming SMS that matched your keyword.

    On incoming SMS, playSMS will pass variables to the command.

    They are:

    * {SMSDATETIME} replaced by incoming SMS date/time
    * {SMSSENDER} replaced by sender mobile phone number
    * {COMMANDKEYWORD} replaced by incoming SMS keyword
    * {COMMANDPARAM} replaced by incoming SMS parameter
    * {COMMANDRAW} replaced by SMS raw message

3.  How to utilized a defined SMS command?

    Answer:

    Send SMS to your playSMS with format:

    `[COMMAND KEYWORD] [COMMAND PARAM]`

    Examples:

	* reg email devteam@playsms.org
	* retr pop3 pop3.ngoprek.org devteam mypwd
	* uptime

4.  What commands are available from SMS command?

    Answer:

    Any commands are available to be executed from SMS command.

5.  What is the check box 'Make return as reply' for?

    Answer:

    playSMS will pickup output of executed command as reply to sender


## SMS Custom

1.  What is SMS custom?

    Answer:

    SMS custom is used to process incoming SMS by passing the SMS
    information to another application in other server using HTTP.

    `[sender] --GSM-- [playSMS] --IP-- [other host processing SMS]`

2.  How to setup SMS custom?

    Answer:

    Add new SMS custom from menu, and then set custom URL of the host
    handling SMS.

    On incoming SMS, playSMS will pass variables to the URL.

    They are:

    * {SMSDATETIME} replaced by SMS incoming date/time
    * {SMSSENDER} replaced by sender mobile phones number
    * {CUSTOMKEYWORD} replaced by custom keyword
    * {CUSTOMPARAM} replaced by custom parameter
    * {CUSTOMRAW} replaced by SMS raw message

3.  How to utilized a defined SMS custom?

    Answer:

    Send SMS to your playSMS with format:

    `[CUSTOM KEYWORD] [CUSTOM PARAM]`

    Examples:

	* reg email devteam@playsms.org
	* retr pop3 pop3.ngoprek.org devteam mypwd
	* uptime

4.  What is the check box 'Make return as reply' for?

    Answer:

    playSMS will pickup output of URL/other host as reply to sender


## SMS Poll

1.  How to use SMS poll system?

    Answer:

    Add new poll or list/edit/delete it. Write down poll keyword and
    each choice keyword you have defined.

    Tell  voters to send SMS to playSMS SMS gateway mobile number with
    format:

    `[POLL KEYWORD] [CHOICE KEYWORD]`

    Examples:

    * food chicken
    * pres 3

2.  Howto show results of SMS poll in other website?

    Answer:

    For example polling with keyword PRES

    Webpage for poll named PRES would be accessible trough this url:

    ```
    http://your_playsms/index.php?app=webservices&ta=sms_poll&keyword=PRES
    ```

    Other parameter you can use:

    * bodybgcolor : set body background color
    * refresh=yes : check latest incoming sms and refresh webpage output

    Using IFRAME html tag:

    ```
    <IFRAME src="http://your_playsms_web_domain/index.php?app=webservices&
    ta=sms_poll&keyword=PRES"></IFRAME>
    ```

3.  What output formats available for SMS poll webservices?

    Answer:

    PHP serialize, json, xml, and graph. Please see menu view in SMS poll.


## SMS Quiz

1.  What is SMS quiz system?

    Answer:

    Administrator can add quiz keywords, questions and answers, and
    define message reply for participants.

    Once a participant send message with quiz keyword and quiz answer
    keyword, system will reply with a message to tell participant
    whether the answer is correct or incorrect.

2.  Howto use SMS quiz system?

    Answer:

    Add new quiz or list/edit/delete it. Write down quiz keyword, quiz
    question, quiz answer and message to participants for correct and
    incorrect message.

    Tell  participants to send SMS to playSMS mobile number with
    format:

    `[QUIZ KEYWORD] [ANSWER KEYWORD]`

    Example:

    * soccer germany
    * answer A


## SMS Subscribe

1.  What is SMS subscribe system?

    Answer:

    Administrator can add SMS keyword which people can subscribe to.
    Once a number subscribed, administrator can send message to all
    subscribed number at once.

2.  Howto use SMS subscribe system?

    Answer:

    Add new subscribe or list/edit/delete it. Write down subscribe
    keyword, subscribe message, and unsubscribe message.

    When a number has subscribed the system will automatically send
    a subscribe message, and when a number has unsubscribe the system
    will automatically send an unsubscribe message.

    Add message to each subscribe keyword, this message will be sent
    to all subscribed number.

    Tell  people to send SMS to playSMS SMS gateway mobile number with
    format:

    `[SUBSCRIBE KEYWORD] [INSTRUCTION]`

    Examples:

    * PLAYSMS REG
    * PLAYSMS UNREG
    * theclub REG
    * theclub off


## Gateway Module

1.  I've heard that from version 0.5 playSMS support any gateway other
    than gnokii. Is this true?

    Answer:

    Yes. From version 0.5 you can write a gateway module and place on
    `plugin/gateway` to load it.

2.  So if I don't have GSM modem or nokia 5110, say I have access to
    an SMSC or Internet SMS Gateway like http://www.clickatell.com can
    I use playSMS?

    Answer:

    Yes. Use gateway module kannel for connecting directly to an SMSC
    or use gateway module clickatell and uplink for connecting to
    other sms gateway/server such as Clickatell or another playSMS.

    Please see `plugin/gateway/clickatell/` for Clickatell and
    `plugin/gateway/uplink/` for Uplink.

3.  Is kannel (http://kannel.org) supported by playSMS?

    Answer:

    Yes, starting version 0.6

    Please see `plugin/gateway/kannel/`

4.  How can I configure each gateway module?

    Answer:

    Starting from version 0.8 a web based control panel for gateway
    modules configurations is available for Administrator.

5.  How can I setup which gateway module active?

    Answer:

    Login as Administrator and activate the chosen gateway by clicking
    "Activate" menu on each gateway module configuration.

6.  Can I build my own gateway module ?

    Answer:

    Yes.


## SMS Rate and Credit

1.  What is Manage SMS rate menu in Administration drop-down menu?

    Answer:

    It is where administrator can set rate by prefix.

2.  What is term 'rate' means?

    Answer:

    Rate is credit value per SMS sent.

3.  What is term 'credit' means?

    Answer:

    Credit is money value equivalent a user have on their balance

4.  What if user send SMS to destination that is not configured in
    Manage SMS rate?

    Answer:

    Default SMS rate in Main configuration menu will be used instead.

5.  How to change credit per user ?

    Answer:

    Go to Manage user menu in Administration drop-down menu and edit
    each user.

6.  What kind of SMS being rated ?

    Answer:

    Sent and delivered SMS.


## Contact

1.  Is there any place where I can discuss playSMS matters?

    Answer:

    Yes. playSMS user group forum or mailing list. It is intended for general
    users but focus on developers.

    Please visit and join the group:

    http://groups.google.com/group/playsmsusergroup

2.  What is the official website for playSMS?

    Answer:

    http://www.playsms.org

3.  Should I tell you when I install playSMS on my site?

    Answer:

    Yes, you should.

4.  If I have wishes, what should I do?

    Answer:

    If its about playSMS, please visit http://www.playsms.org/support

5.  If I found bugs and/or security holes, what should I do?

    Answer:

    Please visit http://www.playsms.org/support
