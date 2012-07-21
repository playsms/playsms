ALTER TABLE `playsms_featureCommand` ADD `command_return_as_reply` tinyint(4) NOT NULL DEFAULT 0 AFTER `command_exec` ;

ALTER TABLE `playsms_featureCustom` ADD `custom_return_as_reply` tinyint(4) NOT NULL DEFAULT 0 AFTER `custom_url` ;

ALTER TABLE `playsms_tblUser` ADD `replace_zero` varchar(5) NOT NULL DEFAULT '' AFTER `fwd_to_inbox` ;
ALTER TABLE `playsms_tblUser` ADD `plus_sign_remove` tinyint(4) NOT NULL DEFAULT '1' AFTER `replace_zero` ;
ALTER TABLE `playsms_tblUser` ADD `plus_sign_add` tinyint(4) NOT NULL DEFAULT '0' AFTER `plus_sign_remove` ;
