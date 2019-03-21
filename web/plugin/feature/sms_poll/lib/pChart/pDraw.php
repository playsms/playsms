<?php
/*
pDraw - class extension with drawing methods

Version     : 2.3.0-dev
Made by     : Jean-Damien POGOLOTTI
Maintainedby: Momchil Bozhinov
Last Update : 01/02/2018

This file can be distributed under the license you can find at:
http://www.pchart.net/license

You can find the whole class documentation on the pChart web site.
*/

namespace pChart;

/* The GD extension is mandatory */
if (!function_exists("gd_info")) {
	echo "GD extension must be loaded. \r\n";
	exit();
}

use pChart\pException;
use pChart\pColor;
use pChart\pColorGradient;
use pChart\pData;

define("DIRECTION_VERTICAL", 690001);
define("DIRECTION_HORIZONTAL", 690002);
define("SCALE_POS_LEFTRIGHT", 690101);
define("SCALE_POS_TOPBOTTOM", 690102);
define("SCALE_MODE_FLOATING", 690201);
define("SCALE_MODE_START0", 690202);
define("SCALE_MODE_ADDALL", 690203);
define("SCALE_MODE_ADDALL_START0", 690204);
define("SCALE_MODE_MANUAL", 690205);
define("SCALE_SKIP_NONE", 690301);
define("SCALE_SKIP_SAME", 690302);
define("SCALE_SKIP_NUMBERS", 690303);
define("TEXT_ALIGN_TOPLEFT", 690401);
define("TEXT_ALIGN_TOPMIDDLE", 690402);
define("TEXT_ALIGN_TOPRIGHT", 690403);
define("TEXT_ALIGN_MIDDLELEFT", 690404);
define("TEXT_ALIGN_MIDDLEMIDDLE", 690405);
define("TEXT_ALIGN_MIDDLERIGHT", 690406);
define("TEXT_ALIGN_BOTTOMLEFT", 690407);
define("TEXT_ALIGN_BOTTOMMIDDLE", 690408);
define("TEXT_ALIGN_BOTTOMRIGHT", 690409);
define("POSITION_TOP", 690501);
define("POSITION_BOTTOM", 690502);
define("LABEL_POS_LEFT", 690601);
define("LABEL_POS_CENTER", 690602);
define("LABEL_POS_RIGHT", 690603);
define("LABEL_POS_TOP", 690604);
define("LABEL_POS_BOTTOM", 690605);
define("LABEL_POS_INSIDE", 690606);
define("LABEL_POS_OUTSIDE", 690607);
define("ORIENTATION_HORIZONTAL", 690701);
define("ORIENTATION_VERTICAL", 690702);
define("ORIENTATION_AUTO", 690703);
define("LEGEND_NOBORDER", 690800);
define("LEGEND_BOX", 690801);
define("LEGEND_ROUND", 690802);
define("LEGEND_VERTICAL", 690901);
define("LEGEND_HORIZONTAL", 690902);
define("LEGEND_FAMILY_BOX", 691051);
define("LEGEND_FAMILY_CIRCLE", 691052);
define("LEGEND_FAMILY_LINE", 691053);
define("DISPLAY_AUTO", 691001);
define("DISPLAY_MANUAL", 691002);
define("LABELING_ALL", 691011);
define("LABELING_DIFFERENT", 691012);
define("BOUND_MIN", 691021);
define("BOUND_MAX", 691022);
define("BOUND_BOTH", 691023);
define("BOUND_LABEL_POS_TOP", 691031);
define("BOUND_LABEL_POS_BOTTOM", 691032);
define("BOUND_LABEL_POS_AUTO", 691033);
define("CAPTION_LEFT_TOP", 691041);
define("CAPTION_RIGHT_BOTTOM", 691042);
define("GRADIENT_SIMPLE", 691051);
define("GRADIENT_EFFECT_CAN", 691052);
define("LABEL_TITLE_NOBACKGROUND", 691061);
define("LABEL_TITLE_BACKGROUND", 691062);
define("LABEL_POINT_NONE", 691071);
define("LABEL_POINT_CIRCLE", 691072);
define("LABEL_POINT_BOX", 691073);
define("ZONE_NAME_ANGLE_AUTO", 691081);
define("PI", 3.14159265);
define("ALL", 69);
define("NONE", 31);
define("AUTO", 690000);
define("OUT_OF_SIGHT", -10000000000000);

class pDraw
{
	/* Image settings, size, quality, .. */
	var $XSize = 0; // Width of the picture
	var $YSize = 0; // Height of the picture
	var $Picture; // GD picture object
	var $Antialias = TRUE; // Turn anti alias on or off
	var $AntialiasQuality = 0; // Quality of the anti aliasing implementation (0-1)
	var $TransparentBackground = FALSE; // Just to know if we need to flush the alpha channels when rendering
	/* Graph area settings */
	var $GraphAreaX1 = 0; // Graph area X origin
	var $GraphAreaY1 = 0; // Graph area Y origin
	var $GraphAreaX2 = 0; // Graph area bottom right X position
	var $GraphAreaY2 = 0; // Graph area bottom right Y position
	var $GraphAreaXdiff = 0; // $X2 - $X1
	var $GraphAreaYdiff = 0; // $Y2 - $Y1
	/* Scale settings */
	# var $ScaleMinDivHeight = 20; // Minimum height for scale divs # UNUSED
	/* Font properties */
	var $FontName = "pChart/fonts/GeosansLight.ttf"; // Default font file
	var $FontSize = 12; // Default font size
	#var $FontBox = NULL; // Return the bounding box of the last written string
	var $FontColor; // Default color settings
	/* Shadow properties */
	var $Shadow = FALSE; // Turn shadows on or off
	var $ShadowX = 0; // X Offset of the shadow
	var $ShadowY = 0; // Y Offset of the shadow
	var $ShadowColor;
	var $ShadowAllocatedColor;

	/* Data Set */
	var $myData;
	
	/* Class constructor */
	function __construct(int $XSize, int $YSize, bool $TransparentBackground = FALSE)
	{

		$this->myData = new pData();

		$this->XSize = $XSize;
		$this->YSize = $YSize;

		if (!($XSize > 0 && $YSize > 0)){
			throw pException::InvalidDimentions("Image dimensions (X * Y) must be > 0!");
		}

		/* Momchil: I will leave it here in case someone needs it
		$memory_limit = ini_get("memory_limit");
		if (intval($memory_limit) * 1024 * 1024 < $XSize * $YSize * 3 * 1.7){ # Momchil: for black & white gifs -> use 1 and not 3
			echo "Memory limit: ".$memory_limit." Mb ".PHP_EOL;
			echo "Estimated required: ".round(($XSize * $YSize * 3 * 1.7)/(1024 * 1024), 3)." Mb ".PHP_EOL;
			$this->Picture = imagecreatetruecolor(1, 1);
			throw pException::InvalidDimentions("Can not allocate enough memory for an image that big! Check your PHP memory_limit configuration option.");
		}
		*/

		$this->Picture = imagecreatetruecolor($XSize, $YSize);
		
		$this->TransparentBackground = $TransparentBackground;
		if ($TransparentBackground) {
			imagealphablending($this->Picture, FALSE); #  TRUE by default on True color images
			imagefilledrectangle($this->Picture, 0, 0, $XSize, $YSize, imagecolorallocatealpha($this->Picture, 255, 255, 255, 127));
			imagealphablending($this->Picture, TRUE);
			imagesavealpha($this->Picture, TRUE);
		} else {
			# Momchil: $this->allocateColor(new pColor(255,255,255,100)); sets alpha at 1.27 which is not completely transparent
			imagefilledrectangle($this->Picture, 0, 0, $XSize, $YSize, imagecolorallocatealpha($this->Picture, 255, 255, 255, 0));
		}
		
		$this->ShadowAllocatedColor = $this->allocateColor(new pColor(0));
		
		/* default font color */
		$this->FontColor = new pColor(255);
	}

	function __destruct()
	{
		if (is_resource($this->Picture)){
			imagedestroy($this->Picture);
		}
	}
	
	/* Destroy the image and start over. UNUSED */
	function resize(int $XSize, int $YSize)
	{
		if (is_resource($this->Picture)){
			imagedestroy($this->Picture);
		}
		$this->__construct($XSize, $YSize, $this->TransparentBackground);
	}

	/* Fix box coordinates */
	function fixBoxCoordinates($Xa, $Ya, $Xb, $Yb)
	{
		return [
			min($Xa, $Xb),
			min($Ya, $Yb),
			max($Xa, $Xb),
			max($Ya, $Yb)
		];
	}

	/* Draw a polygon */
	function drawPolygon(array $Points, array $Format = [])
	{
		$Color = isset($Format["Color"]) ? $Format["Color"] : new pColor(0);
		$NoFill = isset($Format["NoFill"]) ? $Format["NoFill"] : FALSE;
		$NoBorder = isset($Format["NoBorder"]) ? $Format["NoBorder"] : FALSE;
		$BorderColor = isset($Format["BorderColor"]) ? $Format["BorderColor"] : $Color->newOne()->AlphaSlash(2);
		if (isset($Format["Surrounding"])){
			$BorderColor->RGBChange($Format["Surrounding"]);
		}
		
		/* Calling the ImageFilledPolygon() function over the $Points array used to round it */
		
		$PointCount = count($Points);
		
		$RestoreShadow = $this->Shadow;
		
		if (!$NoFill) {
			if ($this->Shadow) {
				$this->Shadow = FALSE;
				$Shadow = []; 
				for ($i = 0; $i <= $PointCount - 1; $i += 2) {
					$Shadow[] = $Points[$i] + $this->ShadowX;
					$Shadow[] = $Points[$i + 1] + $this->ShadowY;
				}

				$this->drawPolygon($Shadow, ["Color" => $this->ShadowColor,"NoBorder" => TRUE]);
			}

			if ($PointCount >= 6) {
				imageFilledPolygon($this->Picture, $Points, $PointCount / 2, $this->allocateColor($Color));
			}
		}

		if (!$NoBorder) {
			
			$BorderSettings = ["Color" => ($NoFill) ? $Color : $BorderColor];

			for ($i = 0; $i <= $PointCount - 1; $i += 2) {
				if (isset($Points[$i + 2])) {
					if (!($Points[$i] == $Points[$i + 2] && $Points[$i + 1] == $Points[$i + 3])){
						$this->drawLine($Points[$i], $Points[$i + 1], $Points[$i + 2], $Points[$i + 3], $BorderSettings);
					}
				} else {
					if (!($Points[$i] == $Points[0] && $Points[$i + 1] == $Points[1])){
						$this->drawLine($Points[$i], $Points[$i + 1], $Points[0], $Points[1], $BorderSettings);
					}
				}
			}
		}

		$this->Shadow = $RestoreShadow;
	}

	/* Apply AALias correction to the rounded box boundaries */
	function offsetCorrection($Value, $Mode) # UNUSED
	{
		$Value = round($Value, 1);
		
		if ($Value == 0 && $Mode == 1) {
			 $ret = .9;
		} elseif ($Value == 0) {
			 $ret = 0;
		} else {
			$matrix = [
				1 => [1 => .9,.1 => .9,.2 => .8,.3 => .8,.4 => .7,.5 => .5,.6 => .8,.7 => .7,.8 => .6,.9 => .9],
				2 => [1 => .9,.1 => .1,.2 => .2,.3 => .3,.4 => .4,.5 => .5,.6 => .8,.7 => .7,.8 => .8,.9 => .9],
				3 => [1 => .9,.1 => .1,.2 => .2,.3 => .3,.4 => .4,.5 => .9,.6 => .6,.7 => .7,.8 => .4,.9 => .5],
				4 => [1 => -1,.1 => .1,.2 => .2,.3 => .3,.4 => .1,.5 => -.1,.6 => .8,.7 => .1,.8 => .1,.9 => .1]
			];
			$ret = $matrix[$Mode][$Value];
		}

		return $ret;

	}

	/* Draw a rectangle with rounded corners */
	function drawRoundedRectangle($X1, $Y1, $X2, $Y2, $Radius, array $Format = [])
	{
		$Color = isset($Format["Color"]) ? $Format["Color"] : new pColor(0);

		list($X1, $Y1, $X2, $Y2) = $this->fixBoxCoordinates($X1, $Y1, $X2, $Y2);
		($X2 - $X1 < $Radius) AND $Radius = floor(($X2 - $X1) / 2);
		($Y2 - $Y1 < $Radius) AND $Radius = floor(($Y2 - $Y1) / 2);
		$Options = ["Color" => $Color,"NoBorder" => TRUE];
		
		if ($Radius <= 0) {
			$this->drawRectangle($X1, $Y1, $X2, $Y2, $Options);
			return;
		}

		if ($this->Antialias) {
			$this->drawLine($X1 + $Radius, $Y1, $X2 - $Radius, $Y1, $Options);
			$this->drawLine($X2, $Y1 + $Radius, $X2, $Y2 - $Radius, $Options);
			$this->drawLine($X2 - $Radius, $Y2, $X1 + $Radius, $Y2, $Options);
			$this->drawLine($X1, $Y1 + $Radius, $X1, $Y2 - $Radius, $Options);
		} else {
			$AllocatedColor = $this->allocateColor($Color);
			imageline($this->Picture, $X1 + $Radius, $Y1, $X2 - $Radius, $Y1, $AllocatedColor);
			imageline($this->Picture, $X2, $Y1 + $Radius, $X2, $Y2 - $Radius, $AllocatedColor);
			imageline($this->Picture, $X2 - $Radius, $Y2, $X1 + $Radius, $Y2, $AllocatedColor);
			imageline($this->Picture, $X1, $Y1 + $Radius, $X1, $Y2 - $Radius, $AllocatedColor);
		}

		$Step = rad2deg(1/$Radius);

		for ($i = 0; $i <= 90; $i += $Step) {

			$cos1 = cos(deg2rad($i + 180)) * $Radius;
			$sin1 = sin(deg2rad($i + 180)) * $Radius;
			$cos2 = cos(deg2rad($i + 90)) * $Radius;
			$sin2 = sin(deg2rad($i + 90)) * $Radius;

			$X = $cos1 + $X1 + $Radius;
			$Y = $sin1 + $Y1 + $Radius;
			$this->drawAntialiasPixel($X, $Y, $Color);
			$X = $cos2 + $X1 + $Radius;
			$Y = $sin2 + $Y2 - $Radius;
			$this->drawAntialiasPixel($X, $Y, $Color);
			$X = -$cos1 + $X2 - $Radius;
			$Y = -$sin1 + $Y2 - $Radius;
			$this->drawAntialiasPixel($X, $Y, $Color);
			$X = -$cos2 + $X2 - $Radius;
			$Y = -$sin2 + $Y1 + $Radius;
			$this->drawAntialiasPixel($X, $Y, $Color);
		}

	}

	/* Draw a rectangle with rounded corners */
	function drawRoundedFilledRectangle($X1, $Y1, $X2, $Y2, $Radius, array $Format = [])
	{
		$Color = isset($Format["Color"]) ? $Format["Color"] : new pColor(0);
		$BorderColor = isset($Format["BorderColor"]) ? $Format["BorderColor"] : $Color->newOne();
		if (isset($Format["Surrounding"])){
			$BorderColor->RGBChange($Format["Surrounding"]);
		}

		/* Temporary fix for AA issue */
		$Y1 = floor($Y1);
		$Y2 = floor($Y2);
		$X1 = floor($X1);
		$X2 = floor($X2);
		
		list($X1, $Y1, $X2, $Y2) = $this->fixBoxCoordinates($X1, $Y1, $X2, $Y2);
		if ($X2 - $X1 < $Radius * 2) {
			$Radius = floor(($X2 - $X1) / 4);
		}

		if ($Y2 - $Y1 < $Radius * 2) {
			$Radius = floor(($Y2 - $Y1) / 4);
		}

		$RestoreShadow = $this->Shadow;
		if ($this->Shadow) {
			$this->Shadow = FALSE;
			$this->drawRoundedFilledRectangle($X1 + $this->ShadowX, $Y1 + $this->ShadowY, $X2 + $this->ShadowX, $Y2 + $this->ShadowY, $Radius, ["Color" => $this->ShadowColor]);
		}

		$Format = ["Color" => $Color,"NoBorder" => TRUE];
		if ($Radius <= 0) {
			$this->drawFilledRectangle($X1, $Y1, $X2, $Y2, $Format);
			return;
		}

		$YTop = $Y1 + $Radius;
		$YBottom = $Y2 - $Radius;
		$Step = rad2deg(1/$Radius);
		$Positions = [];
		$Radius--;
		$MinY = 0;
		$MaxY = 0;
		for ($i = 0; $i <= 90; $i += $Step) {
			$cos = cos(deg2rad($i + 180)) * $Radius;
			$Xp1 = $cos + $X1 + $Radius;
			$Xp2 = -$cos + $X2 - $Radius;
			$Yp = floor(sin(deg2rad($i + 180)) * $Radius + $YTop);
			($MinY == 0 || $Yp > $MinY) AND $MinY = $Yp;
			($Xp1 <= floor($X1)) AND $Xp1++;
			($Xp2 >= floor($X2)) AND $Xp2--;
			$Xp1++;
			if (!isset($Positions[$Yp])) {
				$Positions[$Yp]["X1"] = $Xp1;
				$Positions[$Yp]["X2"] = $Xp2;
			} else {
				$Positions[$Yp]["X1"] = ($Positions[$Yp]["X1"] + $Xp1) / 2;
				$Positions[$Yp]["X2"] = ($Positions[$Yp]["X2"] + $Xp2) / 2;
			}
			
			$cos = cos(deg2rad($i + 90)) * $Radius;
			$Xp1 = $cos + $X1 + $Radius;
			$Xp2 = -$cos + $X2 - $Radius;
			$Yp = floor(sin(deg2rad($i + 90)) * $Radius + $YBottom);
			($MaxY == 0 || $Yp < $MaxY) AND $MaxY = $Yp;
			($Xp1 <= floor($X1)) AND $Xp1++;
			($Xp2 >= floor($X2)) AND $Xp2--;
			$Xp1++;
			if (!isset($Positions[$Yp])) {
				$Positions[$Yp]["X1"] = $Xp1;
				$Positions[$Yp]["X2"] = $Xp2;
			} else {
				$Positions[$Yp]["X1"] = ($Positions[$Yp]["X1"] + $Xp1) / 2;
				$Positions[$Yp]["X2"] = ($Positions[$Yp]["X2"] + $Xp2) / 2;
			}
		}

		foreach($Positions as $Yp => $Bounds) {
			$X1 = $Bounds["X1"];
			$X1Dec = $this->getFirstDecimal($X1);
			if ($X1Dec != 0) {
				$X1 = ceil($X1);
			}

			$X2 = $Bounds["X2"];
			$X2Dec = $this->getFirstDecimal($X2);
			if ($X2Dec != 0) {
				$X2 = floor($X2) - 1;
			}

			imageline($this->Picture, $X1, $Yp, $X2, $Yp, $this->allocateColor($Color));
		}

		$this->drawFilledRectangle($X1, $MinY + 1, floor($X2), $MaxY - 1, $Format);
		$Radius++;
		$this->drawRoundedRectangle($X1, $Y1, $X2 + 1, $Y2 - 1, $Radius, ["Color" => $BorderColor]);
		$this->Shadow = $RestoreShadow;
	}

	/* Draw a rectangle */
	function drawRectangle($X1, $Y1, $X2, $Y2, array $Format = [])
	{
		$Color = isset($Format["Color"]) ? $Format["Color"] : new pColor(0);
		$Ticks = isset($Format["Ticks"]) ? $Format["Ticks"] : NULL;
		$NoAngle = isset($Format["NoAngle"]) ? $Format["NoAngle"] : FALSE;
		
		($X1 > $X2) AND list($X1, $X2) = [$X2,$X1];
		($Y1 > $Y2) AND list($Y1, $Y2) = [$Y2,$Y1];

		$Format = ["Color" => $Color, "Ticks" => $Ticks];
		if ($this->Antialias) {
			if ($NoAngle) {
				$this->drawLine($X1 + 1, $Y1, $X2 - 1, $Y1, $Format);
				$this->drawLine($X2, $Y1 + 1, $X2, $Y2 - 1, $Format);
				$this->drawLine($X2 - 1, $Y2, $X1 + 1, $Y2, $Format);
				$this->drawLine($X1, $Y1 + 1, $X1, $Y2 - 1, $Format);
			} else {
				$this->drawLine($X1 + 1, $Y1, $X2 - 1, $Y1, $Format);
				$this->drawLine($X2, $Y1, $X2, $Y2, $Format);
				$this->drawLine($X2 - 1, $Y2, $X1 + 1, $Y2, $Format);
				$this->drawLine($X1, $Y1, $X1, $Y2, $Format);
			}
		} else {
			imagerectangle($this->Picture, $X1, $Y1, $X2, $Y2, $this->allocateColor($Color));
		}
	}

	/* Draw a filled rectangle */
	function drawFilledRectangle($X1, $Y1, $X2, $Y2, array $Format = [])
	{
		$Color = isset($Format["Color"]) ? $Format["Color"] : new pColor(0);
		$BorderColor = isset($Format["BorderColor"]) ? $Format["BorderColor"] : NULL;
		if (isset($Format["Surrounding"])){
			$BorderColor = $Color->newOne()->RGBChange($Format["Surrounding"]);
		}
		$NoBorder = isset($Format["NoBorder"]) ? $Format["NoBorder"] : FALSE;
		$Ticks = isset($Format["Ticks"]) ? $Format["Ticks"] : NULL;
		$NoAngle = isset($Format["NoAngle"]) ? $Format["NoAngle"] : FALSE;
		$Dash = isset($Format["Dash"]) ? $Format["Dash"] : FALSE;
		$DashStep = isset($Format["DashStep"]) ? $Format["DashStep"] : 4;
		$DashColor = isset($Format["DashColor"]) ? $Format["DashColor"] : new pColor(0,0,0,$Color->Alpha);

		($X1 > $X2) AND list($X1, $X2) = [$X2,$X1];
		($Y1 > $Y2) AND list($Y1, $Y2) = [$Y2,$Y1];
		
		$X1c = ceil($X1);
		$Y1c = ceil($Y1);
		$X2f = floor($X2);
		$Y2f = floor($Y2);

		$RestoreShadow = $this->Shadow;
		if ($this->Shadow) {
			$this->Shadow = FALSE;
			$this->drawFilledRectangle($X1 + $this->ShadowX, $Y1 + $this->ShadowY, $X2 + $this->ShadowX, $Y2 + $this->ShadowY, ["Color" => $this->ShadowColor,"Ticks" => $Ticks,"NoAngle" => $NoAngle]);
		}

		$AllocatedColor = $this->allocateColor($Color);

		if ($NoAngle) {
			imagefilledrectangle($this->Picture, $X1c + 1, $Y1c, $X2f - 1, $Y2f, $AllocatedColor);
			imageline($this->Picture, $X1c, $Y1c + 1, $X1c, $Y2f - 1, $AllocatedColor);
			imageline($this->Picture, $X2f, $Y1c + 1, $X2f, $Y2f - 1, $AllocatedColor);
		} else {
			imagefilledrectangle($this->Picture, $X1c, $Y1c, $X2f, $Y2f, $AllocatedColor);
		}

		if ($Dash) {
			if (!is_null($BorderColor)) {
				$iX1 = $X1 + 1;
				$iY1 = $Y1 + 1;
				$iX2 = $X2 - 1;
				$iY2 = $Y2 - 1;
			} else {
				$iX1 = $X1;
				$iY1 = $Y1;
				$iX2 = $X2;
				$iY2 = $Y2;
			}

			$Y = $iY1 - $DashStep;
			for ($X = $iX1; $X <= $iX2 + ($iY2 - $iY1); $X = $X + $DashStep) {
				$Y += $DashStep;
				if ($X > $iX2) {
					$Xa = $X - ($X - $iX2);
					$Ya = $iY1 + ($X - $iX2);
				} else {
					$Xa = $X;
					$Ya = $iY1;
				}

				if ($Y > $iY2) {
					$Xb = $iX1 + ($Y - $iY2);
					$Yb = $Y - ($Y - $iY2);
				} else {
					$Xb = $iX1;
					$Yb = $Y;
				}

				imageline($this->Picture, $Xa, $Ya, $Xb, $Yb, $this->allocateColor($DashColor));
			}
		}

		if ($this->Antialias && !$NoBorder) {
			if ($X1 < $X1c) {
				imageline($this->Picture, $X1c - 1, $Y1c, $X1c - 1, $Y2f, $this->allocateColor($Color->newOne()->AlphaMultiply($X1c - $X1)));
			}

			if ($Y1 < $Y1c) {
				imageline($this->Picture, $X1c, $Y1c - 1, $X2f, $Y1c - 1, $this->allocateColor($Color->newOne()->AlphaMultiply($Y1c - $Y1)));
			}

			if ($X2 > $X2f) {
				imageline($this->Picture, $X2f + 1, $Y1c, $X2f + 1, $Y2f, $this->allocateColor($Color->newOne()->AlphaMultiply(.5 - ($Y2 - $Y2f))));
			}

			if ($Y2 > $Y2f) {
				imageline($this->Picture, $X1c, $Y2f + 1, $X2f, $Y2f + 1, $this->allocateColor($Color->newOne()->AlphaMultiply(.5 - ($Y2 - $Y2f))));
			}
		}

		if (!is_null($BorderColor)) {
			$this->drawRectangle($X1, $Y1, $X2, $Y2, ["Color" => $BorderColor, "Ticks" => $Ticks,"NoAngle" => $NoAngle]);
		}

		$this->Shadow = $RestoreShadow;
	}

	/* Draw a rectangular marker of the specified size */
	function drawRectangleMarker($X, $Y, array $Format = [])
	{
		$Size = isset($Format["Size"]) ? $Format["Size"] : 4;
		$HalfSize = floor($Size / 2);
		$this->drawFilledRectangle($X - $HalfSize, $Y - $HalfSize, $X + $HalfSize, $Y + $HalfSize, $Format);
	}

