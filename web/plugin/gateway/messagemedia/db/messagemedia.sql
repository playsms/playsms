-- MySQL dump 10.13  Distrib 5.1.48, for redhat-linux-gnu (x86_64)
--
-- Host: localhost    Database: playsmsdev
-- ------------------------------------------------------
-- Server version	5.1.48

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `playsms_gatewayMessagemedia_config`
--

DROP TABLE IF EXISTS `playsms_gatewayMessagemedia_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_gatewayMessagemedia_config` (
  `c_timestamp` int(11) NOT NULL DEFAULT '0',
  `cfg_name` varchar(20) DEFAULT 'clickatell',
  `cfg_api_id` varchar(20) DEFAULT NULL,
  `cfg_username` varchar(100) DEFAULT NULL,
  `cfg_password` varchar(100) DEFAULT NULL,
  `cfg_delay` int(20) DEFAULT NULL,
  `cfg_send_url` varchar(250) DEFAULT NULL,
  `cfg_incoming_path` varchar(250) DEFAULT NULL,
  `cfg_credit` int(11) NOT NULL DEFAULT '0',
  `cfg_additional_param` varchar(250) DEFAULT NULL,
  `cfg_datetime_timezone` varchar(30) NOT NULL DEFAULT '+0700'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `playsms_gatewayMessagemedia_apidata`
--

DROP TABLE IF EXISTS `playsms_gatewayMessagemedia_apidata`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_gatewayMessagemedia_apidata` (
  `c_timestamp` int(11) NOT NULL DEFAULT '0',
  `apidata_id` int(11) NOT NULL AUTO_INCREMENT,
  `smslog_id` int(11) NOT NULL DEFAULT '0',
  `apimsgid` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`apidata_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

-- MySQL dump 10.13  Distrib 5.1.48, for redhat-linux-gnu (x86_64)
--
-- Host: localhost    Database: playsmsdev
-- ------------------------------------------------------
-- Server version	5.1.48-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `playsms_gatewayMessagemedia_config`
--

DROP TABLE IF EXISTS `playsms_gatewayMessagemedia_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `playsms_gatewayMessagemedia_config` (
  `c_timestamp` int(11) NOT NULL DEFAULT '0',
  `cfg_name` varchar(20) DEFAULT 'clickatell',
  `cfg_api_id` varchar(20) DEFAULT NULL,
  `cfg_username` varchar(100) DEFAULT NULL,
  `cfg_password` varchar(100) DEFAULT NULL,
  `cfg_delay` int(20) DEFAULT NULL,
  `cfg_send_url` varchar(250) DEFAULT NULL,
  `cfg_incoming_path` varchar(250) DEFAULT NULL,
  `cfg_credit` int(11) NOT NULL DEFAULT '0',
  `cfg_additional_param` varchar(250) DEFAULT NULL,
  `cfg_datetime_timezone` varchar(30) NOT NULL DEFAULT '+0700'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playsms_gatewayMessagemedia_config`
--

LOCK TABLES `playsms_gatewayMessagemedia_config` WRITE;
/*!40000 ALTER TABLE `playsms_gatewayMessagemedia_config` DISABLE KEYS */;
INSERT INTO `playsms_gatewayMessagemedia_config` VALUES (1298762044,'messagemedia','123456','CorporateExec003','0319185',0,'http://smsmaster.m4u.com.au','',10,'not used','+0000');
/*!40000 ALTER TABLE `playsms_gatewayMessagemedia_config` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2011-10-26 17:55:01
