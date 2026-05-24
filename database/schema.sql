-- Home Inventory System
-- All weights stored in grams

CREATE DATABASE IF NOT EXISTS stocksense CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE stocksense;

CREATE TABLE users (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100) NOT NULL,
    email      VARCHAR(255) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE rooms (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100) NOT NULL,
    qr_code    VARCHAR(64)  NOT NULL UNIQUE,  -- UUID used as QR payload
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE containers (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    room_id    INT UNSIGNED NOT NULL,
    name       VARCHAR(100) NOT NULL,
    type       ENUM('fridge','freezer','shelf','cabinet','drawer','basket','other') DEFAULT 'other',
    qr_code    VARCHAR(64)  NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
);

-- Master catalog of item types (populated on first scan / manual add)
CREATE TABLE items (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(200) NOT NULL,
    product_barcode VARCHAR(64)  DEFAULT NULL UNIQUE,  -- EAN/UPC from packaging
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Actual stock entries
CREATE TABLE inventory (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    item_id        INT UNSIGNED NOT NULL,
    room_id        INT UNSIGNED NOT NULL,
    container_id   INT UNSIGNED DEFAULT NULL,  -- NULL = placed directly in room
    quantity_grams INT UNSIGNED NOT NULL,
    arrival_date   DATE         NOT NULL,
    expiry_date    DATE         DEFAULT NULL,
    notes          TEXT         DEFAULT NULL,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (item_id)      REFERENCES items(id),
    FOREIGN KEY (room_id)      REFERENCES rooms(id),
    FOREIGN KEY (container_id) REFERENCES containers(id) ON DELETE SET NULL
);

-- Consumption log (deductions from inventory)
CREATE TABLE consumption_log (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    inventory_id   INT UNSIGNED NOT NULL,
    user_id        INT UNSIGNED NOT NULL,
    quantity_grams INT UNSIGNED NOT NULL,
    consumed_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (inventory_id) REFERENCES inventory(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)      REFERENCES users(id)
);

-- Indexes for common lookups
CREATE INDEX idx_inventory_room      ON inventory(room_id);
CREATE INDEX idx_inventory_container ON inventory(container_id);
CREATE INDEX idx_inventory_expiry    ON inventory(expiry_date);
CREATE INDEX idx_containers_room     ON containers(room_id);