	/* Drawn a spline based on the bezier function */
	function drawSpline(array $Coordinates, array $Format = [])
	{
		$NoDraw = isset($Format["NoDraw"]) ? $Format["NoDraw"] : FALSE;
		$Force = isset($Format["Force"]) ? $Format["Force"] : 30;
		$Forces = isset($Format["Forces"]) ? $Format["Forces"] : [];
		$Result = [];
		
		$count = count($Coordinates)-1;

		for ($i = 1; $i <= $count; $i++) {
			$X1 = $Coordinates[$i - 1][0];
			$Y1 = $Coordinates[$i - 1][1];
			$X2 = $Coordinates[$i][0];
			$Y2 = $Coordinates[$i][1];
			if (!empty($Forces)) { # Momchil: used in Scatter
				$Force = $Forces[$i];
			}

			/* First segment */
			if ($i == 1) {
				$Xv1 = $X1;
				$Yv1 = $Y1;
			} else {
				$Angle1 = $this->getAngle($XLast, $YLast, $X1, $Y1);
				$Angle2 = $this->getAngle($X1, $Y1, $X2, $Y2);
				$XOff = cos(deg2rad($Angle2)) * $Force + $X1;
				$YOff = sin(deg2rad($Angle2)) * $Force + $Y1;
				$Xv1 = cos(deg2rad($Angle1)) * $Force + $XOff;
				$Yv1 = sin(deg2rad($Angle1)) * $Force + $YOff;
			}

			/* Last segment */
			if ($i == $count) {
				$Xv2 = $X2;
				$Yv2 = $Y2;
			} else {
				# Momchil: it is possible to save some calcs here
				# $Angle2 is already defined if not 0,1,Last member
				# cos(($Angle2 + 180) * PI / 180) is negated cos($Angle2 * PI / 180) (or at least close enough)
				# Not worth the code complexity (very few calls)
				$Angle1 = $this->getAngle($X2, $Y2, $Coordinates[$i + 1][0], $Coordinates[$i + 1][1]);
				$Angle2 = $this->getAngle($X1, $Y1, $X2, $Y2);
				$XOff = cos(deg2rad($Angle2 + 180)) * $Force + $X2;
				$YOff = sin(deg2rad($Angle2 + 180)) * $Force + $Y2;
				$Xv2 = cos(deg2rad($Angle1 + 180)) * $Force + $XOff;
				$Yv2 = sin(deg2rad($Angle1 + 180)) * $Force + $YOff;
			}

			$Path = $this->drawBezier($X1, $Y1, $X2, $Y2, $Xv1, $Yv1, $Xv2, $Yv2, $Format);
			if ($NoDraw) {
				$Result[] = $Path;
			}

			$XLast = $X1;
			$YLast = $Y1;
		}

		return $Result;
	}

	/* Draw a bezier curve with two controls points */
	function drawBezier($X1, $Y1, $X2, $Y2, $Xv1, $Yv1, $Xv2, $Yv2, array $Format = [])
	{
		$Color = isset($Format["Color"]) ? $Format["Color"] : new pColor(0);
		$Segments = isset($Format["Segments"]) ? $Format["Segments"] : NULL;
		$Ticks = isset($Format["Ticks"]) ? $Format["Ticks"] : NULL;
		$NoDraw = isset($Format["NoDraw"]) ? $Format["NoDraw"] : FALSE;
		$Weight = isset($Format["Weight"]) ? $Format["Weight"] : NULL;
		$ShowControl = isset($Format["ShowControl"]) ? $Format["ShowControl"] : FALSE;
		$DrawArrow = isset($Format["DrawArrow"]) ? $Format["DrawArrow"] : FALSE;
		$ArrowSize = isset($Format["ArrowSize"]) ? $Format["ArrowSize"] : 10;
		$ArrowRatio = isset($Format["ArrowRatio"]) ? $Format["ArrowRatio"] : .5;
		$ArrowTwoHeads = isset($Format["ArrowTwoHeads"]) ? $Format["ArrowTwoHeads"] : FALSE;
		
		if (is_null($Segments)) {
			$Length = hypot(($X2 - $X1),($Y2 - $Y1));
			$Precision = ($Length * 125) / 1000;
		} else {
			$Precision = $Segments; # used here: example.spring.complex.php
		}

		$P = [
			["X" => $X1,  "Y" => $Y1],
			["X" => $Xv1, "Y" => $Yv1],
			["X" => $Xv2, "Y" => $Yv2],
			["X" => $X2,  "Y" => $Y2]
		];

		/* Compute the bezier points */
		$Q = [];
		$ID = 0;
		for ($i = 0; $i <= $Precision; $i++) {
			$u = $i / $Precision;
			$C = [
				pow((1 - $u),3),
				($u * 3) * (1 - $u) * (1 - $u),
				3 * $u * $u * (1 - $u),
				pow($u,3)
			];
			
			$Q[$ID] = ["X" => 0, "Y" => 0];
			
			for ($j = 0; $j <= 3; $j++) {
				$Q[$ID]["X"] += ($P[$j]["X"] * $C[$j]);
				$Q[$ID]["Y"] += ($P[$j]["Y"] * $C[$j]);
			}

			$ID++;
		}

		$Q[$ID]["X"] = $X2;
		$Q[$ID]["Y"] = $Y2;
		
		if ($NoDraw) {
			return $Q;
		}
		
		$Cpt = 1;
		$Mode = TRUE;
		$Qcount = count($Q);
		
		/* Draw the bezier */
		for($i=1;$i<$Qcount;$i++){ # omits the first member on purpose
			list($Cpt, $Mode) = $this->drawLine($Q[$i - 1]["X"], $Q[$i - 1]["Y"], $Q[$i]["X"], $Q[$i]["Y"], ["Color" => $Color,"Ticks" => $Ticks,"Cpt" => $Cpt,"Mode" => $Mode,"Weight" => $Weight]);
		}
		
		/* Display the control points */
		if ($ShowControl) {
			$Xv1 = floor($Xv1);
			$Yv1 = floor($Yv1);
			$Xv2 = floor($Xv2);
			$Yv2 = floor($Yv2);
			$this->drawLine($X1, $Y1, $X2, $Y2, ["Color" => new pColor(0,0,0,30)]);
			$this->drawRectangleMarker($Xv1, $Yv1, ["Color" => new pColor(255,0,0,100),"BorderColor" => new pColor(255),"Size" => 4]);
			$this->drawText($Xv1 + 4, $Yv1, "v1");
			$this->drawRectangleMarker($Xv2, $Yv2, ["Color" => new pColor(0,0,255,100),"BorderColor" => new pColor(255),"Size" => 4]);
			$this->drawText($Xv2 + 4, $Yv2, "v2");
		}

		if ($DrawArrow) {
			$ArrowSettings = ["FillColor" => $Color,"Size" => $ArrowSize,"Ratio" => $ArrowRatio];
			if ($ArrowTwoHeads){
				/* Get the first segment */
				$FirstTwo = array_slice($Q, 0, 2);
				$this->drawArrow($FirstTwo[1]["X"], $FirstTwo[1]["Y"], $FirstTwo[0]["X"], $FirstTwo[0]["Y"], $ArrowSettings);
			}
			/* Get the last segment */
			$LastTwo = array_slice($Q, -2, 2);
			$this->drawArrow($LastTwo[0]["X"], $LastTwo[0]["Y"],$LastTwo[1]["X"], $LastTwo[1]["Y"], $ArrowSettings);
		}

		return $Q;
	}

	/* Draw a line between two points */
	function drawLine($X1, $Y1, $X2, $Y2, array $Format = []) # FAST
	{
		$Color = isset($Format["Color"]) ? $Format["Color"] : new pColor(0);
		$Cpt = isset($Format["Cpt"]) ? $Format["Cpt"] : 1;
		$Threshold = isset($Format["Threshold"]) ? $Format["Threshold"] : [];
		$Ticks = isset($Format["Ticks"]) ? $Format["Ticks"] : NULL;
		$Weight = isset($Format["Weight"]) ? $Format["Weight"] : NULL;
		$Mode = isset($Format["Mode"]) ? $Format["Mode"] : 1;
		
		# NULL == 0
		# Keep it as some of the examples pass 0 for Ticks
		# e.g. example.drawArrow.php
		if ($Ticks == 0){
			$Ticks = NULL;
		}

		if ($this->Antialias == FALSE && is_null($Ticks)) {
			if ($this->Shadow) {
				imageline($this->Picture, $X1 + $this->ShadowX, $Y1 + $this->ShadowY, $X2 + $this->ShadowX, $Y2 + $this->ShadowY, $this->ShadowAllocatedColor);
			}

			imageline($this->Picture, $X1, $Y1, $X2, $Y2, $this->allocateColor($Color));
			return;
		}

		$Distance = hypot(($X2 - $X1), ($Y2 - $Y1));
		if ($Distance == 0) {
			# throw pException::InvalidDimentions("Line coordinates are not valid!");
			# Momchil: As of 28.01.2019 all examples generate a total of 20 invalid calls
			# Good way to check your math though
			return;
		}
		
		$XStep = ($X2 - $X1) / $Distance;
		$YStep = ($Y2 - $Y1) / $Distance;

		/* Derivative algorithm for overweighted lines, re-route to polygons primitives */
		if (!is_null($Weight)) {
			$Angle = $this->getAngle($X1, $Y1, $X2, $Y2);
			$AngleCosPlus90 =  cos(deg2rad($Angle + 90)) * $Weight;
			$AngleSinPlus90 =  sin(deg2rad($Angle + 90)) * $Weight;
			
			$PolySettings = ["Color" => $Color];
			
			if (is_null($Ticks)) {
				$Points = [-$AngleCosPlus90 + $X1, -$AngleSinPlus90 + $Y1, $AngleCosPlus90 + $X1, $AngleSinPlus90 + $Y1, $AngleCosPlus90 + $X2, $AngleSinPlus90 + $Y2, -$AngleCosPlus90+ $X2, -$AngleSinPlus90 + $Y2];
				$this->drawPolygon($Points, $PolySettings);
			} else {
				for ($i = 0; $i <= $Distance; $i = $i + $Ticks * 2) {
					$Xa = $XStep * $i + $X1;
					$Ya = $YStep * $i + $Y1;
					$Xb = $XStep * ($i + $Ticks) + $X1;
					$Yb = $YStep * ($i + $Ticks) + $Y1;
					$Points = [-$AngleCosPlus90 + $Xa, -$AngleSinPlus90 + $Ya, $AngleCosPlus90 + $Xa, $AngleSinPlus90 + $Ya, $AngleCosPlus90 + $Xb, $AngleSinPlus90 + $Yb, -$AngleCosPlus90 + $Xb, -$AngleSinPlus90 + $Yb];
					$this->drawPolygon($Points, $PolySettings);
				}
			}

			return;
		}

		if (empty($Threshold) && is_null($Ticks)){ # Momchil: Fast path based on my test cases
			for ($i = 0; $i <= $Distance; $i++) {
				$this->drawAntialiasPixel($i * $XStep + $X1, $i * $YStep + $Y1, $Color);
			}

		} else {

			for ($i = 0; $i <= $Distance; $i++) {
				$X = $i * $XStep + $X1;
				$Y = $i * $YStep + $Y1;
				
				foreach($Threshold as $Parameters) {
					if ($Y <= $Parameters["MinX"] && $Y >= $Parameters["MaxX"]) {
						$Color = $Parameters["Color"];
					}
				}

				if (!is_null($Ticks)) {
					if ($Cpt % $Ticks == 0) {
						$Cpt = 0;
						$Mode ^= 1;
					}
					($Mode) AND $this->drawAntialiasPixel($X, $Y, $Color);
					$Cpt++;
				} else {
					$this->drawAntialiasPixel($X, $Y, $Color);
				}
			}

		}

	}

	/* Draw a circle */
	function drawCircle($Xc, $Yc, $Height, $Width, array $Format = [])
	{
	
		$Color = isset($Format["Color"]) ? $Format["Color"] : new pColor(0);
		$Ticks = isset($Format["Ticks"]) ? $Format["Ticks"] : NULL;
		$Mask = isset($Format["Mask"]) ? $Format["Mask"] : [];

		$Height = abs($Height);
		$Width = abs($Width);
		($Height == 0) AND $Height = 1;
		($Width == 0) AND $Width = 1;
		$Xc = floor($Xc);
		$Yc = floor($Yc);
		$RestoreShadow = $this->Shadow;
		
		if ($this->Shadow) {
			$this->Shadow = FALSE;
			$this->drawCircle($Xc + $this->ShadowX, $Yc + $this->ShadowY, $Height, $Width, ["Color" => $this->ShadowColor,"Ticks" => $Ticks]);
		}

		$Step = rad2deg(1/max($Width, $Height));
		$Mode = TRUE;
		$Cpt = 1;
		
		for ($i = 0; $i <= 360; $i += $Step) {
			$X = cos(deg2rad($i)) * $Height + $Xc;
			$Y = sin(deg2rad($i)) * $Width + $Yc;
			if (!is_null($Ticks)) {
				if ($Cpt % $Ticks == 0) {
					$Cpt = 0;
					$Mode ^= 1; # invert
				}

				if ($Mode) { 
					if (isset($Mask[$Xc])) {
						if (!in_array($Yc, $Mask[$Xc])) {
							$this->drawAntialiasPixel($X, $Y, $Color);
						} 
					} else {
						$this->drawAntialiasPixel($X, $Y, $Color);
					}
				}

				$Cpt++;
			} else {
				if (isset($Mask[$Xc])) {
					if (!in_array($Yc, $Mask[$Xc])) {
						$this->drawAntialiasPixel($X, $Y, $Color);
					} 
				} else {
					$this->drawAntialiasPixel($X, $Y, $Color);
				}
			}
		}

		$this->Shadow = $RestoreShadow;
	}

	/* Draw a filled circle */
	function drawFilledCircle(int $X, int $Y, int $Radius, array $Format = [])
	{

		$Color = isset($Format["Color"]) ? $Format["Color"] : new pColor(0);
		$BorderColor = isset($Format["BorderColor"]) ? $Format["BorderColor"] : NULL;
		if(isset($Format["Surrounding"])){
			$BorderColor = $Color->newOne()->RGBChange($Format["Surrounding"]);
		}
		$Ticks = isset($Format["Ticks"]) ? $Format["Ticks"] : NULL;

		$X = floor($X);
		$Y = floor($Y);
		$Radius = ($Radius == 0) ? 1 : abs($Radius);

		$RestoreShadow = $this->Shadow;
		if ($this->Shadow) {
			$this->Shadow = FALSE;
			$this->drawFilledCircle($X + $this->ShadowX, $Y + $this->ShadowY, $Radius, ["Color" => $this->ShadowColor,"Ticks" => $Ticks]);
		}

		$Mask = [];
		$AllocatedColor = $this->allocateColor($Color);
		for ($i = 0; $i <= $Radius * 2; $i++) {
			$Slice = sqrt($Radius * $Radius - ($Radius - $i) * ($Radius - $i));
			$XPos = floor($Slice);
			$YPos = $Y + $i - $Radius;
			#$AAlias = $Slice - floor($Slice); # Momchil: UNUSED
			$Mask[$X - $XPos][] = $YPos;
			$Mask[$X + $XPos][] = $YPos;
			imageline($this->Picture, $X - $XPos, $YPos, $X + $XPos, $YPos, $AllocatedColor);
		}

		if ($this->Antialias) {
			$this->drawCircle($X, $Y, $Radius, $Radius, ["Color" => $Color,"Ticks" => $Ticks, "Mask" => $Mask]);
		}
		
		if (!is_null($BorderColor)) {
			$this->drawCircle($X, $Y, $Radius, $Radius, ["Color" => $BorderColor,"Ticks" => $Ticks]);
		}

		$this->Shadow = $RestoreShadow;
	}

	/* Write text */
	function drawText($X, $Y, string $Text, array $Format = [])
	{
		$Color = isset($Format["Color"]) ? $Format["Color"] : $this->FontColor;
		$Angle = isset($Format["Angle"]) ? $Format["Angle"] : 0;
		$Align = isset($Format["Align"]) ? $Format["Align"] : TEXT_ALIGN_BOTTOMLEFT;
		$FontName = isset($Format["FontName"]) ? $Format["FontName"] : $this->FontName;
		$FontSize = isset($Format["FontSize"]) ? $Format["FontSize"] : $this->FontSize;
		$ShowOrigine = isset($Format["ShowOrigine"]) ? $Format["ShowOrigine"] : FALSE;
		$TOffset = isset($Format["TOffset"]) ? $Format["TOffset"] : 2;
		$DrawBox = isset($Format["DrawBox"]) ? $Format["DrawBox"] : FALSE;
		$DrawBoxBorder = isset($Format["DrawBoxBorder"]) ? $Format["DrawBoxBorder"] : TRUE;
		$BorderOffset = isset($Format["BorderOffset"]) ? $Format["BorderOffset"] : 6;
		$BoxRounded = isset($Format["BoxRounded"]) ? $Format["BoxRounded"] : FALSE;
		$RoundedRadius = isset($Format["RoundedRadius"]) ? $Format["RoundedRadius"] : 6;
		$BoxColor = isset($Format["BoxColor"]) ? $Format["BoxColor"] : new pColor(255,255,255,50);
		$BoxSurrounding = isset($Format["BoxSurrounding"]) ? $Format["BoxSurrounding"] : 0;
		$BoxBorderColor = isset($Format["BoxBorderColor"]) ? $Format["BoxBorderColor"] : $BoxColor->newOne();
		$NoShadow = isset($Format["NoShadow"]) ? $Format["NoShadow"] : FALSE;

		$Shadow = $this->Shadow;
		($NoShadow) AND $this->Shadow = FALSE;
		
		if ($BoxSurrounding != 0) {
			$BoxBorderColor->RGBChange(-$BoxSurrounding);
			$BoxBorderColor->Alpha = $BoxColor->Alpha;
		}

		if ($ShowOrigine) {
			$this->drawRectangleMarker($X, $Y, ["Color" => new pColor(255,0,0), "BorderColor" => new pColor(255), "Size" => 4]);
		}

		$TxtPos = $this->getTextBox($X, $Y, $FontName, $FontSize, $Angle, $Text);
		if ($DrawBox && in_array($Angle,[0,90,180,270])) {
			$T = ($Angle == 0) ? ["X" => - $TOffset, "Y" => $TOffset] : ["X" => 0, "Y" => 0];
			$X1 = min($TxtPos[0]["X"], $TxtPos[1]["X"], $TxtPos[2]["X"], $TxtPos[3]["X"]) - $BorderOffset + 3;
			$Y1 = min($TxtPos[0]["Y"], $TxtPos[1]["Y"], $TxtPos[2]["Y"], $TxtPos[3]["Y"]) - $BorderOffset;
			$X2 = max($TxtPos[0]["X"], $TxtPos[1]["X"], $TxtPos[2]["X"], $TxtPos[3]["X"]) + $BorderOffset + 3;
			$Y2 = max($TxtPos[0]["Y"], $TxtPos[1]["Y"], $TxtPos[2]["Y"], $TxtPos[3]["Y"]) + $BorderOffset - 3;
			$X1 = $X1 - $TxtPos[$Align]["X"] + $X + $T["X"];
			$Y1 = $Y1 - $TxtPos[$Align]["Y"] + $Y + $T["Y"];
			$X2 = $X2 - $TxtPos[$Align]["X"] + $X + $T["X"];
			$Y2 = $Y2 - $TxtPos[$Align]["Y"] + $Y + $T["Y"];
			$Settings = ["Color" => $BoxColor,"BorderColor" => $BoxBorderColor];
			if ($BoxRounded) {
				#Momchil: Visual fix applied
				$this->drawRoundedFilledRectangle($X1-3, $Y1, $X2, $Y2, $RoundedRadius, $Settings);
			} else {
				$this->drawFilledRectangle($X1, $Y1, $X2, $Y2, $Settings);
			}
		}

		$X = $X + $X - $TxtPos[$Align]["X"];
		$Y = $Y + $Y - $TxtPos[$Align]["Y"];
		if ($this->Shadow) {
			imagettftext($this->Picture, $FontSize, $Angle, $X + $this->ShadowX, $Y + $this->ShadowY, $this->ShadowAllocatedColor, realpath($FontName), $Text);
		}

		imagettftext($this->Picture, $FontSize, $Angle, $X, $Y, $this->AllocateColor($Color), realpath($FontName), $Text);
		$this->Shadow = $Shadow;
		
		return $TxtPos;
	}

	/* Draw a gradient within a defined area */
	function drawGradientArea($X1, $Y1, $X2, $Y2, $Direction, array $GradientColor, $Levels = NULL)
	{

		$GradientColor = new pColorGradient($GradientColor["StartColor"]->newOne(), $GradientColor["EndColor"]->newOne());

		/* Draw a gradient within a defined area */
		$Shadow = $this->Shadow;
		$this->Shadow = FALSE;
		if ($GradientColor->StartColor == $GradientColor->EndColor) {
			$this->drawFilledRectangle($X1, $Y1, $X2, $Y2, ["Color" => $GradientColor->StartColor]);
			return;
		}

		(!is_null($Levels)) AND $GradientColor->EndColor = $GradientColor->StartColor->newOne()->RGBChange($Levels);
		
		($X1 > $X2) AND list($X1, $X2) = [$X2,$X1];
		($Y1 > $Y2) AND list($Y1, $Y2) = [$Y2,$Y1];

		$Step = $GradientColor->FindStep();

		if ($Direction == DIRECTION_VERTICAL){

				$StepSize = abs($Y2 - $Y1)/ $Step;
				$GradientColor->SetSegments($Step);
				$StartY = $Y1;
				$EndY = floor($Y2) + 1;
				$LastY2 = $StartY;

				for ($i = 0; $i <= $Step; $i++) {

					$Y2 = floor($StartY + ($i * $StepSize));
					($Y2 > $EndY) AND $Y2 = $EndY;

					if (($Y1 != $Y2 && $Y1 < $Y2) || $Y2 == $EndY) {
						$this->drawFilledRectangle($X1, $Y1, $X2, $Y2, ["Color" => $GradientColor->getLatest()]);
						$LastY2 = max($LastY2, $Y2);
						$Y1 = $Y2 + 1;
					}

					$GradientColor->Next();
				}

				if ($LastY2 < $EndY) {
					for ($i = $LastY2 + 1; $i <= $EndY; $i++) {
						$this->drawLine($X1, $i, $X2, $i, ["Color" => $GradientColor->getLatest()]);
					}
				}

		} elseif ($Direction == DIRECTION_HORIZONTAL) {

				$StepSize = abs($X2 - $X1) / $Step;
				$GradientColor->SetSegments($Step);
				$StartX = $X1;
				$EndX = $X2;
				
				for ($i = 0; $i <= $Step; $i++) {

					$X2 = floor($StartX + ($i * $StepSize));
					($X2 > $EndX) AND $X2 = $EndX;

					if (($X1 != $X2 && $X1 < $X2) || $X2 == $EndX) {
						$this->drawFilledRectangle($X1, $Y1, $X2, $Y2, ["Color" => $GradientColor->getLatest()]);
						$X1 = $X2 + 1;
					}

					$GradientColor->Next();
				}

				if ($X2 < $EndX) {
					$this->drawFilledRectangle($X2, $Y1, $EndX, $Y2, ["Color" => $GradientColor->getLatest()]);
				}
		}

		$this->Shadow = $Shadow;
	}

	/* Draw an aliased pixel */
	function drawAntialiasPixel($X, $Y, pColor $Color) # FAST
	{
		# Momchil: example.drawingObjects -> drawRoundedFilledRectangle is set to start from -5
		if ($X < 0 || $Y < 0 || ceil($X) > $this->XSize || ceil($Y) > $this->YSize){
			return;
		}

		if (!$this->Antialias) {
			if ($this->Shadow) {
				# That can go out of range
				imagesetpixel($this->Picture, $X + $this->ShadowX, $Y + $this->ShadowY, $this->ShadowAllocatedColor);
			}

			imagesetpixel($this->Picture, $X, $Y, $this->allocateColor($Color));
			return;
		}

		$Xi = floor($X);
		$Yi = floor($Y);
		
		if ($Xi == $X && $Yi == $Y) {

			$this->drawAlphaPixel($X, $Y, $Color);

		} else {
			
			$Yleaf = $Y - $Yi;
			$Xleaf = $X - $Xi;

			# Momchil: Fast path: mostly zeros in my test cases
			# AntialiasQuality does not seem to be in use and is always 0
			# $Xleaf is always > 0 && $Yleaf > 0 => $AlphaX > 0
			if ($this->AntialiasQuality == 0) {
				switch(TRUE){
					case ($Yleaf == 0):
						$this->drawAlphaPixel($Xi, $Yi, $Color->newOne()->AlphaMultiply(1 - $Xleaf));
						$this->drawAlphaPixel($Xi + 1, $Yi, $Color->newOne()->AlphaMultiply($Xleaf));
						break;
					case ($Xleaf == 0):
						$this->drawAlphaPixel($Xi, $Yi, $Color->newOne()->AlphaMultiply(1 - $Yleaf));
						$this->drawAlphaPixel($Xi, $Yi + 1, $Color->newOne()->AlphaMultiply($Yleaf));
						break;
					default:
						$this->drawAlphaPixel($Xi, $Yi, $Color->newOne()->AlphaMultiply((1 - $Xleaf) * (1 - $Yleaf)));
						$this->drawAlphaPixel($Xi + 1, $Yi, $Color->newOne()->AlphaMultiply($Xleaf * (1 - $Yleaf)));
						$this->drawAlphaPixel($Xi, $Yi + 1, $Color->newOne()->AlphaMultiply((1 - $Xleaf) * $Yleaf));
						$this->drawAlphaPixel($Xi + 1, $Yi + 1, $Color->newOne()->AlphaMultiply($Xleaf * $Yleaf));
				}
			} else { # Momchil: no changes here
				$Alpha = $Color->Alpha;
				$Alpha1 = (1 - $Xleaf) * (1 - $Yleaf) * $Alpha;
				if ($Alpha1 > $this->AntialiasQuality) {
					$this->drawAlphaPixel($Xi, $Yi, $Color->newOne()->AlphaSet($Alpha1));
				}

				$Alpha2 = $Xleaf * (1 - $Yleaf) * $Alpha;
				if ($Alpha2 > $this->AntialiasQuality) {
					$this->drawAlphaPixel($Xi + 1, $Yi, $Color->newOne()->AlphaSet($Alpha2));
				}
				
				$Alpha3 = (1 - $Xleaf) * $Yleaf * $Alpha;
				if ($Alpha3 > $this->AntialiasQuality) {
					$this->drawAlphaPixel($Xi, $Yi + 1, $Color->newOne()->AlphaSet($Alpha3));
				}

				$Alpha4 = $Xleaf * $Yleaf * $Alpha;
				if ($Alpha4 > $this->AntialiasQuality) {
					$this->drawAlphaPixel($Xi + 1, $Yi + 1, $Color->newOne()->AlphaSet($Alpha4));
				}
			}

		}
	}

