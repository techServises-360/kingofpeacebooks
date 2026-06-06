-- Add author-request fields to users table (for older deployments)

ALTER TABLE users
  ADD COLUMN IF NOT EXISTS requested_author BOOLEAN DEFAULT FALSE;

ALTER TABLE users
  ADD COLUMN IF NOT EXISTS author_status VARCHAR(20) DEFAULT 'none';

ALTER TABLE users
  ADD COLUMN IF NOT EXISTS author_requested_at TIMESTAMP;

ALTER TABLE users
  ADD COLUMN IF NOT EXISTS author_reviewed_by INTEGER;

ALTER TABLE users
  ADD COLUMN IF NOT EXISTS author_reviewed_at TIMESTAMP;

ALTER TABLE users
  ADD COLUMN IF NOT EXISTS author_reject_reason TEXT;

-- Helpful indexes
CREATE INDEX IF NOT EXISTS idx_users_author_status ON users(author_status);
CREATE INDEX IF NOT EXISTS idx_users_requested_author ON users(requested_author);
