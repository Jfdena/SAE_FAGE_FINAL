CREATE SCHEMA IF NOT EXISTS test;
USE test;

DROP TABLE IF EXISTS USERS, DOCUMENT, DON, DELEGUE, LOGISTIQUE_EVENEMENT, EVENEMENT, INSTANCE_REPRESENTATIVE, PARTENAIRE, GROS_DONATEUR, STATISTIQUE_INDICATEUR, PARTICIPATION, PROJET_NATIONAL, RESPONSABLE, COTISATION, PROFIL_ETUDIANT, BENEVOLE, ASSOCIATION;

-- =========================
-- ASSOCIATION
-- =========================
CREATE TABLE IF NOT EXISTS ASSOCIATION (
                             id_association INT AUTO_INCREMENT PRIMARY KEY,
                             nom VARCHAR(50),
                             ville VARCHAR(50),
                             universite VARCHAR(50),
                             date_creation DATE,
                             statut VARCHAR(50),
                             date_adhesion_fage DATE,
                             nb_membres INT
) ENGINE=InnoDB;

-- =========================
-- BENEVOLE
-- =========================
CREATE TABLE IF NOT EXISTS BENEVOLE (
                          id_benevole INT AUTO_INCREMENT PRIMARY KEY,
                          nom VARCHAR(50),
                          prenom VARCHAR(50),
                          email VARCHAR(50),
                          telephone VARCHAR(50),
                          date_naissance DATE,
                          date_inscription DATE,
                          statut VARCHAR(50)
) ENGINE=InnoDB;

-- =========================
-- PROFIL_ETUDIANT
-- =========================
CREATE TABLE IF NOT EXISTS PROFIL_ETUDIANT (
                                 id_profil INT AUTO_INCREMENT PRIMARY KEY,
                                 universite VARCHAR(50),
                                 filiere VARCHAR(50),
                                 niveau_engagement VARCHAR(50),
                                 competences VARCHAR(50),
                                 id_benevole INT,
                                 FOREIGN KEY (id_benevole) REFERENCES BENEVOLE(id_benevole)
) ENGINE=InnoDB;

-- =========================
-- COTISATION
-- =========================
CREATE TABLE IF NOT EXISTS COTISATION (
                            id_cotisation INT AUTO_INCREMENT PRIMARY KEY,
                            montant DECIMAL(15,2),
                            date_paiement DATE,
                            date_expiration DATE,
                            mode_paiement VARCHAR(50),
                            statut VARCHAR(50),
                            id_association INT,
                            FOREIGN KEY (id_association) REFERENCES ASSOCIATION(id_association)
) ENGINE=InnoDB;

-- =========================
-- RESPONSABLE
-- =========================
CREATE TABLE IF NOT EXISTS RESPONSABLE (
                             id_association INT,
                             id_benevole INT,
                             role VARCHAR(50),
                             date_debut_mandat DATE,
                             date_fin_mandat DATE,
                             statut VARCHAR(50),
                             PRIMARY KEY (id_association, id_benevole),
                             FOREIGN KEY (id_association) REFERENCES ASSOCIATION(id_association),
                             FOREIGN KEY (id_benevole) REFERENCES BENEVOLE(id_benevole)
) ENGINE=InnoDB;

-- =========================
-- PROJET_NATIONAL
-- =========================
CREATE TABLE IF NOT EXISTS PROJET_NATIONAL (
                                 id_projet INT AUTO_INCREMENT PRIMARY KEY,
                                 nom VARCHAR(50),
                                 type VARCHAR(50),
                                 description VARCHAR(50),
                                 budget_alloue DECIMAL(15,2),
                                 date_debut DATE,
                                 date_fin DATE,
                                 statut VARCHAR(50),
                                 id_benevole INT,
                                 FOREIGN KEY (id_benevole) REFERENCES BENEVOLE(id_benevole)
) ENGINE=InnoDB;

-- =========================
-- PARTICIPATION
-- =========================
CREATE TABLE IF NOT EXISTS PARTICIPATION (
                               id_association INT,
                               id_projet INT,
                               date_participation DATE,
                               role VARCHAR(50),
                               contribution VARCHAR(50),
                               PRIMARY KEY (id_association, id_projet),
                               FOREIGN KEY (id_association) REFERENCES ASSOCIATION(id_association),
                               FOREIGN KEY (id_projet) REFERENCES PROJET_NATIONAL(id_projet)
) ENGINE=InnoDB;

-- =========================
-- STATISTIQUE_INDICATEUR
-- =========================
CREATE TABLE IF NOT EXISTS STATISTIQUE_INDICATEUR (
                                        id_indicateur INT AUTO_INCREMENT PRIMARY KEY,
                                        nom VARCHAR(50),
                                        type VARCHAR(50),
                                        valeur_actuelle DECIMAL(15,2),
                                        date_maj DATE,
                                        periode VARCHAR(50),
                                        cible DECIMAL(15,2),
                                        tendance VARCHAR(50)
) ENGINE=InnoDB;

