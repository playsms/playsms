<?php
if(!valid()){forcenoaccess();};

switch ($op) {
    case "sampletools_list":
	tools_sampletools_printout();
	break;
}

?>