-- =============================================
-- DATABASE: traceability-db (Optimized)
-- DESCRIPTION: Optimized database without loops and with direct connections
-- DATE: 2025
-- =============================================

-- =============================================
-- CORE PARAMETRIC TABLES
-- =============================================

CREATE TABLE unit_of_measure(
    unit_id INTEGER PRIMARY KEY,
    code VARCHAR(10) NOT NULL UNIQUE,
    name VARCHAR(50) NOT NULL,
    description VARCHAR(255),
    active BOOLEAN DEFAULT TRUE
);

CREATE TABLE status(
    status_id INTEGER PRIMARY KEY,
    entity_type VARCHAR(50) NOT NULL,
    code VARCHAR(50) NOT NULL,
    name VARCHAR(100) NOT NULL,
    description VARCHAR(255),
    sort_order INTEGER NOT NULL,
    active BOOLEAN DEFAULT TRUE
);

CREATE TABLE movement_type(
    movement_type_id INTEGER PRIMARY KEY,
    code VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    affects_stock BOOLEAN DEFAULT TRUE,
    is_entry BOOLEAN DEFAULT FALSE,
    active BOOLEAN DEFAULT TRUE
);

CREATE TABLE operator_role(
    role_id INTEGER PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    description VARCHAR(255),
    access_level INTEGER DEFAULT 1,
    active BOOLEAN DEFAULT TRUE
);

CREATE TABLE customer(
    customer_id INTEGER PRIMARY KEY,
    business_name VARCHAR(200) NOT NULL,
    trading_name VARCHAR(200),
    tax_id VARCHAR(20) UNIQUE,
    address VARCHAR(255),
    phone VARCHAR(20),
    email VARCHAR(100),
    contact_person VARCHAR(100),
    active BOOLEAN DEFAULT TRUE
);

CREATE TABLE raw_material_category(
    category_id INTEGER PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    description VARCHAR(255),
    active BOOLEAN DEFAULT TRUE
);

CREATE TABLE supplier(
    supplier_id INTEGER PRIMARY KEY,
    business_name VARCHAR(200) NOT NULL,
    trading_name VARCHAR(200),
    tax_id VARCHAR(20) UNIQUE,
    contact_person VARCHAR(100),
    phone VARCHAR(20),
    email VARCHAR(100),
    address VARCHAR(255),
    active BOOLEAN DEFAULT TRUE
);

-- =============================================
-- CONFIGURATION TABLES
-- =============================================

CREATE TABLE standard_variable(
    variable_id INTEGER PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    unit VARCHAR(50),
    description VARCHAR(255),
    active BOOLEAN DEFAULT TRUE
);

CREATE TABLE machine(
    machine_id INTEGER PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    description VARCHAR(255),
    image_url VARCHAR(500),
    active BOOLEAN DEFAULT TRUE
);

CREATE TABLE process(
    process_id INTEGER PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    description VARCHAR(255),
    active BOOLEAN DEFAULT TRUE
);

CREATE TABLE operator(
    operator_id INTEGER PRIMARY KEY,
    role_id INTEGER NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    username VARCHAR(60) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (role_id) REFERENCES operator_role(role_id)
);

-- =============================================
-- INVENTORY TABLES
-- =============================================

CREATE TABLE raw_material_base(
    material_id INTEGER PRIMARY KEY,
    category_id INTEGER NOT NULL,
    unit_id INTEGER NOT NULL,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    description VARCHAR(255),
    available_quantity DECIMAL(15,4) DEFAULT 0,
    minimum_stock DECIMAL(15,4) DEFAULT 0,
    maximum_stock DECIMAL(15,4),
    active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (category_id) REFERENCES raw_material_category(category_id),
    FOREIGN KEY (unit_id) REFERENCES unit_of_measure(unit_id)
);

CREATE TABLE raw_material(
    raw_material_id INTEGER PRIMARY KEY,
    material_id INTEGER NOT NULL,
    supplier_id INTEGER NOT NULL,
    supplier_batch VARCHAR(100),
    invoice_number VARCHAR(100),
    receipt_date DATE NOT NULL,
    expiration_date DATE,
    quantity DECIMAL(15,4) NOT NULL,
    available_quantity DECIMAL(15,4) NOT NULL,
    receipt_conformity BOOLEAN,
    observations VARCHAR(500),
    FOREIGN KEY (material_id) REFERENCES raw_material_base(material_id),
    FOREIGN KEY (supplier_id) REFERENCES supplier(supplier_id)
);

