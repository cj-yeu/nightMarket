CREATE TABLE IF NOT EXISTS night_markets (
    market_id INT AUTO_INCREMENT PRIMARY KEY,
    market_name VARCHAR(150) NOT NULL,
    description TEXT,
    address TEXT NOT NULL,
    area VARCHAR(100) NOT NULL,
    state VARCHAR(100) NOT NULL DEFAULT 'Selangor',
    opening_time TIME,
    closing_time TIME,
    image VARCHAR(255),
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(user_id)
);

CREATE TABLE IF NOT EXISTS market_operating_days (
    operating_day_id INT AUTO_INCREMENT PRIMARY KEY,
    market_id INT NOT NULL,
    day_of_week ENUM(
        'Monday',
        'Tuesday',
        'Wednesday',
        'Thursday',
        'Friday',
        'Saturday',
        'Sunday'
    ) NOT NULL,
    FOREIGN KEY (market_id) REFERENCES night_markets(market_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS stalls (
    stall_id INT AUTO_INCREMENT PRIMARY KEY,
    market_id INT NOT NULL,
    stall_name VARCHAR(150) NOT NULL,
    category VARCHAR(100),
    description TEXT,
    image VARCHAR(255),
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (market_id) REFERENCES night_markets(market_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS foods (
    food_id INT AUTO_INCREMENT PRIMARY KEY,
    stall_id INT NOT NULL,
    food_name VARCHAR(150) NOT NULL,
    description TEXT,
    price_range VARCHAR(50),
    is_must_try TINYINT(1) NOT NULL DEFAULT 0,
    image VARCHAR(255),
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (stall_id) REFERENCES stalls(stall_id) ON DELETE CASCADE
);