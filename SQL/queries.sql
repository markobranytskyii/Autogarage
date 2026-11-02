-- =====================================================================
-- SQL-dossier Garage Sleutelwerk
-- Elke query beantwoordt een vraag van de opdrachtgever (Rachid),
-- of wordt gebruikt in een PHP-pagina (zie de naam boven de query).
-- =====================================================================


-- ---------------------------------------------------------------------
-- Kaart 3 - De bezoeker (gebruikt in index.php en diensten_detail.php)
-- ---------------------------------------------------------------------

-- Vraag: welke verschillende duren hebben onze diensten? (keuzelijst filter)
SELECT DISTINCT duur_minuten
FROM diensten
WHERE deleted_at IS NULL
ORDER BY duur_minuten;

-- Vraag: welke diensten duren 60 minuten en kosten minder dan 150 euro, van duur naar goedkoop?
SELECT naam, duur_minuten, prijs
FROM diensten
WHERE duur_minuten = 60 AND prijs < 150 AND deleted_at IS NULL
ORDER BY prijs DESC;

-- Vraag: hoe vaak is elke dienst geboekt, ook diensten die nog nooit geboekt zijn?
-- Diensten zonder afspraak krijgen bij de LEFT JOIN NULL in alle kolommen van afspraken,
-- want er is geen afspraak die bij die dienst hoort. COUNT(afspraken.afspraak_id) telt
-- NULL niet mee, daarom krijgen die diensten 0 in plaats van 1.
SELECT diensten.naam, COUNT(afspraken.afspraak_id) AS aantal_geboekt
FROM diensten
LEFT JOIN afspraken ON afspraken.dienst_id = diensten.dienst_id
WHERE diensten.deleted_at IS NULL
GROUP BY diensten.dienst_id
ORDER BY aantal_geboekt DESC;

-- Vraag: wat zijn de gegevens van de dienst met id 1? (detailpagina)
SELECT *
FROM diensten
WHERE dienst_id = 1 AND deleted_at IS NULL;


-- ---------------------------------------------------------------------
-- Kolomfuncties: MIN, MAX, COUNT, SUM, AVG
-- ---------------------------------------------------------------------

-- Vraag: wat is het oudste en het nieuwste bouwjaar van de auto's bij ons in de klantenkring?
SELECT MIN(bouwjaar) AS oudste_bouwjaar, MAX(bouwjaar) AS nieuwste_bouwjaar
FROM autos;

-- Vraag: hoeveel auto's staan er in totaal geregistreerd?
SELECT COUNT(*) AS aantal_autos
FROM autos
WHERE deleted_at IS NULL;

-- Vraag: wat is de gemiddelde prijs per afspraak?
SELECT AVG(prijs) AS gemiddelde_prijs
FROM afspraken;

-- Vraag: hoeveel omzet heeft elke dienst opgeleverd?
SELECT diensten.naam, SUM(afspraken.prijs) AS omzet
FROM diensten
LEFT JOIN afspraken ON afspraken.dienst_id = diensten.dienst_id
GROUP BY diensten.dienst_id
ORDER BY omzet DESC;


-- ---------------------------------------------------------------------
-- GROUP BY en HAVING
-- ---------------------------------------------------------------------

-- Vraag: hoeveel auto's hebben we per merk?
SELECT merk, COUNT(*) AS aantal
FROM autos
GROUP BY merk
ORDER BY aantal DESC;

-- Vraag: van welke merken hebben we meer dan twee auto's?
SELECT merk, COUNT(*) AS aantal
FROM autos
GROUP BY merk
HAVING COUNT(*) > 2;


-- ---------------------------------------------------------------------
-- LEFT JOIN (5 zelfbedacht, waarvan 2 met rijen ZONDER koppeling)
-- De omzet-per-dienst en geboekt-per-dienst queries hierboven gebruiken ook een LEFT JOIN.
-- ---------------------------------------------------------------------

-- Vraag: welke auto's hebben nog nooit een afspraak gehad?
-- Auto's zonder afspraak krijgen NULL in alle kolommen van afspraken, omdat er geen
-- afspraak met hun auto_id bestaat. Met WHERE afspraken.afspraak_id IS NULL houd je
-- precies die auto's over.
SELECT autos.kenteken, autos.merk, autos.model
FROM autos
LEFT JOIN afspraken ON afspraken.auto_id = autos.auto_id
WHERE afspraken.afspraak_id IS NULL;

-- Vraag: welke afspraken zijn er, met de naam van de monteur (ook afspraken zonder monteur)?
-- Bij afspraken waar nog geen monteur is toegewezen is monteur_id NULL. De LEFT JOIN
-- vindt dan geen gebruiker, dus users.naam (monteur) wordt ook NULL. Met een gewone
-- JOIN zouden deze afspraken uit de lijst verdwijnen.
SELECT afspraken.datum, afspraken.tijd, users.naam AS monteur
FROM afspraken
LEFT JOIN users ON users.user_id = afspraken.monteur_id
ORDER BY afspraken.datum, afspraken.tijd;

-- Vraag: welke auto's heeft elke klant?
SELECT users.naam AS klant, autos.kenteken, autos.merk, autos.model
FROM users
LEFT JOIN autos ON autos.user_id = users.user_id
ORDER BY users.naam;

-- Vraag: welke rol heeft elke gebruiker?
SELECT users.naam, users.email, rollen.naam AS rol
FROM users
LEFT JOIN rollen ON rollen.rol_id = users.rol_id
ORDER BY rollen.naam, users.naam;

