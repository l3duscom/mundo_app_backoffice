-- ============================================
-- Adiciona coluna de descrição na tabela conquistas
-- ============================================

-- Se você criou a tabela antes da adição do campo descrição,
-- execute este script para adicionar a coluna manualmente

ALTER TABLE `conquistas` 
ADD COLUMN `descricao` TEXT NULL COMMENT 'Descrição detalhada da conquista' 
AFTER `nome_conquista`;

-- ============================================
-- Exemplos de UPDATE para adicionar descrições às conquistas existentes
-- ============================================

-- Atualize conforme suas conquistas específicas:
UPDATE `conquistas` SET `descricao` = 'Participou do evento pela primeira vez' 
WHERE `nome_conquista` = 'Primeira Participação';

UPDATE `conquistas` SET `descricao` = 'Assistiu 3 painéis durante o evento' 
WHERE `nome_conquista` = 'Participou de 3 Painéis';

UPDATE `conquistas` SET `descricao` = 'Participou de Meet & Greet com 5 convidados' 
WHERE `nome_conquista` = 'Conheceu 5 Convidados';

UPDATE `conquistas` SET `descricao` = 'Participou do desfile cosplay e ganhou premiação' 
WHERE `nome_conquista` = 'Mestre Cosplayer';

UPDATE `conquistas` SET `descricao` = 'Participou de todos os itens do cronograma' 
WHERE `nome_conquista` = 'Completou Todo o Cronograma';

UPDATE `conquistas` SET `descricao` = 'Adquiriu produtos no Meet & Greet' 
WHERE `nome_conquista` = 'Comprou no Meet & Greet';

UPDATE `conquistas` SET `descricao` = 'Registrou o momento com um convidado especial' 
WHERE `nome_conquista` = 'Tirou Foto com Convidado';

UPDATE `conquistas` SET `descricao` = 'Participou e acertou questões no quiz' 
WHERE `nome_conquista` = 'Participou do Quiz';

-- ============================================
-- Para remover a coluna (se necessário):
-- ============================================
-- ALTER TABLE `conquistas` DROP COLUMN `descricao`;

