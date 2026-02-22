-- Add password reset columns to users table
-- For forgot password functionality

ALTER TABLE users ADD COLUMN reset_token VARCHAR(255) NULL DEFAULT NULL;
ALTER TABLE users ADD COLUMN reset_token_expires DATETIME NULL DEFAULT NULL;
ALTER TABLE users ADD COLUMN login_method ENUM('manual', 'google') DEFAULT 'manual' AFTER password;

-- Add indexes for better query performance
CREATE INDEX idx_reset_token ON users(reset_token);
CREATE INDEX idx_reset_token_expires ON users(reset_token_expires);
