<?php
if(!valid()){forcenoaccess();};

switch ($op) {
    case "sampletools_list":
	sampletools_printout();
	break;
}

?>