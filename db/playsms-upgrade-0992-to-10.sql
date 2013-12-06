-- beta 1

-- remove default timezone
ALTER TABLE  `playsms_tblUser` CHANGE  `datetime_timezone`  `datetime_timezone` VARCHAR( 30 ) NOT NULL DEFAULT  '';

-- remove default language
ALTER TABLE  `playsms_tblUser` CHANGE  `language_module`  `language_module` VARCHAR( 10 ) NOT NULL DEFAULT  '';
