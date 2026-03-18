CREATE TABLE IF NOT EXISTS attribute_sets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id VARCHAR(255) NOT NULL,
    attribute_id VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    type ENUM('text', 'swatch') NOT NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_product_attribute (product_id, attribute_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
