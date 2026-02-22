-- Create invoices table
CREATE TABLE IF NOT EXISTS invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_number VARCHAR(50) UNIQUE NOT NULL,
    client_id INT NOT NULL,
    psychologist_id INT NOT NULL,
    service_name VARCHAR(255) NOT NULL,
    service_price DECIMAL(10,2) NOT NULL,
    total_payment DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(100) NOT NULL,
    invoice_date DATE NOT NULL,
    due_date DATE,
    status ENUM('pending', 'paid', 'overdue') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (client_id) REFERENCES users(user_id),
    FOREIGN KEY (psychologist_id) REFERENCES users(user_id)
);

-- Create invoice_items table for detailed services
CREATE TABLE IF NOT EXISTS invoice_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT NOT NULL,
    item_name VARCHAR(255) NOT NULL,
    item_description TEXT,
    quantity INT DEFAULT 1,
    unit_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
);

-- Add index for invoice_number for faster lookups
CREATE INDEX idx_invoice_number ON invoices(invoice_number);
CREATE INDEX idx_client_id ON invoices(client_id);
CREATE INDEX idx_psychologist_id ON invoices(psychologist_id);
