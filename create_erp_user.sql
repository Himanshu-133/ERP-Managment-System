-- create_erp_user.sql
-- Run this as a MySQL root user to create the application user and grant privileges

CREATE USER IF NOT EXISTS 'erp_user'@'localhost' IDENTIFIED BY 'Himanshu@1310';
CREATE USER IF NOT EXISTS 'erp_user'@'127.0.0.1' IDENTIFIED BY 'Himanshu@1310';
GRANT ALL PRIVILEGES ON `erp_system`.* TO 'erp_user'@'localhost';
GRANT ALL PRIVILEGES ON `erp_system`.* TO 'erp_user'@'127.0.0.1';
FLUSH PRIVILEGES;

-- End of file