	/* Draw a semi-transparent pixel */
	function drawAlphaPixel($X, $Y, $Color) # FAST
	{
		if ($this->Shadow) {
			$myShadow = $this->ShadowColor->newOne()->AlphaMultiply(floor($Color->Alpha / 100));
			imagesetpixel($this->Picture, $X + $this->ShadowX, $Y + $this->ShadowY, $this->allocateColor($myShadow));
		}

		imagesetpixel($this->Picture, $X, $Y, $this->allocateColor($Color));
	}

	/* Allocate a color with transparency */
	function allocateColor($Color) # FAST
	{
		return imagecolorallocatealpha($this->Picture, $Color->R, $Color->G, $Color->B, (1.27 * (100 - $Color->Alpha)));
	}

	/* Load a PNG file and draw it over the chart */
	function drawFromPNG($X, $Y, $FileName)
	{
		$PicInfo = $this->getPicInfo($FileName);
		$PicInfo[2] = 'imagecreatefrompng'; # force PNG
		$this->drawFromPicture($PicInfo, $FileName, $X, $Y);
	}

	/* Load a GIF file and draw it over the chart */
	function drawFromGIF($X, $Y, $FileName)
	{
		$PicInfo = $this->getPicInfo($FileName);
		$PicInfo[2] = 'imagecreatefromgif'; # force GIF
		$this->drawFromPicture($PicInfo, $FileName, $X, $Y);
	}

	/* Load a JPEG file and draw it over the chart */
	function drawFromJPG($X, $Y, $FileName)
	{
		$PicInfo = $this->getPicInfo($FileName);
		$PicInfo[2] = 'imagecreatefromjpeg'; # force JPG
		$this->drawFromPicture($PicInfo, $FileName, $X, $Y);
	}

	function getPicInfo($FileName)
	{
		if (!file_exists($FileName)) {
			throw pException::InvalidImageType("Image ".$FileName." was not found");
		}
		
		$Info = getimagesize($FileName);
		
		switch ($Info["mime"]){
			case "image/png":
				$Type = 'imagecreatefrompng';
				break;
			case "image/gif":
				$Type = 'imagecreatefromgif';
				break;
			case "image/jpeg":
				$Type = 'imagecreatefromjpeg';
				break;
			default:
				throw pException::InvalidImageType($FileName." is an unsupported type - ".$Info["mime"]);
		}
		
		return [$Info[0],$Info[1],$Type];
	}

	/* Generic loader function for external pictures */
	function drawFromPicture($PicInfo, $FileName, $X, $Y)
	{
		list($Width, $Height, $PicType) = $PicInfo;

		$Raster = $PicType($FileName);

		$RestoreShadow = $this->Shadow;
		if ($this->Shadow) {
			$this->Shadow = FALSE;
			if ($PicType == 'imagecreatefromjpeg') {
				$this->drawFilledRectangle($X + $this->ShadowX, $Y + $this->ShadowY, $X + $Width + $this->ShadowX, $Y + $Height + $this->ShadowY, ["Color" => $this->ShadowColor]);
			} else {
				$TranparentID = imagecolortransparent($Raster);
				$picShadowColor = $this->ShadowColor->newOne();
				for ($Xc = 0; $Xc <= $Width - 1; $Xc++) {
					for ($Yc = 0; $Yc <= $Height - 1; $Yc++) {
						$Values = imagecolorsforindex($Raster, imagecolorat($Raster, $Xc, $Yc));
						if ($Values["alpha"] < 120) {
							$picShadowColor->Alpha = floor($this->ShadowColor->Alpha * (1 - $Values["alpha"]/127));
							$this->drawAlphaPixel($X + $Xc + $this->ShadowX, $Y + $Yc + $this->ShadowY, $picShadowColor);
						}
					}
				}
			}
		}

		$this->Shadow = $RestoreShadow;
		imagecopy($this->Picture, $Raster, $X, $Y, 0, 0, $Width, $Height);
		imagedestroy($Raster);

	} 

	/* Mirror Effect */
	function drawAreaMirror($X, $Y, $Width, $Height, array $Format = [])
	{
		$StartAlpha = isset($Format["StartAlpha"]) ? $Format["StartAlpha"] : 80;
		$EndAlpha = isset($Format["EndAlpha"]) ? $Format["EndAlpha"] : 0;
		$AlphaStep = ($StartAlpha - $EndAlpha) / $Height;
		$Picture = imagecreatetruecolor($this->XSize, $this->YSize);
		imagecopy($Picture, $this->Picture, 0, 0, 0, 0, $this->XSize, $this->YSize);
		for ($i = 1; $i <= $Height; $i++) {
			if ($Y + ($i - 1) < $this->YSize && $Y - $i > 0) {
				imagecopymerge($Picture, $this->Picture, $X, $Y + ($i - 1), $X, $Y - $i, $Width, 1, $StartAlpha - $AlphaStep * $i);
			}
		}

		imagecopy($this->Picture, $Picture, 0, 0, 0, 0, $this->XSize, $this->YSize);
		
		imagedestroy($Picture);
	}
	
	/* Draw an arrow */
	function drawArrow($X1, $Y1, $X2, $Y2, array $Format = [])
	{
		$FillColor = isset($Format["FillColor"]) ? $Format["FillColor"] : new pColor(0);
		$BorderColor = isset($Format["BorderColor"]) ? $Format["BorderColor"] : $FillColor->newOne();
		$Size = isset($Format["Size"]) ? $Format["Size"] : 10;
		$Ratio = isset($Format["Ratio"]) ? $Format["Ratio"] : .5;
		$TwoHeads = isset($Format["TwoHeads"]) ? $Format["TwoHeads"] : FALSE;
		$Ticks = isset($Format["Ticks"]) ? $Format["Ticks"] : NULL;
		
		$RGB = ["Color" => $BorderColor];
		
		/* Override Shadow support, this will be managed internally */
		$RestoreShadow = $this->Shadow;
		if ($this->Shadow) {
			$this->Shadow = FALSE;
			$this->drawArrow($X1 + $this->ShadowX, $Y1 + $this->ShadowY, $X2 + $this->ShadowX, $Y2 + $this->ShadowY, ["FillColor" => $this->ShadowColor,"Size" => $Size,"Ratio" => $Ratio,"TwoHeads" => $TwoHeads,"Ticks" => $Ticks]);
		}

		/* Draw the 1st Head */
		$Angle = $this->getAngle($X1, $Y1, $X2, $Y2);
		$TailX = cos(deg2rad($Angle - 180)) * $Size + $X2;
		$TailY = sin(deg2rad($Angle - 180)) * $Size + $Y2;
		$Scale = $Size * $Ratio;
		$cos90 = cos(deg2rad($Angle - 90)) * $Scale;
		$sin90 = sin(deg2rad($Angle - 90)) * $Scale;
		
		$Points = [$X2, $Y2, $cos90 + $TailX, $sin90 + $TailY, -$cos90 + $TailX, -$sin90 + $TailY, $X2, $Y2];
		/* Visual correction */
		($Angle == 180 || $Angle == 360) AND $Points[4] = $Points[2];
		($Angle == 90 || $Angle == 270) AND $Points[5] = $Points[3];

		imageFilledPolygon($this->Picture, $Points, 4, $this->allocateColor($FillColor));
		$this->drawLine($Points[0], $Points[1], $Points[2], $Points[3], $RGB);
		$this->drawLine($Points[2], $Points[3], $Points[4], $Points[5], $RGB);
		$this->drawLine($Points[0], $Points[1], $Points[4], $Points[5], $RGB);
		/* Draw the second head */
		if ($TwoHeads) {
			$Angle = $this->getAngle($X2, $Y2, $X1, $Y1);
			$cos90 = cos(deg2rad($Angle - 90)) * $Scale;
			$sin90 = sin(deg2rad($Angle - 90)) * $Scale;
			$TailX2 = cos(deg2rad($Angle - 180)) * $Size + $X1;
			$TailY2 = sin(deg2rad($Angle - 180)) * $Size + $Y1;
			$Points = [$X1, $Y1, $cos90 + $TailX2, $sin90 + $TailY2, -$cos90 + $TailX2, -$sin90 + $TailY2, $X1, $Y1];
			/* Visual correction */
			($Angle == 180 || $Angle == 360) AND $Points[4] = $Points[2];
			($Angle == 90 || $Angle == 270) AND $Points[5] = $Points[3];
			
			imageFilledPolygon($this->Picture, $Points, 4, $this->allocateColor($FillColor));
			$this->drawLine($Points[0], $Points[1], $Points[2], $Points[3], $RGB);
			$this->drawLine($Points[2], $Points[3], $Points[4], $Points[5], $RGB);
			$this->drawLine($Points[0], $Points[1], $Points[4], $Points[5], $RGB);
			$this->drawLine($TailX, $TailY, $TailX2, $TailY2, ["Color" => $BorderColor,"Ticks" => $Ticks]);
		} else {
			$this->drawLine($X1, $Y1, $TailX, $TailY, ["Color" => $BorderColor,"Ticks" => $Ticks]);
		}

		/* Re-enable shadows */
		$this->Shadow = $RestoreShadow;
	}

	/* Draw a label with associated arrow */
	function drawArrowLabel($X1, $Y1, $Text, array $Format = [])
	{
		$FillColor = isset($Format["FillColor"]) ? $Format["FillColor"] : new pColor(0);
		$BorderColor = isset($Format["BorderColor"]) ? $Format["BorderColor"] : $FillColor->newOne();
		$FontName = isset($Format["FontName"]) ? $Format["FontName"] : $this->FontName;
		$FontSize = isset($Format["FontSize"]) ? $Format["FontSize"] : $this->FontSize;
		$Length = isset($Format["Length"]) ? $Format["Length"] : 50;
		$Angle = isset($Format["Angle"]) ? $Format["Angle"] : 315;
		$Size = isset($Format["Size"]) ? $Format["Size"] : 10;
		$Position = isset($Format["Position"]) ? $Format["Position"] : POSITION_TOP;
		$RoundPos = isset($Format["RoundPos"]) ? $Format["RoundPos"] : FALSE;
		$Ticks = isset($Format["Ticks"]) ? $Format["Ticks"] : NULL;

		$Angle = $Angle % 360;
		$X2 = sin(deg2rad($Angle + 180)) * $Length + $X1;
		$Y2 = cos(deg2rad($Angle + 180)) * $Length + $Y1;
		($RoundPos && $Angle > 0 && $Angle < 180) AND $Y2 = ceil($Y2);
		($RoundPos && $Angle > 180) AND $Y2 = floor($Y2);

		$this->drawArrow($X2, $Y2, $X1, $Y1, $Format);
		$Size = imagettfbbox($FontSize, 0, realpath($FontName), $Text);
		$TxtWidth = max(abs($Size[2] - $Size[0]), abs($Size[0] - $Size[6]));
		#$TxtHeight = max(abs($Size[1] - $Size[7]), abs($Size[3] - $Size[1])); # UNUSED
		$RGB = ["Color" => $BorderColor];
		
		if ($Angle > 0 && $Angle < 180) {
			$TxtWidth = $X2 - $TxtWidth;
			if ($Position == POSITION_TOP) {
				$RGB["Align"] = TEXT_ALIGN_BOTTOMRIGHT;
				$Y3 = $Y2 - 2;
			} else {
				$RGB["Align"] = TEXT_ALIGN_TOPRIGHT;
				$Y3 = $Y2 + 2;
			}
		} else {
			$TxtWidth = $X2 + $TxtWidth;
			if ($Position == POSITION_TOP) {
				$Y3 = $Y2 - 2;
			} else {
				$RGB["Align"] = TEXT_ALIGN_TOPLEFT;
				$Y3 = $Y2 + 2;
			}
		}

		$this->drawLine($X2, $Y2, $TxtWidth, $Y2, ["Color" => $BorderColor,"Ticks" => $Ticks]);
		$this->drawText($X2, $Y3, $Text, $RGB);

	}

	/* Draw a progress bar filled with specified % */
	function drawProgress($X, $Y, $Percent, array $Format = [])
	{
		($Percent > 100) AND $Percent = 100;
		($Percent < 0) AND $Percent = 0;

		$Width = 200;
		$Height = 20;
		$Orientation = ORIENTATION_HORIZONTAL;
		$ShowLabel = FALSE;
		$LabelPos = LABEL_POS_INSIDE;
		$Margin = 10;
		$Color = isset($Format["Color"]) ? $Format["Color"] : new pColor(130);
		$FadeColor = NULL;
		$BorderColor = $Color->newOne();
		$BoxBorderColor = isset($Format["BoxBorderColor"]) ? $Format["BoxBorderColor"] : new pColor(0);
		$BoxBackColor = isset($Format["BoxBackColor"]) ? $Format["BoxBackColor"] : new pColor(255);
		$Surrounding = NULL;
		$BoxSurrounding = NULL;
		$NoAngle = FALSE;

		/* Override defaults */
		extract($Format);
		
		if (!is_null($Surrounding)) {
			$BorderColor = $Color->newOne()->RGBChange($Surrounding);
		}

		if (!is_null($BoxSurrounding)) {
			$BoxBorderColor = $BoxBackColor->newOne()->RGBChange($Surrounding);
		}

		if ($Orientation == ORIENTATION_VERTICAL) {
			$InnerHeight = (($Height - 2) / 100) * $Percent;
			$this->drawFilledRectangle($X, $Y, $X + $Width, $Y - $Height, ["Color" => $BoxBackColor,"BorderColor" => $BoxBorderColor,"NoAngle" => $NoAngle]);
			$RestoreShadow = $this->Shadow;
			$this->Shadow = FALSE;
			if (!is_null($FadeColor)) {
				$Gradient = new pColorGradient($Color, $FadeColor);
				$Gradient->SetSegments(100);
				$this->drawGradientArea($X + 1, $Y - 1, $X + $Width - 1, $Y - $InnerHeight, DIRECTION_VERTICAL, ["StartColor"=>$Gradient->Next($Percent, TRUE),"EndColor"=>$Color]);
				(!is_null($Surrounding)) AND $this->drawRectangle($X + 1, $Y - 1, $X + $Width - 1, $Y - $InnerHeight, ["Color" => new pColor(255,255,255,$Surrounding)]);
			} else {
				$this->drawFilledRectangle($X + 1, $Y - 1, $X + $Width - 1, $Y - $InnerHeight, ["Color" => $Color,"BorderColor" => $BorderColor]);
			}
			$this->Shadow = $RestoreShadow;
			
			if ($ShowLabel){
				switch ($LabelPos) {
					case LABEL_POS_BOTTOM:
						$this->drawText($X + ($Width / 2), $Y + $Margin, $Percent . "%", ["Align" => TEXT_ALIGN_TOPMIDDLE]);
						break;
					case LABEL_POS_TOP:
						$this->drawText($X + ($Width / 2), $Y - $Height - $Margin, $Percent . "%", ["Align" => TEXT_ALIGN_BOTTOMMIDDLE]);
						break;
					case LABEL_POS_INSIDE:
						$this->drawText($X + ($Width / 2), $Y - $InnerHeight - $Margin, $Percent . "%", ["Align" => TEXT_ALIGN_MIDDLELEFT,"Angle" => 90]);
						break;
					case LABEL_POS_CENTER:
						$this->drawText($X + ($Width / 2), $Y - ($Height / 2), $Percent . "%", ["Align" => TEXT_ALIGN_MIDDLEMIDDLE,"Angle" => 90]);
						break;
				}
			}

		} else {
			$InnerWidth = ($Percent == 100) ? $Width - 1 : (($Width - 2) / 100) * $Percent;
			$this->drawFilledRectangle($X, $Y, $X + $Width, $Y + $Height, ["Color" => $BoxBackColor,"BorderColor" => $BoxBorderColor,"NoAngle" => $NoAngle]);
			$RestoreShadow = $this->Shadow;
			$this->Shadow = FALSE;
			if (!is_null($FadeColor)) {
				$Gradient = new pColorGradient($Color, $FadeColor);
				$Gradient->SetSegments(100);
				$this->drawGradientArea($X + 1, $Y + 1, $X + $InnerWidth, $Y + $Height - 1, DIRECTION_HORIZONTAL, ["StartColor"=>$Color,"EndColor"=>$Gradient->Next($Percent, TRUE)]);
				(!is_null($Surrounding)) AND $this->drawRectangle($X + 1, $Y + 1, $X + $InnerWidth, $Y + $Height - 1, ["Color" => new pColor(255,255,255,$Surrounding)]);
			} else {
				$this->drawFilledRectangle($X + 1, $Y + 1, $X + $InnerWidth, $Y + $Height - 1, ["Color" => $Color,"BorderColor" => $BorderColor]);
			}
			$this->Shadow = $RestoreShadow;
			
			if ($ShowLabel){
				switch ($LabelPos) {
					case LABEL_POS_LEFT:
						$this->drawText($X - $Margin, $Y + ($Height / 2), $Percent . "%", ["Align" => TEXT_ALIGN_MIDDLERIGHT]);
						break;
					case LABEL_POS_RIGHT:
						$this->drawText($X + $Width + $Margin, $Y + ($Height / 2), $Percent . "%", ["Align" => TEXT_ALIGN_MIDDLELEFT]);
						break;
					case LABEL_POS_CENTER:
						$this->drawText($X + ($Width / 2), $Y + ($Height / 2), $Percent . "%", ["Align" => TEXT_ALIGN_MIDDLEMIDDLE]);
						break;
					case LABEL_POS_INSIDE:
						$this->drawText($X + $InnerWidth + $Margin, $Y + ($Height / 2), $Percent . "%", ["Align" => TEXT_ALIGN_MIDDLELEFT]);
						break;
				}
			}
		}

	}
	
	/* Get the legend box size */
	function getLegendSize(array $Format = [])
	{
		$FontName = $this->FontName;
		$FontSize = $this->FontSize;
		$BoxSize = 5;
		$Margin = 5;
		$Style = LEGEND_ROUND;
		$Mode = LEGEND_VERTICAL;
		$BoxWidth = isset($Format["BoxWidth"]) ? $Format["BoxWidth"] : 5;
		$BoxHeight = isset($Format["BoxHeight"]) ? $Format["BoxHeight"] : 5;
		$IconAreaWidth = $BoxWidth;
		$IconAreaHeight = $BoxHeight;
		$XSpacing = 5;
		
		extract($Format);

		foreach($this->myData->Data["Series"] as $SerieName => $Serie) {
			if ($Serie["isDrawable"] && $SerieName != $this->myData->Data["Abscissa"] && !is_null($Serie["Picture"])) {
				list($PicWidth, $PicHeight) = $this->getPicInfo($Serie["Picture"]);
				($IconAreaWidth < $PicWidth) AND $IconAreaWidth = $PicWidth;
				($IconAreaHeight < $PicHeight) AND $IconAreaHeight = $PicHeight;
			}
		}

		$YStep = max($this->FontSize, $IconAreaHeight) + 5;
		#$XStep = $IconAreaWidth + 5;
		$XStep = $XSpacing;
		$X = 100;
		$Y = 100;
		$Boundaries = ["L" => $X, "T" => 100, "R" => 0, "B" => 0];
		$vY = $Y; 
		foreach($this->myData->Data["Series"] as $SerieName => $Serie) {
			if ($Serie["isDrawable"] && $SerieName != $this->myData->Data["Abscissa"]) {
				$Lines = preg_split("/\n/", $Serie["Description"]);
				if ($Mode == LEGEND_VERTICAL) {
					$BoxArray = $this->getTextBox($X + $IconAreaWidth + 4, $vY + $IconAreaHeight / 2, $FontName, $FontSize, 0, $Serie["Description"]);
					($Boundaries["T"] > $BoxArray[2]["Y"] + $IconAreaHeight / 2) AND $Boundaries["T"] = $BoxArray[2]["Y"] + $IconAreaHeight / 2;
					($Boundaries["R"] < $BoxArray[1]["X"] + 2) AND $Boundaries["R"] = $BoxArray[1]["X"] + 2;
					($Boundaries["B"] < $BoxArray[1]["Y"] + 2 + $IconAreaHeight / 2) AND $Boundaries["B"] = $BoxArray[1]["Y"] + 2 + $IconAreaHeight / 2;
					$vY = $vY + max($this->FontSize * count($Lines), $IconAreaHeight) + 5;
				} elseif ($Mode == LEGEND_HORIZONTAL) {
					$Width = [];
					foreach($Lines as $Key => $Value) {
						$BoxArray = $this->getTextBox($X + $IconAreaWidth + 6, $vY + $IconAreaHeight / 2 + (($this->FontSize + 3) * $Key), $FontName, $FontSize, 0, $Value);
						($Boundaries["T"] > $BoxArray[2]["Y"] + $IconAreaHeight / 2) AND $Boundaries["T"] = $BoxArray[2]["Y"] + $IconAreaHeight / 2;
						($Boundaries["R"] < $BoxArray[1]["X"] + 2) AND $Boundaries["R"] = $BoxArray[1]["X"] + 2;
						($Boundaries["B"] < $BoxArray[1]["Y"] + 2 + $IconAreaHeight / 2) AND $Boundaries["B"] = $BoxArray[1]["Y"] + 2 + $IconAreaHeight / 2;
						$Width[] = $BoxArray[1]["X"];
					}
					$X = max($Width) + $XStep;
				}
			}
		}

		$vY = $vY - $YStep;
		$TopOffset = 100 - $Boundaries["T"];
		($Boundaries["B"] - ($vY + $IconAreaHeight) < $TopOffset) AND $Boundaries["B"] = $vY + $IconAreaHeight + $TopOffset;
		
		return [
			"Width" => ($Boundaries["R"] + $Margin) - ($Boundaries["L"] - $Margin),
			"Height" => ($Boundaries["B"] + $Margin) - ($Boundaries["T"] - $Margin)
		];
	}

