<?php
$results = $_GET['results'];
$answers = $_GET['answers'];
include("lib/pChart/class/pData.class.php");
include("lib/pChart/class/pDraw.class.php");
include("lib/pChart/class/pPie.class.php");
include("lib/pChart/class/pImage.class.php");
/* Create and populate the pData object */
$MyData = new pData();   
$MyData->addPoints(explode(",", $results),"ScoreA");  
$MyData->setSerieDescription("ScoreA","Application A");
/* Define the absissa serie */
$MyData->addPoints(explode(",", $answers),"Labels");
$MyData->setAbscissa("Labels");
/* Create the pChart object */
$myPicture = new pImage(700,400,$MyData,TRUE);
/* Set the default font properties */ 
$myPicture->setFontProperties(array("FontName"=>"pChart/fonts/pf_arma_five.ttf","FontSize"=>6,"R"=>80,"G"=>80,"B"=>80));
/* Create the pPie object */ 
$PieChart = new pPie($myPicture,$MyData);
/* Define the slice color */
$PieChart->setSliceColor(0,array("R"=>97,"G"=>77,"B"=>63));
$PieChart->setSliceColor(2,array("R"=>97,"G"=>113,"B"=>63));
/* Enable shadow computing */ 
$myPicture->setShadow(TRUE,array("X"=>3,"Y"=>3,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));
/* Draw a splitted pie chart */ 
$PieChart->draw3DPie(200,120,array("WriteValues"=>TRUE,"DataGapAngle"=>4,"DataGapRadius"=>5,"Border"=>TRUE));
/* Write the legend */
$myPicture->setFontProperties(array("FontName"=>"pChart/fonts/pf_arma_five.ttf","FontSize"=>6));
$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>20));
/* Write the legend box */ 
$myPicture->setFontProperties(array("FontName"=>"pChart/fonts/Silkscreen.ttf","FontSize"=>6,"R"=>255,"G"=>255,"B"=>255));
$PieChart->drawPieLegend(3,8,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL));
/* Render the picture (choose the best way) */
$myPicture->autoOutput();
?>