-- =========================
-- GROS_DONATEUR
-- =========================
CREATE TABLE IF NOT EXISTS GROS_DONATEUR (
                               id_gros_donateur INT AUTO_INCREMENT PRIMARY KEY,
                               nom VARCHAR(50),
                               type VARCHAR(50),
                               contact_nom VARCHAR(50),
                               contact_email VARCHAR(50),
                               telephone VARCHAR(50),
                               date_premier_don DATE,
                               montant_total_donne DECIMAL(15,2),
                               statut_valorisation VARCHAR(50),
                               actions_valorisation VARCHAR(50),
                               date_dernier_contact DATE
) ENGINE=InnoDB;

-- =========================
-- PARTENAIRE
-- =========================
CREATE TABLE IF NOT EXISTS PARTENAIRE (
                            id_partenaire INT AUTO_INCREMENT PRIMARY KEY,
                            nom VARCHAR(50),
                            type VARCHAR(50),
                            contact_nom VARCHAR(50),
                            contact_email VARCHAR(50),
                            telephone VARCHAR(50),
                            date_debut_partenariat DATE,
                            statut VARCHAR(50)
) ENGINE=InnoDB;

-- =========================
-- INSTANCE_REPRESENTATIVE
-- =========================
CREATE TABLE IF NOT EXISTS INSTANCE_REPRESENTATIVE (
                                         id_instance INT AUTO_INCREMENT PRIMARY KEY,
                                         nom VARCHAR(50),
                                         niveau VARCHAR(50),
                                         description VARCHAR(50),
                                         periodicite_reunion VARCHAR(50)
) ENGINE=InnoDB;

-- =========================
-- EVENEMENT
-- =========================
CREATE TABLE IF NOT EXISTS EVENEMENT (
                           id_evenement INT AUTO_INCREMENT PRIMARY KEY,
                           nom VARCHAR(50),
                           type VARCHAR(50),
                           lieu VARCHAR(50),
                           date_debut DATE,
                           date_fin DATE,
                           nb_participants_prevus INT,
                           budget DECIMAL(15,2),
                           statut VARCHAR(50)
) ENGINE=InnoDB;

-- =========================
-- LOGISTIQUE_EVENEMENT
-- =========================
CREATE TABLE IF NOT EXISTS LOGISTIQUE_EVENEMENT (
                                      id_logistique INT AUTO_INCREMENT PRIMARY KEY,
                                      type_besoin VARCHAR(50),
                                      description VARCHAR(50),
                                      quantite INT,
                                      statut VARCHAR(50),
                                      id_evenement INT,
                                      FOREIGN KEY (id_evenement) REFERENCES EVENEMENT(id_evenement)
) ENGINE=InnoDB;

-- =========================
-- DELEGUE
-- =========================
CREATE TABLE IF NOT EXISTS DELEGUE (
                         id_benevole INT,
                         id_instance INT,
                         mandat_debut DATE,
                         mandat_fin DATE,
                         role VARCHAR(50),
                         statut VARCHAR(50),
                         PRIMARY KEY (id_benevole, id_instance),
                         FOREIGN KEY (id_benevole) REFERENCES BENEVOLE(id_benevole),
                         FOREIGN KEY (id_instance) REFERENCES INSTANCE_REPRESENTATIVE(id_instance)
) ENGINE=InnoDB;

-- =========================
-- DON
-- =========================
CREATE TABLE IF NOT EXISTS DON (
                     id_don INT AUTO_INCREMENT PRIMARY KEY,
                     type VARCHAR(50),
                     montant DECIMAL(15,2),
                     description VARCHAR(50),
                     date_don DATE,
                     utilisation_prevue VARCHAR(50),
                     id_gros_donateur INT NULL,
                     id_partenaire INT NULL,
                     FOREIGN KEY (id_gros_donateur) REFERENCES GROS_DONATEUR(id_gros_donateur),
                     FOREIGN KEY (id_partenaire) REFERENCES PARTENAIRE(id_partenaire)
) ENGINE=InnoDB;

-- =========================
-- DOCUMENT
-- =========================
CREATE TABLE IF NOT EXISTS DOCUMENT (
                        id_document INT AUTO_INCREMENT PRIMARY KEY,
                        nom_fichier VARCHAR(50),
                        type VARCHAR(50),
                        chemin_fichier VARCHAR(50),
                        date_upload DATE,
                        statut VARCHAR(50),
                        id_partenaire INT,
                        id_projet INT,
                        id_evenement INT,
                        FOREIGN KEY (id_partenaire) REFERENCES PARTENAIRE(id_partenaire),
                        FOREIGN KEY (id_projet) REFERENCES PROJET_NATIONAL(id_projet),
                        FOREIGN KEY (id_evenement) REFERENCES EVENEMENT(id_evenement)
) ENGINE=InnoDB;

-- =========================
-- GÉRANT
-- =========================
CREATE TABLE IF NOT EXISTS USERS (
                        id_user INT AUTO_INCREMENT PRIMARY KEY,
                        username VARCHAR(50) UNIQUE NOT NULL,
                        password VARCHAR(72) NOT NULL,
                        id_benevole INT UNIQUE,
                        role ENUM('admin', 'user')
) ENGINE=InnoDB;
INSERT INTO USERS (USERNAME, PASSWORD, ROLE) VALUES ("test", "$2y$12$CHfrtMqbd9tFui5YwkrsheNHVpioDzd9PaD7u0MHNm70AXfIrBTnC", "admin")
