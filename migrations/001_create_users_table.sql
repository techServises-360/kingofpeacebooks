-- Create users table for Neon PostgreSQL
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'user' CHECK (role IN ('admin', 'author', 'user')),
    email_verified BOOLEAN DEFAULT FALSE,
    email_verification_code VARCHAR(10),
    email_verification_expires TIMESTAMP,
    requested_author BOOLEAN DEFAULT FALSE,
    author_status VARCHAR(20) DEFAULT 'none' CHECK (author_status IN ('none', 'pending', 'approved', 'rejected')),
    author_requested_at TIMESTAMP,
    author_reviewed_by INTEGER,
    author_reviewed_at TIMESTAMP,
    author_reject_reason TEXT,
    suspended BOOLEAN DEFAULT FALSE,
    suspension_reason TEXT,
    suspended_at TIMESTAMP,
    suspended_by INTEGER,
    unsuspended_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);
CREATE INDEX IF NOT EXISTS idx_users_role ON users(role);
CREATE INDEX IF NOT EXISTS idx_users_email_verified ON users(email_verified);
CREATE INDEX IF NOT EXISTS idx_users_author_status ON users(author_status);
CREATE INDEX IF NOT EXISTS idx_users_suspended ON users(suspended);
