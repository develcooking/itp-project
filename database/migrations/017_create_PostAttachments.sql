DROP TABLE IF EXISTS `postAttachments`;

CREATE TABLE postAttachments (
    attachmentId INT AUTO_INCREMENT PRIMARY KEY,
    postId INT NOT NULL,
    fileName VARCHAR(255) NOT NULL,
    fileType VARCHAR(100),
    fileSize INT,
    fileData LONGBLOB,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX (postId),
    CONSTRAINT fk_postAttachments_post
        FOREIGN KEY (postId) REFERENCES Posts(postId)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;