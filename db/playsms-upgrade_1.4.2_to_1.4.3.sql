-- 1.4.3


-- version
UPDATE `playsms_tblRegistry` SET `registry_value` = '1.4.3' WHERE `registry_group` = 'core' AND `registry_family` = 'config' AND `registry_key` = 'playsms_version' ;

-- twilio
ALTER TABLE `playsms_gatewayTwilio` MODIFY `status` VARCHAR(20) NOT NULL DEFAULT '';
