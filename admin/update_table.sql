USE helpexpert;

-- Adicionar coluna status se não existir
ALTER TABLE leads ADD COLUMN IF NOT EXISTS status VARCHAR(20) NOT NULL DEFAULT 'novo';

-- Atualizar todos os leads existentes para ter o status 'novo'
UPDATE leads SET status = 'novo' WHERE status IS NULL OR status = '';
