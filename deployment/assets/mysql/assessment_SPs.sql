-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Host: db8
-- Gegenereerd op: 19 mrt 2019 om 10:49
-- Serverversie: 8.0.14
-- PHP-versie: 7.2.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

USE assessment;
--
-- Database: `assessment`
--

-- --------------------------------------------------------
DROP PROCEDURE IF EXISTS get_equipment_stock;
DELIMITER $$
CREATE PROCEDURE `get_equipment_stock`(IN `equipment_id` INT, OUT `equipment_qty` INT)
BEGIN
    SELECT stock INTO equipment_qty FROM equipment WHERE id = equipment_id;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS get_planned_equipments_per_date;
DELIMITER $$
CREATE PROCEDURE get_planned_equipments_per_date(IN `date` DATE, IN `equipment_id` INT, OUT `planned_qty` INT)
BEGIN
    SELECT COALESCE(SUM(quantity), 0)
    INTO planned_qty
    FROM planning
    WHERE equipment = equipment_id
      AND `start` <= date
      AND `end` >= date;
END$$
DELIMITER ;


DROP PROCEDURE IF EXISTS get_equipment_timeline;
DELIMITER $$
CREATE PROCEDURE get_equipment_timeline(IN `start_date` DATE, IN `end_date` DATE, IN `equipment_id` INT)
BEGIN
    /* Temp date to loop through date period */
    DECLARE _date DATE;

    /* Get available stock of requested equipment */
    DECLARE stock INT;

    /* Get reserved item count per date */
    DECLARE planned_qty INT;

    /* Calculate available quantity */
    DECLARE available_qty INT;

    /* Declare a table to store the results */
    DROP TEMPORARY TABLE IF EXISTS availability_table;
    CREATE TEMPORARY TABLE availability_table
    (
        id           INT,
        date            DATE,
        stock           INT,
        planned_qty     INT,
        available_qty   INT
    );
    SET _date = start_date;

    CALL get_equipment_stock(equipment_id, stock);

    check_equipment_availability : WHILE _date <= end_date DO
        CALL get_planned_equipments_per_date(_date, equipment_id, planned_qty);

        SET available_qty = stock - planned_qty;

        /* Insert the result into the temporary table */
        INSERT INTO availability_table (id, date, stock, planned_qty, available_qty)
        VALUES (equipment_id, _date, stock, planned_qty, available_qty);

        SET _date = DATE_ADD(_date, INTERVAL 1 DAY);
    END WHILE;

    SELECT * FROM availability_table;
END$$
DELIMITER ;


# Indexing
ALTER TABLE `equipment` ADD INDEX idx_equipment_stock (`stock`);
ALTER TABLE `planning` ADD INDEX idx_planning_equipment (`equipment`);
ALTER TABLE `planning` ADD INDEX idx_planning_quantity (`quantity`);
ALTER TABLE `planning` ADD INDEX idx_planning_start_end (`start`, `end`);
