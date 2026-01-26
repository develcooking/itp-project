DROP TABLE IF EXISTS `benutzer_berufsbereich`;
DROP TABLE IF EXISTS `user_job`;

CREATE TABLE `users_jobs` (
  `userId` int(11) NOT NULL,
  `jobId` int(11) NOT NULL,
  `createdAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modifiedAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`userId`,`jobId`),
  KEY `jobId` (`jobId`),
`createdBy` INT NOT NULL,
  `modifiedBy` INT NOT NULL,
  FOREIGN KEY (`createdBy`) REFERENCES Users(`userId`),
  FOREIGN KEY (`modifiedBy`) REFERENCES Users(`userId`),
  CONSTRAINT `user_job_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `Users` (`userId`) ON DELETE CASCADE,
  CONSTRAINT `user_job_ibfk_2` FOREIGN KEY (`jobId`) REFERENCES `Jobs` (`jobId`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