	/* Draw the legend of the active series */
	function drawLegend($X, $Y, array $Format = [])
	{
		$Family = LEGEND_FAMILY_BOX;
		$FontName = $this->FontName;
		$FontSize = $this->FontSize;
		$FontColor = $this->FontColor;
		$BoxWidth = isset($Format["BoxWidth"]) ? $Format["BoxWidth"] : 5;
		$BoxHeight = isset($Format["BoxHeight"]) ? $Format["BoxHeight"] : 5;
		$IconAreaWidth = $BoxWidth;
		$IconAreaHeight = $BoxHeight;
		$XSpacing = 5;
		$Margin = 5;
		$Color = NULL;
		$BorderColor = NULL;
		$Surrounding = NULL;
		$Style = LEGEND_ROUND;
		$Mode = LEGEND_VERTICAL;

		/* Override defaults */
		extract($Format);

		(is_null($Color)) AND $Color = new pColor(200);
		(is_null($BorderColor)) AND $BorderColor = new pColor(255);
		(!is_null($Surrounding)) AND $BorderColor->RGBChange($Surrounding);
		
		$Data = $this->myData->getData();

		foreach($Data["Series"] as $SerieName => $Serie) {
			if ($Serie["isDrawable"] && $SerieName != $Data["Abscissa"] && !is_null($Serie["Picture"])) {
				list($PicWidth, $PicHeight) = $this->getPicInfo($Serie["Picture"]);
				($IconAreaWidth < $PicWidth) AND $IconAreaWidth = $PicWidth;
				($IconAreaHeight < $PicHeight) AND $IconAreaHeight = $PicHeight;
			}
		}

		$YStep = max($this->FontSize, $IconAreaHeight) + 5;
		#$XStep = $IconAreaWidth + 5;
		$XStep = $XSpacing;
		$Boundaries = ["L" => $X, "T" => $Y, "R" => 0, "B" => 0];
		$vY = $Y;
		$vX = $X;
		foreach($Data["Series"] as $SerieName => $Serie) {
			if ($Serie["isDrawable"] && $SerieName != $Data["Abscissa"]) {
				$Lines = preg_split("/\n/", $Serie["Description"]);
				if ($Mode == LEGEND_VERTICAL) {
					$BoxArray = $this->getTextBox($vX + $IconAreaWidth + 4, $vY + $IconAreaHeight / 2, $FontName, $FontSize, 0, $Serie["Description"]);
					($Boundaries["T"] > $BoxArray[2]["Y"] + $IconAreaHeight / 2) AND $Boundaries["T"] = $BoxArray[2]["Y"] + $IconAreaHeight / 2;
					($Boundaries["R"] < $BoxArray[1]["X"] + 2) AND $Boundaries["R"] = $BoxArray[1]["X"] + 2;
					($Boundaries["B"] < $BoxArray[1]["Y"] + 2 + $IconAreaHeight / 2) AND $Boundaries["B"] = $BoxArray[1]["Y"] + 2 + $IconAreaHeight / 2;
					$vY = $vY + max($this->FontSize * count($Lines), $IconAreaHeight) + 5;
				} elseif ($Mode == LEGEND_HORIZONTAL) {
					$Width = [];
					foreach($Lines as $Key => $Value) {
						$BoxArray = $this->getTextBox($vX + $IconAreaWidth + 6, $Y + $IconAreaHeight / 2 + (($this->FontSize + 3) * $Key), $FontName, $FontSize, 0, $Value);
						($Boundaries["T"] > $BoxArray[2]["Y"] + $IconAreaHeight / 2) AND $Boundaries["T"] = $BoxArray[2]["Y"] + $IconAreaHeight / 2;
						($Boundaries["R"] < $BoxArray[1]["X"] + 2) AND $Boundaries["R"] = $BoxArray[1]["X"] + 2;
						($Boundaries["B"] < $BoxArray[1]["Y"] + 2 + $IconAreaHeight / 2) AND $Boundaries["B"] = $BoxArray[1]["Y"] + 2 + $IconAreaHeight / 2;
						$Width[] = $BoxArray[1]["X"];
					}
					$vX = max($Width) + $XStep;
				}
			}
		}

		$vY = $vY - $YStep;
		$vX = $vX - $XStep;
		$TopOffset = $Y - $Boundaries["T"];
		($Boundaries["B"] - ($vY + $IconAreaHeight) < $TopOffset) AND $Boundaries["B"] = $vY + $IconAreaHeight + $TopOffset;
		
		if ($Style == LEGEND_ROUND) {
			$this->drawRoundedFilledRectangle($Boundaries["L"] - $Margin, $Boundaries["T"] - $Margin, $Boundaries["R"] + $Margin, $Boundaries["B"] + $Margin, $Margin, ["Color" => $Color,"BorderColor" => $BorderColor]);
		} elseif ($Style == LEGEND_BOX) {
			$this->drawFilledRectangle($Boundaries["L"] - $Margin, $Boundaries["T"] - $Margin, $Boundaries["R"] + $Margin, $Boundaries["B"] + $Margin, ["Color" => $Color,"BorderColor" => $BorderColor]);
		}

		$RestoreShadow = $this->Shadow;
		$this->Shadow = FALSE;
		foreach($Data["Series"] as $SerieName => $Serie) {

			if ($Serie["isDrawable"] && $SerieName != $Data["Abscissa"]) {

				$Color = $Serie["Color"];
				$Ticks = $Serie["Ticks"];
				$Weight = $Serie["Weight"];

				if (!is_null($Serie["Picture"])) {
					list($PicWidth, $PicHeight) = $this->getPicInfo($Serie["Picture"]);
					$PicX = $X + $IconAreaWidth / 2;
					$PicY = $Y + $IconAreaHeight / 2;
					$this->drawFromPNG($PicX - $PicWidth / 2, $PicY - $PicHeight / 2, $Serie["Picture"]);

				} else {
					if ($Family == LEGEND_FAMILY_BOX) {
	
						$XOffset = ($BoxWidth != $IconAreaWidth) ? floor(($IconAreaWidth - $BoxWidth) / 2) : 0;
						$YOffset = ($BoxHeight != $IconAreaHeight) ? floor(($IconAreaHeight - $BoxHeight) / 2) : 0;

						$this->drawFilledRectangle($X + 1 + $XOffset, $Y + 1 + $YOffset, $X + $BoxWidth + $XOffset + 1, $Y + $BoxHeight + 1 + $YOffset, ["Color" => new pColor(0,0,0,20)]);
						$this->drawFilledRectangle($X + $XOffset, $Y + $YOffset, $X + $BoxWidth + $XOffset, $Y + $BoxHeight + $YOffset, ["Color" => $Color,"Surrounding" => 20]);

					} elseif ($Family == LEGEND_FAMILY_CIRCLE) {
						$this->drawFilledCircle($X + 1 + $IconAreaWidth / 2, $Y + 1 + $IconAreaHeight / 2, min($IconAreaHeight / 2, $IconAreaWidth / 2), ["Color" => new pColor(0,0,0,20)]);
						$this->drawFilledCircle($X + $IconAreaWidth / 2, $Y + $IconAreaHeight / 2, min($IconAreaHeight / 2, $IconAreaWidth / 2), ["Color" => $Color,"Surrounding" => 20]);

					} elseif ($Family == LEGEND_FAMILY_LINE) {
						$this->drawLine($X + 1, $Y + 1 + $IconAreaHeight / 2, $X + 1 + $IconAreaWidth, $Y + 1 + $IconAreaHeight / 2, ["Color" => new pColor(0,0,0,20),"Ticks" => $Ticks,"Weight" => $Weight]);
						$this->drawLine($X, $Y + $IconAreaHeight / 2, $X + $IconAreaWidth, $Y + $IconAreaHeight / 2, ["Color" => $Color,"Ticks" => $Ticks,"Weight" => $Weight]);
					}
				}

				$Lines = preg_split("/\n/", $Serie["Description"]);
				if ($Mode == LEGEND_VERTICAL) {
					foreach($Lines as $Key => $Value) {
						$this->drawText($X + $IconAreaWidth + 4, $Y + $IconAreaHeight / 2 + (($this->FontSize + 3) * $Key), $Value, ["Color" => $FontColor,"Align" => TEXT_ALIGN_MIDDLELEFT,"FontSize" => $FontSize,"FontName" => $FontName]);
					}
					$Y = $Y + max($this->FontSize * count($Lines), $IconAreaHeight) + 5;
					
				} elseif ($Mode == LEGEND_HORIZONTAL) {
					$Width = [];
					foreach($Lines as $Key => $Value) {
						$BoxArray = $this->drawText($X + $IconAreaWidth + 4, $Y + 2 + $IconAreaHeight / 2 + (($this->FontSize + 3) * $Key), $Value, ["Color" => $FontColor,"Align" => TEXT_ALIGN_MIDDLELEFT,"FontSize" => $FontSize,"FontName" => $FontName]);
						$Width[] = $BoxArray[1]["X"];
					}

					$X = max($Width) + 2 + $XStep;
				}
			}
		}

		$this->Shadow = $RestoreShadow;
	}

