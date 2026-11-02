-- =====================================================================
-- Garage Sleutelwerk - databaseschema en voorbeelddata
-- Wordt automatisch uitgevoerd door MariaDB bij de eerste 'docker compose up'.
-- =====================================================================

SET NAMES utf8mb4;
SET time_zone = '+01:00';

-- ---------------------------------------------------------------------
-- Tabellen
-- ---------------------------------------------------------------------

CREATE TABLE rollen (
  rol_id INT AUTO_INCREMENT PRIMARY KEY,
  naam VARCHAR(20) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE users (
  user_id INT AUTO_INCREMENT PRIMARY KEY,
  rol_id INT NOT NULL,
  naam VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  wachtwoord VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  deleted_at TIMESTAMP NULL DEFAULT NULL,
  FOREIGN KEY (rol_id) REFERENCES rollen(rol_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE autos (
  auto_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  kenteken VARCHAR(10) NOT NULL UNIQUE,
  merk VARCHAR(50) NOT NULL,
  model VARCHAR(50) NOT NULL,
  bouwjaar INT NOT NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  deleted_at TIMESTAMP NULL DEFAULT NULL,
  FOREIGN KEY (user_id) REFERENCES users(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE diensten (
  dienst_id INT AUTO_INCREMENT PRIMARY KEY,
  naam VARCHAR(100) NOT NULL,
  omschrijving TEXT NULL,
  duur_minuten INT NOT NULL,
  prijs DECIMAL(10,2) NOT NULL,
  images VARCHAR(255) NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  deleted_at TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE afspraken (
  afspraak_id INT AUTO_INCREMENT PRIMARY KEY,
  auto_id INT NOT NULL,
  dienst_id INT NOT NULL,
  monteur_id INT NULL,              -- nog niet bekend bij het maken
  datum DATE NOT NULL,
  tijd TIME NOT NULL,
  prijs DECIMAL(10,2) NOT NULL,     -- prijs op moment van boeken
  kilometerstand INT NULL,          -- pas na afloop bekend
  opmerking TEXT NULL,              -- pas na afloop bekend
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (auto_id) REFERENCES autos(auto_id),
  FOREIGN KEY (dienst_id) REFERENCES diensten(dienst_id),
  FOREIGN KEY (monteur_id) REFERENCES users(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE wijzigingen (
  wijziging_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  actie VARCHAR(100) NOT NULL,
  tabel_naam VARCHAR(50) NOT NULL,
  omschrijving TEXT NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------------------------------------------------------------------
-- Voorbeelddata
-- ---------------------------------------------------------------------

INSERT INTO rollen (rol_id, naam) VALUES
(1, 'klant'),
(2, 'monteur'),
(3, 'admin'),
(4, 'medewerker');

-- Wachtwoorden (gehasht met password_hash):
--   admin@sleutelwerk.nl       Admin123!
--   medewerker@sleutelwerk.nl  Medewerker123!
--   klant@sleutelwerk.nl       Klant123!   (ook voor de andere klanten)
--   monteurs                   Monteur123!
INSERT INTO users (user_id, rol_id, naam, email, wachtwoord, created_at) VALUES
(1, 3, 'Rachid Amrani',      'admin@sleutelwerk.nl',       '$2y$10$njgJBn/E6LFaWx06Z/k70OoSfZ70n5fyqjsnMYtQwW1GfJM.5RtpK', '2026-09-01 09:00:00'),
(2, 2, 'Dennis de Vries',    'dennis@sleutelwerk.nl',      '$2y$10$1aRhlpewgorL24nbXH/4guOW898BRvbi288kUlV71xpUa3Z/JfFyW', '2026-09-01 09:05:00'),
(3, 2, 'Sanne Bakker',       'sanne@sleutelwerk.nl',       '$2y$10$1aRhlpewgorL24nbXH/4guOW898BRvbi288kUlV71xpUa3Z/JfFyW', '2026-09-01 09:10:00'),
(4, 1, 'Jan Jansen',         'klant@sleutelwerk.nl',       '$2y$10$t8CQM9jQMFSfdxt4oHkQJeXwo2syxhZTxEaCLHSXDmdkDuvJnIWLW', '2026-09-14 18:22:00'),
(5, 1, 'Fatima El Idrissi',  'fatima@example.com',         '$2y$10$t8CQM9jQMFSfdxt4oHkQJeXwo2syxhZTxEaCLHSXDmdkDuvJnIWLW', '2026-09-20 12:41:00'),
(6, 1, 'Pieter de Boer',     'pieter@example.com',         '$2y$10$t8CQM9jQMFSfdxt4oHkQJeXwo2syxhZTxEaCLHSXDmdkDuvJnIWLW', '2026-10-02 08:13:00'),
(7, 1, 'Lisa Visser',        'lisa@example.com',           '$2y$10$t8CQM9jQMFSfdxt4oHkQJeXwo2syxhZTxEaCLHSXDmdkDuvJnIWLW', '2026-10-05 20:30:00'),
(8, 1, 'Mohammed Yilmaz',    'mohammed@example.com',       '$2y$10$t8CQM9jQMFSfdxt4oHkQJeXwo2syxhZTxEaCLHSXDmdkDuvJnIWLW', '2026-10-11 15:02:00'),
(9, 4, 'Emma de Groot',      'medewerker@sleutelwerk.nl',  '$2y$10$IXbYOQRzVu3XUMeLKOZ76OBepGomhXoD0AmfEdFZcArR.QK67IBd6', '2026-09-01 09:15:00');

INSERT INTO autos (auto_id, user_id, kenteken, merk, model, bouwjaar) VALUES
(1, 4, '12-ABC-3', 'Volkswagen', 'Golf', 2015),
(2, 4, 'K-456-DF', 'Volkswagen', 'Polo', 2019),
(3, 5, '45-GH-JK', 'Toyota', 'Yaris', 2012),
(4, 5, '78-LM-NP', 'Volkswagen', 'Passat', 2008),
(5, 6, 'R-789-ST', 'Opel', 'Corsa', 2017),
(6, 6, 'X-321-VZ', 'Toyota', 'Corolla', 2021),
(7, 7, '91-BD-FG', 'Toyota', 'Aygo', 2010),
(8, 7, '34-HJ-KL', 'Ford', 'Focus', 2005),
(9, 8, 'Z-654-NR', 'Peugeot', '208', 2023),
(10, 8, '6-TVX-21', 'Renault', 'Clio', 2014);

INSERT INTO diensten (dienst_id, naam, omschrijving, duur_minuten, prijs, images, deleted_at) VALUES
(1, 'APK-keuring', 'Wettelijke periodieke keuring van je auto. Zijn er kleine reparaties nodig, dan overleggen we eerst met je.', 45, 39.95, 'images/APK-keuring.jpg', NULL),
(2, 'Kleine beurt', 'Olie en oliefilter vervangen, vloeistoffen bijvullen en banden, verlichting en remmen controleren.', 60, 129.00, 'images/Kleine-beurt.jpg', NULL),
(3, 'Grote beurt', 'Uitgebreid onderhoud volgens fabrieksvoorschrift, inclusief alle filters en een complete controle.', 120, 279.00, 'images/Grote-beurt.jpg', NULL),
(4, 'Banden wisselen', 'Zomer- of winterbanden omzetten, inclusief controle van de bandenspanning en het profiel.', 30, 49.50, 'images/Banden-wisselen.jpg', NULL),
(5, 'Airco-service', 'Airco controleren, reinigen en bijvullen, zodat je auto weer lekker koel blijft.', 60, 89.00, 'images/Airco-service.jpg', NULL),
(6, 'Ruitenwisser vervangen', 'Nieuwe ruitenwisserbladen voor en achter.', 15, 24.95, NULL, '2026-10-20 10:00:00');

INSERT INTO afspraken (auto_id, dienst_id, monteur_id, datum, tijd, prijs, kilometerstand, opmerking) VALUES
(1, 1, 2, '2026-10-06', '10:00:00', 39.95, 142350, 'Goedgekeurd. Remblokken voor bijna op.'),
(3, 2, 3, '2026-10-08', '13:00:00', 129.00, 98410, NULL),
(5, 4, 2, '2026-10-13', '10:00:00', 49.50, 61200, 'Winterbanden gemonteerd.'),
(6, 3, 3, '2026-10-15', '16:00:00', 279.00, 32800, NULL),
(2, 4, 2, '2026-10-20', '13:00:00', 49.50, 54870, NULL),
(7, 1, 3, '2026-10-22', '10:00:00', 39.95, 187300, 'Uitlaat roest, binnenkort vervangen.'),
(1, 5, NULL, '2026-11-04', '10:00:00', 89.00, NULL, NULL),
(4, 2, 2, '2026-11-05', '13:00:00', 129.00, NULL, NULL),
(9, 4, NULL, '2026-11-06', '16:00:00', 49.50, NULL, NULL),
(3, 1, NULL, '2026-11-07', '10:00:00', 39.95, NULL, NULL);

INSERT INTO wijzigingen (user_id, actie, tabel_naam, omschrijving, created_at) VALUES
(9, 'Dienst toegevoegd', 'diensten', 'Nieuwe dienst "Airco-service" voor € 89.00', '2026-10-01 09:12:00'),
(1, 'Dienst bewerkt', 'diensten', 'Dienst "APK-keuring": 45 minuten, € 39.95', '2026-10-03 14:30:00'),
(9, 'Afspraak afgehandeld', 'afspraken', 'Afspraak #1: monteur 2, kilometerstand 142350', '2026-10-06 11:05:00'),
(9, 'Dienst uitgezet', 'diensten', 'Dienst #6 is naar de prullenbak verplaatst', '2026-10-20 10:00:00');
