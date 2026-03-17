DROP TABLE IF EXISTS `Appointments`;

CREATE TABLE `Appointments` (
                                `appointmentId` int(11) NOT NULL AUTO_INCREMENT,
                                `jobId` int(11) NOT NULL,
                                `title` varchar(255) NOT NULL,
                                `start` datetime NOT NULL,
                                `end` datetime NOT NULL,
                                `description` text DEFAULT NULL,

                                `recurrence_type` ENUM('none','weekly','monthly') DEFAULT 'none',
                                `recurrence_interval` INT DEFAULT 1, -- jeden x woche/month
                                `recurrence_until` DATE NULL, -- bis wann die Serie geht

                                `createdAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                `modifiedAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                                `createdBy` INT NOT NULL,
                                `modifiedBy` INT NOT NULL,

                                FOREIGN KEY (`createdBy`) REFERENCES `Users` (`userId`),
                                FOREIGN KEY (`modifiedBy`) REFERENCES `Users` (`userId`),
                                FOREIGN KEY (`jobId`) REFERENCES `Jobs` (`jobId`),

                                PRIMARY KEY (`appointmentId`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
