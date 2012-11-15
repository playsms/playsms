DROP TABLE `playsms_tblErrorString` ;

ALTER TABLE `playsms_tblSMSTemplate` RENAME TO `playsms_toolsMsgtemplate` ;

ALTER TABLE `playsms_tblUser` ADD `register_datetime` varchar(20) NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `plus_sign_add` ;
ALTER TABLE `playsms_tblUser` ADD `lastupdate_datetime` varchar(20) NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `register_datetime` ;

ALTER TABLE `playsms_tblUser` MODIFY `password` varchar(32) NOT NULL ;

ALTER TABLE `playsms_featurePoll` ADD `poll_message_valid` varchar(100) NOT NULL ;
ALTER TABLE `playsms_featurePoll` ADD `poll_message_invalid` varchar(100) NOT NULL ;

ALTER TABLE `playsms_featureSubscribe` ADD `subscribe_param` varchar(20) NOT NULL ;
ALTER TABLE `playsms_featureSubscribe` ADD `unsubscribe_param` varchar(20) NOT NULL ;
ALTER TABLE `playsms_featureSubscribe` ADD `forward_param` varchar(20) NOT NULL ;
