ALTER TABLE `playsms_gatewayKannel_config` ADD `cfg_sendsms_host` varchar(250) DEFAULT NULL AFTER `cfg_bearerbox_host`;

ALTER TABLE `playsms_featureBoard` ADD `board_css` varchar(250) NOT NULL DEFAULT '' AFTER `board_forward_email`;
