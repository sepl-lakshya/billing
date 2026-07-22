-- ============================================================================
--  Billing Application - Seed data for lookup / master tables
--  Run AFTER schema.sql.
--  Values for lookup tables are reasonable defaults reconstructed from the
--  legacy app (no original data dump existed). Adjust as needed.
-- ============================================================================
USE billing;

-- Default "system" user (auth removed - everything is created_by = 1) --------
INSERT INTO users (id, full_name, email, username, user_type, is_active)
VALUES (1, 'System User', 'system@billing.local', 'system', 1, 1)
ON DUPLICATE KEY UPDATE full_name = VALUES(full_name);

-- Months --------------------------------------------------------------------
INSERT INTO month (value, name) VALUES
  (1,'January'),(2,'February'),(3,'March'),(4,'April'),(5,'May'),(6,'June'),
  (7,'July'),(8,'August'),(9,'September'),(10,'October'),(11,'November'),(12,'December');

-- Years ---------------------------------------------------------------------
INSERT INTO year (value, name) VALUES
  (2020,'2020'),(2021,'2021'),(2022,'2022'),(2023,'2023'),(2024,'2024'),
  (2025,'2025'),(2026,'2026'),(2027,'2027'),(2028,'2028'),(2029,'2029'),(2030,'2030');

-- Indian states (GST state codes) -------------------------------------------
INSERT INTO states (value, name) VALUES
  (1,'Jammu and Kashmir'),(2,'Himachal Pradesh'),(3,'Punjab'),(4,'Chandigarh'),
  (5,'Uttarakhand'),(6,'Haryana'),(7,'Delhi'),(8,'Rajasthan'),(9,'Uttar Pradesh'),
  (10,'Bihar'),(11,'Sikkim'),(12,'Arunachal Pradesh'),(13,'Nagaland'),(14,'Manipur'),
  (15,'Mizoram'),(16,'Tripura'),(17,'Meghalaya'),(18,'Assam'),(19,'West Bengal'),
  (20,'Jharkhand'),(21,'Odisha'),(22,'Chhattisgarh'),(23,'Madhya Pradesh'),
  (24,'Gujarat'),(26,'Dadra and Nagar Haveli and Daman and Diu'),(27,'Maharashtra'),
  (28,'Andhra Pradesh (Old)'),(29,'Karnataka'),(30,'Goa'),(31,'Lakshadweep'),
  (32,'Kerala'),(33,'Tamil Nadu'),(34,'Puducherry'),(35,'Andaman and Nicobar Islands'),
  (36,'Telangana'),(37,'Andhra Pradesh'),(38,'Ladakh');

-- GST slabs -----------------------------------------------------------------
INSERT INTO gst_slab (name, percentage) VALUES
  ('0% GST',0),('5% GST',5),('12% GST',12),('18% GST',18),('28% GST',28);

-- Subscription terms --------------------------------------------------------
INSERT INTO subscription_term (name, months) VALUES
  ('Monthly',1),('Quarterly',3),('Half Yearly',6),('Yearly',12),
  ('2 Years',24),('3 Years',36);

-- Billing terms -------------------------------------------------------------
INSERT INTO billing_term (name, months) VALUES
  ('Monthly',1),('Quarterly',3),('Half Yearly',6),('Yearly',12);

-- Cloud categories ----------------------------------------------------------
INSERT INTO cloud_category (value, name) VALUES
  (1,'IaaS'),(2,'PaaS'),(3,'SaaS'),(4,'Support'),(5,'Managed Services');

-- Adjustment types ----------------------------------------------------------
INSERT INTO adjustment_type (value, name) VALUES
  (1,'Addition'),(2,'Deduction');

-- Credit note types ---------------------------------------------------------
INSERT INTO credit_note_type (value, name) VALUES
  (1,'Discount'),(2,'Return'),(3,'Adjustment');

-- Debit note types ----------------------------------------------------------
INSERT INTO debit_note_type (value, name) VALUES
  (1,'Purchase'),(2,'Expense'),(3,'Adjustment');

-- Unit measures -------------------------------------------------------------
INSERT INTO unit_measure (value, name) VALUES
  (1,'Nos'),(2,'Hours'),(3,'GB'),(4,'TB'),(5,'vCPU'),(6,'User'),(7,'Month');

-- Discovery status ----------------------------------------------------------
INSERT INTO project_item_discovery (value, name) VALUES
  (1,'Not Discovered'),(2,'Discovered'),(3,'In Progress');

-- Contract years ------------------------------------------------------------
INSERT INTO contract_year (value, name) VALUES
  (1,'1 Year'),(2,'2 Years'),(3,'3 Years'),(4,'4 Years'),(5,'5 Years');

-- Contract months -----------------------------------------------------------
INSERT INTO contract_month (value, name) VALUES
  (0,'0 Months'),(1,'1 Month'),(2,'2 Months'),(3,'3 Months'),(4,'4 Months'),
  (5,'5 Months'),(6,'6 Months'),(7,'7 Months'),(8,'8 Months'),(9,'9 Months'),
  (10,'10 Months'),(11,'11 Months');

-- Monthly billing progress ---------------------------------------------------
INSERT INTO billing_progress (value, name) VALUES
  (1,'Portal pricing entered'),(2,'Sales pricing entered'),(3,'Purchase pricing entered'),
  (4,'Purchase invoice mapped'),(5,'Cloud inward created'),(6,'Cloud outward created'),
  (7,'Billing completed');

-- A couple of sample master rows to make the UI usable immediately ----------
INSERT INTO oem (name) VALUES ('Microsoft'),('Amazon Web Services'),('Google');
INSERT INTO distributor (name) VALUES ('Ingram Micro'),('Redington'),('Savex');
