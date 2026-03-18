CREATE TABLE IF NOT EXISTS products (
    id VARCHAR(255) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    in_stock TINYINT(1) NOT NULL DEFAULT 1,
    description TEXT,
    category_id INT NOT NULL,
    brand VARCHAR(255),
    type VARCHAR(50) NOT NULL DEFAULT 'simple',
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
