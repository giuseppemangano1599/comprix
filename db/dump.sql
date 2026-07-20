-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Creato il: Dic 05, 2025 alle 18:03
-- Versione del server: 10.4.32-MariaDB
-- Versione PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `comprix_db`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `carrello`
--

CREATE TABLE `carrello` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_cliente` int(10) UNSIGNED NOT NULL,
  `totale_elementi` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `totale_prezzo` decimal(10,2) UNSIGNED DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `carrello`
--

INSERT INTO `carrello` (`id`, `id_cliente`, `totale_elementi`, `totale_prezzo`) VALUES
(1, 1, 0, 0.00),
(2, 2, 0, 0.00);

-- --------------------------------------------------------

--
-- Struttura della tabella `categoria`
--

CREATE TABLE `categoria` (
  `id` int(10) UNSIGNED NOT NULL,
  `nome` varchar(100) NOT NULL,
  `id_categoria_padre` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `categoria`
--

INSERT INTO `categoria` (`id`, `nome`, `id_categoria_padre`) VALUES
(1, 'Informatica', NULL),
(2, 'Console e videogiochi', 1),
(3, 'Pc da gaming', 1),
(4, 'PlayStation 5', 2),
(5, 'Xbox Series X/S', 2),
(6, 'Nintendo Switch', 2),
(7, 'Videogiochi', 2),
(8, 'Accessori per le console', 2),
(9, 'Componenti hardware', 3),
(10, 'Periferiche da gaming', 3),
(11, 'Sedie da gaming', 3),
(12, 'Notebook e laptop', 1),
(13, 'Tablet ed eBook reader', 1),
(14, 'Casa e giardino', NULL),
(15, 'Decorazioni natalizie', 14),
(16, 'Alberi di Natale', 15),
(17, 'Luci e luminarie', 15),
(18, 'Addobbi per l\'albero', 15),
(19, 'Giardino e giardinaggio', 14),
(20, 'Arredamento da esterno', 19),
(21, 'Barbecue e picnic', 19),
(22, 'Utensili da giardinaggio', 19),
(23, 'Piscine e idromassaggio', 19),
(24, 'Bellezza e salute', NULL),
(25, 'Profumi', 24),
(26, 'Profumi donna', 25),
(27, 'Profumi uomo', 25),
(28, 'Benessere e cura della persona', 24),
(29, 'Skincare e viso', 28),
(30, 'Cura dei capelli', 28),
(31, 'Igiene orale', 28),
(32, 'Rasatura e depilazione', 28),
(33, 'Animali domestici', NULL),
(34, 'Cani', 33),
(35, 'Gatti', 33),
(36, 'Acquariologia e pesci', 33),
(37, 'Cibo per animali', 33),
(38, 'Accessori e cucce', 33),
(39, 'Cultura e intrattenimento', NULL),
(40, 'Libri e lettura', 39),
(41, 'Narrativa e romanzi', 40),
(42, 'Gialli e thriller', 40),
(43, 'Fumetti e manga', 40),
(44, 'Motori', NULL),
(45, 'Auto e moto', 44),
(46, 'Accessori auto', 45),
(47, 'Abbigliamento moto', 45),
(48, 'Caschi', 45),
(49, 'Viaggi e valigeria', NULL),
(50, 'Valigie e kit da viaggio', 49),
(51, 'Trolley', 50),
(52, 'Zaini da viaggio', 50),
(53, 'Sport e fitness', NULL),
(54, 'Scarpe da ginnastica', 53),
(55, 'Sneakers', 54),
(56, 'Abbigliamento sportivo', 53),
(57, 'Attrezzi per la palestra', 53);

-- --------------------------------------------------------

--
-- Struttura della tabella `cliente`
--

CREATE TABLE `cliente` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(72) NOT NULL,
  `email` varchar(320) NOT NULL,
  `domanda_segreta` varchar(80) NOT NULL,
  `risposta_segreta_hash` varchar(72) NOT NULL,
  `data_iscrizione` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `cliente`
--

INSERT INTO `cliente` (`id`, `username`, `password_hash`, `email`, `domanda_segreta`, `risposta_segreta_hash`, `data_iscrizione`) VALUES
(1, 'jack_sparrow', '$2y$10$8jrOuMmpySszbgtSpW2/3.iq/OgLTkiWY4AqvfKSFiqCWbh4J721e', 'jack@sparrow.it', 'Qual è il titolo del tuo film preferito?', '$2y$10$IsyCElPXi/XRTdqv3Wy8luUwGRW0kRgRRn.kp4/BC/OLI7gU6D/xC', '2025-12-05 12:56:14'),
(2, 'deliaaa', '$2y$10$yvrJpGeqsYeJpXqgXyUl6unqKYmr.00UpqbszTRJXgLNg8zCbd51K', 'delia@bellezza.it', 'Qual è il nome della tua squadra del cuore?', '$2y$10$duvdx59own3RuMIIWDSdB.nDOQ1xPaSrBnuHzciu4snAJdsFqarTC', '2025-12-05 13:28:10');

--
-- Trigger `cliente`
--
DELIMITER $$
CREATE TRIGGER `tr_controlla_email_cliente_insert` BEFORE INSERT ON `cliente` FOR EACH ROW BEGIN
    IF EXISTS (SELECT 1 FROM negoziante WHERE email = NEW.email) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Questo indirizzo email è già in uso da un negoziante.';
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tr_controlla_email_cliente_update` BEFORE UPDATE ON `cliente` FOR EACH ROW BEGIN
    IF OLD.email != NEW.email AND EXISTS (SELECT 1 FROM negoziante WHERE email = NEW.email) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Questo indirizzo email è già in uso da un negoziante.';
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tr_controlla_username_cliente_insert` BEFORE INSERT ON `cliente` FOR EACH ROW BEGIN
    IF EXISTS (SELECT 1 FROM negoziante WHERE username = NEW.username) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Questo username è già in uso da un negoziante.';
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tr_controlla_username_cliente_update` BEFORE UPDATE ON `cliente` FOR EACH ROW BEGIN
    IF OLD.username != NEW.username AND EXISTS (SELECT 1 FROM negoziante WHERE username = NEW.username) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Questo username è già in uso da un negoziante.';
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tr_crea_carrello_cliente` AFTER INSERT ON `cliente` FOR EACH ROW BEGIN
    INSERT INTO carrello (id_cliente) VALUES (NEW.id);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struttura della tabella `elemento_carrello`
--

CREATE TABLE `elemento_carrello` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_carrello` int(10) UNSIGNED NOT NULL,
  `id_prodotto` int(10) UNSIGNED NOT NULL,
  `quantita_aggiunta` int(10) UNSIGNED NOT NULL CHECK (`quantita_aggiunta` > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Trigger `elemento_carrello`
--
DELIMITER $$
CREATE TRIGGER `tr_aggiorna_carrello_after_delete` AFTER DELETE ON `elemento_carrello` FOR EACH ROW BEGIN
    UPDATE carrello 
    SET 
        totale_elementi = (
            SELECT COALESCE(SUM(quantita_aggiunta), 0) 
            FROM elemento_carrello 
            WHERE id_carrello = OLD.id_carrello
        ),
        totale_prezzo = (
            SELECT COALESCE(SUM(ec.quantita_aggiunta * p.prezzo), 0.00) 
            FROM elemento_carrello ec 
            JOIN prodotto p ON ec.id_prodotto = p.id 
            WHERE ec.id_carrello = OLD.id_carrello
        )
    WHERE id = OLD.id_carrello;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tr_aggiorna_carrello_after_insert` AFTER INSERT ON `elemento_carrello` FOR EACH ROW BEGIN
    UPDATE carrello 
    SET 
        totale_elementi = (
            SELECT COALESCE(SUM(quantita_aggiunta), 0) 
            FROM elemento_carrello 
            WHERE id_carrello = NEW.id_carrello
        ),
        totale_prezzo = (
            SELECT COALESCE(SUM(ec.quantita_aggiunta * p.prezzo), 0.00) 
            FROM elemento_carrello ec 
            JOIN prodotto p ON ec.id_prodotto = p.id 
            WHERE ec.id_carrello = NEW.id_carrello
        )
    WHERE id = NEW.id_carrello;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tr_aggiorna_carrello_after_update` AFTER UPDATE ON `elemento_carrello` FOR EACH ROW BEGIN
    UPDATE carrello 
    SET 
        totale_elementi = (
            SELECT COALESCE(SUM(quantita_aggiunta), 0) 
            FROM elemento_carrello 
            WHERE id_carrello = NEW.id_carrello
        ),
        totale_prezzo = (
            SELECT COALESCE(SUM(ec.quantita_aggiunta * p.prezzo), 0.00) 
            FROM elemento_carrello ec 
            JOIN prodotto p ON ec.id_prodotto = p.id 
            WHERE ec.id_carrello = NEW.id_carrello
        )
    WHERE id = NEW.id_carrello;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tr_controlla_disponibilita_carrello` BEFORE INSERT ON `elemento_carrello` FOR EACH ROW BEGIN
    IF NEW.quantita_aggiunta > (SELECT quantita_disponibile FROM prodotto WHERE id = NEW.id_prodotto) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Quantità richiesta non disponibile in magazzino';
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tr_controlla_disponibilita_carrello_update` BEFORE UPDATE ON `elemento_carrello` FOR EACH ROW BEGIN
    IF NEW.quantita_aggiunta > (SELECT quantita_disponibile FROM prodotto WHERE id = NEW.id_prodotto) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Quantità richiesta non disponibile in magazzino';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struttura della tabella `elemento_ordine`
--

CREATE TABLE `elemento_ordine` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_ordine` int(10) UNSIGNED NOT NULL,
  `id_prodotto` int(10) UNSIGNED DEFAULT NULL,
  `id_negoziante` int(10) UNSIGNED DEFAULT NULL,
  `nome_prodotto` varchar(100) NOT NULL,
  `prezzo_unitario` decimal(10,2) UNSIGNED NOT NULL,
  `quantita_aggiunta` int(10) UNSIGNED NOT NULL CHECK (`quantita_aggiunta` > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `elemento_ordine`
--

INSERT INTO `elemento_ordine` (`id`, `id_ordine`, `id_prodotto`, `id_negoziante`, `nome_prodotto`, `prezzo_unitario`, `quantita_aggiunta`) VALUES
(1, 1, 13, 3, 'Salvatore Sanfilippo - Storie di informatici illustri: Richard Andrew Fox', 3.63, 1),
(2, 2, 11, 2, 'Bosch Tagliaerba/Tosaerba a filo', 134.99, 1),
(3, 3, 10, 2, 'Lettiera per gatti', 109.99, 1),
(4, 4, 6, 2, 'Tappi per le orecchie', 8.99, 4),
(5, 5, 5, 2, 'CeraVe lozione detergente idratante', 10.77, 2);

--
-- Trigger `elemento_ordine`
--
DELIMITER $$
CREATE TRIGGER `tr_aggiorna_stock_ordine` BEFORE INSERT ON `elemento_ordine` FOR EACH ROW BEGIN
    IF NEW.id_prodotto IS NOT NULL THEN
        -- Controllo disponibilità prima di scalare
        IF NEW.quantita_aggiunta > (SELECT quantita_disponibile FROM prodotto WHERE id = NEW.id_prodotto) THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Stock insufficiente per completare l''ordine';
        END IF;
        
        -- Scala lo stock
        UPDATE prodotto 
        SET quantita_disponibile = quantita_disponibile - NEW.quantita_aggiunta
        WHERE id = NEW.id_prodotto;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tr_proteggi_ordine_spedito` BEFORE UPDATE ON `elemento_ordine` FOR EACH ROW BEGIN
    IF (SELECT stato FROM ordine WHERE id = NEW.id_ordine) IN ('spedito', 'consegnato') THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Impossibile modificare ordine già spedito o consegnato';
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tr_proteggi_ordine_spedito_delete` BEFORE DELETE ON `elemento_ordine` FOR EACH ROW BEGIN
    IF (SELECT stato FROM ordine WHERE id = OLD.id_ordine) IN ('spedito', 'consegnato') THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Impossibile eliminare elementi da ordine già spedito o consegnato';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struttura della tabella `foto_prodotto`
--

CREATE TABLE `foto_prodotto` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_prodotto` int(10) UNSIGNED NOT NULL,
  `percorso_file` varchar(300) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `foto_prodotto`
--

INSERT INTO `foto_prodotto` (`id`, `id_prodotto`, `percorso_file`) VALUES
(1, 1, 'uploads/products/prod_1_6932b2a1b32de.jpg'),
(2, 1, 'uploads/products/prod_1_6932b2c45fcf4.jpg'),
(3, 2, 'uploads/products/prod_2_6932b34f9b593.jpg'),
(4, 2, 'uploads/products/prod_2_6932b34f9be12.jpg'),
(7, 3, 'uploads/products/prod_3_6932b477d3c40.jpg'),
(8, 3, 'uploads/products/prod_3_6932b477d44cc.jpg'),
(9, 4, 'uploads/products/prod_4_6932b4f75270d.jpg'),
(10, 4, 'uploads/products/prod_4_6932b4f75300f.jpg'),
(12, 5, 'uploads/products/prod_5_6932b575bf6af.jpg'),
(13, 5, 'uploads/products/prod_5_6932b5a492c73.jpg'),
(15, 6, 'uploads/products/prod_6_6932b7c22ce37.jpg'),
(16, 6, 'uploads/products/prod_6_6932b7d51af04.jpg'),
(17, 7, 'uploads/products/prod_7_6932b87f78312.jpg'),
(18, 7, 'uploads/products/prod_7_6932b88472461.jpg'),
(19, 8, 'uploads/products/prod_8_6932ba5543ca6.jpg'),
(20, 8, 'uploads/products/prod_8_6932ba5f41633.jpg'),
(21, 9, 'uploads/products/prod_9_6932baad044f9.jpg'),
(22, 9, 'uploads/products/prod_9_6932bab47a53b.jpg'),
(23, 10, 'uploads/products/prod_10_6932be38247a7.jpg'),
(24, 10, 'uploads/products/prod_10_6932be4f23294.jpg'),
(25, 11, 'uploads/products/prod_11_6932beefa33d5.jpg'),
(26, 11, 'uploads/products/prod_11_6932bef921e95.jpg'),
(27, 12, 'uploads/products/prod_12_6932c0bfaec93.jpg'),
(28, 12, 'uploads/products/prod_12_6932c0ca18acb.jpg'),
(29, 13, 'uploads/products/prod_13_6932c2134522e.jpg'),
(30, 13, 'uploads/products/prod_13_6932c21abbddb.jpg');

-- --------------------------------------------------------

--
-- Struttura della tabella `foto_recensione`
--

CREATE TABLE `foto_recensione` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_recensione` int(10) UNSIGNED NOT NULL,
  `percorso_file` varchar(300) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `foto_recensione`
--

INSERT INTO `foto_recensione` (`id`, `id_recensione`, `percorso_file`) VALUES
(1, 1, 'uploads/reviews/rev_1_6932ce3f21d0c.jpg');

-- --------------------------------------------------------

--
-- Struttura della tabella `indirizzo_spedizione`
--

CREATE TABLE `indirizzo_spedizione` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_cliente` int(10) UNSIGNED NOT NULL,
  `nome` varchar(50) NOT NULL,
  `cognome` varchar(50) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `indirizzo` varchar(150) NOT NULL,
  `citta` varchar(50) NOT NULL,
  `cap` varchar(10) NOT NULL,
  `provincia` char(2) NOT NULL,
  `descrizione` varchar(70) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `indirizzo_spedizione`
--

INSERT INTO `indirizzo_spedizione` (`id`, `id_cliente`, `nome`, `cognome`, `telefono`, `indirizzo`, `citta`, `cap`, `provincia`, `descrizione`) VALUES
(1, 1, 'Giacomo', 'Persiani', '+39 3284567890', 'Piazza dell\'Indipendenza, 7', 'Roma', '00185', 'RM', 'Casa'),
(2, 2, 'Delia', 'Bellini', '+39 3280987454', 'Via Don Gaetano Boschi 40', 'Pisa', '56126', 'PI', 'Casa fuori sede'),
(3, 2, 'Carla', 'Santini', '+39 3294561237', 'Via Andrea Cirrincione 97/C', 'Palermo', '90143', 'PA', 'Casa mia');

-- --------------------------------------------------------

--
-- Struttura della tabella `metodo_pagamento`
--

CREATE TABLE `metodo_pagamento` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_cliente` int(10) UNSIGNED NOT NULL,
  `tipo` enum('carta_credito','carta_debito','paypal','bonifico','contrassegno') NOT NULL,
  `descrizione` varchar(70) NOT NULL,
  `dati_pagamento` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`dati_pagamento`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `metodo_pagamento`
--

INSERT INTO `metodo_pagamento` (`id`, `id_cliente`, `tipo`, `descrizione`, `dati_pagamento`) VALUES
(1, 1, 'paypal', 'Paypal Personale', '{\"email_paypal\":\"jack@sparrow.it\"}'),
(2, 1, 'contrassegno', 'Contrassegno a casa', '{\"note\":\"Pagamento alla consegna\"}'),
(3, 2, 'carta_credito', 'Carta di Mamma', '{\"numero_carta\":\"1122\",\"data_scadenza\":\"12\\/26\",\"nome_titolare\":\"Carla Santini\"}'),
(4, 2, 'bonifico', 'Conto corrente personale', '{\"iban\":\"IT30V0300203280446284826952\",\"intestatario\":\"Delia Bellini\"}');

--
-- Trigger `metodo_pagamento`
--
DELIMITER $$
CREATE TRIGGER `tr_proteggi_metodo_pagamento` BEFORE DELETE ON `metodo_pagamento` FOR EACH ROW BEGIN
    IF EXISTS (
        SELECT * 
        FROM ordine 
        WHERE id_metodo_pagamento = OLD.id 
        AND stato IN ('confermato', 'spedito')
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Impossibile eliminare metodo di pagamento: ci sono ordini confermati o spediti che lo utilizzano';
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tr_valida_dati_pagamento` BEFORE INSERT ON `metodo_pagamento` FOR EACH ROW BEGIN
    IF NEW.tipo IN ('carta_credito', 'carta_debito') THEN
        IF JSON_UNQUOTE(JSON_EXTRACT(NEW.dati_pagamento, '$.numero_carta')) IS NULL OR
           JSON_UNQUOTE(JSON_EXTRACT(NEW.dati_pagamento, '$.numero_carta')) NOT REGEXP '^[0-9]{4}$' OR
           JSON_EXTRACT(NEW.dati_pagamento, '$.data_scadenza') IS NULL OR
           JSON_EXTRACT(NEW.dati_pagamento, '$.nome_titolare') IS NULL OR
           JSON_CONTAINS_PATH(NEW.dati_pagamento, 'one', '$.cvv') THEN
            SIGNAL SQLSTATE '45000' 
            SET MESSAGE_TEXT = 'Dati carta non validi: sono conservate solo le ultime 4 cifre, la scadenza e il titolare; il CVV non deve essere salvato';
        END IF;
    END IF;
    
    IF NEW.tipo = 'bonifico' THEN
        IF JSON_EXTRACT(NEW.dati_pagamento, '$.iban') IS NULL OR
           JSON_EXTRACT(NEW.dati_pagamento, '$.intestatario') IS NULL THEN
            SIGNAL SQLSTATE '45000' 
            SET MESSAGE_TEXT = 'Dati bonifico mancanti: iban e intestatario sono obbligatori';
        END IF;
    END IF;
    
    IF NEW.tipo = 'paypal' THEN
        IF JSON_EXTRACT(NEW.dati_pagamento, '$.email_paypal') IS NULL THEN
            SIGNAL SQLSTATE '45000' 
            SET MESSAGE_TEXT = 'Email PayPal obbligatoria';
        END IF;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tr_valida_dati_pagamento_update` BEFORE UPDATE ON `metodo_pagamento` FOR EACH ROW BEGIN
    IF NEW.tipo IN ('carta_credito', 'carta_debito') THEN
        IF JSON_UNQUOTE(JSON_EXTRACT(NEW.dati_pagamento, '$.numero_carta')) IS NULL OR
           JSON_UNQUOTE(JSON_EXTRACT(NEW.dati_pagamento, '$.numero_carta')) NOT REGEXP '^[0-9]{4}$' OR
           JSON_EXTRACT(NEW.dati_pagamento, '$.data_scadenza') IS NULL OR
           JSON_EXTRACT(NEW.dati_pagamento, '$.nome_titolare') IS NULL OR
           JSON_CONTAINS_PATH(NEW.dati_pagamento, 'one', '$.cvv') THEN
            SIGNAL SQLSTATE '45000' 
            SET MESSAGE_TEXT = 'Dati carta non validi: sono conservate solo le ultime 4 cifre, la scadenza e il titolare; il CVV non deve essere salvato';
        END IF;
    END IF;
    
    IF NEW.tipo = 'bonifico' THEN
        IF JSON_EXTRACT(NEW.dati_pagamento, '$.iban') IS NULL OR
           JSON_EXTRACT(NEW.dati_pagamento, '$.intestatario') IS NULL THEN
            SIGNAL SQLSTATE '45000' 
            SET MESSAGE_TEXT = 'Dati bonifico mancanti: iban e intestatario sono obbligatori';
        END IF;
    END IF;
    
    IF NEW.tipo = 'paypal' THEN
        IF JSON_EXTRACT(NEW.dati_pagamento, '$.email_paypal') IS NULL THEN
            SIGNAL SQLSTATE '45000' 
            SET MESSAGE_TEXT = 'Email PayPal obbligatoria';
        END IF;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struttura della tabella `negoziante`
--

CREATE TABLE `negoziante` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(72) NOT NULL,
  `email` varchar(320) NOT NULL,
  `domanda_segreta` varchar(80) NOT NULL,
  `risposta_segreta_hash` varchar(72) NOT NULL,
  `data_iscrizione` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `negoziante`
--

INSERT INTO `negoziante` (`id`, `username`, `password_hash`, `email`, `domanda_segreta`, `risposta_segreta_hash`, `data_iscrizione`) VALUES
(1, 'angolotecnologico', '$2y$10$HWhbM/SfmKa5W4Ns2qjxgOSLINCWbm8mH17RToh6OK9PIG/DQjk2C', 'angolo@tecnologico.it', 'Qual era il nome del tuo primo animale domestico?', '$2y$10$5KSawfD2J1qMeGkoMQ2eluz8u6s7qBsQvWOMgvpJ3NxQqJuzczTe2', '2025-12-05 11:02:23'),
(2, 'lacasadimarti', '$2y$10$8Y5XyRAN0Yc00p8Kg4q2OelgxKx7MtZLJU367b6RYIkAn2DuD/p5O', 'martina@casa.it', 'Qual è il tuo cibo preferito?', '$2y$10$o4fSIXYnv8WSDS1d5Tw3BealA6hTm4XAa2xN9V4pSZACKXLQ/zGZy', '2025-12-05 11:17:10'),
(3, 'libreria.delcorso', '$2y$10$Vaw367UoMPS0YbMlsLTllui8V0oa3TbTwHFAZKVkQi1T2qgIo9YZO', 'libreria@delcorso.it', 'Qual è il mese di nascita di tuo fratello/sorella?', '$2y$10$s8a9nQe3NgNuk3qBTnDy.u1f/dSaCuH8eRMYXBOZso2vuQUTu7ys.', '2025-12-05 11:18:31'),
(4, 'nonsolo_sport', '$2y$10$5OTa9KCWrxI.SMJLG4X1puTfRc7h1Wy/ThSOXhYo1nCwAX0S7zoke', 'nonsolo@sport.it', 'In quale città sei nato?', '$2y$10$KqWXLk0Q72RnDr.4cboZ0.t1hIs9O6nYvefqDKl8Eh.zOdGv8syn.', '2025-12-05 11:19:41');

--
-- Trigger `negoziante`
--
DELIMITER $$
CREATE TRIGGER `tr_controlla_email_negoziante_insert` BEFORE INSERT ON `negoziante` FOR EACH ROW BEGIN
    IF EXISTS (SELECT 1 FROM cliente WHERE email = NEW.email) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Questo indirizzo email è già in uso da un cliente.';
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tr_controlla_email_negoziante_update` BEFORE UPDATE ON `negoziante` FOR EACH ROW BEGIN
    IF OLD.email != NEW.email AND EXISTS (SELECT 1 FROM cliente WHERE email = NEW.email) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Questo indirizzo email è già in uso da un cliente.';
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tr_controlla_username_negoziante_insert` BEFORE INSERT ON `negoziante` FOR EACH ROW BEGIN
    IF EXISTS (SELECT 1 FROM cliente WHERE username = NEW.username) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Questo username è già in uso da un cliente.';
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tr_controlla_username_negoziante_update` BEFORE UPDATE ON `negoziante` FOR EACH ROW BEGIN
    IF OLD.username != NEW.username AND EXISTS (SELECT 1 FROM cliente WHERE username = NEW.username) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Questo username è già in uso da un cliente.';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struttura della tabella `ordine`
--

CREATE TABLE `ordine` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_cliente` int(10) UNSIGNED DEFAULT NULL,
  `id_metodo_pagamento` int(10) UNSIGNED DEFAULT NULL,
  `id_indirizzo_spedizione` int(10) UNSIGNED DEFAULT NULL,
  `totale` decimal(10,2) UNSIGNED NOT NULL,
  `data_ordine` datetime NOT NULL DEFAULT current_timestamp(),
  `stato` enum('confermato','spedito','consegnato','annullato') NOT NULL,
  `nome_destinatario` varchar(50) NOT NULL,
  `cognome_destinatario` varchar(50) NOT NULL,
  `telefono_destinatario` varchar(20) NOT NULL,
  `indirizzo_destinatario` varchar(150) NOT NULL,
  `citta_destinatario` varchar(50) NOT NULL,
  `cap_destinatario` varchar(10) NOT NULL,
  `provincia_destinatario` varchar(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `ordine`
--

INSERT INTO `ordine` (`id`, `id_cliente`, `id_metodo_pagamento`, `id_indirizzo_spedizione`, `totale`, `data_ordine`, `stato`, `nome_destinatario`, `cognome_destinatario`, `telefono_destinatario`, `indirizzo_destinatario`, `citta_destinatario`, `cap_destinatario`, `provincia_destinatario`) VALUES
(1, 1, 1, 1, 3.63, '2025-12-05 13:19:17', 'consegnato', 'Giacomo', 'Persiani', '+39 3284567890', 'Piazza dell\'Indipendenza, 7', 'Roma', '00185', 'RM'),
(2, 1, 2, 1, 134.99, '2025-12-05 13:23:51', 'annullato', 'Giacomo', 'Persiani', '+39 3284567890', 'Piazza dell\'Indipendenza, 7', 'Roma', '00185', 'RM'),
(3, 1, 2, 1, 109.99, '2025-12-05 13:25:00', 'consegnato', 'Giacomo', 'Persiani', '+39 3284567890', 'Piazza dell\'Indipendenza, 7', 'Roma', '00185', 'RM'),
(4, 2, 4, 2, 35.96, '2025-12-05 13:39:54', 'consegnato', 'Delia', 'Bellini', '+39 3280987454', 'Via Don Gaetano Boschi 40', 'Pisa', '56126', 'PI'),
(5, 2, 3, 3, 21.54, '2025-12-05 13:44:18', 'consegnato', 'Carla', 'Santini', '+39 3294561237', 'Via Andrea Cirrincione 97/C', 'Palermo', '90143', 'PA');

--
-- Trigger `ordine`
--
DELIMITER $$
CREATE TRIGGER `tr_ripristina_stock_annullamento` AFTER UPDATE ON `ordine` FOR EACH ROW BEGIN
    -- Solo se l'ordine passa da qualsiasi stato ad "annullato"
    IF OLD.stato != 'annullato' AND NEW.stato = 'annullato' THEN
        -- Rimetto la roba in magazzino
        UPDATE prodotto p
        INNER JOIN elemento_ordine eo ON p.id = eo.id_prodotto
        SET p.quantita_disponibile = p.quantita_disponibile + eo.quantita_aggiunta
        WHERE eo.id_ordine = NEW.id AND eo.id_prodotto IS NOT NULL;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struttura della tabella `prodotto`
--

CREATE TABLE `prodotto` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_negoziante` int(10) UNSIGNED NOT NULL,
  `id_categoria` int(10) UNSIGNED NOT NULL,
  `marca` varchar(100) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `prezzo` decimal(10,2) UNSIGNED NOT NULL,
  `descrizione` text DEFAULT NULL,
  `quantita_disponibile` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `prodotto`
--

INSERT INTO `prodotto` (`id`, `id_negoziante`, `id_categoria`, `marca`, `nome`, `prezzo`, `descrizione`, `quantita_disponibile`) VALUES
(1, 1, 2, 'Sony', 'Console PlayStation®5 Pro - 2 TB', 799.99, 'Gioca ai titoli per PS5 con la grafica più sorprendente di sempre su una console PlayStation.', 37),
(2, 1, 3, 'Greed', 'Greed MK2 4K High End Gaming PC', 860.90, 'Il Greed® MK2 4K High End Gaming PC è un desktop da gioco ultra veloce con illuminazione RGB, equipaggiato con processore AMD Ryzen 7 5700X, scheda grafica Nvidia GeForce RTX 5060 con Raytracing, 32 GB di RAM e un SSD da 1 TB.', 19),
(3, 2, 16, 'Brigros', 'Albero di Natale', 59.00, 'Albero di Natale artificiale effetto realistico innevato (150cm) in Pvc ignifugo, con aghi di pino spessi e rami a gancio, con borsa e base in metallo', 100),
(4, 2, 26, 'Sol de Janeiro', 'Sol de Janeiro Jet Set (Bum Bum)', 30.99, 'Tre Skincare-Stars in un pratico set da viaggio con il profumo celestiale di Cheirosa \'62, per tutti coloro che non vogliono fare a meno delle cure di Beauty quando viaggiano', 45),
(5, 2, 28, 'CeraVe', 'CeraVe lozione detergente idratante', 10.77, 'Detergente idratante 236ml per pelli da normali a secche, pulizia delicata per il corpo e il viso, con la presenza di 3 ceramidi essenziali e ialurone', 198),
(6, 2, 28, 'Carehabi', 'Tappi per le orecchie', 8.99, '60 paia di tappi auricolari riutilizzabili con protezione acustica superiore (SNR 38 dB). Ideali per garantire la protezione dell\'udito in ambienti rumorosi o per favorire un sonno profondo e indisturbato.', 296),
(7, 3, 40, 'Mondadori', 'Benedetta Rossi - La mia cucina per tutti. Oltre 150 ricette facili e gustose per ogni esigenza', 22.90, 'Tante ricette facili e gustose proposte anche con varianti senza glutine, senza uova e senza lattosio. Una cucina inclusiva e creativa che tiene conto dei bisogni, delle abitudini e delle preferenze di ognuno', 56),
(8, 4, 47, 'DAINESE', 'DAINESE Air Frame D1 Tex Jacket', 190.00, 'Quando le giornate diventano più calde, non c\'è niente di meglio della giacca Air Frame D1 in tessuto Quick Dry in cui sono inseriti ampi pannelli in mesh che lasciano davvero passare l\'aria', 15),
(9, 4, 55, 'Puma', 'Scarpe Puma Smash V2 L', 36.40, 'La sneaker unisex Puma Smash v2 L è una interpretazione del leggendario Puma Smash; Ispirata al tennis, la sneaker convince con una migliore calzata e una tomaia in morbida pelle', 93),
(10, 2, 35, 'PalNests', 'Lettiera per gatti', 109.99, 'Lettiera per gatti in acciaio inox XXL con coperchio ribaltabile a 105° e paletta, con deodorante incluso', 9),
(11, 2, 19, 'Bosch', 'Bosch Tagliaerba/Tosaerba a filo', 134.99, 'L\'efficiente motore offre prestazioni di taglio insuperabili\r\nImpugnatura per il trasporto, per sollevare la macchina leggera più facilmente. Bordi precisi garantiscono tagli perfetti lungo i bordi e in prossimità dei muri, con impugnature pieghevoli per riporre la macchina in spazi stretti', 5),
(12, 4, 49, 'Eastpak', 'Valigia Eastpak TRANVERZ L', 147.99, 'Trolley da viaggio per lunghi viaggi e soggiorni prolungati con lucchetto TSA integrato con codice a tre cifre', 21),
(13, 3, 39, 'Pubblicazione indipendente', 'Salvatore Sanfilippo - Storie di informatici illustri: Richard Andrew Fox', 3.63, 'Nasce a Wolverhampton all’alba del 15 Agosto del 1970. La madre è coordinatrice del personale di terra presso l’aeroporto di Birmingham; è al nono mese di gravidanza, ma gode di una salute eccellente. Secondo i calcoli mancano ancora due settimane, così, d’accordo col suo supervisore, e forte del benestare del medico, continua a lavorare per metà giornata, evitando ogni compito gravoso per il fisico. Alle 16:42 del giorno prima, Susan controlla il biglietto di un passeggero British Overseas: è diretto a Heathrow, con destinazione finale Chicago. Sente un’altra fitta: non ha più dubbi.', 17);

--
-- Trigger `prodotto`
--
DELIMITER $$
CREATE TRIGGER `tr_aggiorna_carrello_after_prezzo_update` AFTER UPDATE ON `prodotto` FOR EACH ROW BEGIN
    IF OLD.prezzo != NEW.prezzo THEN
        UPDATE carrello c
        SET 
            totale_prezzo = (
                SELECT COALESCE(SUM(ec.quantita_aggiunta * p.prezzo), 0.00) 
                FROM elemento_carrello ec 
                JOIN prodotto p ON ec.id_prodotto = p.id 
                WHERE ec.id_carrello = c.id
            )
        WHERE c.id IN (
            SELECT DISTINCT ec.id_carrello 
            FROM elemento_carrello ec 
            WHERE ec.id_prodotto = NEW.id
        );
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struttura della tabella `recensione`
--

CREATE TABLE `recensione` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_cliente` int(10) UNSIGNED NOT NULL,
  `id_prodotto` int(10) UNSIGNED NOT NULL,
  `valutazione` tinyint(3) UNSIGNED NOT NULL CHECK (`valutazione` >= 0 and `valutazione` <= 5),
  `testo` text NOT NULL,
  `data_recensione` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `recensione`
--

INSERT INTO `recensione` (`id`, `id_cliente`, `id_prodotto`, `valutazione`, `testo`, `data_recensione`) VALUES
(1, 1, 13, 5, 'Lettura molto scorrevole. Non riesci ad interrompere la lettura e molto probabilmente lo leggerai tutto d\'un fiato.', '2025-12-05 13:21:19'),
(2, 1, 10, 3, 'La lettiera è ottima, la spedizione però ci ha messo un\'eternità!!', '2025-12-05 13:26:40'),
(3, 2, 6, 5, 'Perfetti!!! Spedizione rapidissima!!!!\r\n\r\nFinalmente posso dormire tranquilla perché il mio coinquilino quando russa lo sente tutto il quartiere.', '2025-12-05 13:45:22'),
(4, 2, 5, 4, 'La confezione di carta è arrivata ammaccata. Il contenitore con dentro la lozione era intatto. Fate attenzione coi corrieri.', '2025-12-05 13:46:51');

--
-- Trigger `recensione`
--
DELIMITER $$
CREATE TRIGGER `tr_recensioni_solo_prodotti_acquistati` BEFORE INSERT ON `recensione` FOR EACH ROW BEGIN
    IF NOT EXISTS (
        SELECT *
        FROM elemento_ordine eo
        INNER JOIN ordine o ON eo.id_ordine = o.id
        WHERE o.id_cliente = NEW.id_cliente
          AND eo.id_prodotto = NEW.id_prodotto
          AND o.stato = 'consegnato'
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Puoi recensire solo prodotti che hai acquistato (e che ti sono stati consegnati)';
    END IF;
END
$$
DELIMITER ;

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `carrello`
--
ALTER TABLE `carrello`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_cliente` (`id_cliente`);

--
-- Indici per le tabelle `categoria`
--
ALTER TABLE `categoria`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_categoria_padre` (`id_categoria_padre`);

--
-- Indici per le tabelle `cliente`
--
ALTER TABLE `cliente`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indici per le tabelle `elemento_carrello`
--
ALTER TABLE `elemento_carrello`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_carrello_prodotto` (`id_carrello`,`id_prodotto`),
  ADD KEY `fk_id_prodotto_elemento` (`id_prodotto`);

--
-- Indici per le tabelle `elemento_ordine`
--
ALTER TABLE `elemento_ordine`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_id_ordine_elemento` (`id_ordine`),
  ADD KEY `fk_id_negoziante_elemento` (`id_negoziante`),
  ADD KEY `fk_id_prodotto_elemento_carrello` (`id_prodotto`);

--
-- Indici per le tabelle `foto_prodotto`
--
ALTER TABLE `foto_prodotto`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_id_foto_prodotto` (`id_prodotto`);

--
-- Indici per le tabelle `foto_recensione`
--
ALTER TABLE `foto_recensione`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_id_foto_recensione` (`id_recensione`);

--
-- Indici per le tabelle `indirizzo_spedizione`
--
ALTER TABLE `indirizzo_spedizione`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_id_cliente_indirizzo` (`id_cliente`);

--
-- Indici per le tabelle `metodo_pagamento`
--
ALTER TABLE `metodo_pagamento`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_proprietario_pagamento` (`id_cliente`);

--
-- Indici per le tabelle `negoziante`
--
ALTER TABLE `negoziante`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indici per le tabelle `ordine`
--
ALTER TABLE `ordine`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_id_cliente_ordine` (`id_cliente`),
  ADD KEY `fk_id_pagamento_ordine` (`id_metodo_pagamento`),
  ADD KEY `fk_id_indirizzo_ordine` (`id_indirizzo_spedizione`);

--
-- Indici per le tabelle `prodotto`
--
ALTER TABLE `prodotto`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_negoziante_prodotto` (`id_negoziante`),
  ADD KEY `fk_categoria_prodotto` (`id_categoria`);

--
-- Indici per le tabelle `recensione`
--
ALTER TABLE `recensione`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_recensione_cliente_prodotto` (`id_cliente`,`id_prodotto`),
  ADD KEY `fk_prodotto_recensione` (`id_prodotto`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `carrello`
--
ALTER TABLE `carrello`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT per la tabella `categoria`
--
ALTER TABLE `categoria`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT per la tabella `cliente`
--
ALTER TABLE `cliente`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT per la tabella `elemento_carrello`
--
ALTER TABLE `elemento_carrello`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT per la tabella `elemento_ordine`
--
ALTER TABLE `elemento_ordine`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT per la tabella `foto_prodotto`
--
ALTER TABLE `foto_prodotto`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT per la tabella `foto_recensione`
--
ALTER TABLE `foto_recensione`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT per la tabella `indirizzo_spedizione`
--
ALTER TABLE `indirizzo_spedizione`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT per la tabella `metodo_pagamento`
--
ALTER TABLE `metodo_pagamento`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT per la tabella `negoziante`
--
ALTER TABLE `negoziante`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT per la tabella `ordine`
--
ALTER TABLE `ordine`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT per la tabella `prodotto`
--
ALTER TABLE `prodotto`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT per la tabella `recensione`
--
ALTER TABLE `recensione`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `carrello`
--
ALTER TABLE `carrello`
  ADD CONSTRAINT `fk_proprietario_carrello` FOREIGN KEY (`id_cliente`) REFERENCES `cliente` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `categoria`
--
ALTER TABLE `categoria`
  ADD CONSTRAINT `fk_categoria_padre` FOREIGN KEY (`id_categoria_padre`) REFERENCES `categoria` (`id`) ON UPDATE CASCADE;

--
-- Limiti per la tabella `elemento_carrello`
--
ALTER TABLE `elemento_carrello`
  ADD CONSTRAINT `fk_id_carrello_elemento_carrello` FOREIGN KEY (`id_carrello`) REFERENCES `carrello` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_id_prodotto_elemento` FOREIGN KEY (`id_prodotto`) REFERENCES `prodotto` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `elemento_ordine`
--
ALTER TABLE `elemento_ordine`
  ADD CONSTRAINT `fk_id_negoziante_elemento` FOREIGN KEY (`id_negoziante`) REFERENCES `negoziante` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_id_ordine_elemento` FOREIGN KEY (`id_ordine`) REFERENCES `ordine` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_id_prodotto_elemento_carrello` FOREIGN KEY (`id_prodotto`) REFERENCES `prodotto` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Limiti per la tabella `foto_prodotto`
--
ALTER TABLE `foto_prodotto`
  ADD CONSTRAINT `fk_id_foto_prodotto` FOREIGN KEY (`id_prodotto`) REFERENCES `prodotto` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `foto_recensione`
--
ALTER TABLE `foto_recensione`
  ADD CONSTRAINT `fk_id_foto_recensione` FOREIGN KEY (`id_recensione`) REFERENCES `recensione` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `indirizzo_spedizione`
--
ALTER TABLE `indirizzo_spedizione`
  ADD CONSTRAINT `fk_id_cliente_indirizzo` FOREIGN KEY (`id_cliente`) REFERENCES `cliente` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `metodo_pagamento`
--
ALTER TABLE `metodo_pagamento`
  ADD CONSTRAINT `fk_proprietario_pagamento` FOREIGN KEY (`id_cliente`) REFERENCES `cliente` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `ordine`
--
ALTER TABLE `ordine`
  ADD CONSTRAINT `fk_id_cliente_ordine` FOREIGN KEY (`id_cliente`) REFERENCES `cliente` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_id_indirizzo_ordine` FOREIGN KEY (`id_indirizzo_spedizione`) REFERENCES `indirizzo_spedizione` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `fk_id_pagamento_ordine` FOREIGN KEY (`id_metodo_pagamento`) REFERENCES `metodo_pagamento` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;

--
-- Limiti per la tabella `prodotto`
--
ALTER TABLE `prodotto`
  ADD CONSTRAINT `fk_categoria_prodotto` FOREIGN KEY (`id_categoria`) REFERENCES `categoria` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_negoziante_prodotto` FOREIGN KEY (`id_negoziante`) REFERENCES `negoziante` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `recensione`
--
ALTER TABLE `recensione`
  ADD CONSTRAINT `fk_cliente_recensione` FOREIGN KEY (`id_cliente`) REFERENCES `cliente` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_prodotto_recensione` FOREIGN KEY (`id_prodotto`) REFERENCES `prodotto` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
