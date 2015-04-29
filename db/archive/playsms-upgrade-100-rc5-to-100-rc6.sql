-- 1.0-rc6

-- version
UPDATE `playsms_tblRegistry` SET `registry_value` = '1.0-rc6' WHERE `registry_group` = 'core' AND `registry_family` = 'config' AND `registry_key` = 'playsms_version' ;


--
-- Table structure for table `playsms_gatewayTelerivet`
--

DROP TABLE IF EXISTS `playsms_gatewayTelerivet`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_gatewayTelerivet` (
  `c_timestamp` int(11) NOT NULL DEFAULT '0',
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `local_slid` int(11) NOT NULL DEFAULT '0',
  `remote_slid` varchar(40) NOT NULL DEFAULT '',
  `status` varchar(20) NOT NULL DEFAULT '',
  `phone_id` varchar(40) NOT NULL DEFAULT '',
  `message_type` varchar(20) NOT NULL DEFAULT '',
  `source` varchar(20) NOT NULL DEFAULT '',
  `error_text` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playsms_gatewayTelerivet`
--

LOCK TABLES `playsms_gatewayTelerivet` WRITE;
/*!40000 ALTER TABLE `playsms_gatewayTelerivet` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_gatewayTelerivet` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playsms_gatewayTelerivet_config`
--

DROP TABLE IF EXISTS `playsms_gatewayTelerivet_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_gatewayTelerivet_config` (
  `c_timestamp` int(11) NOT NULL DEFAULT '0',
  `cfg_name` varchar(20) NOT NULL DEFAULT 'telerivet',
  `cfg_url` varchar(250) DEFAULT NULL,
  `cfg_api_key` varchar(250) DEFAULT NULL,
  `cfg_project_id` varchar(250) DEFAULT NULL,
  `cfg_status_url` varchar(250) DEFAULT NULL,
  `cfg_status_secret` varchar(250) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playsms_gatewayTelerivet_config`
--

LOCK TABLES `playsms_gatewayTelerivet_config` WRITE;
/*!40000 ALTER TABLE `playsms_gatewayTelerivet_config` DISABLE KEYS */;
INSERT INTO `playsms_gatewayTelerivet_config` VALUES (0,'telerivet','https://api.telerivet.com/','12345678','abc123cde456','https://localhost/plugin/gateway/telerivet/callback.php','myS3cr3t');
/*!40000 ALTER TABLE `playsms_gatewayTelerivet_config` ENABLE KEYS */;
UNLOCK TABLES;
