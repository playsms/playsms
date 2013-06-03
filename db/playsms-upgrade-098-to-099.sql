ALTER TABLE `playsms_tblUser` ADD `send_as_unicode` TINYINT(4) NOT NULL DEFAULT '0' AFTER `plus_sign_add`;

ALTER TABLE `playsms_tblSMSOutgoing_queue` ADD `sms_count` INT(11) NOT NULL DEFAULT '0' AFTER `flag`;
