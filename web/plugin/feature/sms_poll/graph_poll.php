<?php

/**
 * This file is part of playSMS.
 *
 * playSMS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * playSMS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with playSMS. If not, see <http://www.gnu.org/licenses/>.
 */
defined('_SECURE_') or die('Forbidden');

$c_path = $core_config['apps_path']['plug'] . '/feature/sms_poll';

include ($c_path . "/lib/pChart/pDraw.php");
include ($c_path . "/lib/pChart/pData.php");
include ($c_path . "/lib/pChart/pPie.php");
include ($c_path . "/lib/pChart/pColor.php");

use pChart\pDraw;
use pChart\pPie;

/* Create the pChart object */
$myPicture = new pDraw(400, 200);

/* Create and populate the pData object */
//$myPicture = new pData();
$myPicture->myData->addPoints($results, "ScoreA");
$myPicture->myData->setSerieDescription("ScoreA", "Application A");

/* Define the absissa serie */
$myPicture->myData->addPoints($choices, "Labels");
$myPicture->myData->setAbscissa("Labels");

/* Create the pPie object */
$PieChart = new pPie($myPicture);

/* Enable shadow computing */
$myPicture->setShadow(TRUE, array(
	"X" => 3,
	"Y" => 3,
	"R" => 0,
	"G" => 0,
	"B" => 0,
	"Alpha" => 10 
));
/* Draw a splitted pie chart */
$PieChart->draw3DPie(150, 120, array(
	"WriteValues" => TRUE,
	"DataGapAngle" => 4,
	"DataGapRadius" => 5,
	"Border" => TRUE 
));

/* Write the legend */
$myPicture->setShadow(TRUE, array(
	"X" => 1,
	"Y" => 1,
	"R" => 0,
	"G" => 0,
	"B" => 0,
	"Alpha" => 20 
));

/* Write the legend box */
$myPicture->setFontProperties(array(
	"FontName" => $c_path . "/lib/pChart/fonts/MankSans.ttf",
	"FontSize" => 8,
	"R" => 100,
	"G" => 100,
	"B" => 100 
));
$PieChart->drawPieLegend(3, 8, array(
	"Style" => LEGEND_NOBORDER,
	"Mode" => LEGEND_HORIZONTAL 
));

/* Render the picture (choose the best way) */
ob_end_clean();
header('Content-disposition: filename=sms_poll_graph.png');
$myPicture->autoOutput();
