-- Ajouter la colonne fake_name à la table community_messages
-- Cette colonne permet à l'admin de poster des messages sous un faux nom

ALTER TABLE community_messages 
ADD COLUMN fake_name VARCHAR(255) NULL DEFAULT NULL 
AFTER message;

-- Optionnel: Ajouter un index pour de meilleures performances
-- CREATE INDEX idx_fake_name ON community_messages(fake_name);
