ALTER TABLE `playsms_tblUser` ADD `send_as_unicode` TINYINT(4) NOT NULL DEFAULT '0' AFTER `plus_sign_add` ;

ALTER TABLE `playsms_tblSMSOutgoing_queue` ADD `sms_count` INT(11) NOT NULL DEFAULT '0' AFTER `flag` ;

ALTER TABLE `playsms_featureSubscribe` ADD `unknown_format_msg` VARCHAR(140) NOT NULL DEFAULT '' AFTER `forward_param` ;

ALTER TABLE `playsms_featureSubscribe` ADD `already_member_msg` VARCHAR(140) NOT NULL DEFAULT '' AFTER `unknown_format_msg` ;

ALTER TABLE `playsms_featureSubscribe` CHANGE `subscribe_msg` `subscribe_msg` VARCHAR(140) NOT NULL DEFAULT '' ;

ALTER TABLE `playsms_featureSubscribe` CHANGE `unsubscribe_msg` `unsubscribe_msg` VARCHAR(140) NOT NULL DEFAULT '' ;

