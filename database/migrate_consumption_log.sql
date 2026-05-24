-- Preserve context in consumption_log so history survives inventory deletions
ALTER TABLE consumption_log
    ADD COLUMN item_id        INT UNSIGNED     DEFAULT NULL AFTER inventory_id,
    ADD COLUMN item_name      VARCHAR(200)     DEFAULT NULL AFTER item_id,
    ADD COLUMN room_name      VARCHAR(100)     DEFAULT NULL AFTER item_name,
    ADD COLUMN container_name VARCHAR(100)     DEFAULT NULL AFTER room_name,
    ADD FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE SET NULL;
