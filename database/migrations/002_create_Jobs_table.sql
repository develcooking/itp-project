/* weg */
DROP TABLE IF EXISTS `Berufsbereiche`;
DROP TABLE IF EXISTS `Job`;

CREATE TABLE `Jobs`
(
    `jobId`      int(11) NOT NULL AUTO_INCREMENT,
    `name`       varchar(100) NOT NULL,
    `createdAt`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `modifiedAt` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `createdBy`  INT,
    `modifiedBy` INT,
    FOREIGN KEY (`createdBy`) REFERENCES Users (`userId`),
    FOREIGN KEY (`modifiedBy`) REFERENCES Users (`userId`),
    PRIMARY KEY (`jobId`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