	function drawScale(array $Format = [])
	{
		$Pos = isset($Format["Pos"]) ? $Format["Pos"] : SCALE_POS_LEFTRIGHT;
		$Floating = isset($Format["Floating"]) ? $Format["Floating"] : FALSE;
		$Mode = isset($Format["Mode"]) ? $Format["Mode"] : SCALE_MODE_FLOATING;
		$RemoveXAxis = isset($Format["RemoveXAxis"]) ? $Format["RemoveXAxis"] : FALSE;
		$RemoveYAxis = isset($Format["RemoveYAxis"]) ? $Format["RemoveYAxis"] : FALSE;
		$MinDivHeight = isset($Format["MinDivHeight"]) ? $Format["MinDivHeight"] : 20;
		$Factors = isset($Format["Factors"]) ? $Format["Factors"] : [1,2,5];
		$ManualScale = isset($Format["ManualScale"]) ? $Format["ManualScale"] : array("0" => ["Min" => - 100,"Max" => 100]);
		$XMargin = isset($Format["XMargin"]) ? $Format["XMargin"] : AUTO;
		$YMargin = isset($Format["YMargin"]) ? $Format["YMargin"] : 0;
		$ScaleSpacing = isset($Format["ScaleSpacing"]) ? $Format["ScaleSpacing"] : 15;
		$InnerTickWidth = isset($Format["InnerTickWidth"]) ? $Format["InnerTickWidth"] : 2;
		$OuterTickWidth = isset($Format["OuterTickWidth"]) ? $Format["OuterTickWidth"] : 2;
		$DrawXLines = isset($Format["DrawXLines"]) ? $Format["DrawXLines"] : TRUE;
		$DrawYLines = isset($Format["DrawYLines"]) ? $Format["DrawYLines"] : ALL;
		$GridTicks = isset($Format["GridTicks"]) ? $Format["GridTicks"] : 4;
		$GridColor = isset($Format["GridColor"]) ? ["Color" => $Format["GridColor"]] : ["Color" => new pColor(255,255,255,40)];
		$AxisColor = isset($Format["AxisColor"]) ? ["Color" => $Format["AxisColor"]] : ["Color" => new pColor(0)];
		$TickColor = isset($Format["TickColor"]) ? ["Color" => $Format["TickColor"]] : ["Color" => new pColor(0)];
		$DrawSubTicks = isset($Format["DrawSubTicks"]) ? $Format["DrawSubTicks"] : FALSE;
		$InnerSubTickWidth = isset($Format["InnerSubTickWidth"]) ? $Format["InnerSubTickWidth"] : 0;
		$OuterSubTickWidth = isset($Format["OuterSubTickWidth"]) ? $Format["OuterSubTickWidth"] : 2;
		$SubTickColor = isset($Format["TickColor"]) ? ["Color" => $Format["TickColor"]] : ["Color" => new pColor(255,0,0,100)];
		$AutoAxisLabels = isset($Format["AutoAxisLabels"]) ? $Format["AutoAxisLabels"] : TRUE;
		$XReleasePercent = isset($Format["XReleasePercent"]) ? $Format["XReleasePercent"] : 1;
		$DrawArrows = isset($Format["DrawArrows"]) ? $Format["DrawArrows"] : FALSE;
		$ArrowSize = isset($Format["ArrowSize"]) ? $Format["ArrowSize"] : 8;
		$CycleBackground = isset($Format["CycleBackground"]) ? $Format["CycleBackground"] : FALSE;
		$BackgroundColor1 = isset($Format["BackgroundColor1"]) ? ["Color" => $Format["BackgroundColor1"]] : ["Color" => new pColor(255,255,255,20)];
		$BackgroundColor2 = isset($Format["BackgroundColor2"]) ? ["Color" => $Format["BackgroundColor2"]] : ["Color" => new pColor(230,230,230,20)];
		$LabelingMethod = isset($Format["LabelingMethod"]) ? $Format["LabelingMethod"] : LABELING_ALL;
		$LabelSkip = isset($Format["LabelSkip"]) ? $Format["LabelSkip"] : 0;
		$LabelRotation = isset($Format["LabelRotation"]) ? $Format["LabelRotation"] : 0;
		$RemoveSkippedAxis = isset($Format["RemoveSkippedAxis"]) ? $Format["RemoveSkippedAxis"] : FALSE;
		$SkippedAxisTicks = isset($Format["SkippedAxisTicks"]) ? $Format["SkippedAxisTicks"] : $GridTicks + 2;
		$SkippedAxisColor = isset($Format["SkippedAxisColor"]) ? $Format["SkippedAxisColor"] : $GridColor["Color"]->newOne()->AlphaChange(-30);
		$SkippedTickColor = isset($Format["SkippedTickColor"]) ? ["Color" => $Format["SkippedTickColor"]] : ["Color" => $TickColor["Color"]->newOne()->AlphaChange(-80)];
		$SkippedInnerTickWidth = isset($Format["SkippedInnerTickWidth"]) ? $Format["SkippedInnerTickWidth"] : 0;
		$SkippedOuterTickWidth = isset($Format["SkippedOuterTickWidth"]) ? $Format["SkippedOuterTickWidth"] : 2;

		$SkippedAxisColor = ["Color" => $SkippedAxisColor, "Ticks" => $SkippedAxisTicks];
		$GridColor["Ticks"] = $GridTicks;

		/* Floating scale require X & Y margins to be set manually */
		($Floating && ($XMargin == AUTO || $YMargin == 0)) AND $Floating = FALSE;

		/* Skip a NOTICE event in case of an empty array */
		($DrawYLines == NONE || $DrawYLines == FALSE) AND $DrawYLines = ["zarma" => "31"];
		
		/* Check LabelRotation range */
		if (($LabelRotation < 0) || ($LabelRotation > 359)){
			throw pException::InvalidInput("drawScale: LabelRotation must be between 0 and 359");
		}

		$Data = $this->myData->getData();
		$Abscissa = (isset($Data["Abscissa"])) ? $Data["Abscissa"] : NULL;

		/* Unset the abscissa axis, needed if we display multiple charts on the same picture */
		if (!is_null($Abscissa)) {
			foreach($Data["Axis"] as $AxisID => $Parameters) {
				if ($Parameters["Identity"] == AXIS_X) {
					unset($Data["Axis"][$AxisID]);
				}
			}
		}

		/* Build the scale settings */
		$GotXAxis = FALSE;
		foreach($Data["Axis"] as $AxisID => $AxisParameter) {
			if ($AxisParameter["Identity"] == AXIS_X) {
				$GotXAxis = TRUE;
			}

			if ($Pos == SCALE_POS_LEFTRIGHT && $AxisParameter["Identity"] == AXIS_Y) {
				$Height = $this->GraphAreaYdiff - $YMargin * 2;
			} elseif ($Pos == SCALE_POS_LEFTRIGHT && $AxisParameter["Identity"] == AXIS_X) {
				$Height = $this->GraphAreaXdiff;
			} elseif ($Pos == SCALE_POS_TOPBOTTOM && $AxisParameter["Identity"] == AXIS_Y) {
				$Height = $this->GraphAreaXdiff - $YMargin * 2;;
			} else {
				$Height = $this->GraphAreaYdiff;
			}

			$AxisMin = ABSOLUTE_MAX;
			$AxisMax = OUT_OF_SIGHT;
			if ($Mode == SCALE_MODE_FLOATING || $Mode == SCALE_MODE_START0) {
				foreach($Data["Series"] as $SerieID => $SerieParameter) {
					if ($SerieParameter["Axis"] == $AxisID && $Data["Series"][$SerieID]["isDrawable"] && $Data["Abscissa"] != $SerieID) {
						if (!is_numeric($Data["Series"][$SerieID]["Max"]) || !is_numeric($Data["Series"][$SerieID]["Min"])){
							throw pException::InvalidInput("Series ".$SerieID.": non-numeric input");
						}
						$AxisMax = max($AxisMax, $Data["Series"][$SerieID]["Max"]);
						$AxisMin = min($AxisMin, $Data["Series"][$SerieID]["Min"]);
					}
				}

				$AutoMargin = (($AxisMax - $AxisMin) / 100) * $XReleasePercent;
				$Data["Axis"][$AxisID]["Min"] = $AxisMin - $AutoMargin;
				$Data["Axis"][$AxisID]["Max"] = $AxisMax + $AutoMargin;
				if ($Mode == SCALE_MODE_START0) {
					$Data["Axis"][$AxisID]["Min"] = 0;
				}
				
			} elseif ($Mode == SCALE_MODE_MANUAL) {
				
				if (isset($ManualScale[$AxisID]["Min"]) && isset($ManualScale[$AxisID]["Max"])) {
					$Data["Axis"][$AxisID]["Min"] = $ManualScale[$AxisID]["Min"];
					$Data["Axis"][$AxisID]["Max"] = $ManualScale[$AxisID]["Max"];
				} else {
					throw pException::InvalidInput("Manual scale boundaries not set.");
				}
				
			} elseif ($Mode == SCALE_MODE_ADDALL || $Mode == SCALE_MODE_ADDALL_START0) {
				
				$Series = [];
				foreach($Data["Series"] as $SerieID => $SerieParameter) {
					if ($SerieParameter["Axis"] == $AxisID && $SerieParameter["isDrawable"] && $Data["Abscissa"] != $SerieID) {
						$Series[$SerieID] = count($Data["Series"][$SerieID]["Data"]);
					}
				}

				for ($ID = 0; $ID <= max($Series) - 1; $ID++) {
					$PointMin = 0;
					$PointMax = 0;
					foreach($Series as $SerieID => $ValuesCount) {
						if (isset($Data["Series"][$SerieID]["Data"][$ID]) && !is_null($Data["Series"][$SerieID]["Data"][$ID])) {
							$Value = $Data["Series"][$SerieID]["Data"][$ID];
							if ($Value > 0) {
								$PointMax += $Value;
							} else { 
								$PointMin += $Value;
							}
						}
					}

					$AxisMax = max($AxisMax, $PointMax);
					$AxisMin = min($AxisMin, $PointMin);
				}

				$AutoMargin = (($AxisMax - $AxisMin) / 100) * $XReleasePercent;
				$Data["Axis"][$AxisID]["Min"] = $AxisMin - $AutoMargin;
				$Data["Axis"][$AxisID]["Max"] = $AxisMax + $AutoMargin;
			}

			$MaxDivs = floor($Height / $MinDivHeight);
			if ($Mode == SCALE_MODE_ADDALL_START0) {
				$Data["Axis"][$AxisID]["Min"] = 0;
			}

			$Scale = $this->computeScale($Data["Axis"][$AxisID]["Min"], $Data["Axis"][$AxisID]["Max"], $MaxDivs, $Factors, $AxisID);
			$Data["Axis"][$AxisID]["Margin"] = $AxisParameter["Identity"] == AXIS_X ? $XMargin : $YMargin;
			$Data["Axis"][$AxisID]["ScaleMin"] = $Scale["XMin"];
			$Data["Axis"][$AxisID]["ScaleMax"] = $Scale["XMax"];
			$Data["Axis"][$AxisID]["Rows"] = $Scale["Rows"];
			$Data["Axis"][$AxisID]["RowHeight"] = $Scale["RowHeight"];
			(isset($Scale["Format"])) AND $Data["Axis"][$AxisID]["Format"] = $Scale["Format"];
			(!isset($Data["Axis"][$AxisID]["Display"])) AND $Data["Axis"][$AxisID]["Display"] = NULL;
			(!isset($Data["Axis"][$AxisID]["Format"])) AND 	$Data["Axis"][$AxisID]["Format"] = NULL;
			(!isset($Data["Axis"][$AxisID]["Unit"])) AND $Data["Axis"][$AxisID]["Unit"] = NULL;
		}

		/* Still no X axis */
		if ($GotXAxis == FALSE) {
			if (!is_null($Abscissa)) {
				$Points = count($Data["Series"][$Abscissa]["Data"]);
				if ($AutoAxisLabels) {
					$AxisName = isset($Data["Series"][$Abscissa]["Description"]) ? $Data["Series"][$Abscissa]["Description"] : NULL;
				} else {
					$AxisName = NULL;
				}
			} else {
				$Points = 0;
				$AxisName = isset($Data["XAxisName"]) ? $Data["XAxisName"] : NULL;
				foreach($Data["Series"] as $SerieParameter) {
					if ($SerieParameter["isDrawable"]) {
						$Points = max($Points, count($SerieParameter["Data"]));
					}
				}
			}

			$AxisID = count($Data["Axis"]);
			$Data["Axis"][$AxisID]["Identity"] = AXIS_X;
			$Data["Axis"][$AxisID]["Position"] = ($Pos == SCALE_POS_LEFTRIGHT) ? AXIS_POSITION_BOTTOM : AXIS_POSITION_LEFT;
			(isset($Data["AbscissaName"])) AND $Data["Axis"][$AxisID]["Name"] = $Data["AbscissaName"];
			
			if ($XMargin == AUTO) {
				$Height = ($Pos == SCALE_POS_LEFTRIGHT) ? $this->GraphAreaXdiff : $this->GraphAreaYdiff;
				$Data["Axis"][$AxisID]["Margin"] = ($Points == 1) ? ($Height / 2) : (($Height / $Points) / 2);
			} else {
				$Data["Axis"][$AxisID]["Margin"] = $XMargin;
			}

			$Data["Axis"][$AxisID]["Rows"] = $Points - 1;
			(!isset($Data["Axis"][$AxisID]["Display"])) AND $Data["Axis"][$AxisID]["Display"] = NULL;
			(!isset($Data["Axis"][$AxisID]["Format"])) AND $Data["Axis"][$AxisID]["Format"] = NULL;
			(!isset($Data["Axis"][$AxisID]["Unit"])) AND $Data["Axis"][$AxisID]["Unit"] = NULL;
		}

		/* Do we need to reverse the abscissa position? */
		if ($Pos != SCALE_POS_LEFTRIGHT) {
			$Data["AbsicssaPosition"] = ($Data["AbsicssaPosition"] == AXIS_POSITION_BOTTOM) ? AXIS_POSITION_LEFT : AXIS_POSITION_RIGHT;
		}

		$Data["Axis"][$AxisID]["Position"] = $Data["AbsicssaPosition"];
		$this->myData->saveOrientation($Pos);
		$this->myData->saveAxisConfig($Data["Axis"]);
		$this->myData->saveYMargin($YMargin);

		$AxisPos = ["L" => $this->GraphAreaX1, "R" => $this->GraphAreaX2, "T" => $this->GraphAreaY1, "B" => $this->GraphAreaY2];

		foreach($Data["Axis"] as $AxisID => $Parameters) {
			if (isset($Parameters["Color"])) {
				$ColorAxis = ["Color" => $Parameters["Color"]];
				$ColorTick = ["Color" => $Parameters["Color"]];
			} else {
				$ColorAxis = $AxisColor;
				$ColorTick = $TickColor;
			}
			
			$ColorAxis["FontName"] = $this->FontName;
			$ColorAxis["FontSize"] = $this->FontSize;

			$ColorAxisArrow = ["FillColor" => $ColorAxis['Color'],"Size" => $ArrowSize];
			$LastValue = "w00t";
			$ID = 1;
			
			if ($Parameters["Identity"] == AXIS_X) {
				if ($Pos == SCALE_POS_LEFTRIGHT) {
					if ($Parameters["Position"] == AXIS_POSITION_BOTTOM) {
						
						switch(TRUE){
							case ($LabelRotation == 0):
								$LabelAlign = TEXT_ALIGN_TOPMIDDLE;
								$YLabelOffset = 2;
								break;
							case ($LabelRotation > 0 && $LabelRotation < 190):
								$LabelAlign = TEXT_ALIGN_MIDDLERIGHT;
								$YLabelOffset = 5;
								break;
							case ($LabelRotation == 180):
								$LabelAlign = TEXT_ALIGN_BOTTOMMIDDLE;
								$YLabelOffset = 5;
								break;
							case ($LabelRotation > 180 && $LabelRotation < 360):
								$LabelAlign = TEXT_ALIGN_MIDDLELEFT;
								$YLabelOffset = 2;
								break;
						}

						if (!$RemoveXAxis) {
							if ($Floating) {
								$FloatingOffset = $YMargin;
								$this->drawLine($this->GraphAreaX1 + $Parameters["Margin"], $AxisPos["B"], $this->GraphAreaX2 - $Parameters["Margin"], $AxisPos["B"], $ColorAxis);
							} else {
								$FloatingOffset = 0;
								$this->drawLine($this->GraphAreaX1, $AxisPos["B"], $this->GraphAreaX2, $AxisPos["B"], $ColorAxis);
							}

							if ($DrawArrows) {
								$this->drawArrow($this->GraphAreaX2 - $Parameters["Margin"], $AxisPos["B"], $this->GraphAreaX2 + ($ArrowSize * 2), $AxisPos["B"], $ColorAxisArrow);
							}
						}

						$Width = $this->GraphAreaXdiff - $Parameters["Margin"] * 2;
						$Step = ($Parameters["Rows"] == 0) ? $Width : $Width / ($Parameters["Rows"]);
						$MaxBottom = $AxisPos["B"];
						
						for ($i = 0; $i <= $Parameters["Rows"]; $i++) {
							$XPos = $this->GraphAreaX1 + $Parameters["Margin"] + $Step * $i;
							$YPos = $AxisPos["B"];
							if (!is_null($Abscissa)) {
								if (isset($Data["Series"][$Abscissa]["Data"][$i])) {
									$Value = $this->scaleFormat($Data["Series"][$Abscissa]["Data"][$i], $Data["XAxisDisplay"], $Data["XAxisFormat"], $Data["XAxisUnit"]);
								} else {
									$Value = "";
								}
							} else {
								if (isset($Parameters["ScaleMin"]) && isset($Parameters["RowHeight"])) {
									$Value = $this->scaleFormat($Parameters["ScaleMin"] + $Parameters["RowHeight"] * $i, $Data["XAxisDisplay"], $Data["XAxisFormat"], $Data["XAxisUnit"]);
								} else {
									$Value = $i;
								}
							}

							$ID++;
							$Skipped = TRUE;
							if ($this->isValidLabel($Value, $LastValue, $LabelingMethod, $ID, $LabelSkip) && !$RemoveXAxis) {
								$Bounds = $this->drawText($XPos, $YPos + $OuterTickWidth + $YLabelOffset, $Value, ["Angle" => $LabelRotation,"Align" => $LabelAlign] + $ColorAxis);
								$TxtBottom = $YPos + $OuterTickWidth + 2 + ($Bounds[0]["Y"] - $Bounds[2]["Y"]);
								$MaxBottom = max($MaxBottom, $TxtBottom);
								$LastValue = $Value;
								$Skipped = FALSE;
							}

							($RemoveXAxis) AND $Skipped = FALSE;
							
							if ($Skipped) {
								if ($DrawXLines && !$RemoveSkippedAxis) {
									$this->drawLine($XPos, $this->GraphAreaY1 + $FloatingOffset, $XPos, $this->GraphAreaY2 - $FloatingOffset, $SkippedAxisColor);
								}

								if (($SkippedInnerTickWidth != 0 || $SkippedOuterTickWidth != 0) && !$RemoveXAxis && !$RemoveSkippedAxis) {
									$this->drawLine($XPos, $YPos - $SkippedInnerTickWidth, $XPos, $YPos + $SkippedOuterTickWidth, $SkippedTickColor);
								}
							} else {
								if ($DrawXLines && ($XPos != $this->GraphAreaX1 && $XPos != $this->GraphAreaX2)) {
									$this->drawLine($XPos, $this->GraphAreaY1 + $FloatingOffset, $XPos, $this->GraphAreaY2 - $FloatingOffset, $GridColor);
								}

								if (($InnerTickWidth != 0 || $OuterTickWidth != 0) && !$RemoveXAxis) {
									$this->drawLine($XPos, $YPos - $InnerTickWidth, $XPos, $YPos + $OuterTickWidth, $ColorTick);
								}
							}
						}

						if (isset($Parameters["Name"]) && !$RemoveXAxis) {
							$YPos = $MaxBottom + 2;
							$XPos = $this->GraphAreaX1 + ($this->GraphAreaXdiff) / 2;
							$Bounds = $this->drawText($XPos, $YPos, $Parameters["Name"], ["Align" => TEXT_ALIGN_TOPMIDDLE] + $ColorAxis);
							$MaxBottom = $Bounds[0]["Y"];
						}

						$AxisPos["B"] = $MaxBottom + $ScaleSpacing;
						
					} elseif ($Parameters["Position"] == AXIS_POSITION_TOP) {

						switch(TRUE){
							case ($LabelRotation == 0):
								$LabelAlign = TEXT_ALIGN_BOTTOMMIDDLE;
								$YLabelOffset = 2;
								break;
							case ($LabelRotation > 0 && $LabelRotation < 190):
								$LabelAlign = TEXT_ALIGN_MIDDLELEFT;
								$YLabelOffset = 2;
								break;
							case ($LabelRotation == 180):
								$LabelAlign = TEXT_ALIGN_TOPMIDDLE;
								$YLabelOffset = 5;
								break;
							case ($LabelRotation > 180 && $LabelRotation < 360):
								$LabelAlign = TEXT_ALIGN_MIDDLERIGHT;
								$YLabelOffset = 5;
								break;
						}

						if (!$RemoveXAxis) {
							if ($Floating) {
								$FloatingOffset = $YMargin;
								$this->drawLine($this->GraphAreaX1 + $Parameters["Margin"], $AxisPos["T"], $this->GraphAreaX2 - $Parameters["Margin"], $AxisPos["T"], $ColorAxis);
							} else {
								$FloatingOffset = 0;
								$this->drawLine($this->GraphAreaX1, $AxisPos["T"], $this->GraphAreaX2, $AxisPos["T"], $ColorAxis);
							}

							if ($DrawArrows) {
								$this->drawArrow($this->GraphAreaX2 - $Parameters["Margin"], $AxisPos["T"], $this->GraphAreaX2 + ($ArrowSize * 2), $AxisPos["T"], $ColorAxisArrow);
							}
						}

						$Width = $this->GraphAreaXdiff - $Parameters["Margin"] * 2;
						$Step = ($Parameters["Rows"] == 0) ? $Width : $Width / $Parameters["Rows"];
						$MinTop = $AxisPos["T"];
						
						for ($i = 0; $i <= $Parameters["Rows"]; $i++) {
							$XPos = $this->GraphAreaX1 + $Parameters["Margin"] + $Step * $i;
							$YPos = $AxisPos["T"];
							if (!is_null($Abscissa)) {
								if (isset($Data["Series"][$Abscissa]["Data"][$i])) {
									$Value = $this->scaleFormat($Data["Series"][$Abscissa]["Data"][$i], $Data["XAxisDisplay"], $Data["XAxisFormat"], $Data["XAxisUnit"]);
								} else {
									$Value = "";
								}
							} else {
								if (isset($Parameters["ScaleMin"]) && isset($Parameters["RowHeight"])) {
									$Value = $this->scaleFormat($Parameters["ScaleMin"] + $Parameters["RowHeight"] * $i, $Data["XAxisDisplay"], $Data["XAxisFormat"], $Data["XAxisUnit"]);
								} else {
									$Value = $i;
								}
							}

							$ID++;
							$Skipped = TRUE;
							if ($this->isValidLabel($Value, $LastValue, $LabelingMethod, $ID, $LabelSkip) && !$RemoveXAxis) {
								$Bounds = $this->drawText($XPos, $YPos - $OuterTickWidth - $YLabelOffset, $Value, ["Angle" => $LabelRotation,"Align" => $LabelAlign] + $ColorAxis);
								$TxtBox = $YPos - $OuterTickWidth - 2 - ($Bounds[0]["Y"] - $Bounds[2]["Y"]);
								$MinTop = min($MinTop, $TxtBox);
								$LastValue = $Value;
								$Skipped = FALSE;
							}

							($RemoveXAxis) AND $Skipped = FALSE;
							
							if ($Skipped) {
								if ($DrawXLines && !$RemoveSkippedAxis) {
									$this->drawLine($XPos, $this->GraphAreaY1 + $FloatingOffset, $XPos, $this->GraphAreaY2 - $FloatingOffset, $SkippedAxisColor);
								}

								if (($SkippedInnerTickWidth != 0 || $SkippedOuterTickWidth != 0) && !$RemoveXAxis && !$RemoveSkippedAxis) {
									$this->drawLine($XPos, $YPos + $SkippedInnerTickWidth, $XPos, $YPos - $SkippedOuterTickWidth, $SkippedTickColor);
								}
							} else {
								if ($DrawXLines) {
									$this->drawLine($XPos, $this->GraphAreaY1 + $FloatingOffset, $XPos, $this->GraphAreaY2 - $FloatingOffset, $GridColor);
								}

								if (($InnerTickWidth != 0 || $OuterTickWidth != 0) && !$RemoveXAxis) {
									$this->drawLine($XPos, $YPos + $InnerTickWidth, $XPos, $YPos - $OuterTickWidth, $ColorTick);
								}
							}
						}

						if (isset($Parameters["Name"]) && !$RemoveXAxis) {
							$YPos = $MinTop - 2;
							$XPos = $this->GraphAreaX1 + $this->GraphAreaXdiff / 2;
							$Bounds = $this->drawText($XPos, $YPos, $Parameters["Name"], ["Align" => TEXT_ALIGN_BOTTOMMIDDLE] + $ColorAxis);
							$MinTop = $Bounds[2]["Y"];
						}

						$AxisPos["T"] = $MinTop - $ScaleSpacing;
					}

				} elseif ($Pos == SCALE_POS_TOPBOTTOM) {
					
					if ($Parameters["Position"] == AXIS_POSITION_LEFT) {
						
						switch(TRUE){
							case ($LabelRotation == 0):
								$LabelAlign = TEXT_ALIGN_MIDDLERIGHT;
								$XLabelOffset = - 2;
								break;
							case ($LabelRotation > 0 && $LabelRotation < 190):
								$LabelAlign = TEXT_ALIGN_MIDDLERIGHT;
								$XLabelOffset = - 6;
								break;
							case ($LabelRotation == 180):
								$LabelAlign = TEXT_ALIGN_MIDDLELEFT;
								$XLabelOffset = - 2;
								break;
							case ($LabelRotation > 180 && $LabelRotation < 360):
								$LabelAlign = TEXT_ALIGN_MIDDLELEFT;
								$XLabelOffset = - 5;
								break;
						}

						if (!$RemoveXAxis) {
							if ($Floating) {
								$FloatingOffset = $YMargin;
								$this->drawLine($AxisPos["L"], $this->GraphAreaY1 + $Parameters["Margin"], $AxisPos["L"], $this->GraphAreaY2 - $Parameters["Margin"], $ColorAxis);
							} else {
								$FloatingOffset = 0;
								$this->drawLine($AxisPos["L"], $this->GraphAreaY1, $AxisPos["L"], $this->GraphAreaY2, $ColorAxis);
							}

							if ($DrawArrows) {
								$this->drawArrow($AxisPos["L"], $this->GraphAreaY2 - $Parameters["Margin"], $AxisPos["L"], $this->GraphAreaY2 + ($ArrowSize * 2), $ColorAxisArrow);
							}
						}

						$Height = $this->GraphAreaYdiff - $Parameters["Margin"] * 2;
						$Step = ($Parameters["Rows"] == 0) ? $Height :  $Height / $Parameters["Rows"];
						$MinLeft = $AxisPos["L"];
						
						for ($i = 0; $i <= $Parameters["Rows"]; $i++) {
							$YPos = $this->GraphAreaY1 + $Parameters["Margin"] + $Step * $i;
							$XPos = $AxisPos["L"];
							if (!is_null($Abscissa)) {
								if (isset($Data["Series"][$Abscissa]["Data"][$i])) {
									$Value = $this->scaleFormat($Data["Series"][$Abscissa]["Data"][$i], $Data["XAxisDisplay"], $Data["XAxisFormat"], $Data["XAxisUnit"]);
								} else {
									$Value = "";
								}
							} else {
								if (isset($Parameters["ScaleMin"]) && isset($Parameters["RowHeight"])) {
									$Value = $this->scaleFormat($Parameters["ScaleMin"] + $Parameters["RowHeight"] * $i, $Data["XAxisDisplay"], $Data["XAxisFormat"], $Data["XAxisUnit"]);
								} else {
									$Value = strval($i);
								}
							}

							$ID++;
							$Skipped = TRUE;
							if ($this->isValidLabel($Value, $LastValue, $LabelingMethod, $ID, $LabelSkip) && !$RemoveXAxis) {
								$Bounds = $this->drawText($XPos - $OuterTickWidth + $XLabelOffset, $YPos, $Value, ["Angle" => $LabelRotation,"Align" => $LabelAlign] + $ColorAxis);
								$TxtBox = $XPos - $OuterTickWidth - 2 - ($Bounds[1]["X"] - $Bounds[0]["X"]);
								$MinLeft = min($MinLeft, $TxtBox);
								$LastValue = $Value;
								$Skipped = FALSE;
							}

							($RemoveXAxis) AND $Skipped = FALSE;
							
							if ($Skipped) {
								if ($DrawXLines && !$RemoveSkippedAxis) {
									$this->drawLine($this->GraphAreaX1 + $FloatingOffset, $YPos, $this->GraphAreaX2 - $FloatingOffset, $YPos, $SkippedAxisColor);
								}

								if (($SkippedInnerTickWidth != 0 || $SkippedOuterTickWidth != 0) && !$RemoveXAxis && !$RemoveSkippedAxis) {
									$this->drawLine($XPos - $SkippedOuterTickWidth, $YPos, $XPos + $SkippedInnerTickWidth, $YPos, $SkippedTickColor);
								}
							} else {
								if ($DrawXLines && ($YPos != $this->GraphAreaY1 && $YPos != $this->GraphAreaY2)) {
									$this->drawLine($this->GraphAreaX1 + $FloatingOffset, $YPos, $this->GraphAreaX2 - $FloatingOffset, $YPos, $GridColor);
								}

								if (($InnerTickWidth != 0 || $OuterTickWidth != 0) && !$RemoveXAxis) {
									$this->drawLine($XPos - $OuterTickWidth, $YPos, $XPos + $InnerTickWidth, $YPos, $ColorTick);
								}
							}
						}

						if (isset($Parameters["Name"]) && !$RemoveXAxis) {
							$XPos = $MinLeft - 2;
							$YPos = $this->GraphAreaY1 + ($this->GraphAreaY2 - $this->GraphAreaY1) / 2;
							$Bounds = $this->drawText($XPos, $YPos, $Parameters["Name"], ["Align" => TEXT_ALIGN_BOTTOMMIDDLE,"Angle" => 90] + $ColorAxis);
							$MinLeft = $Bounds[0]["X"];
						}

						$AxisPos["L"] = $MinLeft - $ScaleSpacing;

					} elseif ($Parameters["Position"] == AXIS_POSITION_RIGHT) {

						switch(TRUE){
							case ($LabelRotation == 0):
								$LabelAlign = TEXT_ALIGN_MIDDLELEFT;
								$XLabelOffset = 2;
								break;
							case ($LabelRotation > 0 && $LabelRotation < 190):
								$LabelAlign = TEXT_ALIGN_MIDDLELEFT;
								$XLabelOffset = 6;
								break;
							case ($LabelRotation == 180):
								$LabelAlign = TEXT_ALIGN_MIDDLERIGHT;
								$XLabelOffset = 5;
								break;
							case ($LabelRotation > 180 && $LabelRotation < 360):
								$LabelAlign = TEXT_ALIGN_MIDDLERIGHT;
								$XLabelOffset = 7;
								break;
						}

						if (!$RemoveXAxis) {
							if ($Floating) {
								$FloatingOffset = $YMargin;
								$this->drawLine($AxisPos["R"], $this->GraphAreaY1 + $Parameters["Margin"], $AxisPos["R"], $this->GraphAreaY2 - $Parameters["Margin"], $ColorAxis);
							} else {
								$FloatingOffset = 0;
								$this->drawLine($AxisPos["R"], $this->GraphAreaY1, $AxisPos["R"], $this->GraphAreaY2, $ColorAxis);
							}

							if ($DrawArrows) {
								$this->drawArrow($AxisPos["R"], $this->GraphAreaY2 - $Parameters["Margin"], $AxisPos["R"], $this->GraphAreaY2 + ($ArrowSize * 2), $ColorAxisArrow);
							}
						}

						$Height = $this->GraphAreaYdiff - $Parameters["Margin"] * 2;
						$Step = ($Parameters["Rows"] == 0) ? $Height : $Height / $Parameters["Rows"];
						$MaxRight = $AxisPos["R"];
						
						for ($i = 0; $i <= $Parameters["Rows"]; $i++) {
							$YPos = $this->GraphAreaY1 + $Parameters["Margin"] + $Step * $i;
							$XPos = $AxisPos["R"];
							if (!is_null($Abscissa)) {
								if (isset($Data["Series"][$Abscissa]["Data"][$i])) {
									$Value = $this->scaleFormat($Data["Series"][$Abscissa]["Data"][$i], $Data["XAxisDisplay"], $Data["XAxisFormat"], $Data["XAxisUnit"]);
								} else {
									$Value = "";
								}
							} else {
								if (isset($Parameters["ScaleMin"]) && isset($Parameters["RowHeight"])) {
									$Value = $this->scaleFormat($Parameters["ScaleMin"] + $Parameters["RowHeight"] * $i, $Data["XAxisDisplay"], $Data["XAxisFormat"], $Data["XAxisUnit"]);
								} else {
									$Value = strval($i);
								}
							}

							$ID++;
							$Skipped = TRUE;
							if ($this->isValidLabel($Value, $LastValue, $LabelingMethod, $ID, $LabelSkip) && !$RemoveXAxis) {
								$Bounds = $this->drawText($XPos + $OuterTickWidth + $XLabelOffset, $YPos, $Value, ["Angle" => $LabelRotation,"Align" => $LabelAlign] + $ColorAxis);
								$TxtBox = $XPos + $OuterTickWidth + 2 + ($Bounds[1]["X"] - $Bounds[0]["X"]);
								$MaxRight = max($MaxRight, $TxtBox);
								$LastValue = $Value;
								$Skipped = FALSE;
							}

							($RemoveXAxis) AND $Skipped = FALSE;
							
							if ($Skipped) {
								if ($DrawXLines && !$RemoveSkippedAxis) {
									$this->drawLine($this->GraphAreaX1 + $FloatingOffset, $YPos, $this->GraphAreaX2 - $FloatingOffset, $YPos, $SkippedAxisColor);
								}

								if (($SkippedInnerTickWidth != 0 || $SkippedOuterTickWidth != 0) && !$RemoveXAxis && !$RemoveSkippedAxis) {
									$this->drawLine($XPos + $SkippedOuterTickWidth, $YPos, $XPos - $SkippedInnerTickWidth, $YPos, $SkippedTickColor);
								}
							} else {
								if ($DrawXLines) {
									$this->drawLine($this->GraphAreaX1 + $FloatingOffset, $YPos, $this->GraphAreaX2 - $FloatingOffset, $YPos, $GridColor);
								}

								if (($InnerTickWidth != 0 || $OuterTickWidth != 0) && !$RemoveXAxis) {
									$this->drawLine($XPos + $OuterTickWidth, $YPos, $XPos - $InnerTickWidth, $YPos, $ColorTick);
								}
							}
						}

						if (isset($Parameters["Name"]) && !$RemoveXAxis) {
							$XPos = $MaxRight + 4;
							$YPos = $this->GraphAreaY1 + $this->GraphAreaYdiff / 2;
							$Bounds = $this->drawText($XPos, $YPos, $Parameters["Name"], ["Align" => TEXT_ALIGN_BOTTOMMIDDLE,"Angle" => 270] + $ColorAxis);
							$MaxRight = $Bounds[1]["X"];
						}

						$AxisPos["R"] = $MaxRight + $ScaleSpacing;
					}
				}

			} elseif ($Parameters["Identity"] == AXIS_Y) {

				if ($Pos == SCALE_POS_LEFTRIGHT) {
					if ($Parameters["Position"] == AXIS_POSITION_LEFT) {

						if ($Floating) {
							$FloatingOffset = $XMargin;
							if (!$RemoveYAxis){
								$this->drawLine($AxisPos["L"], $this->GraphAreaY1 + $Parameters["Margin"], $AxisPos["L"], $this->GraphAreaY2 - $Parameters["Margin"], $ColorAxis);
							}
						} else {
							$FloatingOffset = 0;
							if (!$RemoveYAxis){
								$this->drawLine($AxisPos["L"], $this->GraphAreaY1, $AxisPos["L"], $this->GraphAreaY2, $ColorAxis);
							}
						}

						if ($DrawArrows) {
							$this->drawArrow($AxisPos["L"], $this->GraphAreaY1 + $Parameters["Margin"], $AxisPos["L"], $this->GraphAreaY1 - ($ArrowSize * 2), $ColorAxisArrow);
						}

						$Height = $this->GraphAreaYdiff - $Parameters["Margin"] * 2;
						$Step = $Height / $Parameters["Rows"];
						$SubTicksSize = $Step / 2;
						$MinLeft = $AxisPos["L"];
						$LastY = NULL;
						for ($i = 0; $i <= $Parameters["Rows"]; $i++) {
							$YPos = $this->GraphAreaY2 - $Parameters["Margin"] - $Step * $i;
							$XPos = $AxisPos["L"];
							$Value = $this->scaleFormat($Parameters["ScaleMin"] + $Parameters["RowHeight"] * $i, $Parameters["Display"], $Parameters["Format"], $Parameters["Unit"]);
							$BGColor = ($i % 2 == 1) ? $BackgroundColor1 : $BackgroundColor2;

							if (!is_null($LastY) && $CycleBackground && ($DrawYLines == ALL || in_array($AxisID, $DrawYLines))) {
								$this->drawFilledRectangle($this->GraphAreaX1 + $FloatingOffset, $LastY, $this->GraphAreaX2 - $FloatingOffset, $YPos, $BGColor);
							}

							if ($DrawYLines == ALL || in_array($AxisID, $DrawYLines)) {
								$this->drawLine($this->GraphAreaX1 + $FloatingOffset, $YPos, $this->GraphAreaX2 - $FloatingOffset, $YPos, $GridColor);
							}

							if ($DrawSubTicks && $i != $Parameters["Rows"]) {
								$this->drawLine($XPos - $OuterSubTickWidth, $YPos - $SubTicksSize, $XPos + $InnerSubTickWidth, $YPos - $SubTicksSize, $SubTickColor);
							}
							
							$this->drawLine($XPos - $OuterTickWidth, $YPos, $XPos + $InnerTickWidth, $YPos, $ColorTick);
							$Bounds = $this->drawText($XPos - $OuterTickWidth - 2, $YPos, $Value, ["Align" => TEXT_ALIGN_MIDDLERIGHT] + $ColorAxis);
							$TxtLeft = $XPos - $OuterTickWidth - 2 - ($Bounds[1]["X"] - $Bounds[0]["X"]);
							$MinLeft = min($MinLeft, $TxtLeft);
							$LastY = $YPos;
						}

						if (isset($Parameters["Name"])) {
							$XPos = $MinLeft - 2;
							$YPos = $this->GraphAreaY1 + $this->GraphAreaYdiff / 2;
							$Bounds = $this->drawText($XPos, $YPos, $Parameters["Name"], ["Align" => TEXT_ALIGN_BOTTOMMIDDLE,"Angle" => 90] + $ColorAxis);
							$MinLeft = $Bounds[2]["X"];
						}

						$AxisPos["L"] = $MinLeft - $ScaleSpacing;
						
					} elseif ($Parameters["Position"] == AXIS_POSITION_RIGHT) {
						
						if ($Floating) {
							$FloatingOffset = $XMargin;
							$this->drawLine($AxisPos["R"], $this->GraphAreaY1 + $Parameters["Margin"], $AxisPos["R"], $this->GraphAreaY2 - $Parameters["Margin"], $ColorAxis);
						} else {
							$FloatingOffset = 0;
							$this->drawLine($AxisPos["R"], $this->GraphAreaY1, $AxisPos["R"], $this->GraphAreaY2, $ColorAxis);
						}

						if ($DrawArrows) {
							$this->drawArrow($AxisPos["R"], $this->GraphAreaY1 + $Parameters["Margin"], $AxisPos["R"], $this->GraphAreaY1 - ($ArrowSize * 2), $ColorAxisArrow);
						}

						$Height = $this->GraphAreaYdiff - $Parameters["Margin"] * 2;
						$Step = $Height / $Parameters["Rows"];
						$SubTicksSize = $Step / 2;
						$MaxLeft = $AxisPos["R"];
						$LastY = NULL;
						for ($i = 0; $i <= $Parameters["Rows"]; $i++) {
							$YPos = $this->GraphAreaY2 - $Parameters["Margin"] - $Step * $i;
							$XPos = $AxisPos["R"];
							$Value = $this->scaleFormat($Parameters["ScaleMin"] + $Parameters["RowHeight"] * $i, $Parameters["Display"], $Parameters["Format"], $Parameters["Unit"]);
							$BGColor = ($i % 2 == 1) ? $BackgroundColor1 : $BackgroundColor2;

							if (!is_null($LastY) && $CycleBackground && ($DrawYLines == ALL || in_array($AxisID, $DrawYLines))) {
								$this->drawFilledRectangle($this->GraphAreaX1 + $FloatingOffset, $LastY, $this->GraphAreaX2 - $FloatingOffset, $YPos, $BGColor);
							}

							if ($DrawYLines == ALL || in_array($AxisID, $DrawYLines)) {
								$this->drawLine($this->GraphAreaX1 + $FloatingOffset, $YPos, $this->GraphAreaX2 - $FloatingOffset, $YPos, $GridColor);
							}

							if ($DrawSubTicks && $i != $Parameters["Rows"]) {
								$this->drawLine($XPos - $OuterSubTickWidth, $YPos - $SubTicksSize, $XPos + $InnerSubTickWidth, $YPos - $SubTicksSize, $SubTickColor);
							}
							$this->drawLine($XPos - $InnerTickWidth, $YPos, $XPos + $OuterTickWidth, $YPos, $ColorTick);
							$Bounds = $this->drawText($XPos + $OuterTickWidth + 2, $YPos, $Value, ["Align" => TEXT_ALIGN_MIDDLELEFT] + $ColorAxis);
							$TxtLeft = $XPos + $OuterTickWidth + 2 + ($Bounds[1]["X"] - $Bounds[0]["X"]);
							$MaxLeft = max($MaxLeft, $TxtLeft);
							$LastY = $YPos;
						}

						if (isset($Parameters["Name"])) {
							$XPos = $MaxLeft + 6;
							$YPos = $this->GraphAreaY1 + $this->GraphAreaYdiff / 2;
							$Bounds = $this->drawText($XPos, $YPos, $Parameters["Name"], ["Align" => TEXT_ALIGN_BOTTOMMIDDLE,"Angle" => 270] + $ColorAxis);
							$MaxLeft = $Bounds[2]["X"];
						}

						$AxisPos["R"] = $MaxLeft + $ScaleSpacing;
					}
					
				} elseif ($Pos == SCALE_POS_TOPBOTTOM) {
					
					if ($Parameters["Position"] == AXIS_POSITION_TOP) {
						if ($Floating) {
							$FloatingOffset = $XMargin;
							$this->drawLine($this->GraphAreaX1 + $Parameters["Margin"], $AxisPos["T"], $this->GraphAreaX2 - $Parameters["Margin"], $AxisPos["T"], $ColorAxis);
						} else {
							$FloatingOffset = 0;
							$this->drawLine($this->GraphAreaX1, $AxisPos["T"], $this->GraphAreaX2, $AxisPos["T"], $ColorAxis);
						}

						if ($DrawArrows) {
							$this->drawArrow($this->GraphAreaX2 - $Parameters["Margin"], $AxisPos["T"], $this->GraphAreaX2 + ($ArrowSize * 2), $AxisPos["T"], $ColorAxisArrow);
						}

						$Width = $this->GraphAreaXdiff - $Parameters["Margin"] * 2;
						$Step = $Width / $Parameters["Rows"];
						$SubTicksSize = $Step / 2;
						$MinTop = $AxisPos["T"];
						$LastX = NULL;
						for ($i = 0; $i <= $Parameters["Rows"]; $i++) {
							$XPos = $this->GraphAreaX1 + $Parameters["Margin"] + $Step * $i;
							$YPos = $AxisPos["T"];
							$Value = $this->scaleFormat($Parameters["ScaleMin"] + $Parameters["RowHeight"] * $i, $Parameters["Display"], $Parameters["Format"], $Parameters["Unit"]);
							$BGColor = ($i % 2 == 1) ? $BackgroundColor1 : $BackgroundColor2;

							if (!is_null($LastX) && $CycleBackground && ($DrawYLines == ALL || in_array($AxisID, $DrawYLines))) {
								$this->drawFilledRectangle($LastX, $this->GraphAreaY1 + $FloatingOffset, $XPos, $this->GraphAreaY2 - $FloatingOffset, $BGColor);
							}

							if ($DrawYLines == ALL || in_array($AxisID, $DrawYLines)) {
								$this->drawLine($XPos, $this->GraphAreaY1 + $FloatingOffset, $XPos, $this->GraphAreaY2 - $FloatingOffset, $GridColor);
							}

							if ($DrawSubTicks && $i != $Parameters["Rows"]) {
								$this->drawLine($XPos + $SubTicksSize, $YPos - $OuterSubTickWidth, $XPos + $SubTicksSize, $YPos + $InnerSubTickWidth, $SubTickColor);
							}

							$this->drawLine($XPos, $YPos - $OuterTickWidth, $XPos, $YPos + $InnerTickWidth, $ColorTick);
							$Bounds = $this->drawText($XPos, $YPos - $OuterTickWidth - 2, $Value, ["Align" => TEXT_ALIGN_BOTTOMMIDDLE] + $ColorAxis);
							$TxtHeight = $YPos - $OuterTickWidth - 2 - ($Bounds[1]["Y"] - $Bounds[2]["Y"]);
							$MinTop = min($MinTop, $TxtHeight);
							$LastX = $XPos;
						}

						if (isset($Parameters["Name"])) {
							$YPos = $MinTop - 2;
							$XPos = $this->GraphAreaX1 + $this->GraphAreaXdiff / 2;
							$Bounds = $this->drawText($XPos, $YPos, $Parameters["Name"], ["Align" => TEXT_ALIGN_BOTTOMMIDDLE] + $ColorAxis);
							$MinTop = $Bounds[2]["Y"];
						}

						$AxisPos["T"] = $MinTop - $ScaleSpacing;

					} elseif ($Parameters["Position"] == AXIS_POSITION_BOTTOM) {
						if ($Floating) {
							$FloatingOffset = $XMargin;
							$this->drawLine($this->GraphAreaX1 + $Parameters["Margin"], $AxisPos["B"], $this->GraphAreaX2 - $Parameters["Margin"], $AxisPos["B"], $ColorAxis);
						} else {
							$FloatingOffset = 0;
							$this->drawLine($this->GraphAreaX1, $AxisPos["B"], $this->GraphAreaX2, $AxisPos["B"] ,$ColorAxis);
						}

						if ($DrawArrows) {
							$this->drawArrow($this->GraphAreaX2 - $Parameters["Margin"], $AxisPos["B"], $this->GraphAreaX2 + ($ArrowSize * 2), $AxisPos["B"], $ColorAxisArrow);
						}

						$Width = $this->GraphAreaXdiff - $Parameters["Margin"] * 2;
						$Step = $Width / $Parameters["Rows"];
						$SubTicksSize = $Step / 2;
						$MaxBottom = $AxisPos["B"];
						$LastX = NULL;
						for ($i = 0; $i <= $Parameters["Rows"]; $i++) {
							$XPos = $this->GraphAreaX1 + $Parameters["Margin"] + $Step * $i;
							$YPos = $AxisPos["B"];
							$Value = $this->scaleFormat($Parameters["ScaleMin"] + $Parameters["RowHeight"] * $i, $Parameters["Display"], $Parameters["Format"], $Parameters["Unit"]);
							$BGColor = ($i % 2 == 1) ? $BackgroundColor1 : $BackgroundColor2;

							if (!is_null($LastX) && $CycleBackground && ($DrawYLines == ALL || in_array($AxisID, $DrawYLines))) {
								$this->drawFilledRectangle($LastX, $this->GraphAreaY1 + $FloatingOffset, $XPos, $this->GraphAreaY2 - $FloatingOffset, $BGColor);
							}

							if ($DrawYLines == ALL || in_array($AxisID, $DrawYLines)) {
								$this->drawLine($XPos, $this->GraphAreaY1 + $FloatingOffset, $XPos, $this->GraphAreaY2 - $FloatingOffset, $GridColor);
							}

							if ($DrawSubTicks && $i != $Parameters["Rows"]) {
								$this->drawLine($XPos + $SubTicksSize, $YPos - $OuterSubTickWidth, $XPos + $SubTicksSize, $YPos + $InnerSubTickWidth, $SubTickColor);
							}
							
							$this->drawLine($XPos, $YPos - $OuterTickWidth, $XPos, $YPos + $InnerTickWidth, $ColorTick);
							$Bounds = $this->drawText($XPos, $YPos + $OuterTickWidth + 2, $Value, ["Align" => TEXT_ALIGN_TOPMIDDLE] + $ColorAxis);
							$TxtHeight = $YPos + $OuterTickWidth + 2 + ($Bounds[1]["Y"] - $Bounds[2]["Y"]);
							$MaxBottom = max($MaxBottom, $TxtHeight);
							$LastX = $XPos;
						}

						if (isset($Parameters["Name"])) {
							$YPos = $MaxBottom + 2;
							$XPos = $this->GraphAreaX1 + $this->GraphAreaXdiff / 2;
							$Bounds = $this->drawText($XPos, $YPos, $Parameters["Name"], ["Align" => TEXT_ALIGN_TOPMIDDLE] + $ColorAxis);
							$MaxBottom = $Bounds[0]["Y"];
						}

						$AxisPos["B"] = $MaxBottom + $ScaleSpacing;
					}
				}
			}
		}
	}

