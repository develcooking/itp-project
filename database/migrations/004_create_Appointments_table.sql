DROP TABLE IF EXISTS `Termine`;
DROP TABLE IF EXISTS `Appointment`;

CREATE TABLE `Appointments` (
  `appointmentId` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `start` datetime NOT NULL,
  `end` datetime NOT NULL,
  `description` text DEFAULT NULL,
  `userId` int(11) NOT NULL,
  `createdAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modifiedAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `createdBy` INT,
  `modifiedBy` INT,
  FOREIGN KEY (`createdBy`) REFERENCES Users(`userId`),
  FOREIGN KEY (`modifiedBy`) REFERENCES Users(`userId`),
  PRIMARY KEY (`appointmentId`),
  KEY `userId` (`userId`),
  CONSTRAINT `Appointment_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `Users` (`userId`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
