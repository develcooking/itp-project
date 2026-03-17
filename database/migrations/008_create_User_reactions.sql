CREATE TABLE user_reactions (
    userId INT NOT NULL,
    postId INT NOT NULL,
    hasReacted BOOLEAN NOT NULL DEFAULT FALSE,

    PRIMARY KEY (userId, postId),

    CONSTRAINT fk_user_reactions_user
        FOREIGN KEY (userId)
        REFERENCES Users(userId)
        ON DELETE CASCADE,

    CONSTRAINT fk_user_reactions_post
        FOREIGN KEY (postId)
        REFERENCES Posts(postId)
        ON DELETE CASCADE
);