	function isValidLabel($Value, $LastValue, $LabelingMethod, $ID, $LabelSkip)
	{
		$ret = TRUE;
		
		switch(TRUE){
			case ($LabelingMethod == LABELING_DIFFERENT && $Value != $LastValue):
				break;
			case ($LabelingMethod == LABELING_DIFFERENT && $Value == $LastValue):
				$ret = FALSE;
				break;
			case ($LabelingMethod == LABELING_ALL && $LabelSkip == 0):
				break;
			case ($LabelingMethod == LABELING_ALL && ($ID + $LabelSkip) % ($LabelSkip + 1) != 1):
				$ret = FALSE;
				break;
		}

		return $ret;
	}

	/* Compute the scale, check for the best visual factors */
	function computeScale($XMin, $XMax, $MaxDivs, array $Factors, $AxisID = 0)
	{
		$Results = [];
		$GoodScaleFactors = [];
		
		/* Compute each factors */
		foreach($Factors as $Factor) {
			$Results[$Factor] = $this->processScale($XMin, $XMax, $MaxDivs, [$Factor], $AxisID);
		}
		
		/* Remove scales that are creating to much decimals */
		foreach($Results as $Key => $Result) {
			if (($Result["RowHeight"] - floor($Result["RowHeight"])) < .6) {
				$GoodScaleFactors[] = $Key;
			}
		}

		/* Found no correct scale, shame,... returns the 1st one as default */
		if (empty($GoodScaleFactors)) {
			return $Results[$Factors[0]];
		}

		/* Find the factor that cause the maximum number of Rows */
		$MaxRows = 0;
		$BestFactor = 0;
		foreach($GoodScaleFactors as $Factor) {
			if ($Results[$Factor]["Rows"] > $MaxRows) {
				$MaxRows = $Results[$Factor]["Rows"];
				$BestFactor = $Factor;
			}
		}

		/* Return the best visual scale */
		return $Results[$BestFactor];
	}

	/* Compute the best matching scale based on size & factors */
	function processScale($XMin, $XMax, $MaxDivs, array $Factors, $AxisID)
	{
		$ScaleHeight = abs(ceil($XMax) - floor($XMin));
		$Format = (isset($this->myData->Data["Axis"][$AxisID]["Format"])) ?  $this->myData->Data["Axis"][$AxisID]["Format"] : NULL;
		$Mode = (isset($this->myData->Data["Axis"][$AxisID]["Display"])) ? $this->myData->Data["Axis"][$AxisID]["Display"] : AXIS_FORMAT_DEFAULT;
		$Scale = [];
		
		if ($XMin != $XMax) {
			$Found = FALSE;
			$Rescaled = FALSE;
			$Scaled10Factor = .0001;
			$Result = 0;
			while (!$Found) {
				foreach($Factors as $Factor) {
					if (!$Found) {
						$R = $Factor * $Scaled10Factor;
						if ($Factor == 0 || floor($Factor) == 0){ # Momchil: avoid division by 0
							throw pException::InvalidInput("Scale factor must be > 1.00");
						} else {
							if (floor($R) != 0){
								$XMinRescaled = ((($XMin % $R) != 0) || ($XMin != floor($XMin))) ? (floor($XMin / $R) * $R) : $XMin;
								$XMaxRescaled = ((($XMax % $R) != 0) || ($XMax != floor($XMax))) ? (floor($XMax / $R) * $R + $R) : $XMax;
							} else {
								$XMinRescaled = floor($XMin / $R) * $R;
								$XMaxRescaled = floor($XMax / $R) * $R + $R;
							}
						}
						
						$ScaleHeightRescaled = abs($XMaxRescaled - $XMinRescaled);
						
						if (!$Found && floor($ScaleHeightRescaled / $R) <= $MaxDivs) {
							$Found = TRUE;
							$Rescaled = TRUE;
							$Result = $R;
						}
					}
				}

				$Scaled10Factor = $Scaled10Factor * 10;
			}

			/* ReCall Min / Max / Height */
			if ($Rescaled) {
				$XMin = $XMinRescaled;
				$XMax = $XMaxRescaled;
				$ScaleHeight = $ScaleHeightRescaled;
			}

			/* Compute rows size */
			$Rows = floor($ScaleHeight / $Result);
			($Rows == 0) AND $Rows = 1;
			$RowHeight = $ScaleHeight / $Rows;
			
			/* Return the results */
			$Scale["Rows"] = $Rows;
			$Scale["RowHeight"] = $RowHeight;
			$Scale["XMin"] = $XMin;
			$Scale["XMax"] = $XMax;
			/* Compute the needed decimals for the metric view to avoid repetition of the same X Axis labels */
			if ($Mode == AXIS_FORMAT_METRIC && is_null($Format)) {

				$GoodDecimals = 0;
				for ($Decimals = 0; $Decimals <= 10; $Decimals++) {
					$LastLabel = "zob";
					$ScaleOK = TRUE;
					for ($i = 0; $i <= $Rows; $i++) {
						$Label = $this->scaleFormat(($XMin + $i * $RowHeight), AXIS_FORMAT_METRIC, $Decimals);
						($LastLabel == $Label) AND $ScaleOK = FALSE;
						$LastLabel = $Label;
					}

					if ($ScaleOK) {
						$GoodDecimals = $Decimals;
						break;
					}
				}

				$Scale["Format"] = $GoodDecimals;
			}
		} else {
			/* If all values are the same we keep a +1/-1 scale */
			$Scale["Rows"] = 2;
			$Scale["RowHeight"] = 1;
			$Scale["XMin"] = $XMax - 1;
			$Scale["XMax"] = $XMax + 1;
		}

		return $Scale;
	}

	/* Draw an X threshold */
	function drawXThreshold(array $Values, array $Format = [])
	{
		$Color = isset($Format["Color"]) ? $Format["Color"] : new pColor(255,0,0,50);
		$Weight = isset($Format["Weight"]) ? $Format["Weight"] : NULL;
		$Ticks = isset($Format["Ticks"]) ? $Format["Ticks"] : 6;
		$Wide = isset($Format["Wide"]) ? $Format["Wide"] : FALSE;
		$WideFactor = isset($Format["WideFactor"]) ? $Format["WideFactor"] : 5;
		$WriteCaption = isset($Format["WriteCaption"]) ? $Format["WriteCaption"] : FALSE;
		$Caption = isset($Format["Caption"]) ? $Format["Caption"] : NULL;
		$CaptionAlign = isset($Format["CaptionAlign"]) ? $Format["CaptionAlign"] : CAPTION_LEFT_TOP;
		$CaptionOffset = isset($Format["CaptionOffset"]) ? $Format["CaptionOffset"] : 5;
		$CaptionColor = isset($Format["CaptionColor"]) ? $Format["CaptionColor"] : new pColor(255);
		$DrawBox = isset($Format["DrawBox"]) ? $Format["DrawBox"] : TRUE;
		$DrawBoxBorder = isset($Format["DrawBoxBorder"]) ? $Format["DrawBoxBorder"] : FALSE;
		$BorderOffset = isset($Format["BorderOffset"]) ? $Format["BorderOffset"] : 3;
		$BoxRounded = isset($Format["BoxRounded"]) ? $Format["BoxRounded"] : TRUE;
		$RoundedRadius = isset($Format["RoundedRadius"]) ? $Format["RoundedRadius"] : 3;
		$BoxColor = isset($Format["BoxColor"]) ? $Format["BoxColor"] : new pColor(0,0,0,30);
		$BoxSurrounding = isset($Format["BoxSurrounding"]) ? $Format["BoxSurrounding"] : 0;
		$BoxBorderColor = isset($Format["BoxBorderColor"]) ? $Format["BoxBorderColor"] : new pColor(255);
		$ValueIsLabel = isset($Format["ValueIsLabel"]) ? $Format["ValueIsLabel"] : FALSE;
				
		$AbscissaMargin = $this->myData->getAbscissaMargin();
		$XScale = $this->myData->scaleGetXSettings();
		$Data = $this->myData->getData();
		
		$CaptionSettings = [
			"DrawBox" => $DrawBox,
			"DrawBoxBorder" => $DrawBoxBorder,
			"BorderOffset" => $BorderOffset,
			"BoxRounded" => $BoxRounded,
			"RoundedRadius" => $RoundedRadius,
			"BoxColor" => $BoxColor,
			"BoxSurrounding" => $BoxSurrounding,
			"BoxBorderColor" => $BoxBorderColor, # Momchil: must match drawThreshold
			"Color" => $CaptionColor
		];

		$WideColor = $Color->newOne()->AlphaSlash($WideFactor);

		foreach($Values as $Value){

			if ($ValueIsLabel) {
				$Format["ValueIsLabel"] = FALSE;
				foreach($Data["Series"][$Data["Abscissa"]]["Data"] as $Key => $SerieValue) {
					if ($SerieValue == $Value) {
						$this->drawXThreshold([$Key], $Format);
					}
				}
				return;
			}
			
			if (is_null($Caption)) {
				if (isset($Data["Abscissa"])) {
					$Caption = (isset($Data["Series"][$Data["Abscissa"]]["Data"][$Value])) ? $Data["Series"][$Data["Abscissa"]]["Data"][$Value] : $Value;
				} else {
					$Caption = $Value;
				}
			}

			if ($Data["Orientation"] == SCALE_POS_LEFTRIGHT) {
				$XStep = ($this->GraphAreaXdiff - $XScale[0] * 2) / $XScale[1];
				$XPos = $this->GraphAreaX1 + $XScale[0] + $XStep * $Value;
				$YPos1 = $this->GraphAreaY1 + $Data["YMargin"];
				$YPos2 = $this->GraphAreaY2 - $Data["YMargin"];
				if ($XPos >= $this->GraphAreaX1 + $AbscissaMargin && $XPos <= $this->GraphAreaX2 - $AbscissaMargin) {
					$this->drawLine($XPos, $YPos1, $XPos, $YPos2, ["Color" => $Color,"Ticks" => $Ticks,"Weight" => $Weight]);
					if ($Wide) {
						$this->drawLine($XPos - 1, $YPos1, $XPos - 1, $YPos2, ["Color" => $WideColor,"Ticks" => $Ticks]);
						$this->drawLine($XPos + 1, $YPos1, $XPos + 1, $YPos2, ["Color" => $WideColor,"Ticks" => $Ticks]);
					}

					if ($WriteCaption) {
						if ($CaptionAlign == CAPTION_LEFT_TOP) {
							$Y = $YPos1 + $CaptionOffset;
							$CaptionSettings["Align"] = TEXT_ALIGN_TOPMIDDLE;
						} else {
							$Y = $YPos2 - $CaptionOffset;
							$CaptionSettings["Align"] = TEXT_ALIGN_BOTTOMMIDDLE;
						}

						$this->drawText($XPos, $Y, $Caption, $CaptionSettings);
					}
				}
				
			} elseif ($Data["Orientation"] == SCALE_POS_TOPBOTTOM) {
				$XStep = ($this->GraphAreaYdiff - $XScale[0] * 2) / $XScale[1];
				$XPos = $this->GraphAreaY1 + $XScale[0] + $XStep * $Value;
				$YPos1 = $this->GraphAreaX1 + $Data["YMargin"];
				$YPos2 = $this->GraphAreaX2 - $Data["YMargin"];
				if ($XPos >= $this->GraphAreaY1 + $AbscissaMargin && $XPos <= $this->GraphAreaY2 - $AbscissaMargin) {
					$this->drawLine($YPos1, $XPos, $YPos2, $XPos, ["Color" => $Color,"Ticks" => $Ticks,"Weight" => $Weight]);
					if ($Wide) {
						
						$this->drawLine($YPos1, $XPos - 1, $YPos2, $XPos - 1, ["Color" => $WideColor,"Ticks" => $Ticks]);
						$this->drawLine($YPos1, $XPos + 1, $YPos2, $XPos + 1, ["Color" => $WideColor,"Ticks" => $Ticks]);
					}

					if ($WriteCaption) {
						if ($CaptionAlign == CAPTION_LEFT_TOP) {
							$Y = $YPos1 + $CaptionOffset;
							$CaptionSettings["Align"] = TEXT_ALIGN_MIDDLELEFT;
						} else {
							$Y = $YPos2 - $CaptionOffset;
							$CaptionSettings["Align"] = TEXT_ALIGN_MIDDLERIGHT;
						}

						$this->drawText($Y, $XPos, $Caption, $CaptionSettings);
					}
				}
			}
		
		} # foreach
		
	}

	/* Draw an X threshold area */
	function drawXThresholdArea($Value1, $Value2, array $Format = []) 
	{
		$Color = isset($Format["Color"]) ? $Format["Color"] : new pColor(255,0,0,20);
		$Border = isset($Format["Border"]) ? $Format["Border"] : TRUE;
		$BorderColor = isset($Format["BorderColor"]) ? $Format["BorderColor"] : NULL;
		$BorderTicks = isset($Format["BorderTicks"]) ? $Format["BorderTicks"] : 2;
		$AreaName = isset($Format["AreaName"]) ? $Format["AreaName"] : NULL;
		$NameAngle = isset($Format["NameAngle"]) ? $Format["NameAngle"] : ZONE_NAME_ANGLE_AUTO;
		$NameColor = isset($Format["NameColor"]) ? $Format["NameColor"] : new pColor(255);
		$DisableShadowOnArea = isset($Format["DisableShadowOnArea"]) ? $Format["DisableShadowOnArea"] : TRUE;

		if (is_null($BorderColor)){
			$BorderColor = $Color->newOne()->AlphaChange(20);
		}
		
		$RestoreShadow = $this->Shadow;
		($DisableShadowOnArea && $this->Shadow) AND $this->Shadow = FALSE;
		$XScale = $this->myData->scaleGetXSettings();
		#$AbscissaMargin =  $this->myData->getAbscissaMargin(); # UNUSED
		$Data = $this->myData->getData();
		
		if ($Data["Orientation"] == SCALE_POS_LEFTRIGHT) {
			$XStep = ($this->GraphAreaXdiff - $XScale[0] * 2) / $XScale[1];
			$XPos1 = $this->GraphAreaX1 + $XScale[0] + $XStep * $Value1;
			$XPos2 = $this->GraphAreaX1 + $XScale[0] + $XStep * $Value2;
			$YPos1 = $this->GraphAreaY1 + $Data["YMargin"];
			$YPos2 = $this->GraphAreaY2 - $Data["YMargin"];
			($XPos1 < $this->GraphAreaX1 + $XScale[0]) AND $XPos1 = $this->GraphAreaX1 + $XScale[0];
			($XPos1 > $this->GraphAreaX2 - $XScale[0]) AND $XPos1 = $this->GraphAreaX2 - $XScale[0];
			($XPos2 < $this->GraphAreaX1 + $XScale[0]) AND $XPos2 = $this->GraphAreaX1 + $XScale[0];
			($XPos2 > $this->GraphAreaX2 - $XScale[0]) AND $XPos2 = $this->GraphAreaX2 - $XScale[0];

			$this->drawFilledRectangle($XPos1, $YPos1, $XPos2, $YPos2, ["Color" => $Color]);
			if ($Border) {
				$this->drawLine($XPos1, $YPos1, $XPos1, $YPos2, ["Color" => $BorderColor,"Ticks" => $BorderTicks]);
				$this->drawLine($XPos2, $YPos1, $XPos2, $YPos2, ["Color" => $BorderColor,"Ticks" => $BorderTicks]);
			}

			if (!is_null($AreaName)) {
				$XPos = ($XPos2 - $XPos1) / 2 + $XPos1;
				$YPos = ($YPos2 - $YPos1) / 2 + $YPos1;
				if ($NameAngle == ZONE_NAME_ANGLE_AUTO) {
					$TxtPos = $this->getTextBox($XPos, $YPos, $this->FontName, $this->FontSize, 0, $AreaName);
					$TxtWidth = $TxtPos[1]["X"] - $TxtPos[0]["X"];
					$NameAngle = (abs($XPos2 - $XPos1) > $TxtWidth) ? 0 : 90; 
				}

				$this->Shadow = $RestoreShadow;
				$this->drawText($XPos, $YPos, $AreaName, ["Color" => $NameColor,"Angle" => $NameAngle,"Align" => TEXT_ALIGN_MIDDLEMIDDLE]);
				if ($DisableShadowOnArea) {
					$this->Shadow = FALSE;
				}
			}

		} elseif ($Data["Orientation"] == SCALE_POS_TOPBOTTOM) {
			$XStep = ($this->GraphAreaYdiff - $XScale[0] * 2) / $XScale[1];
			$XPos1 = $this->GraphAreaY1 + $XScale[0] + $XStep * $Value1;
			$XPos2 = $this->GraphAreaY1 + $XScale[0] + $XStep * $Value2;
			$YPos1 = $this->GraphAreaX1 + $Data["YMargin"];
			$YPos2 = $this->GraphAreaX2 - $Data["YMargin"];
			($XPos1 < $this->GraphAreaY1 + $XScale[0]) AND $XPos1 = $this->GraphAreaY1 + $XScale[0];
			($XPos1 > $this->GraphAreaY2 - $XScale[0]) AND $XPos1 = $this->GraphAreaY2 - $XScale[0];
			($XPos2 < $this->GraphAreaY1 + $XScale[0]) AND $XPos2 = $this->GraphAreaY1 + $XScale[0];
			($XPos2 > $this->GraphAreaY2 - $XScale[0]) AND $XPos2 = $this->GraphAreaY2 - $XScale[0];

			$this->drawFilledRectangle($YPos1, $XPos1, $YPos2, $XPos2, ["Color" => $Color]);
			if ($Border) {
				$this->drawLine($YPos1, $XPos1, $YPos2, $XPos1, ["Color" => $BorderColor,"Ticks" => $BorderTicks]);
				$this->drawLine($YPos1, $XPos2, $YPos2, $XPos2, ["Color" => $BorderColor,"Ticks" => $BorderTicks]);
			}

			if (!is_null($AreaName)) {
				$XPos = ($XPos2 - $XPos1) / 2 + $XPos1;
				$YPos = ($YPos2 - $YPos1) / 2 + $YPos1;
				$this->Shadow = $RestoreShadow;
				$this->drawText($YPos, $XPos, $AreaName, ["Color" => $NameColor,"Angle" => 0,"Align" => TEXT_ALIGN_MIDDLEMIDDLE]);
				if ($DisableShadowOnArea) {
					$this->Shadow = FALSE;
				}
			}	
		}
		
		$this->Shadow = $RestoreShadow;
	}

	/* Draw an Y threshold with the computed scale */
	function drawThreshold(array $Values, array $Format = [])
	{

		$AxisID = isset($Format["AxisID"]) ? $Format["AxisID"] : 0;
		$Color = isset($Format["Color"]) ? $Format["Color"] : new pColor(255,0,0,20);
		$Weight = isset($Format["Weight"]) ? $Format["Weight"] : NULL;
		$Ticks = isset($Format["Ticks"]) ? $Format["Ticks"] : 6;
		$Wide = isset($Format["Wide"]) ? $Format["Wide"] : FALSE;
		$WideFactor = isset($Format["WideFactor"]) ? $Format["WideFactor"] : 5;
		$WriteCaption = isset($Format["WriteCaption"]) ? $Format["WriteCaption"] : FALSE;
		$Caption = isset($Format["Caption"]) ? $Format["Caption"] : NULL;
		$CaptionAlign = isset($Format["CaptionAlign"]) ? $Format["CaptionAlign"] : CAPTION_LEFT_TOP;
		$CaptionOffset = isset($Format["CaptionOffset"]) ? $Format["CaptionOffset"] : 10;
		$CaptionColor = isset($Format["CaptionColor"]) ? $Format["CaptionColor"] : new pColor(255);
		$DrawBox = isset($Format["DrawBox"]) ? $Format["DrawBox"] : TRUE;
		$DrawBoxBorder = isset($Format["DrawBoxBorder"]) ? $Format["DrawBoxBorder"] : FALSE;
		$BorderOffset = isset($Format["BorderOffset"]) ? $Format["BorderOffset"] : 5;
		$BoxRounded = isset($Format["BoxRounded"]) ? $Format["BoxRounded"] : TRUE;
		$RoundedRadius = isset($Format["RoundedRadius"]) ? $Format["RoundedRadius"] : 3;
		$BoxColor = isset($Format["BoxColor"]) ? $Format["BoxColor"] : new pColor(0,0,0,20);
		$BoxSurrounding = isset($Format["BoxSurrounding"]) ? $Format["BoxSurrounding"] : 0;
		$BoxBorderColor = isset($Format["BoxBorderColor"]) ? $Format["BoxBorderColor"] : new pColor(255);
		$NoMargin = isset($Format["NoMargin"]) ? $Format["NoMargin"] : FALSE;
		
		$WideColor = $Color->newOne()->AlphaSlash($WideFactor);
		
		$Data = $this->myData->getData();
				
		if (!isset($Data["Axis"][$AxisID])) {
			throw pException::InvalidInput("Axis ID is invalid");
		}
		
		$CaptionSettings = [
			"DrawBox" => $DrawBox,
			"DrawBoxBorder" => $DrawBoxBorder,
			"BorderOffset" => $BorderOffset,
			"BoxRounded" => $BoxRounded,
			"RoundedRadius" => $RoundedRadius,
			"BoxColor" => $BoxColor,
			"BoxSurrounding" => $BoxSurrounding,
			"BoxBorderColor" => $BoxColor, # Momchil: that was done to match the example
			"Color" => $CaptionColor
		];

		$AbscissaMargin =  $this->myData->getAbscissaMargin();
		($NoMargin) AND $AbscissaMargin = 0;
		
		foreach ($Values as $Value){
			(is_null($Caption)) AND $Caption = $Value;

			if ($Data["Orientation"] == SCALE_POS_LEFTRIGHT) {
				$YPos = $this->scaleComputeYSingle($Value, $AxisID);
				if ($YPos >= $this->GraphAreaY1 + $Data["Axis"][$AxisID]["Margin"] && $YPos <= $this->GraphAreaY2 - $Data["Axis"][$AxisID]["Margin"]) {
					$X1 = $this->GraphAreaX1 + $AbscissaMargin;
					$X2 = $this->GraphAreaX2 - $AbscissaMargin;
					$this->drawLine($X1, $YPos, $X2, $YPos, ["Color" => $Color,"Ticks" => $Ticks,"Weight" => $Weight]);
					if ($Wide) {
						$this->drawLine($X1, $YPos - 1, $X2, $YPos - 1, ["Color" => $WideColor,"Ticks" => $Ticks]);
						$this->drawLine($X1, $YPos + 1, $X2, $YPos + 1, ["Color" => $WideColor,"Ticks" => $Ticks]);
					}

					if ($WriteCaption) {
						if ($CaptionAlign == CAPTION_LEFT_TOP) {
							$X = $X1 + $CaptionOffset;
							$CaptionSettings["Align"] = TEXT_ALIGN_MIDDLELEFT;
						} else {
							$X = $X2 - $CaptionOffset;
							$CaptionSettings["Align"] = TEXT_ALIGN_MIDDLERIGHT;
						}
						$this->drawText($X, $YPos, $Caption, $CaptionSettings);
					}
				}

			}

			if ($Data["Orientation"] == SCALE_POS_TOPBOTTOM) {
				$XPos = $this->scaleComputeYSingle($Value, $AxisID);
				if ($XPos >= $this->GraphAreaX1 + $Data["Axis"][$AxisID]["Margin"] && $XPos <= $this->GraphAreaX2 - $Data["Axis"][$AxisID]["Margin"]) {
					$Y1 = $this->GraphAreaY1 + $AbscissaMargin;
					$Y2 = $this->GraphAreaY2 - $AbscissaMargin;
					$this->drawLine($XPos, $Y1, $XPos, $Y2,["Color" => $Color,"Ticks" => $Ticks,"Weight" => $Weight]);
					if ($Wide) {
						$this->drawLine($XPos - 1, $Y1, $XPos - 1, $Y2, ["Color" => $WideColor,"Ticks" => $Ticks]);
						$this->drawLine($XPos + 1, $Y1, $XPos + 1, $Y2, ["Color" => $WideColor,"Ticks" => $Ticks]);
					}

					if ($WriteCaption) {
						if ($CaptionAlign == CAPTION_LEFT_TOP) {
							$Y = $Y1 + $CaptionOffset;
							$CaptionSettings["Align"] = TEXT_ALIGN_TOPMIDDLE;
						} else {
							$Y = $Y2 - $CaptionOffset;
							$CaptionSettings["Align"] = TEXT_ALIGN_BOTTOMMIDDLE;
						}

						$CaptionSettings["Align"] = TEXT_ALIGN_TOPMIDDLE;
						$this->drawText($XPos, $Y, $Caption, $CaptionSettings);
					}
				}
			}
		
		} # foreach
	}

