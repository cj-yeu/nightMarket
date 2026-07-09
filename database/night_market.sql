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

CREATE TABLE IF NOT EXISTS reviews (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    market_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    visit_date DATE,
    status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (market_id) REFERENCES night_markets(market_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS review_images (
    review_image_id INT AUTO_INCREMENT PRIMARY KEY,
    review_id INT NOT NULL,
    image VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (review_id) REFERENCES reviews(review_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS visit_plans (
    plan_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    plan_title VARCHAR(150) NOT NULL,
    plan_date DATE NOT NULL,
    notes TEXT,
    status ENUM('planned', 'completed', 'cancelled') NOT NULL DEFAULT 'planned',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS visit_plan_items (
    plan_item_id INT AUTO_INCREMENT PRIMARY KEY,
    plan_id INT NOT NULL,
    market_id INT NOT NULL,
    planned_time TIME,
    sequence_no INT DEFAULT 1,
    notes TEXT,
    FOREIGN KEY (plan_id) REFERENCES visit_plans(plan_id) ON DELETE CASCADE,
    FOREIGN KEY (market_id) REFERENCES night_markets(market_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS social_media_data (
    social_data_id INT AUTO_INCREMENT PRIMARY KEY,
    market_id INT,
    platform ENUM('Facebook', 'Instagram', 'TikTok', 'Xiaohongshu', 'Other') NOT NULL,
    post_url VARCHAR(255),
    post_title VARCHAR(150),
    post_content TEXT,
    extracted_keywords VARCHAR(255),
    mentioned_food VARCHAR(150),
    image VARCHAR(255),
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    added_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (market_id) REFERENCES night_markets(market_id) ON DELETE SET NULL,
    FOREIGN KEY (added_by) REFERENCES users(user_id) ON DELETE SET NULL
);