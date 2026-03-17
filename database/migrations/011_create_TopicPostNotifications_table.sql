DROP TABLE IF EXISTS `TopicPostNotifications`;

CREATE TABLE `TopicPostNotifications` (
  `notificationId` int(11) NOT NULL AUTO_INCREMENT,
  `topicId` int(11) NOT NULL,
  `postId` int(11) NOT NULL,
  `recipientUserId` int(11) NOT NULL,
  `recipientEmail` varchar(255) NOT NULL,
  `status` enum('sent', 'failed') NOT NULL DEFAULT 'failed',
  `errorMessage` varchar(1000) DEFAULT NULL,
  `createdAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modifiedAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `createdBy` int(11) NOT NULL,
  `modifiedBy` int(11) NOT NULL,
  PRIMARY KEY (`notificationId`),
  UNIQUE KEY `uq_topic_post_recipient` (`topicId`, `postId`, `recipientUserId`),
  KEY `fk_tpn_topic` (`topicId`),
  KEY `fk_tpn_post` (`postId`),
  KEY `fk_tpn_recipient` (`recipientUserId`),
  KEY `fk_tpn_createdBy` (`createdBy`),
  KEY `fk_tpn_modifiedBy` (`modifiedBy`),
  CONSTRAINT `fk_tpn_topic` FOREIGN KEY (`topicId`) REFERENCES `Topics` (`topicId`) ON DELETE CASCADE,
  CONSTRAINT `fk_tpn_post` FOREIGN KEY (`postId`) REFERENCES `Posts` (`postId`) ON DELETE CASCADE,
  CONSTRAINT `fk_tpn_recipient` FOREIGN KEY (`recipientUserId`) REFERENCES `Users` (`userId`) ON DELETE CASCADE,
  CONSTRAINT `fk_tpn_createdBy` FOREIGN KEY (`createdBy`) REFERENCES `Users` (`userId`),
  CONSTRAINT `fk_tpn_modifiedBy` FOREIGN KEY (`modifiedBy`) REFERENCES `Users` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
