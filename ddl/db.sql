-- -------------------------------
-- 1) MASTER DATA
-- -------------------------------
CREATE TABLE IF NOT EXISTS master_uom (
  uom_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS master_products (
  product_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  sku_code VARCHAR(100) NOT NULL UNIQUE,
  name VARCHAR(255) NOT NULL,
  rotation ENUM('FIFO','FEFO') NOT NULL DEFAULT 'FIFO',
  base_uom_id BIGINT NOT NULL,
  barcode VARCHAR(100),
  is_pack_free BOOLEAN NOT NULL DEFAULT FALSE,
  is_active BOOLEAN NOT NULL DEFAULT TRUE,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_mp_base_uom FOREIGN KEY (base_uom_id) REFERENCES master_uom(uom_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS product_uom (
  product_uom_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  product_id BIGINT NOT NULL,
  uom_id BIGINT NOT NULL,
  factor_to_base DECIMAL(18,6) NOT NULL CHECK (factor_to_base > 0),
  is_default BOOLEAN NOT NULL DEFAULT FALSE,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_product_uom (product_id, uom_id),
  CONSTRAINT fk_pu_product FOREIGN KEY (product_id) REFERENCES master_products(product_id) ON DELETE CASCADE,
  CONSTRAINT fk_pu_uom FOREIGN KEY (uom_id) REFERENCES master_uom(uom_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS partners (
  partner_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  role ENUM('vendor','customer','warehouse') NOT NULL,
  is_active BOOLEAN NOT NULL DEFAULT TRUE,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS partner_products (
  partner_product_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  partner_id BIGINT NOT NULL,
  product_id BIGINT NOT NULL,
  customer_sku VARCHAR(100),
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_partner_product (partner_id, product_id),
  CONSTRAINT fk_pp_partner FOREIGN KEY (partner_id) REFERENCES partners(partner_id) ON DELETE CASCADE,
  CONSTRAINT fk_pp_product FOREIGN KEY (product_id) REFERENCES master_products(product_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS warehouses (
  warehouse_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  address TEXT,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS locations (
  location_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  warehouse_id BIGINT NOT NULL,
  location_code VARCHAR(100) NOT NULL,
  location_type ENUM('dock','staging','storage','pickface','qc','quarantine','disposal','outbound') NOT NULL,
  is_active BOOLEAN NOT NULL DEFAULT TRUE,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_loc_wh_code (warehouse_id, location_code),
  CONSTRAINT fk_loc_wh FOREIGN KEY (warehouse_id) REFERENCES warehouses(warehouse_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Lots / batches (product_lot)
CREATE TABLE IF NOT EXISTS product_lot (
  lot_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  product_id BIGINT NOT NULL,
  lot_no VARCHAR(100) NOT NULL,
  mfg_date DATE,
  exp_date DATE,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_lot_product (product_id, lot_no),
  CONSTRAINT fk_lot_product FOREIGN KEY (product_id) REFERENCES master_products(product_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------
-- 2) DOCUMENTS & LINES
-- -------------------------------
CREATE TABLE IF NOT EXISTS documents (
  document_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  doc_type ENUM('PO','SO','INBOUND','RETURN_IN','RTV','DISPOSAL','MOVE','ADJ','PICK','PACK','SHIP','OWNER_TRANSFER') NOT NULL,
  partner_id BIGINT NOT NULL,                 -- owner of stock affected
  counterparty_id BIGINT DEFAULT NULL,        -- vendor/customer other side
  warehouse_id BIGINT NOT NULL,
  ref_number VARCHAR(100),
  status ENUM('draft','open','partial','completed','cancelled') NOT NULL DEFAULT 'draft',
  ref_document_id BIGINT DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_doc_partner FOREIGN KEY (partner_id) REFERENCES partners(partner_id),
  CONSTRAINT fk_doc_counterparty FOREIGN KEY (counterparty_id) REFERENCES partners(partner_id),
  CONSTRAINT fk_doc_wh FOREIGN KEY (warehouse_id) REFERENCES warehouses(warehouse_id),
  CONSTRAINT fk_doc_refdoc FOREIGN KEY (ref_document_id) REFERENCES documents(document_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX idx_documents_type_status ON documents(doc_type, status);
CREATE INDEX idx_documents_partner ON documents(partner_id);

CREATE TABLE IF NOT EXISTS document_lines (
  document_line_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  document_id BIGINT NOT NULL,
  product_id BIGINT NOT NULL,
  uom_id BIGINT NOT NULL,
  qty_ordered DECIMAL(18,6) DEFAULT 0,
  qty_received DECIMAL(18,6) DEFAULT 0,
  qty_accepted DECIMAL(18,6) DEFAULT 0,
  qty_damaged DECIMAL(18,6) DEFAULT 0,
  qty_short DECIMAL(18,6) DEFAULT 0,
  qty_allocated DECIMAL(18,6) DEFAULT 0,
  qty_picked DECIMAL(18,6) DEFAULT 0,
  qty_shipped DECIMAL(18,6) DEFAULT 0,
  source_location_id BIGINT DEFAULT NULL,
  target_location_id BIGINT DEFAULT NULL,
  lot_id BIGINT DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_dl_document FOREIGN KEY (document_id) REFERENCES documents(document_id) ON DELETE CASCADE,
  CONSTRAINT fk_dl_product FOREIGN KEY (product_id) REFERENCES master_products(product_id),
  CONSTRAINT fk_dl_uom FOREIGN KEY (uom_id) REFERENCES master_uom(uom_id),
  CONSTRAINT fk_dl_source_loc FOREIGN KEY (source_location_id) REFERENCES locations(location_id),
  CONSTRAINT fk_dl_target_loc FOREIGN KEY (target_location_id) REFERENCES locations(location_id),
  CONSTRAINT fk_dl_lot FOREIGN KEY (lot_id) REFERENCES product_lot(lot_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX idx_doc_lines_doc ON document_lines(document_id);
CREATE INDEX idx_doc_lines_product ON document_lines(product_id);

-- -------------------------------
-- 3) INVENTORY: STOCK, TRANSACTIONS, SNAPSHOTS, OPNAME
-- -------------------------------
CREATE TABLE IF NOT EXISTS stock (
  stock_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  product_id BIGINT NOT NULL,
  partner_id BIGINT NOT NULL,
  location_id BIGINT NOT NULL,
  lot_id BIGINT DEFAULT NULL,
  uom_id BIGINT NOT NULL,
  qty_on_hand DECIMAL(18,6) NOT NULL DEFAULT 0,
  qty_reserved DECIMAL(18,6) NOT NULL DEFAULT 0,
  in_date DATE NOT NULL DEFAULT (CURRENT_DATE),
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_stock_product FOREIGN KEY (product_id) REFERENCES master_products(product_id),
  CONSTRAINT fk_stock_partner FOREIGN KEY (partner_id) REFERENCES partners(partner_id),
  CONSTRAINT fk_stock_location FOREIGN KEY (location_id) REFERENCES locations(location_id),
  CONSTRAINT fk_stock_lot FOREIGN KEY (lot_id) REFERENCES product_lot(lot_id),
  CONSTRAINT fk_stock_uom FOREIGN KEY (uom_id) REFERENCES master_uom(uom_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Unique enforcement for (product,partner,location,lot) using a stored generated column for null-safe behavior:
ALTER TABLE stock
  ADD COLUMN lot_id_key BIGINT GENERATED ALWAYS AS (COALESCE(lot_id, 0)) VIRTUAL,
  ADD UNIQUE KEY uq_stock_product_partner_loc_lot (product_id, partner_id, location_id, lot_id_key);

CREATE INDEX idx_stock_owner ON stock(partner_id);
CREATE INDEX idx_stock_loc ON stock(location_id);
CREATE INDEX idx_stock_product ON stock(product_id);
CREATE INDEX idx_stock_lot ON stock(lot_id);

CREATE TABLE IF NOT EXISTS stock_transactions (
  transaction_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  document_id BIGINT DEFAULT NULL,
  document_line_id BIGINT DEFAULT NULL,
  product_id BIGINT NOT NULL,
  partner_id BIGINT NOT NULL,
  uom_id BIGINT NOT NULL,
  source_location_id BIGINT DEFAULT NULL,
  target_location_id BIGINT DEFAULT NULL,
  lot_id BIGINT DEFAULT NULL,
  movement_type ENUM('IN','OUT','MOVE','ADJ') NOT NULL,
  movement_flag SMALLINT NOT NULL CHECK (movement_flag IN (-1,1)),
  qty DECIMAL(18,6) NOT NULL CHECK (qty > 0),
  transaction_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  created_by VARCHAR(100),
  CONSTRAINT fk_stx_document FOREIGN KEY (document_id) REFERENCES documents(document_id) ON DELETE SET NULL,
  CONSTRAINT fk_stx_document_line FOREIGN KEY (document_line_id) REFERENCES document_lines(document_line_id) ON DELETE SET NULL,
  CONSTRAINT fk_stx_product FOREIGN KEY (product_id) REFERENCES master_products(product_id),
  CONSTRAINT fk_stx_partner FOREIGN KEY (partner_id) REFERENCES partners(partner_id),
  CONSTRAINT fk_stx_uom FOREIGN KEY (uom_id) REFERENCES master_uom(uom_id),
  CONSTRAINT fk_stx_src_loc FOREIGN KEY (source_location_id) REFERENCES locations(location_id),
  CONSTRAINT fk_stx_tgt_loc FOREIGN KEY (target_location_id) REFERENCES locations(location_id),
  CONSTRAINT fk_stx_lot FOREIGN KEY (lot_id) REFERENCES product_lot(lot_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX idx_stx_doc ON stock_transactions(document_id);
CREATE INDEX idx_stx_product ON stock_transactions(product_id);
CREATE INDEX idx_stx_partner ON stock_transactions(partner_id);
CREATE INDEX idx_stx_date ON stock_transactions(transaction_date);
CREATE INDEX idx_stx_lot ON stock_transactions(lot_id);

CREATE TABLE IF NOT EXISTS stock_snapshots (
  snapshot_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  product_id BIGINT NOT NULL,
  partner_id BIGINT NOT NULL,
  warehouse_id BIGINT NOT NULL,
  location_id BIGINT NOT NULL,
  lot_id BIGINT DEFAULT NULL,
  uom_id BIGINT NOT NULL,
  snapshot_qty DECIMAL(18,6) NOT NULL,
  snapshot_date DATE NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_snapshot (product_id, partner_id, location_id, lot_id, snapshot_date),
  CONSTRAINT fk_snap_product FOREIGN KEY (product_id) REFERENCES master_products(product_id),
  CONSTRAINT fk_snap_partner FOREIGN KEY (partner_id) REFERENCES partners(partner_id),
  CONSTRAINT fk_snap_wh FOREIGN KEY (warehouse_id) REFERENCES warehouses(warehouse_id),
  CONSTRAINT fk_snap_loc FOREIGN KEY (location_id) REFERENCES locations(location_id),
  CONSTRAINT fk_snap_lot FOREIGN KEY (lot_id) REFERENCES product_lot(lot_id),
  CONSTRAINT fk_snap_uom FOREIGN KEY (uom_id) REFERENCES master_uom(uom_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX idx_snapshots_date ON stock_snapshots(snapshot_date);

CREATE TABLE IF NOT EXISTS stock_opname (
  opname_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  warehouse_id BIGINT NOT NULL,
  location_id BIGINT NOT NULL,
  product_id BIGINT NOT NULL,
  partner_id BIGINT NOT NULL,
  uom_id BIGINT NOT NULL,
  lot_id BIGINT DEFAULT NULL,
  system_qty DECIMAL(18,6) NOT NULL DEFAULT 0,
  counted_qty DECIMAL(18,6) NOT NULL DEFAULT 0,
  difference DECIMAL(18,6) GENERATED ALWAYS AS (counted_qty - system_qty) VIRTUAL,
  status ENUM('open','confirmed','posted') NOT NULL DEFAULT 'open',
  opname_date DATE NOT NULL DEFAULT (CURRENT_DATE),
  created_by VARCHAR(100),
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_op_wh FOREIGN KEY (warehouse_id) REFERENCES warehouses(warehouse_id),
  CONSTRAINT fk_op_loc FOREIGN KEY (location_id) REFERENCES locations(location_id),
  CONSTRAINT fk_op_product FOREIGN KEY (product_id) REFERENCES master_products(product_id),
  CONSTRAINT fk_op_partner FOREIGN KEY (partner_id) REFERENCES partners(partner_id),
  CONSTRAINT fk_op_lot FOREIGN KEY (lot_id) REFERENCES product_lot(lot_id),
  CONSTRAINT fk_op_uom FOREIGN KEY (uom_id) REFERENCES master_uom(uom_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX idx_opname_status ON stock_opname(status);

-- -------------------------------
-- 4) HANDLING FEES
-- -------------------------------
CREATE TABLE IF NOT EXISTS handling_fee_rules (
  rule_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  partner_id BIGINT NOT NULL,
  product_id BIGINT DEFAULT NULL,
  uom_id BIGINT DEFAULT NULL,
  fee_type ENUM('inbound','outbound','storage','move','return','adjustment','special') NOT NULL,
  trigger_event ENUM('transaction','snapshot_daily','manual') NOT NULL,
  charge_basis ENUM('per_item','per_carton','per_pallet','per_order','per_cbm','per_day') NOT NULL,
  unit_price DECIMAL(18,2) NOT NULL DEFAULT 0,
  currency VARCHAR(10) NOT NULL DEFAULT 'IDR',
  effective_date DATE NOT NULL DEFAULT (CURRENT_DATE),
  expiry_date DATE DEFAULT NULL,
  is_active BOOLEAN NOT NULL DEFAULT TRUE,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_fee_rule_partner FOREIGN KEY (partner_id) REFERENCES partners(partner_id),
  CONSTRAINT fk_fee_rule_product FOREIGN KEY (product_id) REFERENCES master_products(product_id),
  CONSTRAINT fk_fee_rule_uom FOREIGN KEY (uom_id) REFERENCES master_uom(uom_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX idx_fee_rules_partner ON handling_fee_rules(partner_id);
CREATE INDEX idx_fee_rules_active ON handling_fee_rules(is_active, effective_date, expiry_date);

CREATE TABLE IF NOT EXISTS handling_fee_transactions (
  fee_txn_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  rule_id BIGINT DEFAULT NULL,
  partner_id BIGINT NOT NULL,
  product_id BIGINT DEFAULT NULL,
  uom_id BIGINT DEFAULT NULL,
  qty_basis DECIMAL(18,6) NOT NULL DEFAULT 0,
  unit_price DECIMAL(18,2) NOT NULL DEFAULT 0,
  total_fee DECIMAL(18,2) NOT NULL DEFAULT 0,
  currency VARCHAR(10) NOT NULL DEFAULT 'IDR',
  ref_doc_type VARCHAR(50) DEFAULT NULL,
  ref_doc_id BIGINT DEFAULT NULL,
  txn_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  status ENUM('pending','billed','cancelled') NOT NULL DEFAULT 'pending',
  CONSTRAINT fk_fee_txn_rule FOREIGN KEY (rule_id) REFERENCES handling_fee_rules(rule_id),
  CONSTRAINT fk_fee_txn_partner FOREIGN KEY (partner_id) REFERENCES partners(partner_id),
  CONSTRAINT fk_fee_txn_product FOREIGN KEY (product_id) REFERENCES master_products(product_id),
  CONSTRAINT fk_fee_txn_uom FOREIGN KEY (uom_id) REFERENCES master_uom(uom_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX idx_fee_txn_partner ON handling_fee_transactions(partner_id);
CREATE INDEX idx_fee_txn_ref ON handling_fee_transactions(ref_doc_id, ref_doc_type);

-- -------------------------------
-- 5) AUDIT / LOGGING (optional)
-- -------------------------------
CREATE TABLE IF NOT EXISTS audit_log (
  audit_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  entity_name VARCHAR(100) NOT NULL,
  entity_id BIGINT,
  action ENUM('create','update','delete','move','adjust','approve','reject') NOT NULL,
  details TEXT,
  user_id BIGINT,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX idx_audit_entity ON audit_log(entity_name, entity_id);

-- =======================
-- USERS & AUTH STRUCTURE
-- =======================

CREATE TABLE role (
    role_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL,       -- e.g. SuperAdmin, Vendor, WarehouseAdmin, Customer
    description VARCHAR(255) NULL
);

CREATE TABLE user (
    user_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role_id BIGINT NOT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES role(role_id)
);

CREATE TABLE user_partner (
    user_partner_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    partner_id BIGINT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES user(user_id),
    FOREIGN KEY (partner_id) REFERENCES partners(partner_id)
);

CREATE TABLE permission (
    permission_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    permission_name VARCHAR(50) NOT NULL   -- e.g. view, create, update, delete
);

CREATE TABLE menu (
    menu_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    menu_name VARCHAR(255) NOT NULL,
    menu_icon VARCHAR(255) NULL
);

CREATE TABLE sub_menu (
    sub_menu_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    menu_id BIGINT NOT NULL,
    sub_menu_name VARCHAR(255) NOT NULL,
    sub_menu_icon VARCHAR(255) NULL,
    sub_menu_url VARCHAR(255) NOT NULL,
    FOREIGN KEY (menu_id) REFERENCES menu(menu_id)
);

CREATE TABLE group_permission (
    group_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    role_id BIGINT NOT NULL,
    permission_id BIGINT NOT NULL,
    menu_id BIGINT NOT NULL,
    sub_menu_id BIGINT NOT NULL,
    FOREIGN KEY (role_id) REFERENCES role(role_id),
    FOREIGN KEY (permission_id) REFERENCES permission(permission_id),
    FOREIGN KEY (menu_id) REFERENCES menu(menu_id),
    FOREIGN KEY (sub_menu_id) REFERENCES sub_menu(sub_menu_id)
);

CREATE INDEX idx_permission ON group_permission(role_id, permission_id, menu_id, sub_menu_id);
INSERT INTO role (role_name)
VALUES('superadmin'),('customer'),('vendor'),('warehouse');

INSERT INTO permission (permission_name)
VALUES('view'),('create'),('update'),('delete');

INSERT INTO menu (menu_name, menu_icon)
VALUES('dashboard', '<i class="fa-solid fa-grid-horizontal"></i>'),
('master', '<i class="fa-solid fa-database"></i>');

INSERT INTO sub_menu (menu_id, sub_menu_name, sub_menu_icon, sub_menu_url)
VALUES (2, 'product', '<i class="fa-solid fa-boxes-stacked"></i>', '/product'),
(2, 'uom', '<i class="fa-solid fa-boxes-packing"></i>', '/uom'),
(2, 'partner', '<i class="fa-solid fa-handshake"></i>', '/partner'),
(2, 'warehouse', '<i class="fa-solid fa-warehouse"></i>', '/warehouse');

INSERT INTO group_permission (role_id, permission_id, menu_id, sub_menu_id)
VALUES (1, 1, 2, 1), (1, 2, 2, 1), (1, 3, 2, 1), (1, 4, 2, 1),
(1, 1, 2, 2), (1, 2, 2, 2), (1, 3, 2, 2), (1, 4, 2, 2),
(1, 1, 2, 3), (1, 2, 2, 3), (1, 3, 2, 3), (1, 4, 2, 3),
(1, 1, 2, 4), (1, 2, 2, 4), (1, 3, 2, 4), (1, 4, 2, 4);

INSERT INTO user (user_name, email, password, role_id)
VALUES('superadmin', 'admin@admin.com', '$2y$10$.b7vMGxqGSXxoNIOJZrdoe5p289BLJJ.Co.7AQN4lds2j57dO3uci', 1);



