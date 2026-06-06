ALTER TABLE books ADD COLUMN IF NOT EXISTS public_id VARCHAR(32);

UPDATE books
SET public_id = 'bk_' || substr(md5(id::text || '-' || created_at::text || '-' || random()::text), 1, 16)
WHERE public_id IS NULL OR public_id = '';

ALTER TABLE books ALTER COLUMN public_id SET NOT NULL;

CREATE UNIQUE INDEX IF NOT EXISTS idx_books_public_id ON books(public_id);
