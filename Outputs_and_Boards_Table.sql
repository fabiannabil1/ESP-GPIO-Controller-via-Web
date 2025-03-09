-- Membuat tabel Boards
CREATE TABLE Boards (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    board INT,
    last_request TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO Boards (board) VALUES (1);

-- Membuat tabel Outputs dengan kolom 'type' langsung didefinisikan
CREATE TABLE Outputs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(64),
    board INT,
    gpio INT,
    state INT,
    type ENUM('input', 'output') NOT NULL DEFAULT 'output'
);

INSERT INTO Outputs (name, board, gpio, state) VALUES ('Built-in LED', 1, 2, 0);
