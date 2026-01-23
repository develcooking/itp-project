
DROP TABLE IF EXISTS `Thema`;
DROP TABLE IF EXISTS `Topic`;

CREATE TABLE `Topics` (
  `topicId` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `jobId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `createdAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modifiedAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
`createdBy` INT NOT NULL,
  `modifiedBy` INT NOT NULL,
  FOREIGN KEY (`createdBy`) REFERENCES Users(`userId`),
  FOREIGN KEY (`modifiedBy`) REFERENCES Users(`userId`),
  PRIMARY KEY (`topicId`),
  KEY `fk_job` (`jobId`),
  KEY `fk_user` (`userId`),
  CONSTRAINT `fk_user` FOREIGN KEY (`userId`) REFERENCES `Users` (`userId`),
  CONSTRAINT `fk_job` FOREIGN KEY (`jobId`) REFERENCES `Jobs` (`jobId`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
