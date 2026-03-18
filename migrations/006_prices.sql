CREATE TABLE IF NOT EXISTS prices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id VARCHAR(255) NOT NULL,
    amount INT NOT NULL,
    currency_label VARCHAR(10) NOT NULL,
    currency_symbol VARCHAR(10) NOT NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_product_currency (product_id, currency_label)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