-- =============================================
-- PRODUCTION TABLES (MAIN HIERARCHY)
-- =============================================

CREATE TABLE customer_order(
    order_id INTEGER PRIMARY KEY,
    customer_id INTEGER NOT NULL,
    order_number VARCHAR(50) NOT NULL UNIQUE,
    creation_date DATE DEFAULT CURRENT_DATE,
    delivery_date DATE,
    priority INTEGER DEFAULT 1,
    description TEXT,
    observations TEXT,
    FOREIGN KEY (customer_id) REFERENCES customer(customer_id)
);

CREATE TABLE production_batch(
    batch_id INTEGER PRIMARY KEY,
    order_id INTEGER NOT NULL,
    batch_code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(100) DEFAULT 'Unnamed Batch',
    creation_date DATE DEFAULT CURRENT_DATE,
    start_time TIMESTAMP,
    end_time TIMESTAMP,
    target_quantity DECIMAL(15,4),
    produced_quantity DECIMAL(15,4),
    observations VARCHAR(500),
    FOREIGN KEY (order_id) REFERENCES customer_order(order_id)
);

-- =============================================
-- CONSUMPTION AND MOVEMENT TABLES
-- =============================================

CREATE TABLE batch_raw_material(
    batch_material_id INTEGER PRIMARY KEY,
    batch_id INTEGER NOT NULL,
    raw_material_id INTEGER NOT NULL,
    planned_quantity DECIMAL(15,4) NOT NULL,
    used_quantity DECIMAL(15,4),
    FOREIGN KEY (batch_id) REFERENCES production_batch(batch_id),
    FOREIGN KEY (raw_material_id) REFERENCES raw_material(raw_material_id)
);

CREATE TABLE material_movement_log(
    log_id INTEGER PRIMARY KEY,
    material_id INTEGER NOT NULL,
    movement_type_id INTEGER NOT NULL,
    user_id INTEGER,
    quantity DECIMAL(15,4) NOT NULL,
    previous_balance DECIMAL(15,4),
    new_balance DECIMAL(15,4),
    description VARCHAR(500),
    movement_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (material_id) REFERENCES raw_material_base(material_id),
    FOREIGN KEY (movement_type_id) REFERENCES movement_type(movement_type_id),
    FOREIGN KEY (user_id) REFERENCES operator(operator_id)
);

-- =============================================
-- PRODUCTION PROCESS TABLES
-- =============================================

CREATE TABLE process_machine(
    process_machine_id INTEGER PRIMARY KEY,
    process_id INTEGER NOT NULL,
    machine_id INTEGER NOT NULL,
    step_order INTEGER NOT NULL,
    name VARCHAR(100) NOT NULL,
    description VARCHAR(255),
    estimated_time INTEGER,
    FOREIGN KEY (process_id) REFERENCES process(process_id),
    FOREIGN KEY (machine_id) REFERENCES machine(machine_id)
);

CREATE TABLE process_machine_variable(
    variable_id INTEGER PRIMARY KEY,
    process_machine_id INTEGER NOT NULL,
    standard_variable_id INTEGER NOT NULL,
    min_value DECIMAL(10,2) NOT NULL,
    max_value DECIMAL(10,2) NOT NULL,
    target_value DECIMAL(10,2),
    mandatory BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (process_machine_id) REFERENCES process_machine(process_machine_id),
    FOREIGN KEY (standard_variable_id) REFERENCES standard_variable(variable_id)
);

CREATE TABLE process_machine_record(
    record_id INTEGER PRIMARY KEY,
    batch_id INTEGER NOT NULL,
    process_machine_id INTEGER NOT NULL,
    operator_id INTEGER NOT NULL,
    entered_variables TEXT NOT NULL,
    meets_standard BOOLEAN NOT NULL,
    observations VARCHAR(500),
    start_time TIMESTAMP,
    end_time TIMESTAMP,
    record_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (batch_id) REFERENCES production_batch(batch_id),
    FOREIGN KEY (process_machine_id) REFERENCES process_machine(process_machine_id),
    FOREIGN KEY (operator_id) REFERENCES operator(operator_id)
);

