ALTER TABLE `playsms_tblUserInbox` ADD `in_receiver` VARCHAR( 20 ) NOT NULL AFTER `in_sender` ;
ALTER TABLE `playsms_tblSMSIncoming` ADD `in_receiver` VARCHAR( 20 ) NOT NULL AFTER `in_sender` ;
