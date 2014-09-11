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

include ($c_path . "/lib/pChart/class/pData.class.php");
include ($c_path . "/lib/pChart/class/pDraw.class.php");
include ($c_path . "/lib/pChart/class/pPie.class.php");
include ($c_path . "/lib/pChart/class/pImage.class.php");

/* Create and populate the pData object */
$MyData = new pData();
$MyData->addPoints($results, "ScoreA");
$MyData->setSerieDescription("ScoreA", "Application A");
/* Define the absissa serie */
$MyData->addPoints($choices, "Labels");
$MyData->setAbscissa("Labels");
/* Create the pChart object */
$myPicture = new pImage(400, 200, $MyData, TRUE);
/* Set the default font properties */
$myPicture->setFontProperties(array(
	"FontName" => $c_path . "/lib/pChart/fonts/pf_arma_five.ttf",
	"FontSize" => 8,
	"R" => 80,
	"G" => 80,
	"B" => 80 
));
/* Create the pPie object */
$PieChart = new pPie($myPicture, $MyData);
/* Define the slice color */
$PieChart->setSliceColor(0, array(
	"R" => 97,
	"G" => 77,
	"B" => 63 
));
$PieChart->setSliceColor(2, array(
	"R" => 97,
	"G" => 113,
	"B" => 63 
));
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
$myPicture->setFontProperties(array(
	"FontName" => $c_path . "/lib/pChart/fonts/pf_arma_five.ttf",
	"FontSize" => 8 
));
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
	"FontName" => $c_path . "/lib/pChart/fonts/calibri.ttf",
	"FontSize" => 10,
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
