--
-- Table structure for table `playsms_tblAuth_acl`
--

DROP TABLE IF EXISTS `playsms_tblAuth_acl`;
CREATE TABLE `playsms_tblAuth_acl` (
  `c_timestamp` bigint(20) NOT NULL DEFAULT '0',
  `id` int(11) NOT NULL,
  `name` varchar(100) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `plugin` text CHARACTER SET utf8 NOT NULL,
  `url` text CHARACTER SET utf8 NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `playsms_tblAuth_acl`
--

INSERT INTO `playsms_tblAuth_acl` (`c_timestamp`, `id`, `name`, `plugin`, `url`) VALUES
(0, 1, 'Broadcast', '', 'inc=core_sendsms,\r\ninc=core_user,\r\ninc=feature_report,\r\ninc=feature_msgtemplate,\r\ninc=feature_queuelog,\r\ninc=feature_credit,\r\ninc=feature_report&route=user\r\n');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `playsms_tblAuth_acl`
--
ALTER TABLE `playsms_tblAuth_acl`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `playsms_tblAuth_acl`
--
ALTER TABLE `playsms_tblAuth_acl`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
  