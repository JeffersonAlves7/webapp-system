-- NOVA TRIGGER
DELIMITER / /

CREATE TRIGGER after_product_insert
AFTER INSERT ON products
FOR EACH ROW
BEGIN
    -- Inserir na tabela quantity_in_stock para stock_ID 1
    INSERT INTO
        quantity_in_stock (product_ID, stock_ID)
    VALUES (NEW.ID, 1);
    
    -- Inserir na tabela quantity_in_stock para stock_ID 2
    INSERT INTO
        quantity_in_stock (product_ID, stock_ID)
    VALUES (NEW.ID, 2);
END;

/ /

DELIMITER;

-- products_in_stock: ON DELETE CASCADE

ALTER TABLE `products_in_container`
DROP FOREIGN KEY `products_in_container_ibfk_1`;

ALTER TABLE `products_in_container`
ADD CONSTRAINT `products_in_container_ibfk_1` FOREIGN KEY (`product_ID`) REFERENCES `products` (`ID`) ON DELETE CASCADE;

-- quantity_in_stock: novas colunas da tabela
ALTER TABLE quantity_in_stock
ADD COLUMN last_entries VARCHAR(255) DEFAULT NULL,
ADD COLUMN entry_quantity INT DEFAULT 0;

-- quantity_in_stock: ON DELETE CASCADE
ALTER TABLE quantity_in_stock
DROP FOREIGN KEY quantity_in_stock_ibfk_1;

ALTER TABLE quantity_in_stock
ADD CONSTRAINT quantity_in_stock_ibfk_1 FOREIGN KEY (product_ID) REFERENCES products (ID) ON DELETE CASCADE;