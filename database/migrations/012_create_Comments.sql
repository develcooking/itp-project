DROP TABLE IF EXISTS `Comments`;

CREATE TABLE Comments (
    commentId INT(11) NOT NULL AUTO_INCREMENT,
    postId INT(11) NOT NULL,
    userId INT(11) NOT NULL,
    content TEXT NOT NULL,
    createdAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
    modifiedAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP(),
    createdBy INT(11) DEFAULT NULL,
    modifiedBy INT(11) DEFAULT NULL,
    PRIMARY KEY (commentId),
    KEY idx_postId (postId),
    KEY idx_userId (userId),
);