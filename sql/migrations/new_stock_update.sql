ALTER TABLE `products_in_container`
ADD COLUMN `to_stock` INTEGER NOT NULL DEFAULT 1;

INSERT INTO `stocks` (`ID`, `name`) VALUES (3, 'Galpão 2')