ALTER TABLE user_reactions
ADD COLUMN voteType ENUM('up','down','noreaction') NOT NULL DEFAULT 'noreaction',
DROP COLUMN hasReacted;