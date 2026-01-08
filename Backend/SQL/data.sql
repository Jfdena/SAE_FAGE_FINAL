-- Backend/SQL/data.sql

-- Créer la base de données avec UTF-8
CREATE DATABASE IF NOT EXISTS FAGE
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE FAGE;

-- Table bénévole avec contraintes (SANS CURDATE DANS LES CHECK)
CREATE TABLE IF NOT EXISTS Benevole (
                                        id_benevole INT AUTO_INCREMENT PRIMARY KEY,
                                        nom VARCHAR(50) NOT NULL,
                                        prenom VARCHAR(50) NOT NULL,
                                        email VARCHAR(100) UNIQUE,
                                        telephone VARCHAR(20),
                                        date_naissance DATE,
                                        date_inscription DATE NOT NULL DEFAULT (CURRENT_DATE),
                                        statut ENUM('actif','inactif') DEFAULT 'actif',
                                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Contraintes (sans CURDATE)
                                        CHECK (date_naissance IS NULL OR date_naissance < '2100-01-01'), -- Date raisonnable
                                        CHECK (date_inscription IS NULL OR date_inscription < '2100-01-01'),

    -- Index pour les recherches
                                        INDEX idx_nom_prenom (nom, prenom),
                                        INDEX idx_email (email),
                                        INDEX idx_statut (statut)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table membre du bureau
CREATE TABLE IF NOT EXISTS MembreBureau (
                                            id_membre INT AUTO_INCREMENT PRIMARY KEY,
                                            nom VARCHAR(50) NOT NULL,
                                            prenom VARCHAR(50) NOT NULL,
                                            email VARCHAR(100) NOT NULL UNIQUE,
                                            password VARCHAR(255) NOT NULL,
                                            role ENUM('admin','responsable') NOT NULL DEFAULT 'responsable',
                                            statut ENUM('actif','inactif') DEFAULT 'actif',
                                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                            last_login TIMESTAMP NULL,

    -- Index
                                            INDEX idx_email (email),
                                            INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table partenaires avec contraintes (SANS CURDATE)
CREATE TABLE IF NOT EXISTS Partenaire (
                                          id_partenaire INT AUTO_INCREMENT PRIMARY KEY,
                                          nom VARCHAR(100) NOT NULL,
                                          type ENUM('donateur','subvention','partenaire') NOT NULL,
                                          montant DECIMAL(10,2) CHECK (montant IS NULL OR montant >= 0),
                                          date_contribution DATE,
                                          contact_email VARCHAR(100),
                                          contact_telephone VARCHAR(20),
                                          description TEXT,
                                          statut ENUM('actif','inactif') DEFAULT 'actif',
                                          date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                          updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Contraintes
                                          UNIQUE KEY unique_nom_type (nom, type(50)),
                                          CHECK (date_contribution IS NULL OR date_contribution < '2100-01-01'),

    -- Index
                                          INDEX idx_nom (nom),
                                          INDEX idx_type (type),
                                          INDEX idx_statut (statut),
                                          INDEX idx_date_contribution (date_contribution)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table événements
CREATE TABLE IF NOT EXISTS Evenement (
                                         id_evenement INT AUTO_INCREMENT PRIMARY KEY,
                                         nom VARCHAR(100) NOT NULL,
                                         type VARCHAR(50),
                                         details TEXT,
                                         lieu VARCHAR(100),
                                         date_debut DATE NOT NULL,
                                         date_fin DATE NOT NULL,
                                         nb_participants_prevus INT CHECK (nb_participants_prevus IS NULL OR nb_participants_prevus >= 0),
                                         budget DECIMAL(10,2) CHECK (budget IS NULL OR budget >= 0),
                                         statut ENUM('planifié', 'en cours', 'termine', 'annule') DEFAULT 'planifié',
                                         created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                         updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Contraintes (sans CURDATE)
                                         CHECK (date_fin >= date_debut),
                                         UNIQUE KEY unique_nom_date (nom, date_debut),

    -- Index
                                         INDEX idx_nom (nom),
                                         INDEX idx_date_debut (date_debut),
                                         INDEX idx_statut (statut)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table cotisation (SANS CURDATE)
CREATE TABLE IF NOT EXISTS cotisation (
                                          id_cotisation INT AUTO_INCREMENT PRIMARY KEY,
                                          id_benevole INT NOT NULL,
                                          montant DECIMAL(10,2) NOT NULL CHECK (montant > 0),
                                          date_paiement DATE NOT NULL,
                                          mode_paiement ENUM('espèces', 'chèque', 'virement', 'autre') DEFAULT 'espèces',
                                          annee YEAR NOT NULL,
                                          reference VARCHAR(50),
                                          date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    -- Clés étrangères
                                          FOREIGN KEY (id_benevole)
                                              REFERENCES benevole(id_benevole)
                                              ON DELETE CASCADE
                                              ON UPDATE CASCADE,

    -- Contraintes (sans CURDATE)
                                          UNIQUE KEY unique_benevole_annee (id_benevole, annee),
                                          CHECK (date_paiement IS NULL OR date_paiement < '2100-01-01'),
                                          CHECK (annee BETWEEN 2020 AND 2100),

    -- Index
                                          INDEX idx_benevole (id_benevole),
                                          INDEX idx_annee (annee),
                                          INDEX idx_date_paiement (date_paiement)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table participation (SANS CURDATE)
CREATE TABLE IF NOT EXISTS participation (
                                             id_participation INT AUTO_INCREMENT PRIMARY KEY,
                                             id_benevole INT NOT NULL,
                                             id_evenement INT NOT NULL,
                                             date_participation DATE NOT NULL DEFAULT (CURRENT_DATE),
                                             role ENUM('participant', 'organisateur', 'responsable') DEFAULT 'participant',
                                             note_participation TEXT,

    -- Clés étrangères
                                             FOREIGN KEY (id_benevole)
                                                 REFERENCES benevole(id_benevole)
                                                 ON DELETE CASCADE
                                                 ON UPDATE CASCADE,

                                             FOREIGN KEY (id_evenement)
                                                 REFERENCES evenement(id_evenement)
                                                 ON DELETE CASCADE
                                                 ON UPDATE CASCADE,

    -- Contraintes
                                             UNIQUE KEY unique_participation (id_benevole, id_evenement),
                                             CHECK (date_participation IS NULL OR date_participation < '2100-01-01'),

    -- Index
                                             INDEX idx_benevole (id_benevole),
                                             INDEX idx_evenement (id_evenement)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table pour les documents
CREATE TABLE IF NOT EXISTS document_evenement (
                                                  id_document INT AUTO_INCREMENT PRIMARY KEY,
                                                  id_evenement INT NOT NULL,
                                                  nom_fichier VARCHAR(255) NOT NULL,
                                                  type_document ENUM('affiche', 'compte_rendu', 'budget', 'autre') DEFAULT 'autre',
                                                  chemin_fichier VARCHAR(500) NOT NULL,
                                                  taille_fichier INT,
                                                  date_upload TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    -- Clés étrangères
                                                  FOREIGN KEY (id_evenement)
                                                      REFERENCES evenement(id_evenement)
                                                      ON DELETE CASCADE
                                                      ON UPDATE CASCADE,

    -- Index
                                                  INDEX idx_evenement (id_evenement),
                                                  INDEX idx_type_document (type_document)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table pour l'historique des modifications
CREATE TABLE IF NOT EXISTS historique_modifications (
                                                        id_historique INT AUTO_INCREMENT PRIMARY KEY,
                                                        table_modifiee VARCHAR(50) NOT NULL,
                                                        id_enregistrement INT NOT NULL,
                                                        action VARCHAR(20) NOT NULL, -- INSERT, UPDATE, DELETE
                                                        anciennes_valeurs JSON,
                                                        nouvelles_valeurs JSON,
                                                        id_utilisateur INT,
                                                        date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    -- Index
                                                        INDEX idx_table (table_modifiee),
                                                        INDEX idx_date (date_modification),
                                                        INDEX idx_utilisateur (id_utilisateur)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table pour les logs d'activité
CREATE TABLE IF NOT EXISTS logs_activite (
                                             id_log INT AUTO_INCREMENT PRIMARY KEY,
                                             id_utilisateur INT,
                                             action VARCHAR(100) NOT NULL,
                                             details TEXT,
                                             ip_address VARCHAR(45),
                                             user_agent TEXT,
                                             date_log TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    -- Index
                                             INDEX idx_utilisateur (id_utilisateur),
                                             INDEX idx_action (action),
                                             INDEX idx_date (date_log)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Triggers pour l'historique
DELIMITER $$

-- Trigger pour les partenaires
CREATE TRIGGER before_partenaire_update
    BEFORE UPDATE ON Partenaire
    FOR EACH ROW
BEGIN
    INSERT INTO historique_modifications
    (table_modifiee, id_enregistrement, action, anciennes_valeurs, nouvelles_valeurs)
    VALUES (
               'Partenaire',
               OLD.id_partenaire,
               'UPDATE',
               JSON_OBJECT(
                       'nom', OLD.nom,
                       'type', OLD.type,
                       'montant', OLD.montant,
                       'statut', OLD.statut,
                       'contact_email', OLD.contact_email
               ),
               JSON_OBJECT(
                       'nom', NEW.nom,
                       'type', NEW.type,
                       'montant', NEW.montant,
                       'statut', NEW.statut,
                       'contact_email', NEW.contact_email
               )
           );
END$$

-- Trigger pour les bénévoles
CREATE TRIGGER before_benevole_update
    BEFORE UPDATE ON Benevole
    FOR EACH ROW
BEGIN
    INSERT INTO historique_modifications
    (table_modifiee, id_enregistrement, action, anciennes_valeurs, nouvelles_valeurs)
    VALUES (
               'Benevole',
               OLD.id_benevole,
               'UPDATE',
               JSON_OBJECT(
                       'nom', OLD.nom,
                       'prenom', OLD.prenom,
                       'email', OLD.email,
                       'statut', OLD.statut
               ),
               JSON_OBJECT(
                       'nom', NEW.nom,
                       'prenom', NEW.prenom,
                       'email', NEW.email,
                       'statut', NEW.statut
               )
           );
END$$

DELIMITER ;

-- Vues utiles
CREATE OR REPLACE VIEW vue_partenaires_actifs AS
SELECT
    p.*,
    COUNT(DISTINCT c.id_cotisation) as nb_contributions,
    SUM(c.montant) as total_contributions
FROM Partenaire p
         LEFT JOIN cotisation c ON p.id_partenaire = c.id_benevole
WHERE p.statut = 'actif'
GROUP BY p.id_partenaire;

CREATE OR REPLACE VIEW vue_benevoles_avec_cotisations AS
SELECT
    b.*,
    (SELECT MAX(date_paiement) FROM cotisation WHERE id_benevole = b.id_benevole) as derniere_cotisation,
    (SELECT COUNT(*) FROM cotisation WHERE id_benevole = b.id_benevole) as nb_cotisations,
    (SELECT SUM(montant) FROM cotisation WHERE id_benevole = b.id_benevole) as total_cotise,
    (SELECT GROUP_CONCAT(DISTINCT annee ORDER BY annee DESC SEPARATOR ', ')
     FROM cotisation WHERE id_benevole = b.id_benevole) as annees_cotisees
FROM Benevole b
WHERE b.statut = 'actif';

CREATE OR REPLACE VIEW vue_evenements_prochains AS
SELECT
    e.*,
    DATEDIFF(e.date_debut, CURDATE()) as jours_restants,
    (SELECT COUNT(*) FROM participation WHERE id_evenement = e.id_evenement) as nb_participants
FROM Evenement e
WHERE e.date_debut >= CURDATE()
  AND e.statut = 'planifié'
ORDER BY e.date_debut;

-- Insérer admin de test
INSERT IGNORE INTO MembreBureau (nom, prenom, email, password, role) VALUES
                                                                         ('Admin', 'System', 'admin@fage.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
                                                                         ('Lambert', 'Sophie', 'sophie@fage.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
                                                                         ('Benali', 'Nadia', 'nadia@fage.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'responsable'),
                                                                         ('Morel', 'Thomas', 'thomas@fage.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'responsable');

-- Insérer bénévoles de test
INSERT IGNORE INTO Benevole (nom, prenom, email, telephone, date_naissance, date_inscription) VALUES
                                                                                                  ('Martin', 'Sophie', 'sophie.martin@email.com', '0612345678', '1992-03-12', '2024-01-15'),
                                                                                                  ('Dupont', 'Jean', 'jean.dupont@email.com', '0623456789', '1990-07-25', '2024-02-20'),
                                                                                                  ('Bernard', 'Marie', 'marie.bernard@email.com', '0634567890', '1995-11-08', '2024-03-10'),
                                                                                                  ('Petit', 'Luc', 'luc.petit@email.com', '0645678901', '1988-05-30', '2024-04-05'),
                                                                                                  ('Moreau', 'Antoine', 'antoine.moreau@email.com', '0656789012', '1985-09-05', '2023-11-15');

-- Insérer cotisations de test
INSERT IGNORE INTO cotisation (id_benevole, montant, date_paiement, mode_paiement, annee) VALUES
                                                                                              (1, 15.00, '2025-01-15', 'espèces', 2025),
                                                                                              (2, 15.00, '2025-02-20', 'virement', 2025),
                                                                                              (3, 20.00, '2025-03-10', 'chèque', 2025),
                                                                                              (1, 15.00, '2024-01-20', 'espèces', 2024),
                                                                                              (2, 15.00, '2024-02-25', 'virement', 2024);

-- Insérer événements de test
INSERT IGNORE INTO Evenement (nom, type, details, lieu, date_debut, date_fin, nb_participants_prevus, budget, statut) VALUES
                                                                                                                          ('Assemblée Générale', 'réunion', 'Assemblée générale annuelle de l''association', 'Salle des fêtes', '2025-04-15', '2025-04-15', 50, 200.00, 'planifié'),
                                                                                                                          ('Collecte alimentaire', 'collecte', 'Collecte de denrées alimentaires pour les étudiants', 'Centre-ville', '2025-03-20', '2025-03-20', 30, 150.00, 'planifié'),
                                                                                                                          ('Conférence éducation', 'conférence', 'Conférence sur l''accès à l''éducation', 'Amphithéâtre universitaire', '2025-05-10', '2025-05-10', 100, 500.00, 'planifié'),
                                                                                                                          ('Atelier CV', 'atelier', 'Atelier de rédaction de CV et lettres de motivation', 'Bibliothèque', '2025-02-28', '2025-02-28', 25, 100.00, 'termine');

-- Insérer participations
INSERT IGNORE INTO participation (id_benevole, id_evenement, role) VALUES
                                                                       (1, 1, 'organisateur'),
                                                                       (2, 1, 'participant'),
                                                                       (3, 1, 'participant'),
                                                                       (1, 2, 'responsable'),
                                                                       (4, 2, 'participant');

-- Insérer partenaires de test
INSERT IGNORE INTO Partenaire (nom, type, montant, date_contribution, contact_email, contact_telephone, description) VALUES
                                                                                                                         ('Mairie de Paris', 'subvention', 5000.00, '2025-01-10', 'subventions@paris.fr', '01 42 76 43 21', 'Subvention annuelle pour les actions sociales'),
                                                                                                                         ('Entreprise Solidarité SA', 'donateur', 2500.00, '2025-02-15', 'contact@solidarite-sa.com', '01 34 56 78 90', 'Entreprise partenaire pour les collectes alimentaires'),
                                                                                                                         ('Fondation Étudiante', 'donateur', 3000.00, '2025-03-01', 'info@fondation-etudiante.org', '01 23 45 67 89', 'Soutien aux projets éducatifs'),
                                                                                                                         ('Université Paris-Saclay', 'partenaire', NULL, '2025-01-20', 'partenariats@universite-ps.fr', '01 69 85 74 12', 'Partenariat pour les conférences et ateliers');