PLAYSMS TEMPLATE ENGINE
-----------------------

playSMS template engine is provided by `plugin/core/tpl`. Checkout tpl `fn.php` to understand more about how it works.


Template file Locations
=======================

Template files are text files with .html extension.

Please avoid to name a template file as `index.html`

Example: `user_inbox.html` or `page_welcome.html`

Template files are located under directory `templates`.

You can find directory `templates` in several places, they are:

1. under each plugin directory
2. under directory `plugin/themes/<themes name>/`
3. under directory `plugin/themes/common/`

Above items are listed based on search priority as well. Example, when the $tpl['name'] data is 'auth_block', it means the template engine will start looking for `auth_block.html` following above priority.

We can also use sub-folder by setting the template name to something like this:

`$tpl['name'] = 'subfolder/file'`


Code samples
============

Below are a pieace of code showing howto use template.

File `plugin/core/auth/block.php`:

```
...
...
// prepare template data
$tpl = array(
    'name' => 'auth_block',
	'var' => array(
		'ERROR' => $error_content,
		'HTTP_PATH_BASE' => $core_config['http_path']['base'],
		'Home' => _('Home'),
	),
	'ifs' => array(
		'valid' => auth_isvalid(),
	),
);

// apply template data and inject $user_config to template
// note: _p() is currently only a shortcut to echo()
_p(tpl_apply($tpl, array('user_config')));
...
...
```

Template file `plugin/core/auth/templates/auth_block.html`:

```
<div align='center'>

{ERROR}

<if.valid>
<p>You are currently logged in as {{ _p($user_config['username']); }}
</if.valid>

<p><a href='{HTTP_PATH_BASE}'>{Home}</a></p>

</div>
```

More examples, with demonstrations of logic IF and LOOP in templates, are located in:

- inc/app/main.php
- inc/user/user_inbox.php
- themes/common/templates/user_inbox.html
- inc/user/send_sms.php
- themes/common/templates/send_sms.html
- plugin/feature/inboxgroup/inboxgroup.php
- plugin/feature/inboxgroup/templates/inboxgroup.html
- plugin/tools/report/report.php
- plugin/tools/report/templates/report_admin.html
- plugin/tools/report/templates/report_user.html
- plugin/gateway/nexmo/nexmo.php
- plugin/gateway/nexmo/templates/nexmo.html
