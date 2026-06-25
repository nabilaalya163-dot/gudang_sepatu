-- ============================================================
-- Database: db_inventaris_sepatu
-- Sistem Inventaris Gudang Sepatu
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS `db_inventaris_sepatu`
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_general_ci;

USE `db_inventaris_sepatu`;

-- Drop tables in reverse FK order
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `detail_barang_keluar`;
DROP TABLE IF EXISTS `detail_barang_masuk`;
DROP TABLE IF EXISTS `barang_keluar`;
DROP TABLE IF EXISTS `barang_masuk`;
DROP TABLE IF EXISTS `sepatu`;
DROP TABLE IF EXISTS `supplier`;
DROP TABLE IF EXISTS `user`;
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- TABEL SEPATU
-- ============================================================
CREATE TABLE `sepatu` (
  `id_sepatu`     CHAR(6)        NOT NULL,
  `nama_seri`     VARCHAR(100)   NOT NULL,
  `nama_brand`    VARCHAR(100)   NOT NULL,
  `kategori`      VARCHAR(100)   NOT NULL,
  `harga_beli`    DECIMAL(15,2)  DEFAULT 0.00,
  `harga_jual`    DECIMAL(15,2)  DEFAULT 0.00,
  `warna`         VARCHAR(50)    DEFAULT NULL,
  `ukuran`        INT(11)        DEFAULT NULL,
  `stok`          INT(11)        DEFAULT 0,
  `status_sepatu` ENUM('aktif','nonaktif') DEFAULT 'aktif',
  `created_at`    TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_sepatu`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `sepatu` (`id_sepatu`,`nama_seri`,`nama_brand`,`kategori`,`harga_beli`,`harga_jual`,`warna`,`ukuran`,`stok`,`status_sepatu`) VALUES
('SEP101','Air Jordan 1 Low','Nike','Sneakers',1500000,2200000,'Bred Toe (Merah Hitam)',38,20,'aktif'),
('SEP102','Air Force 1','Nike','Sneakers',1200000,1800000,'Triple White (Putih Polos)',39,25,'aktif'),
('SEP103','Nike Dunk Low','Nike','Sneakers',1400000,2100000,'Panda (Hitam Putih)',40,27,'aktif'),
('SEP201','Samba OG','Adidas','Sneakers',1100000,1700000,'White Gum (Putih)',37,20,'aktif'),
('SEP202','Stan Smith','Adidas','Casual',1000000,1500000,'White Green (Putih Hijau)',40,25,'aktif'),
('SEP301','Ventela Classic White','Ventela','Casual',250000,450000,'Full White (Putih)',39,30,'aktif'),
('SEP302','Ventela Public','Ventela','Casual',270000,480000,'Black/White (Hitam Strip Putih)',40,40,'aktif');

-- ============================================================
-- TABEL SUPPLIER
-- ============================================================
CREATE TABLE `supplier` (
  `id_supplier`   CHAR(6)       NOT NULL,
  `nama_supplier` VARCHAR(100)  NOT NULL,
  `email`         VARCHAR(100)  DEFAULT NULL,
  `created_at`    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_supplier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `supplier` (`id_supplier`,`nama_supplier`,`email`) VALUES
('SUP101','PT Nike Indonesia','nike@supplier.id'),
('SUP201','PT Adidas Indonesia','adidas@supplier.id'),
('SUP301','CV Ventela Official','ventela@supplier.id');

-- ============================================================
-- TABEL USER
-- ============================================================
CREATE TABLE `user` (
  `id_user`     CHAR(6)       NOT NULL,
  `nama_user`   VARCHAR(100)  NOT NULL,
  `posisi`      VARCHAR(50)   NOT NULL,
  `status_user` ENUM('aktif','nonaktif') DEFAULT 'aktif',
  `created_at`  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `user` (`id_user`,`nama_user`,`posisi`,`status_user`) VALUES
('USR101','Nabila Alya','Admin','aktif'),
('USR201','Balqis Fathimatuzzahra','Kepala Gudang','aktif'),
('USR301','Raisah Farah','Staff Gudang','aktif'),
('USR302','Amadea Fasya','Staff Gudang','aktif'),
('USR303','Fatimah Azahra','Staff Gudang','aktif');

-- ============================================================
-- TABEL BARANG MASUK
-- ============================================================
CREATE TABLE `barang_masuk` (
  `id_masuk`     CHAR(10)  NOT NULL,
  `id_supplier`  CHAR(6)   NOT NULL,
  `id_user`      CHAR(6)   NOT NULL,
  `tanggal_masuk` DATE     NOT NULL,
  `status_masuk`  ENUM('pending','diterima','dibatalkan') DEFAULT 'pending',
  `created_at`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_masuk`),
  CONSTRAINT `bm_ibfk_1` FOREIGN KEY (`id_supplier`) REFERENCES `supplier` (`id_supplier`),
  CONSTRAINT `bm_ibfk_2` FOREIGN KEY (`id_user`)     REFERENCES `user`     (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `barang_masuk` (`id_masuk`,`id_supplier`,`id_user`,`tanggal_masuk`,`status_masuk`) VALUES
('BM24010001','SUP101','USR201','2024-01-05','diterima'),
('BM24010002','SUP101','USR201','2024-01-05','diterima'),
('BM24010003','SUP201','USR201','2024-01-10','diterima'),
('BM24010004','SUP201','USR201','2024-01-10','diterima');

-- ============================================================
-- TABEL DETAIL BARANG MASUK
-- ============================================================
CREATE TABLE `detail_barang_masuk` (
  `id_masuk`           CHAR(10)      NOT NULL,
  `id_sepatu`          CHAR(6)       NOT NULL,
  `jumlah_masuk`       INT(11)       NOT NULL DEFAULT 0,
  `harga_satuan_masuk` DECIMAL(15,2) DEFAULT 0.00,
  PRIMARY KEY (`id_masuk`,`id_sepatu`),
  CONSTRAINT `dbm_ibfk_1` FOREIGN KEY (`id_masuk`)  REFERENCES `barang_masuk` (`id_masuk`),
  CONSTRAINT `dbm_ibfk_2` FOREIGN KEY (`id_sepatu`) REFERENCES `sepatu`       (`id_sepatu`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `detail_barang_masuk` (`id_masuk`,`id_sepatu`,`jumlah_masuk`,`harga_satuan_masuk`) VALUES
('BM24010001','SEP101',5,1500000.00),
('BM24010002','SEP103',5,1400000.00),
('BM24010003','SEP201',5,1100000.00),
('BM24010004','SEP202',5,1000000.00);

-- ============================================================
-- TABEL BARANG KELUAR
-- ============================================================
CREATE TABLE `barang_keluar` (
  `id_keluar`        CHAR(10)     NOT NULL,
  `id_user`          CHAR(6)      NOT NULL,
  `tanggal_keluar`   DATE         NOT NULL,
  `nama_toko_tujuan` VARCHAR(100) NOT NULL,
  `status_keluar`    ENUM('pending','diproses','selesai','dibatalkan') DEFAULT 'pending',
  `created_at`       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_keluar`),
  CONSTRAINT `bk_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `barang_keluar` (`id_keluar`,`id_user`,`tanggal_keluar`,`nama_toko_tujuan`,`status_keluar`) VALUES
('BK24010001','USR302','2024-01-08','Toko Sepatu Maju Jaya','selesai'),
('BK24010002','USR303','2024-01-12','Sneakers Hub Store','selesai'),
('BK24010003','USR301','2024-01-15','Sole Destination','selesai'),
('BK24010004','USR302','2024-01-20','Footlocker 23 Paskal','selesai'),
('BK24010005','USR303','2024-01-31','Sneakerzone','selesai');

-- ============================================================
-- TABEL DETAIL BARANG KELUAR
-- ============================================================
CREATE TABLE `detail_barang_keluar` (
  `id_keluar`           CHAR(10)      NOT NULL,
  `id_sepatu`           CHAR(6)       NOT NULL,
  `jumlah_keluar`       INT(11)       NOT NULL DEFAULT 0,
  `harga_satuan_keluar` DECIMAL(15,2) DEFAULT 0.00,
  PRIMARY KEY (`id_keluar`,`id_sepatu`),
  CONSTRAINT `dbk_ibfk_1` FOREIGN KEY (`id_keluar`) REFERENCES `barang_keluar` (`id_keluar`),
  CONSTRAINT `dbk_ibfk_2` FOREIGN KEY (`id_sepatu`) REFERENCES `sepatu`        (`id_sepatu`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `detail_barang_keluar` (`id_keluar`,`id_sepatu`,`jumlah_keluar`,`harga_satuan_keluar`) VALUES
('BK24010001','SEP101',2,2200000.00),
('BK24010002','SEP102',2,1800000.00),
('BK24010003','SEP201',1,1700000.00),
('BK24010004','SEP103',2,2100000.00),
('BK24010005','SEP301',3,450000.00);
