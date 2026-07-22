-- ============================================================================
--  Billing Application - MySQL Schema (modern rewrite)
--  Converted from the legacy PHP application.
--  The legacy app used two databases (`billing` + `portal`); they are merged
--  here into a single `billing` database. Authentication has been removed, but
--  the `users` table (formerly portal.login_detail) is kept because many
--  records reference a "created_by" / "assigned" user.
-- ============================================================================

CREATE DATABASE IF NOT EXISTS billing
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE billing;

SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------------
--  Users (formerly portal.login_detail). Auth removed - kept for references.
-- ---------------------------------------------------------------------------
DROP TABLE IF EXISTS users;
CREATE TABLE users (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  hash         VARCHAR(64),
  email        VARCHAR(191),
  username     VARCHAR(191),
  full_name    VARCHAR(191) NOT NULL,
  user_type    INT NOT NULL DEFAULT 2,   -- 1 = admin, 2 = user (kept for parity)
  is_active    TINYINT NOT NULL DEFAULT 1,
  created_on   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- ===========================================================================
--  MASTER / LOOKUP TABLES
--  Standardised on `id` (code) + `name`. Extra columns where the legacy
--  code required them (gst_slab.percentage).
-- ===========================================================================
DROP TABLE IF EXISTS month;
CREATE TABLE month (
  value INT PRIMARY KEY,
  name  VARCHAR(30) NOT NULL
);

DROP TABLE IF EXISTS year;
CREATE TABLE year (
  value INT PRIMARY KEY,
  name  VARCHAR(10) NOT NULL
);

DROP TABLE IF EXISTS states;
CREATE TABLE states (
  value INT PRIMARY KEY,      -- GST state code
  name  VARCHAR(100) NOT NULL
);

DROP TABLE IF EXISTS gst_slab;
CREATE TABLE gst_slab (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(30) NOT NULL,
  percentage DECIMAL(6,2) NOT NULL
);

DROP TABLE IF EXISTS subscription_term;
CREATE TABLE subscription_term (
  id    INT AUTO_INCREMENT PRIMARY KEY,
  name  VARCHAR(50) NOT NULL,
  months INT NOT NULL DEFAULT 1
);

DROP TABLE IF EXISTS billing_term;
CREATE TABLE billing_term (
  id    INT AUTO_INCREMENT PRIMARY KEY,
  name  VARCHAR(50) NOT NULL,
  months INT NOT NULL DEFAULT 1
);

DROP TABLE IF EXISTS cloud_category;
CREATE TABLE cloud_category (
  value INT PRIMARY KEY,
  name  VARCHAR(80) NOT NULL
);

DROP TABLE IF EXISTS adjustment_type;
CREATE TABLE adjustment_type (
  value INT PRIMARY KEY,
  name  VARCHAR(80) NOT NULL
);

DROP TABLE IF EXISTS credit_note_type;
CREATE TABLE credit_note_type (
  value INT PRIMARY KEY,
  name  VARCHAR(80) NOT NULL
);

DROP TABLE IF EXISTS debit_note_type;
CREATE TABLE debit_note_type (
  value INT PRIMARY KEY,
  name  VARCHAR(80) NOT NULL
);

DROP TABLE IF EXISTS unit_measure;
CREATE TABLE unit_measure (
  value INT PRIMARY KEY,
  name  VARCHAR(50) NOT NULL
);

DROP TABLE IF EXISTS project_item_discovery;
CREATE TABLE project_item_discovery (
  value INT PRIMARY KEY,
  name  VARCHAR(50) NOT NULL
);

DROP TABLE IF EXISTS contract_year;
CREATE TABLE contract_year (
  value INT PRIMARY KEY,
  name  VARCHAR(30) NOT NULL
);

DROP TABLE IF EXISTS contract_month;
CREATE TABLE contract_month (
  value INT PRIMARY KEY,
  name  VARCHAR(30) NOT NULL
);

DROP TABLE IF EXISTS billing_progress;
CREATE TABLE billing_progress (
  value INT PRIMARY KEY,
  name  VARCHAR(80) NOT NULL
);

-- ===========================================================================
--  MASTER ENTITIES
-- ===========================================================================
DROP TABLE IF EXISTS oem;
CREATE TABLE oem (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(150) NOT NULL,
  is_active  TINYINT NOT NULL DEFAULT 1,
  created_on DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

DROP TABLE IF EXISTS distributor;
CREATE TABLE distributor (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(150) NOT NULL,
  is_active  TINYINT NOT NULL DEFAULT 1,
  created_on DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

DROP TABLE IF EXISTS product;
CREATE TABLE product (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  name         VARCHAR(200) NOT NULL,
  oem          INT NOT NULL,                  -- FK -> oem.id
  category     INT NOT NULL,                  -- 1 = cloud
  sub_category INT NOT NULL DEFAULT 0,        -- FK -> cloud_category.value
  is_active    TINYINT NOT NULL DEFAULT 1,
  created_on   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

DROP TABLE IF EXISTS purchase_header;
CREATE TABLE purchase_header (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  hash       VARCHAR(64),
  name       VARCHAR(150) NOT NULL,
  project_id INT NOT NULL DEFAULT 0,          -- 0 = global/shared header
  is_active  TINYINT NOT NULL DEFAULT 1,
  created_on DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- ===========================================================================
--  CLOUD PROJECTS
-- ===========================================================================
DROP TABLE IF EXISTS project;
CREATE TABLE project (
  id               INT AUTO_INCREMENT PRIMARY KEY,
  hash             VARCHAR(64) NOT NULL,
  created_by       INT NOT NULL DEFAULT 1,        -- FK -> users.id
  product_category INT NOT NULL DEFAULT 1,        -- 1 = cloud
  name             VARCHAR(200) NOT NULL,
  city             VARCHAR(120),
  state            INT,                           -- FK -> states.value
  distributor      INT NOT NULL DEFAULT 0,        -- FK -> distributor.id (assigned distributor for the project)
  tender_ref_no    VARCHAR(120),
  start_date       DATE,
  description      TEXT,
  is_active        TINYINT NOT NULL DEFAULT 1,
  created_on       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

DROP TABLE IF EXISTS project_header;
CREATE TABLE project_header (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  hash        VARCHAR(64),
  project_id  INT NOT NULL,
  name        VARCHAR(200) NOT NULL,
  quantity    DECIMAL(14,2) NOT NULL DEFAULT 0,
  amount      DECIMAL(14,2) NOT NULL DEFAULT 0,
  description TEXT,
  is_deleted  TINYINT NOT NULL DEFAULT 0
);

DROP TABLE IF EXISTS project_item;
CREATE TABLE project_item (
  id                INT AUTO_INCREMENT PRIMARY KEY,
  hash              VARCHAR(64),
  project_id        INT NOT NULL,
  header_id         INT NOT NULL,
  product           INT,                        -- FK -> product.id
  product_type      INT,                        -- FK -> cloud_category.value
  distributor       INT,                        -- FK -> distributor.id
  deployment_start  DATE,
  deployment_end    DATE NULL,
  unit_measure      INT,                        -- FK -> unit_measure.value
  unit_price        DECIMAL(14,2) NOT NULL DEFAULT 0,
  quantity          DECIMAL(14,2) NOT NULL DEFAULT 0,
  deployed_product  INT,                        -- FK -> product.id (legacy: deployed_product)
  discovery_status  INT,                        -- FK -> project_item_discovery.value
  purchase_header_id INT NOT NULL DEFAULT 0,    -- FK -> purchase_header.id
  description       TEXT,
  resource_id       VARCHAR(191),
  model             VARCHAR(191),
  status            INT NOT NULL DEFAULT 1,      -- 1 = active, 0 = disabled
  is_deleted        TINYINT NOT NULL DEFAULT 0
);

DROP TABLE IF EXISTS project_discount;
CREATE TABLE project_discount (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  project_id    INT NOT NULL,
  ri_discount   DECIMAL(8,2) NOT NULL DEFAULT 0,
  payg_discount DECIMAL(8,2) NOT NULL DEFAULT 0,
  credit_days   INT NOT NULL DEFAULT 0,
  from_date     DATE,
  to_date       DATE NULL,
  created_on    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

DROP TABLE IF EXISTS project_adjustment;
CREATE TABLE project_adjustment (
  id                 INT AUTO_INCREMENT PRIMARY KEY,
  project_id         INT NOT NULL,
  adjustment_type_id INT NOT NULL,              -- FK -> adjustment_type.value
  amount             DECIMAL(14,2) NOT NULL DEFAULT 0,
  reference_no       VARCHAR(120),
  created_on         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

DROP TABLE IF EXISTS project_attachment;
CREATE TABLE project_attachment (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  project_id    INT NOT NULL,
  title         VARCHAR(200),
  original_name VARCHAR(255),
  file_name     VARCHAR(255),
  created_on    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

DROP TABLE IF EXISTS project_user_mapping;
CREATE TABLE project_user_mapping (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  project_id  INT NOT NULL,
  user_id     INT NOT NULL,
  assigned_by INT NOT NULL DEFAULT 1,
  created_on  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- ===========================================================================
--  BILLING (cloud projects)
-- ===========================================================================
DROP TABLE IF EXISTS bill;
CREATE TABLE bill (
  id                     INT AUTO_INCREMENT PRIMARY KEY,
  hash                   VARCHAR(64) NOT NULL,
  project_id             INT NOT NULL,
  month                  INT NOT NULL,          -- FK -> month.value
  year                   INT NOT NULL,          -- FK -> year.value
  ri_discount            DECIMAL(8,2) NOT NULL DEFAULT 0,
  payg_discount          DECIMAL(8,2) NOT NULL DEFAULT 0,
  status                 INT NOT NULL DEFAULT 0, -- 0 draft,1 portal,2 sales,3 purchase
  purchase_header_status INT NOT NULL DEFAULT 0,
  progress               INT NOT NULL DEFAULT 0,
  is_deleted             TINYINT NOT NULL DEFAULT 0,
  created_on             DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

DROP TABLE IF EXISTS bill_item;
CREATE TABLE bill_item (
  id                 INT AUTO_INCREMENT PRIMARY KEY,
  bill_id            INT NOT NULL,
  project_id         INT NOT NULL,
  project_item_id    INT NOT NULL,
  purchase_header_id INT NOT NULL DEFAULT 0,
  portal_price       DECIMAL(14,2) NULL,
  purchase_price     DECIMAL(14,2) NULL,
  sales_price        DECIMAL(14,2) NULL
);

DROP TABLE IF EXISTS bill_header;
CREATE TABLE bill_header (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  bill_id    INT NOT NULL,
  project_id INT NOT NULL,
  header_id  INT NOT NULL,
  amount     DECIMAL(14,2) NOT NULL DEFAULT 0,
  is_deleted TINYINT NOT NULL DEFAULT 0
);

DROP TABLE IF EXISTS bill_purchase_header;
CREATE TABLE bill_purchase_header (
  id                 INT AUTO_INCREMENT PRIMARY KEY,
  bill_id            INT NOT NULL,
  project_id         INT NOT NULL,
  purchase_header_id INT NOT NULL,
  amount             DECIMAL(14,2) NOT NULL DEFAULT 0
);

DROP TABLE IF EXISTS bill_invoice_mapping;
CREATE TABLE bill_invoice_mapping (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  bill_id    INT NOT NULL,
  project_id INT NOT NULL,
  invoice_id INT NOT NULL
);

-- ===========================================================================
--  INVOICES / CLOUD INWARD-OUTWARD / NOTES
-- ===========================================================================
DROP TABLE IF EXISTS purchase_invoice;
CREATE TABLE purchase_invoice (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  project_id   INT NOT NULL,
  number       VARCHAR(120) NOT NULL,
  amount       DECIMAL(14,2) NOT NULL DEFAULT 0,
  gst_slab     DECIMAL(6,2) NOT NULL DEFAULT 0,
  invoice_date DATE,
  description  TEXT,
  type         INT NOT NULL DEFAULT 0,
  is_deleted   TINYINT NOT NULL DEFAULT 0,
  created_on   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

DROP TABLE IF EXISTS debit_note;
CREATE TABLE debit_note (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  project_id  INT NOT NULL,
  bill_id     INT NOT NULL DEFAULT 0,
  invoice_id  INT NOT NULL,
  type        INT NOT NULL DEFAULT 0,   -- FK -> debit_note_type.value
  credit_type INT NOT NULL DEFAULT 0,   -- FK -> credit_note_type.value
  amount      DECIMAL(14,2) NOT NULL DEFAULT 0,
  remark      TEXT,
  created_on  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

DROP TABLE IF EXISTS credit_note;
CREATE TABLE credit_note (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  debit_note_id INT NOT NULL,
  project_id    INT NOT NULL,
  bill_id       INT NOT NULL DEFAULT 0,
  invoice_id    INT NOT NULL,
  reference_no  VARCHAR(120),
  amount        DECIMAL(14,2) NOT NULL DEFAULT 0,
  remark        TEXT,
  created_on    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

SET FOREIGN_KEY_CHECKS = 1;

-- Helpful indexes
CREATE INDEX idx_project_item_project   ON project_item (project_id);
CREATE INDEX idx_project_item_resource  ON project_item (project_id, resource_id, product, model);
CREATE INDEX idx_project_header_project ON project_header (project_id);
CREATE INDEX idx_bill_project           ON bill (project_id, month, year);
CREATE INDEX idx_bill_item_bill         ON bill_item (bill_id, project_id);
CREATE INDEX idx_purchase_invoice_proj  ON purchase_invoice (project_id);
