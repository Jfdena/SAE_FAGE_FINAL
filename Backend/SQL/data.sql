-- Backend/SQL/data.sql

CREATE DATABASE IF NOT EXISTS FAGE;
USE FAGE;

-- Table bénévoles
CREATE TABLE IF NOT EXISTS Benevole (
                                        id_benevole INT AUTO_INCREMENT PRIMARY KEY,
                                        nom VARCHAR(50) NOT NULL,
                                        prenom VARCHAR(50) NOT NULL,
                                        email VARCHAR(100),
                                        telephone VARCHAR(20),
                                        date_naissance DATE,
                                        date_inscription DATE NOT NULL,
                                        statut ENUM('actif','inactif') DEFAULT 'actif'
);

-- Table membres du bureau
CREATE TABLE IF NOT EXISTS MembreBureau (
                                            id_membre INT AUTO_INCREMENT PRIMARY KEY,
                                            nom VARCHAR(50) NOT NULL,
                                            prenom VARCHAR(50) NOT NULL,
                                            email VARCHAR(100) NOT NULL UNIQUE,
                                            password VARCHAR(255) NOT NULL, -- hash mot de passe
                                            role ENUM('admin','responsable') NOT NULL
);

-- Table partenaires
CREATE TABLE IF NOT EXISTS Partenaire (
                                          id_partenaire INT AUTO_INCREMENT PRIMARY KEY,
                                          nom VARCHAR(100),
                                          type ENUM('donateur','subvention') NOT NULL,
                                          montant DECIMAL(10,2),
                                          date_contribution DATE
);
CREATE TABLE IF NOT EXISTS Evenement (
                                         id_evenement INT AUTO_INCREMENT PRIMARY KEY,
                                         nom VARCHAR(100) NOT NULL,
                                         type VARCHAR(50),
                                         details TEXT,
                                         lieu VARCHAR(100),
                                         date_debut DATE,
                                         date_fin DATE,
                                         nb_participants_prevus INT,
                                         budget DECIMAL(10,2),
                                         statut ENUM('planifié', 'en cours', 'termine', 'annule') DEFAULT 'planifié'
);