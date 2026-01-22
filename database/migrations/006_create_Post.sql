
DROP TABLE IF EXISTS `Beitraege`;
DROP TABLE IF EXISTS `Post`;

CREATE TABLE `Posts` (
  `postId` int(11) NOT NULL AUTO_INCREMENT,
  `topicId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `content` TEXT NOT NULL,
  `description` text DEFAULT NULL,
  `reaction_negative` int(11) DEFAULT 0,
  `reaction_positive` int(11) DEFAULT 0,
  `createdAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modifiedAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `createdBy` INT,
  `modifiedBy` INT,
  FOREIGN KEY (`createdBy`) REFERENCES Users(`userId`),
  FOREIGN KEY (`modifiedBy`) REFERENCES Users(`userId`),
  PRIMARY KEY (`postId`),
  KEY `topicID` (`topicId`),
  KEY `userId` (`userId`),
  CONSTRAINT `Post_ibfk_1` FOREIGN KEY (`topicId`) REFERENCES `Topics` (`topicId`) ON DELETE CASCADE,
  CONSTRAINT `Post_ibfk_2` FOREIGN KEY (`userId`) REFERENCES `Users` (`userId`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