-- Vraag: welke afspraken zijn er, met kenteken, dienst en prijs?
SELECT afspraken.datum, autos.kenteken, diensten.naam AS dienst, afspraken.prijs
FROM afspraken
LEFT JOIN autos ON autos.auto_id = afspraken.auto_id
LEFT JOIN diensten ON diensten.dienst_id = afspraken.dienst_id
ORDER BY afspraken.datum DESC;


-- ---------------------------------------------------------------------
-- INSERT, UPDATE en DELETE (op testdata; in de applicatie gebruiken we soft delete)
-- ---------------------------------------------------------------------

-- Vraag: voeg de nieuwe dienst "Airco service" toe.
INSERT INTO diensten (naam, omschrijving, duur_minuten, prijs)
VALUES ('Airco service', 'Airco controleren en bijvullen.', 60, 89.00);

-- Vraag: de prijs van de APK-keuring wordt 42,50 euro.
UPDATE diensten
SET prijs = 42.50
WHERE naam = 'APK-keuring';

-- Vraag: verwijder de test-dienst "Airco service" weer.
DELETE FROM diensten
WHERE naam = 'Airco service';

-- ---------------------------------------------------------------------
-- Queries uit de PHP-pagina's
-- ---------------------------------------------------------------------

-- user_register_add.php : nieuwe klant registreren (rol_id 1 = klant)
INSERT INTO users (email, wachtwoord, naam, rol_id)
VALUES (:email, :wachtwoord, :naam, :rol_id);

-- login_process.php : gebruiker opzoeken bij inloggen (met rolnaam)
SELECT users.*, rollen.naam AS rol
FROM users
JOIN rollen ON rollen.rol_id = users.rol_id
WHERE users.email = :email;

-- profile.php / profile_edit.php : eigen gegevens tonen
SELECT users.*, rollen.naam AS rol
FROM users
JOIN rollen ON rollen.rol_id = users.rol_id
WHERE users.user_id = :user_id;

-- profile_edit_process.php : eigen naam en email wijzigen
UPDATE users
SET naam = :naam, email = :email
WHERE user_id = :user_id;

-- scripts/hashing.php : wachtwoord van een gebruiker omzetten naar hash
SELECT email, wachtwoord FROM users;
UPDATE users SET wachtwoord = :password WHERE email = :email;

-- autos_index.php : eigen auto's tonen
SELECT *
FROM autos
WHERE user_id = :user_id
AND deleted_at IS NULL
ORDER BY merk, model;

-- auto_edit.php : één eigen auto ophalen om te bewerken
SELECT * FROM autos
WHERE auto_id = :id AND user_id = :user_id;

-- auto_edit_process.php : eigen auto wijzigen
UPDATE autos
SET kenteken = :kenteken, merk = :merk, model = :model, bouwjaar = :bouwjaar
WHERE auto_id = :auto_id AND user_id = :user_id;

-- autos_toevoegen_process.php : nieuwe auto toevoegen
INSERT INTO autos (user_id, kenteken, merk, model, bouwjaar)
VALUES (:user_id, :kenteken, :merk, :model, :bouwjaar);

-- index.php / afspraken_create.php : diensten tonen met aantal boekingen
SELECT diensten.*, COUNT(afspraken.afspraak_id) AS aantal_geboekt
FROM diensten
LEFT JOIN afspraken ON afspraken.dienst_id = diensten.dienst_id
WHERE diensten.deleted_at IS NULL
GROUP BY diensten.dienst_id
ORDER BY diensten.naam;

-- diensten_detail.php : één dienst tonen
SELECT * FROM diensten WHERE dienst_id = :id;

-- afspraken_date.php : gekozen dienst + eigen auto's ophalen
SELECT * FROM diensten WHERE dienst_id = :dienst_id AND deleted_at IS NULL;
SELECT * FROM autos WHERE user_id = :user_id AND deleted_at IS NULL ORDER BY merk, model;

-- afspraak_add.php : controleren of auto van de gebruiker is
SELECT auto_id FROM autos
WHERE auto_id = :auto_id AND user_id = :user_id AND deleted_at IS NULL;

-- afspraak_add.php : prijs van de dienst op de server ophalen
SELECT prijs FROM diensten WHERE dienst_id = :dienst_id AND deleted_at IS NULL;

-- afspraak_add.php : afspraak opslaan (prijs van de server, monteur_id nog leeg)
INSERT INTO afspraken (auto_id, dienst_id, datum, tijd, prijs)
VALUES (:auto_id, :dienst_id, :datum, :tijd, :prijs);

-- afspraak_add.php / nav.php : aantal afspraken van de gebruiker tellen (teller)
SELECT COUNT(*) FROM afspraken
JOIN autos ON autos.auto_id = afspraken.auto_id
WHERE autos.user_id = :user_id;

-- afspraken_index.php : overzicht eigen afspraken (kenteken, dienst, prijs)
SELECT afspraken.datum, afspraken.tijd, afspraken.prijs,
       autos.kenteken, diensten.naam AS dienst
FROM afspraken
LEFT JOIN autos ON autos.auto_id = afspraken.auto_id
LEFT JOIN diensten ON diensten.dienst_id = afspraken.dienst_id
WHERE autos.user_id = :user_id
ORDER BY afspraken.datum, afspraken.tijd;