	/* Draw a threshold with the computed scale */
	function drawThresholdArea($Value1, $Value2, array $Format = []) 
	{
		$AxisID = isset($Format["AxisID"]) ? $Format["AxisID"] : 0;
		$Color = isset($Format["Color"]) ? $Format["Color"] : new pColor(255,0,0,20);
		$Border = isset($Format["Border"]) ? $Format["Border"] : TRUE;
		$BorderColor = isset($Format["BorderColor"]) ? $Format["BorderColor"] : $Color->newOne()->AlphaChange(20);
		$BorderTicks = isset($Format["BorderTicks"]) ? $Format["BorderTicks"] : 2;
		$AreaName = isset($Format["AreaName"]) ? $Format["AreaName"] : NULL;
		$NameAngle = isset($Format["NameAngle"]) ? $Format["NameAngle"] : ZONE_NAME_ANGLE_AUTO;
		$NameColor = isset($Format["NameColor"]) ? $Format["NameColor"] : new pColor(255);
		$DisableShadowOnArea = isset($Format["DisableShadowOnArea"]) ? $Format["DisableShadowOnArea"] : TRUE;
		$NoMargin = isset($Format["NoMargin"]) ? $Format["NoMargin"] : FALSE;
		
		$Data = $this->myData->getData();
		
		if (!isset($Data["Axis"][$AxisID])) {
			throw pException::InvalidInput("Axis ID is invalid");
		}
		
		$margin = $Data["Axis"][$AxisID]["Margin"];
		
		if ($Value1 > $Value2) {
			list($Value1, $Value2) = [$Value2,$Value1];
		}

		$RestoreShadow = $this->Shadow;
		($DisableShadowOnArea && $this->Shadow) AND $this->Shadow = FALSE;

		$AbscissaMargin = $this->myData->getAbscissaMargin();
		($NoMargin) AND $AbscissaMargin = 0;
	
		if ($Data["Orientation"] == SCALE_POS_LEFTRIGHT) {
			$XPos1 = $this->GraphAreaX1 + $AbscissaMargin;
			$XPos2 = $this->GraphAreaX2 - $AbscissaMargin;
			$YPos1 = $this->scaleComputeYSingle($Value1, $AxisID);
			$YPos2 = $this->scaleComputeYSingle($Value2, $AxisID);
			
			($YPos1 < $this->GraphAreaY1 + $margin) AND $YPos1 = $this->GraphAreaY1 + $margin;
			($YPos1 > $this->GraphAreaY2 - $margin) AND $YPos1 = $this->GraphAreaY2 - $margin;
			($YPos2 < $this->GraphAreaY1 + $margin) AND $YPos2 = $this->GraphAreaY1 + $margin;
			($YPos2 > $this->GraphAreaY2 - $margin) AND $YPos2 = $this->GraphAreaY2 - $margin;
			
			$this->drawFilledRectangle($XPos1, $YPos1, $XPos2, $YPos2, ["Color" => $Color]);
			if ($Border) {
				$this->drawLine($XPos1, $YPos1, $XPos2, $YPos1, ["Color" => $BorderColor,"Ticks" => $BorderTicks]);
				$this->drawLine($XPos1, $YPos2, $XPos2, $YPos2, ["Color" => $BorderColor,"Ticks" => $BorderTicks,"Ticks" => $BorderTicks]);
			}

			if (!is_null($AreaName)) {
				$XPos = ($XPos2 - $XPos1) / 2 + $XPos1;
				$YPos = ($YPos2 - $YPos1) / 2 + $YPos1;
				$this->Shadow = $RestoreShadow;
				$this->drawText($XPos, $YPos, $AreaName, ["Color" => $NameColor,"Angle" => 0,"Align" => TEXT_ALIGN_MIDDLEMIDDLE]);
				if ($DisableShadowOnArea) {
					$this->Shadow = FALSE;
				}
			}

		} elseif ($Data["Orientation"] == SCALE_POS_TOPBOTTOM) {
			
			$YPos1 = $this->GraphAreaY1 + $AbscissaMargin;
			$YPos2 = $this->GraphAreaY2 - $AbscissaMargin;
			$XPos1 = $this->scaleComputeYSingle($Value1, $AxisID);
			$XPos2 = $this->scaleComputeYSingle($Value2, $AxisID);
			
			($XPos1 < $this->GraphAreaX1 + $margin) AND $XPos1 = $this->GraphAreaX1 + $margin;
			($XPos1 > $this->GraphAreaX2 - $margin) AND $XPos1 = $this->GraphAreaX2 - $margin;
			($XPos2 < $this->GraphAreaX1 + $margin) AND $XPos2 = $this->GraphAreaX1 + $margin;
			($XPos2 > $this->GraphAreaX2 - $margin) AND $XPos2 = $this->GraphAreaX2 - $margin;
			
			$this->drawFilledRectangle($XPos1, $YPos1, $XPos2, $YPos2, ["Color" => $Color]);
			if ($Border) {
				$this->drawLine($XPos1, $YPos1, $XPos1, $YPos2, ["Color" => $BorderColor,"Ticks" => $BorderTicks]);
				$this->drawLine($XPos2, $YPos1, $XPos2, $YPos2, ["Color" => $BorderColor,"Ticks" => $BorderTicks]);
			}

			if (!is_null($AreaName)) {
				$XPos = ($YPos2 - $YPos1) / 2 + $YPos1;
				$YPos = ($XPos2 - $XPos1) / 2 + $XPos1;
				if ($NameAngle == ZONE_NAME_ANGLE_AUTO) {
					$TxtPos = $this->getTextBox($XPos, $YPos, $this->FontName, $this->FontSize, 0, $AreaName);
					$TxtWidth = $TxtPos[1]["X"] - $TxtPos[0]["X"];
					$NameAngle = (abs($XPos2 - $XPos1) > $TxtWidth) ? 0 : 90;
				}

				$this->Shadow = $RestoreShadow;
				$this->drawText($YPos, $XPos, $AreaName, ["Color" => $NameColor,"Angle" => $NameAngle,"Align" => TEXT_ALIGN_MIDDLEMIDDLE]);
				if ($DisableShadowOnArea) {
					$this->Shadow = FALSE;
				}
			}
		}
		
		$this->Shadow = $RestoreShadow;
	}

	function scaleComputeYSingle($Value, int $AxisID)
	{
		if ($Value == VOID) {
			return VOID;
		}
		
		$Data = $this->myData->getData();

		$Result = 0;
		$Scale = $Data["Axis"][$AxisID]["ScaleMax"] - $Data["Axis"][$AxisID]["ScaleMin"];
		$Margin = $Data["Axis"][$AxisID]["Margin"];
		
		if ($Data["Orientation"] == SCALE_POS_LEFTRIGHT) {
			$Height = $this->GraphAreaYdiff - $Margin * 2;	
			$Result = $this->GraphAreaY2 - $Margin - (($Height / $Scale) * ($Value - $Data["Axis"][$AxisID]["ScaleMin"]));
		} else {
			$Width = $this->GraphAreaXdiff - $Margin * 2;
			$Result = $this->GraphAreaX1 + $Margin + (($Width / $Scale) * ($Value - $Data["Axis"][$AxisID]["ScaleMin"]));
		}

		return $Result;
	}

	function scaleComputeY(array $Values, int $AxisID)
	{

		$Data = $this->myData->getData();
		
		if (!isset($Data["Axis"][$AxisID])) {
			throw pException::InvalidInput("Invalid serie ID");
		}
		
		$Result = [];
		foreach($Values as $Val){
			$Result[] = $this->scaleComputeYSingle($Val, $AxisID);
		}

		return $Result;
	}

	/* Used in pCharts->drawStackedAreaChart() & pCharts->drawStackedBarChart() */
	function scaleComputeY0HeightOnly(array $Values, int $AxisID)
	{
		$Data = $this->myData->getData();
		$Scale = $Data["Axis"][$AxisID]["ScaleMax"] - $Data["Axis"][$AxisID]["ScaleMin"];
		$Result = [];
		
		if ($Data["Orientation"] == SCALE_POS_LEFTRIGHT) {
			$Height = $this->GraphAreaYdiff - $Data["Axis"][$AxisID]["Margin"] * 2;
			foreach($Values as $Value) {
				$Result[] = ($Value == VOID) ? VOID : ($Height / $Scale) * $Value;
			}
		} else {
			$Width = $this->GraphAreaXdiff - $Data["Axis"][$AxisID]["Margin"] * 2;
			foreach($Values as $Value) {
				$Result[] = ($Value == VOID) ? VOID : ($Width / $Scale) * $Value;
			}
		}

		return $Result;
	}

	/* Format the axis values */
	function scaleFormat($Value, $Mode = NULL, $Format = NULL, $Unit = NULL)
	{
		if ($Value == VOID) {
			return "";
		}

		# Momchil: this is not the same as default for the switch
		# $Value comes as an INT or FLOAT but is used as a STRING as well
		$ret = strval($Value) . $Unit;
		
		switch ($Mode) {
			case AXIS_FORMAT_TRAFFIC:
				if ($Value == 0) {
					$ret = "0B";
				} else {
					$Units = ["B","KB","MB","GB","TB","PB"];
					$Sign = "";
					
					if ($Value < 0) {
						$Value = abs($Value);
						$Sign = "-";
					}

					$Value = number_format($Value / pow(1024, ($Scale = floor(log($Value, 1024)))), 2, ",", ".");
					$ret = $Sign . strval($Value) . " " . $Units[$Scale];
				}
				break;
			case AXIS_FORMAT_CUSTOM:
				if (function_exists($Format)) {
					$ret = (call_user_func($Format, $Value));
				}
				break;
			case AXIS_FORMAT_DATE:
				$ret = gmdate((is_null($Format)) ? "d/m/Y" : $Format, $Value);
				break;
			case AXIS_FORMAT_TIME:
				$ret = gmdate((is_null($Format)) ? "H:i:s" : $Format, $Value);
				break;
			case AXIS_FORMAT_CURRENCY:
				$ret = $Format . number_format($Value, 2);
				break;
			case AXIS_FORMAT_METRIC:
				if (abs($Value) >= 1000) {
					$ret = (round($Value / 1000, $Format) . "k" . $Unit);
				} elseif (abs($Value) > 1000000) {
					$ret = (round($Value / 1000000, $Format) . "m" . $Unit);
				} elseif (abs($Value) > 1000000000) {
					$ret = (round($Value / 1000000000, $Format) . "g" . $Unit);
				}
				break;
		}

		return strval($ret);
	}

	/* Write Max value on a chart */
	function writeBounds($Type = BOUND_BOTH, array $Format = [])
	{
		$MaxLabelTxt = "max=";
		$MinLabelTxt = "min=";
		$Decimals = 1;
		$ExcludedSeries = [];
		$DisplayOffset = 4;
		$DisplayColor = DISPLAY_MANUAL;
		$MaxDisplayColor = new pColor(0);
		$MinDisplayColor = new pColor(255);
		$MinLabelPos = BOUND_LABEL_POS_AUTO;
		$MaxLabelPos = BOUND_LABEL_POS_AUTO;
		$DrawBox = TRUE;
		$DrawBoxBorder = FALSE;
		$BorderOffset = 5;
		$BoxRounded = TRUE;
		$RoundedRadius = 3;
		$BoxColor = new pColor(0,0,0,30);
		$BoxSurrounding = 0;
		$BoxBorderColor = new pColor(0,0,0,50);
		
		/* Override defaults */
		extract($Format);
		
		$CaptionSettings = [
			"DrawBox" => $DrawBox,
			"DrawBoxBorder" => $DrawBoxBorder,
			"BorderOffset" => $BorderOffset,
			"BoxRounded" => $BoxRounded,
			"RoundedRadius" => $RoundedRadius,
			"BoxColor" => $BoxColor,
			"BoxSurrounding" => $BoxSurrounding,
			"BoxBorderColor" => $BoxBorderColor
		];

		list($XMargin, $XDivs) = $this->myData->scaleGetXSettings();
		
		$Data = $this->myData->getData();
		
		if ($Data["Orientation"] == SCALE_POS_LEFTRIGHT) {
			$XStep = ($this->GraphAreaXdiff - $XMargin * 2) / $XDivs;
		} else {
			$XStep = ($this->GraphAreaYdiff - $XMargin * 2) / $XDivs;
		}
		
		foreach($Data["Series"] as $SerieName => $Serie) {
			if ($Serie["isDrawable"] && $SerieName != $Data["Abscissa"] && !isset($ExcludedSeries[$SerieName])) {

				$MinValue = $Serie["Min"];
				$MaxValue = $Serie["Max"];
				$MinPos = array_search($MinValue, $Serie["Data"]);
				$MaxPos = array_search($MaxValue, $Serie["Data"]);
				$AxisID = $Serie["Axis"];
				$Mode = $Data["Axis"][$AxisID]["Display"];
				$Format = $Data["Axis"][$AxisID]["Format"];
				$Unit = $Data["Axis"][$AxisID]["Unit"];
				$PosArray = $this->scaleComputeY($Serie["Data"], $Serie["Axis"]);
				$SerieOffset = $Serie["XOffset"];

				if ($Data["Orientation"] == SCALE_POS_LEFTRIGHT) {

					$X = $this->GraphAreaX1 + $XMargin;

					if ($Type == BOUND_MAX || $Type == BOUND_BOTH) {
						if ($MaxLabelPos == BOUND_LABEL_POS_TOP || ($MaxLabelPos == BOUND_LABEL_POS_AUTO && $MaxValue >= 0)) {
							$YPos = $PosArray[$MaxPos] - $DisplayOffset + 2;
							$Align = TEXT_ALIGN_BOTTOMMIDDLE;
						}

						if ($MaxLabelPos == BOUND_LABEL_POS_BOTTOM || ($MaxLabelPos == BOUND_LABEL_POS_AUTO && $MaxValue < 0)) {
							$YPos = $PosArray[$MaxPos] + $DisplayOffset + 2;
							$Align = TEXT_ALIGN_TOPMIDDLE;
						}

						$XPos = $X + $MaxPos * $XStep + $SerieOffset;
						$Label = $MaxLabelTxt . $this->scaleFormat(round($MaxValue, $Decimals), $Mode, $Format, $Unit);
						$TxtPos = $this->getTextBox($XPos, $YPos, $this->FontName, $this->FontSize, 0, $Label);
						$XOffset = 0;
						$YOffset = 0;
						
						($TxtPos[0]["X"] < $this->GraphAreaX1) AND $XOffset = (($this->GraphAreaX1 - $TxtPos[0]["X"]) / 2);
						($TxtPos[1]["X"] > $this->GraphAreaX2) AND $XOffset = - (($TxtPos[1]["X"] - $this->GraphAreaX2) / 2);
						($TxtPos[2]["Y"] < $this->GraphAreaY1) AND $YOffset = $this->GraphAreaY1 - $TxtPos[2]["Y"];
						($TxtPos[0]["Y"] > $this->GraphAreaY2) AND $YOffset = - ($TxtPos[0]["Y"] - $this->GraphAreaY2);

						$CaptionSettings["Color"] = $MaxDisplayColor;
						$CaptionSettings["Align"] = $Align;
						$this->drawText($XPos + $XOffset, $YPos + $YOffset, $Label, $CaptionSettings);
					}

					if ($Type == BOUND_MIN || $Type == BOUND_BOTH) {
						if ($MinLabelPos == BOUND_LABEL_POS_TOP || ($MinLabelPos == BOUND_LABEL_POS_AUTO && $MinValue >= 0)) {
							$YPos = $PosArray[$MinPos] - $DisplayOffset + 2;
							$Align = TEXT_ALIGN_BOTTOMMIDDLE;
						}

						if ($MinLabelPos == BOUND_LABEL_POS_BOTTOM || ($MinLabelPos == BOUND_LABEL_POS_AUTO && $MinValue < 0)) {
							$YPos = $PosArray[$MinPos] + $DisplayOffset + 2;
							$Align = TEXT_ALIGN_TOPMIDDLE;
						}

						$XPos = $X + $MinPos * $XStep + $SerieOffset;
						$Label = $MinLabelTxt . $this->scaleFormat(round($MinValue, $Decimals), $Mode, $Format, $Unit);
						$TxtPos = $this->getTextBox($XPos, $YPos, $this->FontName, $this->FontSize, 0, $Label);
						$XOffset = 0;
						$YOffset = 0;
						
						($TxtPos[0]["X"] < $this->GraphAreaX1) AND $XOffset = (($this->GraphAreaX1 - $TxtPos[0]["X"]) / 2);
						($TxtPos[1]["X"] > $this->GraphAreaX2) AND $XOffset = - (($TxtPos[1]["X"] - $this->GraphAreaX2) / 2);
						($TxtPos[2]["Y"] < $this->GraphAreaY1) AND $YOffset = $this->GraphAreaY1 - $TxtPos[2]["Y"];
						($TxtPos[0]["Y"] > $this->GraphAreaY2) AND $YOffset = - ($TxtPos[0]["Y"] - $this->GraphAreaY2);

						$CaptionSettings["Color"] = $MinDisplayColor;
						$CaptionSettings["Align"] = $Align;
						$this->drawText($XPos + $XOffset, $YPos - $DisplayOffset + $YOffset, $Label, $CaptionSettings);
					}

				} else {

					$X = $this->GraphAreaY1 + $XMargin;

					if ($Type == BOUND_MAX || $Type == BOUND_BOTH) {
						if ($MaxLabelPos == BOUND_LABEL_POS_TOP || ($MaxLabelPos == BOUND_LABEL_POS_AUTO && $MaxValue >= 0)) {
							$YPos = $PosArray[$MaxPos] + $DisplayOffset + 2;
							$Align = TEXT_ALIGN_MIDDLELEFT;
						}

						if ($MaxLabelPos == BOUND_LABEL_POS_BOTTOM || ($MaxLabelPos == BOUND_LABEL_POS_AUTO && $MaxValue < 0)) {
							$YPos = $PosArray[$MaxPos] - $DisplayOffset + 2;
							$Align = TEXT_ALIGN_MIDDLERIGHT;
						}

						$XPos = $X + $MaxPos * $XStep + $SerieOffset;
						$Label = $MaxLabelTxt . $this->scaleFormat($MaxValue, $Mode, $Format, $Unit);
						$TxtPos = $this->getTextBox($YPos, $XPos, $this->FontName, $this->FontSize, 0, $Label);
						$XOffset = 0;
						$YOffset = 0;
						
						($TxtPos[0]["X"] < $this->GraphAreaX1) AND $XOffset = $this->GraphAreaX1 - $TxtPos[0]["X"];
						($TxtPos[1]["X"] > $this->GraphAreaX2) AND $XOffset = - ($TxtPos[1]["X"] - $this->GraphAreaX2);
						($TxtPos[2]["Y"] < $this->GraphAreaY1) AND $YOffset = ($this->GraphAreaY1 - $TxtPos[2]["Y"]) / 2;
						($TxtPos[0]["Y"] > $this->GraphAreaY2) AND $YOffset = - (($TxtPos[0]["Y"] - $this->GraphAreaY2) / 2);
	
						$CaptionSettings["Color"] = $MaxDisplayColor;
						$CaptionSettings["Align"] = $Align;
						$this->drawText($YPos + $XOffset, $XPos + $YOffset, $Label, $CaptionSettings);
					}

					if ($Type == BOUND_MIN || $Type == BOUND_BOTH) {
						if ($MinLabelPos == BOUND_LABEL_POS_TOP || ($MinLabelPos == BOUND_LABEL_POS_AUTO && $MinValue >= 0)) {
							$YPos = $PosArray[$MinPos] + $DisplayOffset + 2;
							$Align = TEXT_ALIGN_MIDDLELEFT;
						}

						if ($MinLabelPos == BOUND_LABEL_POS_BOTTOM || ($MinLabelPos == BOUND_LABEL_POS_AUTO && $MinValue < 0)) {
							$YPos = $PosArray[$MinPos] - $DisplayOffset + 2;
							$Align = TEXT_ALIGN_MIDDLERIGHT;
						}

						$XPos = $X + $MinPos * $XStep + $SerieOffset;
						$Label = $MinLabelTxt . $this->scaleFormat($MinValue, $Mode, $Format, $Unit);
						$TxtPos = $this->getTextBox($YPos, $XPos, $this->FontName, $this->FontSize, 0, $Label);
						$XOffset = 0;
						$YOffset = 0;
						
						($TxtPos[0]["X"] < $this->GraphAreaX1) AND $XOffset = $this->GraphAreaX1 - $TxtPos[0]["X"];
						($TxtPos[1]["X"] > $this->GraphAreaX2) AND $XOffset = - ($TxtPos[1]["X"] - $this->GraphAreaX2);
						($TxtPos[2]["Y"] < $this->GraphAreaY1) AND $YOffset = ($this->GraphAreaY1 - $TxtPos[2]["Y"]) / 2;
						($TxtPos[0]["Y"] > $this->GraphAreaY2) AND $YOffset = - (($TxtPos[0]["Y"] - $this->GraphAreaY2) / 2);

						$CaptionSettings["Color"] = $MinDisplayColor;
						$CaptionSettings["Align"] = $Align;
						$this->drawText($YPos + $XOffset, $XPos + $YOffset, $Label, $CaptionSettings);
					}
				}
			}
		}
	}
	
