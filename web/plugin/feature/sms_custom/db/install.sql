--
-- Table structure for table `playsms_featureCustom`
--

DROP TABLE IF EXISTS `playsms_featureCustom`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_featureCustom` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT '0',
  `custom_id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0',
  `service_name` varchar(255) NOT NULL DEFAULT '',
  `custom_keyword` varchar(255) NOT NULL DEFAULT '',
  `sms_receiver` varchar(20) NOT NULL DEFAULT '',
  `custom_url` text NOT NULL,
  `custom_return_as_reply` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`custom_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playsms_featureCustom`
--

LOCK TABLES `playsms_featureCustom` WRITE;
/*!40000 ALTER TABLE `playsms_featureCustom` DISABLE KEYS */;
/*!40000 ALTER TABLE `playsms_featureCustom` ENABLE KEYS */;
UNLOCK TABLES;
