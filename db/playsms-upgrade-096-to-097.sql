ALTER TABLE `playsms_featureCommand` ADD `command_return_as_reply` tinyint(4) NOT NULL DEFAULT 0 AFTER `command_exec` ;

ALTER TABLE `playsms_featureCustom` ADD `custom_return_as_reply` tinyint(4) NOT NULL DEFAULT 0 AFTER `custom_url` ;