	/* Write labels */
	function writeLabel(array $SeriesName, array $Indexes, array $Format = [])
	{
		$OverrideTitle = isset($Format["OverrideTitle"]) ? $Format["OverrideTitle"] : NULL;
		$ForceLabels = isset($Format["ForceLabels"]) ? $Format["ForceLabels"] : [];
		$DrawPoint = isset($Format["DrawPoint"]) ? $Format["DrawPoint"] : LABEL_POINT_BOX;
		$DrawVerticalLine = isset($Format["DrawVerticalLine"]) ? $Format["DrawVerticalLine"] : FALSE;
		$OverrideColors = isset($Format["OverrideColors"]) ? $Format["OverrideColors"] : [];
		$VerticalLineColor = isset($Format["VerticalLineColor"]) ? $Format["VerticalLineColor"] : new pColor(0,0,0,40);
		$VerticalLineTicks = isset($Format["VerticalLineTicks"]) ? $Format["VerticalLineTicks"] : 2;
		$forStackedChart = isset($Format["forStackedChart"]) ? $Format["forStackedChart"] : FALSE;
		
		list($XMargin, $XDivs) = $this->myData->scaleGetXSettings();
		$Data = $this->myData->getData();
		
		if ($Data["Orientation"] == SCALE_POS_LEFTRIGHT) {
			if ($XDivs == 0) {
				$XStep = $this->GraphAreaXdiff / 4;
			} else {
				$XStep = ($this->GraphAreaXdiff - $XMargin * 2) / $XDivs;
			}
		} else {
			if ($XDivs == 0) {
				$XStep = $this->GraphAreaYdiff / 4;
			} else {
				$XStep = ($this->GraphAreaYdiff - $XMargin * 2) / $XDivs;
			}
		}

		foreach($Indexes as $Key => $Index) {
			$Series = [];
			$Index = intval($Index);
			$AbscissaDataSet = isset($Data["Series"][$Data["Abscissa"]]["Data"][$Index]);
			
			if ($Data["Orientation"] == SCALE_POS_LEFTRIGHT) {

				$X = $this->GraphAreaX1 + $XMargin + $Index * $XStep;
				if ($DrawVerticalLine) {
					$this->drawLine($X, $this->GraphAreaY1 + $Data["YMargin"], $X, $this->GraphAreaY2 - $Data["YMargin"], ["Color" => $VerticalLineColor,"Ticks" => $VerticalLineTicks]);
				}
				
				$MinY = $this->GraphAreaY2;
				
				foreach($SeriesName as $SerieName) {
					
					$SerieName = strval($SerieName);
					
					if (isset($Data["Series"][$SerieName]["Data"][$Index])) {
						$AxisID = $Data["Series"][$SerieName]["Axis"];
						
						if (isset($Data["Abscissa"]) && $AbscissaDataSet) {
							$XLabel = $this->scaleFormat($Data["Series"][$Data["Abscissa"]]["Data"][$Index], $Data["XAxisDisplay"], $Data["XAxisFormat"], $Data["XAxisUnit"]);
						} else {
							$XLabel = "";
						}

						if (!is_null($OverrideTitle)) {
							$Description = $OverrideTitle;
						} elseif (count($SeriesName) == 1) {
							$Description = $Data["Series"][$SerieName]["Description"] . " - " . $XLabel;
						} elseif (isset($Data["Abscissa"]) && $AbscissaDataSet) {
							$Description = $XLabel;
						}
						
						# Momchil: Was Extended Data
						if (!empty($OverrideColors)) {
							if (isset($OverrideColors[$Index])) {
								$SerieFormat = $OverrideColors[$Index];
							} else {
								$SerieFormat = new pColor();
							}
						} else {
							$SerieFormat = $Data["Series"][$SerieName]["Color"];
						}

						$SerieOffset = (count($SeriesName) == 1) ? $Data["Series"][$SerieName]["XOffset"] : 0;
						$Value = $Data["Series"][$SerieName]["Data"][$Index];
						($Value == VOID) AND $Value = "NaN";
						
						if (!empty($ForceLabels)) {
							$Caption = isset($ForceLabels[$Key]) ? $ForceLabels[$Key] : "Not set";
						} else {
							$Caption = $this->scaleFormat($Value, $Data["Axis"][$AxisID]["Display"], $Data["Axis"][$AxisID]["Format"], $Data["Axis"][$AxisID]["Unit"]);
						}

						if ($forStackedChart) {
							$LookFor = ($Value >= 0) ? "+" : "-";
							$Value = 0;
							foreach($Data["Series"] as $Name => $SerieLookup) {
								if ($SerieLookup["isDrawable"] && $Name != $Data["Abscissa"]) {
									if (isset($SerieLookup["Data"][$Index]) && $SerieLookup["Data"][$Index] != VOID) {
										if ($SerieLookup["Data"][$Index] >= 0 && $LookFor == "+") {
											$Value = $Value + $SerieLookup["Data"][$Index];
										}

										if ($SerieLookup["Data"][$Index] < 0 && $LookFor == "-") {
											$Value = $Value - $SerieLookup["Data"][$Index];
										}

										if ($Name == $SerieName) {
											break;
										}
									}
								}
							}
						}

						$X = floor($this->GraphAreaX1 + $XMargin + $Index * $XStep + $SerieOffset);
						$Y = floor($this->scaleComputeYSingle($Value, $AxisID));
						if ($Y < $MinY) {
							$MinY = $Y;
						}

						if ($DrawPoint == LABEL_POINT_CIRCLE) {
							$this->drawFilledCircle($X, $Y, 3, ["Color" => new pColor(255),"BorderColor" => new pColor(0)]);
						} elseif ($DrawPoint == LABEL_POINT_BOX) {
							$this->drawFilledRectangle($X - 2, $Y - 2, $X + 2, $Y + 2, ["Color" => new pColor(255),"BorderColor" => new pColor(0)]);
						}

						$Series[] = ["Format" => $SerieFormat,"Caption" => $Caption];
					}
				}

				$this->drawLabelBox($X, $MinY - 3, $Description, $Series, $Format);
				
			} else {

				$Y = $this->GraphAreaY1 + $XMargin + $Index * $XStep;
				if ($DrawVerticalLine) {
					$this->drawLine($this->GraphAreaX1 + $Data["YMargin"], $Y, $this->GraphAreaX2 - $Data["YMargin"], $Y, ["Color" => $VerticalLineColor,"Ticks" => $VerticalLineTicks]);
				}

				$MinX = $this->GraphAreaX2;
				foreach($SeriesName as $SerieName) {
					if (isset($Data["Series"][$SerieName]["Data"][$Index])) {
						$AxisID = $Data["Series"][$SerieName]["Axis"];

						if (isset($Data["Abscissa"]) && $AbscissaDataSet) {
							$XLabel = $this->scaleFormat($Data["Series"][$Data["Abscissa"]]["Data"][$Index], $Data["XAxisDisplay"], $Data["XAxisFormat"], $Data["XAxisUnit"]);
						} else {
							$XLabel = "";
						}

						if (!is_null($OverrideTitle)) {
							$Description = $OverrideTitle;
						} elseif (count($SeriesName) == 1) {
							if (isset($Data["Abscissa"]) && $AbscissaDataSet){
								$Description = $Data["Series"][$SerieName]["Description"] . " - " . $XLabel;
							}
						} elseif (isset($Data["Abscissa"]) && $AbscissaDataSet) {
							$Description = $XLabel;
						}

						# Momchil: Was Extended Data
						if (!empty($OverrideColors)) {
							if (isset($OverrideColors[$Index])) {
								$SerieFormat = $OverrideColors[$Index];
							} else {
								$SerieFormat = new pColor();
							}
						} else {
							$SerieFormat = $Data["Series"][$SerieName]["Color"];
						}
	
						$SerieOffset = (count($SeriesName) == 1) ? $Data["Series"][$SerieName]["XOffset"] : 0;
						$Value = $Data["Series"][$SerieName]["Data"][$Index];
						($Value == VOID) AND $Value = "NaN";
						
						if (!empty($ForceLabels)) { # Momchil: example.drawLabel.caption.php shows these correspond to Index and not Serie
							$Caption = isset($ForceLabels[$Key]) ? $ForceLabels[$Key] : "Not set";
						} else {
							$Caption = $this->scaleFormat($Value, $Data["Axis"][$AxisID]["Display"],  $Data["Axis"][$AxisID]["Format"], $Data["Axis"][$AxisID]["Unit"]);
						}

						if ($forStackedChart) {
							$LookFor = ($Value >= 0) ? "+" : "-";
							$Value = 0;
							foreach($Data["Series"] as $Name => $SerieLookup) {
								if ($SerieLookup["isDrawable"] && $Name != $Data["Abscissa"]) {
									if (isset($SerieLookup["Data"][$Index]) && $SerieLookup["Data"][$Index] != VOID) {
										if ($SerieLookup["Data"][$Index] >= 0 && $LookFor == "+") {
											$Value = $Value + $SerieLookup["Data"][$Index];
										}

										if ($SerieLookup["Data"][$Index] < 0 && $LookFor == "-") {
											$Value = $Value - $SerieLookup["Data"][$Index];
										}

										if ($Name == $SerieName) {
											break;
										}
									}
								}
							}
						}

						$X = floor($this->scaleComputeYSingle($Value, $AxisID));
						$Y = floor($this->GraphAreaY1 + $XMargin + $Index * $XStep + $SerieOffset);
						if ($X < $MinX) {
							$MinX = $X;
						}

						if ($DrawPoint == LABEL_POINT_CIRCLE) {
							$this->drawFilledCircle($X, $Y, 3, ["Color" => new pColor(255),"BorderColor" => new pColor(0)]);
						} elseif ($DrawPoint == LABEL_POINT_BOX) {
							$this->drawFilledRectangle($X - 2, $Y - 2, $X + 2, $Y + 2, ["Color" => new pColor(255),"BorderColor" => new pColor(0)]);
						}

						$Series[] = ["Format" => $SerieFormat,"Caption" => $Caption];
					}
				}

				$this->drawLabelBox($MinX, $Y - 3, $Description, $Series, $Format);
			}
		}
	}

	/* Draw a label box */
	function drawLabelBox($X, $Y, $Title, $Captions, array $Format = [])
	{
		$NoTitle = FALSE;
		$BoxWidth = 50;
		$DrawSerieColor = TRUE;
		$SerieBoxSize = 6;
		$SerieBoxSpacing = 4;
		$VerticalMargin = 10;
		$HorizontalMargin = 8;
		$Color = $this->FontColor;
		$FontName = $this->FontName;
		$FontSize = $this->FontSize;
		$TitleMode = LABEL_TITLE_NOBACKGROUND;
		$TitleColor = $Color;
		$TitleBackgroundColor = NULL;
		$GradientStartColor = NULL;
		$GradientEndColor = NULL;
		$BoxAlpha = 100;
		
		/* Override defaults */
		extract($Format);
		
		if(is_null($TitleBackgroundColor)){
			$TitleBackgroundColor = new pColor(0,0,0, $BoxAlpha);
		}
		
		if(is_null($GradientStartColor)){
			$GradientStartColor = new pColor(255,255,255, $BoxAlpha);
		}
		
		if(is_null($GradientEndColor)){
			$GradientEndColor = new pColor(220,220,220, $BoxAlpha);
		}
		
		if (!$DrawSerieColor) {
			$SerieBoxSize = 0;
			$SerieBoxSpacing = 0;
		}
		
		if ($NoTitle) {
			$TitleWidth = 0;
			$TitleHeight = 0;
		} else {
			$TxtPos = $this->getTextBox($X, $Y, $FontName, $FontSize, 0, $Title);
			$TitleWidth = ($TxtPos[1]["X"] - $TxtPos[0]["X"]) + $VerticalMargin * 2;
			$TitleHeight = ($TxtPos[0]["Y"] - $TxtPos[2]["Y"]);
		}
		
		$CaptionWidth = 0;
		$CaptionHeight = - $HorizontalMargin;
		if (isset($Captions["Caption"])){ 
			$Captions = [$Captions];
		}
		
		foreach($Captions as $Caption) {
			$TxtPos = $this->getTextBox($X, $Y, $FontName, $FontSize, 0, $Caption["Caption"]);
			$CaptionWidth = max($CaptionWidth, ($TxtPos[1]["X"] - $TxtPos[0]["X"]) + $VerticalMargin * 2);
			$CaptionHeight +=  max(($TxtPos[0]["Y"] - $TxtPos[2]["Y"]), ($SerieBoxSize + 2)) + $HorizontalMargin;
		}

		($CaptionHeight <= 5) AND $CaptionHeight += $HorizontalMargin / 2;
		($DrawSerieColor) AND $CaptionWidth += $SerieBoxSize + $SerieBoxSpacing;
		$BoxHeight = $TitleHeight + $CaptionHeight + $HorizontalMargin * (($NoTitle) ? 2 : 3);
		$BoxWidth = max($BoxWidth, $TitleWidth, $CaptionWidth);
		$XMin = $X - 5 - floor(($BoxWidth - 10) / 2);
		$XMax = $X + 5 + floor(($BoxWidth - 10) / 2);
		$RestoreShadow = $this->Shadow;
		$ShadowX = $this->ShadowX; 
		
		if ($this->Shadow) {
			$this->Shadow = FALSE;
			$Poly = [
				$X + $ShadowX,
				$Y + $ShadowX,
				$X + 5 + $ShadowX,
				$Y - 5 + $ShadowX,
				$XMax + $ShadowX,
				$Y - 5 + $ShadowX,
				$XMax + $ShadowX,
				$Y - 5 - $BoxHeight + $ShadowX,
				$XMin + $ShadowX,
				$Y - 5 - $BoxHeight + $ShadowX,
				$XMin +  $ShadowX,
				$Y - 5 + $ShadowX,
				$X - 5 + $ShadowX,
				$Y - 5 + $ShadowX
			];

			$this->drawPolygon($Poly, ["Color" => $this->ShadowColor]);
		}

		/* Draw the background */
		$this->drawGradientArea($XMin, $Y - 5 - $BoxHeight, $XMax, $Y - 6, DIRECTION_VERTICAL, ["StartColor"=>$GradientStartColor,"EndColor"=>$GradientEndColor]);

		$Poly = [$X, $Y, $X - 5, $Y - 5, $X + 5, $Y - 5];
		$this->drawPolygon($Poly, ["Color" => $GradientEndColor,"NoBorder" => TRUE]);
		/* Outer border */
		$OuterBorderColor = $this->allocateColor(new pColor(100, 100, 100, $BoxAlpha));
		imageline($this->Picture, $XMin, $Y - 5, $X - 5, $Y - 5, $OuterBorderColor);
		imageline($this->Picture, $X, $Y, $X - 5, $Y - 5, $OuterBorderColor);
		imageline($this->Picture, $X, $Y, $X + 5, $Y - 5, $OuterBorderColor);
		imageline($this->Picture, $X + 5, $Y - 5, $XMax, $Y - 5, $OuterBorderColor);
		imageline($this->Picture, $XMin, $Y - 5 - $BoxHeight, $XMin, $Y - 5, $OuterBorderColor);
		imageline($this->Picture, $XMax, $Y - 5 - $BoxHeight, $XMax, $Y - 5, $OuterBorderColor);
		imageline($this->Picture, $XMin, $Y - 5 - $BoxHeight, $XMax, $Y - 5 - $BoxHeight, $OuterBorderColor);
	
		/* Inner border */
		$InnerBorderColor = $this->allocateColor(new pColor(255, 255, 255, $BoxAlpha));
		imageline($this->Picture, $XMin + 1, $Y - 6, $X - 5, $Y - 6, $InnerBorderColor);
		imageline($this->Picture, $X, $Y - 1, $X - 5, $Y - 6, $InnerBorderColor);
		imageline($this->Picture, $X, $Y - 1, $X + 5, $Y - 6, $InnerBorderColor);
		imageline($this->Picture, $X + 5, $Y - 6, $XMax - 1, $Y - 6, $InnerBorderColor);
		imageline($this->Picture, $XMin + 1, $Y - 4 - $BoxHeight, $XMin + 1, $Y - 6, $InnerBorderColor);
		imageline($this->Picture, $XMax - 1, $Y - 4 - $BoxHeight, $XMax - 1, $Y - 6, $InnerBorderColor);
		imageline($this->Picture, $XMin + 1, $Y - 4 - $BoxHeight, $XMax - 1, $Y - 4 - $BoxHeight, $InnerBorderColor);

		/* Draw the separator line */
		if ($TitleMode == LABEL_TITLE_NOBACKGROUND && !$NoTitle) {
			$YPos = $Y - 7 - $CaptionHeight - $HorizontalMargin - $HorizontalMargin / 2;
			$XMargin = $VerticalMargin / 2;
			$this->drawLine($XMin + $XMargin, $YPos + 1, $XMax - $XMargin, $YPos + 1, ["Color" => $GradientEndColor->newOne()->AlphaSet($BoxAlpha)]);
			$this->drawLine($XMin + $XMargin, $YPos, $XMax - $XMargin, $YPos, ["Color" => $GradientStartColor->newOne()->AlphaSet($BoxAlpha)]);
		} elseif ($TitleMode == LABEL_TITLE_BACKGROUND) {
			$this->drawFilledRectangle($XMin, $Y - 5 - $TitleHeight - $CaptionHeight - $HorizontalMargin * 3, $XMax, $Y - 5 - $TitleHeight - $CaptionHeight - $HorizontalMargin / 2, ["Color" => $TitleBackgroundColor]);
			imageline($this->Picture, $XMin + 1, $Y - 5 - $TitleHeight - $CaptionHeight - $HorizontalMargin / 2 + 1, $XMax - 1, $Y - 5 - $TitleHeight - $CaptionHeight - $HorizontalMargin / 2 + 1, $InnerBorderColor);
		}

		/* Write the description */
		if (!$NoTitle) {
			$this->drawText($XMin + $VerticalMargin, $Y - 7 - $CaptionHeight - $HorizontalMargin * 2, $Title, ["Align" => TEXT_ALIGN_BOTTOMLEFT,"Color" => $TitleColor]);
		}

		/* Write the value */
		$YPos = $Y - 5 - $HorizontalMargin;
		$XPos = $XMin + $VerticalMargin + $SerieBoxSize + $SerieBoxSpacing;

		foreach($Captions as $Caption) {
			$TxtPos = $this->getTextBox($XPos, $YPos, $FontName, $FontSize, 0, $Caption["Caption"]);
			$CaptionHeight = ($TxtPos[0]["Y"] - $TxtPos[2]["Y"]);
			/* Write the serie color if needed */
			if ($DrawSerieColor) {
				$BoxSettings = ["Color" => $Caption["Format"],"BorderColor" => new pColor(0)];
				$this->drawFilledRectangle($XMin + $VerticalMargin, $YPos - $SerieBoxSize, $XMin + $VerticalMargin + $SerieBoxSize, $YPos, $BoxSettings);
			}

			$this->drawText($XPos, $YPos, $Caption["Caption"], ["Align" => TEXT_ALIGN_BOTTOMLEFT]);
			$YPos = $YPos - $CaptionHeight - $HorizontalMargin;
		}

		$this->Shadow = $RestoreShadow;
	}

	/* Draw a basic shape */
	function drawShape($X, $Y, $Shape, $PlotSize, $PlotBorder, $BorderSize, pColor $Color, pColor $BorderColor)
	{

		switch ($Shape){
			case SERIE_SHAPE_FILLEDCIRCLE:
				if ($PlotBorder) {
					$this->drawFilledCircle($X, $Y, $PlotSize + $BorderSize, ["Color" => $BorderColor]);
				}
				$this->drawFilledCircle($X, $Y, $PlotSize,["Color" => $Color]);
				break;
			case SERIE_SHAPE_FILLEDSQUARE:
				if ($PlotBorder) {
					$this->drawFilledRectangle($X - $PlotSize - $BorderSize, $Y - $PlotSize - $BorderSize, $X + $PlotSize + $BorderSize, $Y + $PlotSize + $BorderSize, ["Color" => $BorderColor]);
				}
				$this->drawFilledRectangle($X - $PlotSize, $Y - $PlotSize, $X + $PlotSize, $Y + $PlotSize, ["Color" => $Color]);
				break;
			case SERIE_SHAPE_FILLEDTRIANGLE:
				if ($PlotBorder) {
					$this->drawPolygon([$X, $Y - $PlotSize - $BorderSize, $X - $PlotSize - $BorderSize, $Y + $PlotSize + $BorderSize, $X + $PlotSize + $BorderSize, $Y + $PlotSize + $BorderSize], ["Color" => $BorderColor]);
				}
				$this->drawPolygon([$X, $Y - $PlotSize, $X - $PlotSize, $Y + $PlotSize, $X + $PlotSize, $Y + $PlotSize], ["Color" => $Color]);
				break;
			case SERIE_SHAPE_TRIANGLE:
				$this->drawLine($X, $Y - $PlotSize, $X - $PlotSize, $Y + $PlotSize, ["Color" => $Color]);
				$this->drawLine($X - $PlotSize, $Y + $PlotSize, $X + $PlotSize, $Y + $PlotSize, ["Color" => $Color]);
				$this->drawLine($X + $PlotSize, $Y + $PlotSize, $X, $Y - $PlotSize, ["Color" => $Color]);
				break;
			case SERIE_SHAPE_SQUARE:
				$this->drawRectangle($X - $PlotSize, $Y - $PlotSize, $X + $PlotSize, $Y + $PlotSize, ["Color" => $Color]);
				break;
			case SERIE_SHAPE_CIRCLE:
				$this->drawCircle($X, $Y, $PlotSize, $PlotSize, ["Color" => $Color]);
				break;
			case SERIE_SHAPE_DIAMOND:
				$this->drawPolygon([$X - $PlotSize, $Y, $X, $Y - $PlotSize, $X + $PlotSize, $Y, $X, $Y + $PlotSize], ["NoFill" => TRUE,"Color" => $BorderColor]);
				break;
			case SERIE_SHAPE_FILLEDDIAMOND:
				if ($PlotBorder) {
					$this->drawPolygon([$X - $PlotSize - $BorderSize, $Y, $X, $Y - $PlotSize - $BorderSize, $X + $PlotSize + $BorderSize, $Y, $X, $Y + $PlotSize + $BorderSize], ["Color" => $BorderColor]);
				}
				$this->drawPolygon([$X - $PlotSize, $Y, $X, $Y - $PlotSize, $X + $PlotSize, $Y, $X, $Y + $PlotSize], ["Color" => $Color]);
				break;
		}
	}

	/* Enable / Disable and set shadow properties */
	function setShadow(bool $Enabled = TRUE, array $Format = [])
	{

		$this->Shadow = $Enabled;
		
		/* Disable the shadow and exit */
		if (!$Enabled){
			return;
		}
		
		$X = isset($Format["X"]) ? $Format["X"] : 2;
		$Y = isset($Format["Y"]) ? $Format["Y"] : 2;
		$Color = isset($Format["Color"]) ? $Format["Color"] : new pColor(0,0,0,10);
		
		if ($X == 0 || $Y == 0){
			throw pException::InvalidInput("Invalid shadow specs");
		}

		$this->ShadowX = $X;
		$this->ShadowY = $Y;
		
		$this->ShadowColor = $Color;
		$this->ShadowAllocatedColor = $this->allocateColor($this->ShadowColor);

	}

	/* Set the graph area position */
	function setGraphArea($X1, $Y1, $X2, $Y2)
	{
		if ($X2 < $X1 || $X1 == $X2 || $Y2 < $Y1 || $Y1 == $Y2) {
			throw pException::InvalidInput("Invalid graph specs");
		}

		$this->GraphAreaX1 = $X1;
		$this->GraphAreaY1 = $Y1;
		$this->GraphAreaX2 = $X2;
		$this->GraphAreaY2 = $Y2;
		
		$this->GraphAreaXdiff = $X2 - $X1;
		$this->GraphAreaYdiff = $Y2 - $Y1;
	}

	/* Return the orientation of a line */
	function getAngle($X1, $Y1, $X2, $Y2)
	{
		#$Opposite = $Y2 - $Y1;
		#$Adjacent = $X2 - $X1;
		$Angle = rad2deg(atan2($Y2 - $Y1, $X2 - $X1));
		
		return (($Angle > 0) ? $Angle : (360 - abs($Angle)));
	}

	/* Return the surrounding box of text area */
	function getTextBox($X, $Y, $FontName, $FontSize, $Angle, $Text)
	{
		$coords = imagettfbbox($FontSize, 0, realpath($FontName), $Text);
		$a = deg2rad($Angle);
		$ca = cos($a);
		$sa = sin($a);
		$Pos = [];
		for ($i = 0; $i < 7; $i+= 2) {
			$Pos[$i / 2]["X"] = $X + round($coords[$i] * $ca + $coords[$i + 1] * $sa);
			$Pos[$i / 2]["Y"] = $Y + round($coords[$i + 1] * $ca - $coords[$i] * $sa);
		}

		$Pos[TEXT_ALIGN_BOTTOMLEFT] = $Pos[0];
		$Pos[TEXT_ALIGN_BOTTOMRIGHT] = $Pos[1];
		$Pos[TEXT_ALIGN_TOPLEFT] = $Pos[3];
		$Pos[TEXT_ALIGN_TOPRIGHT] = $Pos[2];
		$Pos[TEXT_ALIGN_BOTTOMMIDDLE]["X"] = ($Pos[1]["X"] - $Pos[0]["X"]) / 2 + $Pos[0]["X"];
		$Pos[TEXT_ALIGN_BOTTOMMIDDLE]["Y"] = ($Pos[0]["Y"] - $Pos[1]["Y"]) / 2 + $Pos[1]["Y"];
		$Pos[TEXT_ALIGN_TOPMIDDLE]["X"] = ($Pos[2]["X"] - $Pos[3]["X"]) / 2 + $Pos[3]["X"];
		$Pos[TEXT_ALIGN_TOPMIDDLE]["Y"] = ($Pos[3]["Y"] - $Pos[2]["Y"]) / 2 + $Pos[2]["Y"];
		$Pos[TEXT_ALIGN_MIDDLELEFT]["X"] = ($Pos[0]["X"] - $Pos[3]["X"]) / 2 + $Pos[3]["X"];
		$Pos[TEXT_ALIGN_MIDDLELEFT]["Y"] = ($Pos[0]["Y"] - $Pos[3]["Y"]) / 2 + $Pos[3]["Y"];
		$Pos[TEXT_ALIGN_MIDDLERIGHT]["X"] = ($Pos[1]["X"] - $Pos[2]["X"]) / 2 + $Pos[2]["X"];
		$Pos[TEXT_ALIGN_MIDDLERIGHT]["Y"] = ($Pos[1]["Y"] - $Pos[2]["Y"]) / 2 + $Pos[2]["Y"];
		$Pos[TEXT_ALIGN_MIDDLEMIDDLE]["X"] = ($Pos[1]["X"] - $Pos[3]["X"]) / 2 + $Pos[3]["X"];
		$Pos[TEXT_ALIGN_MIDDLEMIDDLE]["Y"] = ($Pos[0]["Y"] - $Pos[2]["Y"]) / 2 + $Pos[2]["Y"];
		
		return $Pos;
	}

	/* Set current font properties */
	function setFontProperties(array $Format = [])
	{
		$this->FontColor = (isset($Format['Color'])) ? $Format['Color'] : new pColor(0);
		
		(isset($Format['FontSize'])) AND $this->FontSize = $Format['FontSize'];

		if (isset($Format['FontName'])){
			$this->FontName = $Format['FontName'];
			if (!file_exists($this->FontName)){
				throw pException::InvalidResourcePath("Font path ".$this->FontName. " does not exist!");
			}
		}
	}

	/* Returns the 1st decimal values (used to correct AA bugs) */
	function getFirstDecimal($Value)
	{
		return floor(($Value - floor($Value))*10);
	}

	/* Reverse an array of points */
	function reversePlots(array $Plots)
	{
		$Result = [];
		for ($i = count($Plots) - 2; $i >= 0; $i = $i - 2) {
			$Result[] = $Plots[$i];
			$Result[] = $Plots[$i + 1];
		}

		return $Result;
	}
	
	/* Return the width of the picture */
	function getWidth()
	{
		return $this->XSize;
	}

	/* Return the height of the picture */
	function getHeight()
	{
		return $this->YSize;
	}
	
	/* http://php.net/manual/en/function.imagefilter.php */
	function setFilter(int $filtertype, int $arg1 = 0, int $arg2 = 0, int $arg3 = 0, int $arg4 = 0){
	
		$ret = imagefilter($this->Picture, $filtertype, $arg1, $arg2, $arg3, $arg4);
		
		if (!$ret){
			throw pException::InvalidImageFilter("Could not apply image filter!");
		}
	}

	/* Render the picture to a file */
	function render(string $FileName, int $Compression = 6, $Filters = PNG_NO_FILTER)
	{
		if ($this->TransparentBackground) {
			imagealphablending($this->Picture, FALSE);
		}

		imagepng($this->Picture, $FileName, $Compression, $Filters);
	}

	/* Render the picture to a web browser stream */
	function stroke(bool $BrowserExpire = FALSE, int $Compression = 6, $Filters = PNG_NO_FILTER)
	{
		if ($this->TransparentBackground) {
			imagealphablending($this->Picture, FALSE);
		}

		if ($BrowserExpire) {
			header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
			header("Cache-Control: no-cache, must-revalidate"); # HTTP/1.1
			header("Pragma: no-cache");
		}

		header('Content-type: image/png');
		imagepng($this->Picture, NULL, $Compression, $Filters);
	}

	/*	Automatic output method based on the calling interface
		Momchil: Added support for Compression & Filters
		Compression must be between 0 and 9 -> http://php.net/manual/en/function.imagepng.php 
		http://php.net/manual/en/image.constants.php
		https://www.w3.org/TR/PNG-Filters.html
	*/
	function autoOutput(string $FileName = "output.png", int $Compression = 6, $Filters = PNG_NO_FILTER)
	{
		if (php_sapi_name() == "cli") {
			$this->Render($FileName, $Compression, $Filters);
		} else {
			$this->Stroke(TRUE, $Compression, $Filters);
		}
	}

}