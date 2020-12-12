<?php

defined('_SECURE_') or die('Forbidden');

error_reporting(0);

use CpChart\Chart\Pie;
use CpChart\Data;
use CpChart\Image;

/* Create and populate the Data object */
$data = new Data();
$data->addPoints($results, "ScoreA");
$data->setSerieDescription("ScoreA", "Application A");

/* Define the absissa serie */
$data->addPoints($choices, "Labels");
$data->setAbscissa("Labels");

/* Create the Image object */
$sms_poll_image_width = 400;
$sms_poll_image_height = 200;
$image = new Image($sms_poll_image_width, $sms_poll_image_height, $data, true);

/* Create the pPie object */
$pieChart = new Pie($image, $data);

/* Draw an AA pie chart */
$sms_poll_image_width_center = round($sms_poll_image_width / 2 * 0.95);
$sms_poll_image_height_center = round($sms_poll_image_height / 2 * 1.18);
$pieChart->draw3DPie($sms_poll_image_width_center, $sms_poll_image_height_center, ["DrawLabels" => true, "Border" => true]);

/* Render the picture (choose the best way) */
ob_end_clean();
header('Content-disposition: filename=sms_poll_graph.png');
$image->autoOutput();
