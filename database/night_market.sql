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