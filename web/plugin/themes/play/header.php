<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title><?php echo $web_title; ?></title>
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Le HTML5 shim, for IE6-8 support of HTML elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <script type="text/javascript"
	    src="<?php echo $http_path['themes']; ?>/<?php echo $themes_module; ?>/jscss/selectbox.js"></script>
    <script type="text/javascript"
	    src="<?php echo $http_path['themes']; ?>/default/jscss/common.js"></script>
    <script type="text/javascript"
	    src="<?php echo $http_path['themes']; ?>/<?php echo $themes_module; ?>/jscss/dtree.js"></script>
    <script type="text/javascript"
	    src="<?php echo $http_path['themes']; ?>/<?php echo $themes_module; ?>/jscss/sorttable.js"></script>
    
    
    <script type="text/javascript"
	    src="<?php echo $http_path['themes']; ?>/<?php echo $themes_module; ?>/jscss/jquery-1.5.2.min.js"></script>
    <script type="text/javascript"
	    src="<?php echo $http_path['themes']; ?>/<?php echo $themes_module; ?>/jscss/bootstrap-dropdown.js"></script>
    <script type="text/javascript"
	    src="<?php echo $http_path['themes']; ?>/<?php echo $themes_module; ?>/jscss/bootstrap-twipsy.js"></script>
    <script type="text/javascript"
	    src="<?php echo $http_path['themes']; ?>/<?php echo $themes_module; ?>/jscss/bootstrap-scrollspy.js"></script>
    <!-- Le styles -->
    <link href="<?php echo $http_path['themes'].'/'.$themes_module; ?>/jscss/bootstrap.css" rel="stylesheet">
    <!-- play styles -->
    <link href="<?php echo $http_path['themes'].'/'.$themes_module; ?>/jscss/play.css" rel="stylesheet">

    <style type="text/css">
      /* <![CDATA[ */
      /* Override some defaults */
      html, body {
        background-color: #eee;
      }
      body {
        padding-top: 40px; /* 40px to make the container go all the way to the 
                              bottom of the topbar */
      }
      .container > footer p {
        text-align: center; /* center align it with the container */
      }
      .container {
        width: 820px; /* downsize our container to make the content feel a bit 
                         tighter and more cohesive. 
                         NOTE: this removes two full columns from the grid, 
                         meaning you only go to 14 columns and not 16. */
      }

      /* The white background content wrapper */
      .container > .content {
        background-color: #fff;
        padding: 20px;
        margin: 0 -20px; /* negative indent the amount of the padding to maintain 
                            the grid system */
        -webkit-border-radius: 0 0 6px 6px;
           -moz-border-radius: 0 0 6px 6px;
                border-radius: 0 0 6px 6px;
        -webkit-box-shadow: 0 1px 2px rgba(0,0,0,.15);
           -moz-box-shadow: 0 1px 2px rgba(0,0,0,.15);
                box-shadow: 0 1px 2px rgba(0,0,0,.15);
      }

      /* Page header tweaks */
      .page-header {
        background-color: #f5f5f5;
        padding: 20px 20px 10px;
        margin: -20px -20px 20px;
      }

      /* Styles you shouldn't keep as they are for displaying this base example only */
      .content .span10,
      .content .span4 {
        min-height: 500px;
      }
      /* Give a quick and non-cross-browser friendly divider */
      .content .span4 {
        margin-left: 0;
        padding-left: 19px;
        border-left: 1px solid #eee;
      }

      .topbar .btn {
        border: 0;
      }
    /* ]]> */
    </style>

    <!-- Le fav and touch icons -->
    <!--
    <link rel="shortcut icon" href="images/favicon.ico">
    <link rel="apple-touch-icon" href="images/apple-touch-icon.png">
    <link rel="apple-touch-icon" sizes="72x72" href="images/apple-touch-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="114x114" href="images/apple-touch-icon-114x114.png">
    -->
  </head>

  <body>

    <?php if (valid()) { ?>
    <div class="topbar" data-dropdown="dropdown" >
      <div class="fill">
        <div class="container">
          <a class="brand" href="<?php echo $http_path['base'];?>"><?php echo _('Home'); ?></a>
          <!-- <ul class="nav"> -->
            <?php echo theme_play_build_menu(); ?>
          <!-- </ul> -->
          <!--
          <span class="pull-right">
            <p>
                  <?php echo "<a>"._('Logged in') .': '. $username."</a>"; ?>
            </p>
          </span>
          -->
        </div>
      </div>
    </div>
    <?php } ?>

    <div class="container">
    
      <div class="content">
        <div class="page-header">
          <div style="float:left;">
	<?php if (isset($theme_image)  &&  !empty($theme_image)) { ?>
          <img style="vertical-align: middle;" src="plugin/themes/play/images/default_logo.png" alt="<?php echo $theme_play_head1; ?>" >
          </div>
          <div style="float:left; height: 85px; padding-left: 20px;">
	<?php } ?>
          <h1 style="line-height: 85px;"><?php echo $theme_play_head1; ?></h1>
          <h1><small><?php echo $theme_play_head2; ?></small></h1>
          </div>
          <div style="clear:both;"></div> 
        </div>
        <div class="row">
          <div class="span14">
            <!-- playSMS content -->
            <div class="main">
