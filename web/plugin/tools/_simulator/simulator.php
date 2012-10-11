<?php
defined('_SECURE_') or die('Forbidden');
if(!valid()){forcenoaccess();};

// error messages
$error_content = '';
if ($err = $_SESSION['error_string']) {
	$error_content = "<div class=error_string>$err</div>";
}

switch ($op) {
	case 'simulator_list':
		$content = '<h2>'._('Simulator').'</h2><p />';
		if ($error_content) {
			$content .= '<p>'.$error_content.'</p>';
		}
		$content .= "
			<form method='post' action='index.php?app=menu&inc=tools_simulator&op=submit'>
                        <p>Message: <input type='text' name='message'></p>
			<p><input class='button' type='submit' value='"._('Submit')."'></p>
			</form>
		";
		echo $content;
		break;
        case 'submit':
                $content = '<h2>'._('Simulator').'</h2><p />';
                break;
}

?>