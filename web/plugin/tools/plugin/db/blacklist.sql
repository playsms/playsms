--
-- Table structure for table `playsms_toolsBlacklist_cfg`
--

DROP TABLE IF EXISTS `playsms_toolsBlacklist_cfg`;
CREATE TABLE `playsms_toolsBlacklist_cfg` (
  `c_timestamp` int(11) NOT NULL DEFAULT '0',
  `blacklist_name` varchar(20) NOT NULL DEFAULT 'kannel',
  `blacklist_rule` varchar(250) DEFAULT NULL,
  `blacklist_enable` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `playsms_toolsBlacklist_cfg`
--
INSERT INTO `playsms_toolsBlacklist_cfg` VALUES (1308205479,'blacklist','kljdjdk',1);

--
-- Table structure for table `playsms_toolsBlacklist`
--

DROP TABLE IF EXISTS `playsms_toolsBlacklist`;
CREATE TABLE `playsms_toolsBlacklist` (
  `c_timestamp` int(11) NOT NULL DEFAULT '0',
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `blacklist_name` varchar(20) NOT NULL,
  `blacklist_type` varchar(20) NOT NULL,
  `blacklist_match` varchar(250) DEFAULT NULL,
  `blacklist_rule` varchar(20) DEFAULT NULL,
  `blacklist_replace` varchar(250) DEFAULT NULL,
  `blacklist_enable` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `playsms_toolsBlacklist`
--

INSERT INTO `playsms_toolsBlacklist` VALUES (1308576340,1,'block','recipient','^\\+687773502','block','',1),(1308576435,8,'replace','word','manu','replace','tintin',1),(1308576436,12,'replace','recipient','^\\+687773502','replace','+687444444',1),(1308576434,13,'block','word','puce','block','',1);
