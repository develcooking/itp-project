DROP TABLE IF EXISTS `Users`;

CREATE TABLE `Users`
(
    `userId`         int(11) NOT NULL AUTO_INCREMENT,
    `userName`       varchar(255) NOT NULL UNIQUE,
    `firstName`      varchar(100)          DEFAULT NULL,
    `lastName`       varchar(100)          DEFAULT NULL,
    `email`          varchar(255) NOT NULL UNIQUE,
    `password`       char(60)     NOT NULL,
    `role`           enum('Ausbilder','Lehrer','Admin') NOT NULL,
    `securityAnswer` char(60)     NOT NULL,
    `activated`      tinyint(1) NOT NULL DEFAULT 0,
    `createdAt`      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `modifiedAt`     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `createdBy`      INT                   DEFAULT NULL,
    `modifiedBy`     INT                   DEFAULT NULL,
    PRIMARY KEY (`userId`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;