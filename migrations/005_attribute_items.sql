CREATE TABLE IF NOT EXISTS attribute_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    attribute_set_id INT NOT NULL,
    item_id VARCHAR(255) NOT NULL,
    display_value VARCHAR(255) NOT NULL,
    value VARCHAR(255) NOT NULL,
    FOREIGN KEY (attribute_set_id) REFERENCES attribute_sets(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