-- =============================================
-- QUALITY CONTROL AND STORAGE TABLES
-- =============================================

CREATE TABLE process_final_evaluation(
    evaluation_id INTEGER PRIMARY KEY,
    batch_id INTEGER NOT NULL,
    inspector_id INTEGER,
    reason VARCHAR(500),
    observations VARCHAR(500),
    evaluation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (batch_id) REFERENCES production_batch(batch_id),
    FOREIGN KEY (inspector_id) REFERENCES operator(operator_id)
);

CREATE TABLE storage(
    storage_id INTEGER PRIMARY KEY,
    batch_id INTEGER NOT NULL,
    location VARCHAR(100) NOT NULL,
    condition VARCHAR(100) NOT NULL,
    quantity DECIMAL(15,4) NOT NULL,
    observations VARCHAR(500),
    storage_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    retrieval_date TIMESTAMP,
    FOREIGN KEY (batch_id) REFERENCES production_batch(batch_id)
);

-- =============================================
-- MATERIAL MANAGEMENT TABLES
-- =============================================

CREATE TABLE material_request(
    request_id INTEGER PRIMARY KEY,
    order_id INTEGER NOT NULL,
    request_number VARCHAR(50) NOT NULL UNIQUE,
    request_date DATE DEFAULT CURRENT_DATE,
    required_date DATE NOT NULL,
    priority INTEGER DEFAULT 1,
    observations TEXT,
    FOREIGN KEY (order_id) REFERENCES customer_order(order_id)
);

CREATE TABLE material_request_detail(
    detail_id INTEGER PRIMARY KEY,
    request_id INTEGER NOT NULL,
    material_id INTEGER NOT NULL,
    requested_quantity DECIMAL(15,4) NOT NULL,
    approved_quantity DECIMAL(15,4),
    FOREIGN KEY (request_id) REFERENCES material_request(request_id),
    FOREIGN KEY (material_id) REFERENCES raw_material_base(material_id)
);

CREATE TABLE supplier_response(
    response_id INTEGER PRIMARY KEY,
    request_id INTEGER NOT NULL,
    supplier_id INTEGER NOT NULL,
    response_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    confirmed_quantity DECIMAL(15,4),
    delivery_date DATE,
    observations TEXT,
    price DECIMAL(15,2),
    FOREIGN KEY (request_id) REFERENCES material_request(request_id),
    FOREIGN KEY (supplier_id) REFERENCES supplier(supplier_id)
);

-- =============================================
-- SEQUENCES FOR MANUAL ID MANAGEMENT
-- =============================================

CREATE SEQUENCE unit_of_measure_seq START WITH 1;
CREATE SEQUENCE status_seq START WITH 1;
CREATE SEQUENCE movement_type_seq START WITH 1;
CREATE SEQUENCE operator_role_seq START WITH 1;
CREATE SEQUENCE customer_seq START WITH 1;
CREATE SEQUENCE raw_material_category_seq START WITH 1;
CREATE SEQUENCE supplier_seq START WITH 1;
CREATE SEQUENCE standard_variable_seq START WITH 1;
CREATE SEQUENCE machine_seq START WITH 1;
CREATE SEQUENCE process_seq START WITH 1;
CREATE SEQUENCE operator_seq START WITH 1;
CREATE SEQUENCE raw_material_base_seq START WITH 1;
CREATE SEQUENCE raw_material_seq START WITH 1;
CREATE SEQUENCE customer_order_seq START WITH 1;
CREATE SEQUENCE production_batch_seq START WITH 1;
CREATE SEQUENCE batch_raw_material_seq START WITH 1;
CREATE SEQUENCE material_movement_log_seq START WITH 1;
CREATE SEQUENCE process_machine_seq START WITH 1;
CREATE SEQUENCE process_machine_variable_seq START WITH 1;
CREATE SEQUENCE process_machine_record_seq START WITH 1;
CREATE SEQUENCE process_final_evaluation_seq START WITH 1;
CREATE SEQUENCE storage_seq START WITH 1;
CREATE SEQUENCE material_request_seq START WITH 1;
CREATE SEQUENCE material_request_detail_seq START WITH 1;
CREATE SEQUENCE supplier_response_seq START WITH 1;

