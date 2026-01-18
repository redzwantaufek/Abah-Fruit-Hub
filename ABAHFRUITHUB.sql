CREATE SEQUENCE cust_id_seq 
INCREMENT BY 1
START WITH 1000
MAXVALUE 10000
NOCACHE
NOCYCLE;

CREATE SEQUENCE staff_id_seq
INCREMENT BY 1 
START WITH 3000
MAXVALUE 30000
NOCACHE
NOCYCLE;

CREATE SEQUENCE supp_id_seq
START WITH 5000
INCREMENT BY 1 
MAXVALUE 50000
NOCACHE
NOCYCLE;

CREATE SEQUENCE fruit_id_seq
START WITH 100
INCREMENT BY 1
MAXVALUE 1000
NOCACHE
NOCYCLE;

CREATE SEQUENCE order_id_seq
START WITH 4000 
INCREMENT BY 1
MAXVALUE 40000
NOCACHE
NOCYCLE;

CREATE SEQUENCE orderdtl_id_seq 
START WITH 1 
INCREMENT BY 1 
MAXVALUE 1000
NOCACHE
NOCYCLE;



CREATE TABLE CUSTOMER (
    CustId NUMBER(5),
    CustName VARCHAR2(100),
    CustPhone VARCHAR2(20),
    CustEmail VARCHAR2(100),
    CustAddress VARCHAR2(255),
    
    CONSTRAINT custid_pk PRIMARY KEY (CustId),
    CONSTRAINT custname_nn CHECK (CustName IS NOT NULL),
    CONSTRAINT custemail_nn CHECK (CustEmail IS NOT NULL),
    CONSTRAINT custemail_uk UNIQUE (CustEmail),
    CONSTRAINT custaddress_nn CHECK (CustAddress IS NOT NULL)
);

CREATE TABLE SUPPLIER (
    SupplierId NUMBER(5),
    SupplierName VARCHAR2(100) NOT NULL,
    SupplierContact VARCHAR2(100),
    SupplierPhone VARCHAR2(20),
    SupplierEmail VARCHAR2(100),
    SupplierType VARCHAR2(15),
    
    CONSTRAINT supplierid_pk PRIMARY KEY (SupplierId),
    CONSTRAINT suppliername_nn CHECK (SupplierName IS NOT NULL),
    CONSTRAINT supplierphone_nn CHECK (SupplierPhone IS NOT NULL),
    CONSTRAINT supplieremail_nn CHECK (SupplierEmail IS NOT NULL),
    CONSTRAINT supplieremail_uk UNIQUE (SupplierEmail),
    CONSTRAINT suppliertype_nn CHECK (SupplierType IS NOT NULL),
    CONSTRAINT suppliertype_type CHECK (SupplierType IN ('LOCALFARM', 'DISTRIBUTOR'))
);

CREATE TABLE LOCALFARM (
    SupplierId NUMBER(5),
    FarmAddress VARCHAR2(255) NOT NULL,
    CONSTRAINT supplierid_pk1 PRIMARY KEY (SupplierId),
    CONSTRAINT supplierid_fk1 FOREIGN KEY (SupplierId) REFERENCES SUPPLIER(SupplierId) ON DELETE CASCADE,
    CONSTRAINT farmAddress_nn CHECK (FarmAddress IS NOT NULL)
);

CREATE TABLE DISTRIBUTOR (
    SupplierId NUMBER(5),
    BusinessLicenseNo VARCHAR2(50),
    DistributionCenterId VARCHAR2(50),
    LogisticPartner VARCHAR2(50),
    CONSTRAINT supplierid_pk2 PRIMARY KEY (SupplierId),
    CONSTRAINT supplierid_fk2 FOREIGN KEY (SupplierId) REFERENCES SUPPLIER(SupplierId) ON DELETE CASCADE,
    CONSTRAINT businesslicense_nn CHECK (BusinessLicenseNo IS NOT NULL)
);

CREATE TABLE STAFFS (
    StaffId NUMBER(5),
    StaffName VARCHAR2(100),
    StaffSalary NUMBER(10, 2),
    StaffAddress VARCHAR2(255),
    StaffEmail VARCHAR2(100),
    StaffPhone VARCHAR2(20),
    ManagerId NUMBER(5),
    StaffPassword VARCHAR2(15),
    StaffRole VARCHAR2(20) DEFAULT 'STAFF',
    HireDate DATE DEFAULT SYSDATE,
    
    CONSTRAINT staffid_pk PRIMARY KEY (StaffId),
    CONSTRAINT staffname_nn CHECK (StaffName IS NOT NULL),
    CONSTRAINT staffsalary_ck CHECK (StaffSalary > 0),
    CONSTRAINT managerid_fk FOREIGN KEY (ManagerId) REFERENCES STAFFS(StaffId) ON DELETE SET NULL,
    CONSTRAINT staffemail_nn CHECK (StaffEmail IS NOT NULL),
    CONSTRAINT staffemail_uk UNIQUE (StaffEmail),
    CONSTRAINT staffpassword_nn CHECK (StaffPassword IS NOT NULL),
    CONSTRAINT staffrole_ck CHECK (StaffRole IN ('ADMIN', 'STAFF'))
);

CREATE TABLE FRUITS (
    FruitId NUMBER(5),
    FruitName VARCHAR2(100),
    FruitPrice NUMBER(10, 2),
    QuantityStock NUMBER(10, 2) DEFAULT 0,--tukar ni
    Category VARCHAR2(50),
    ExpireDate DATE,
    ImageURL VARCHAR2(255),
    SupplierId NUMBER(5),
    
    CONSTRAINT fruitid_pk PRIMARY KEY (FruitId),
    CONSTRAINT fruitname_nn CHECK (FruitName IS NOT NULL),
    CONSTRAINT quantitystock_ck CHECK (QuantityStock >= 0),
    CONSTRAINT expiredate_nn CHECK (ExpireDate IS NOT NULL),
    CONSTRAINT supplierid_fk3 FOREIGN KEY (SupplierId) REFERENCES SUPPLIER(SupplierId) ON DELETE SET NULL
);

CREATE TABLE ORDERS (
    OrderId NUMBER(10),
    OrderDate DATE DEFAULT SYSDATE,
    CustId NUMBER(5),
    StaffId NUMBER(5),
    TotalAmount NUMBER(10, 2) DEFAULT 0, 
    PaymentMethod VARCHAR2(50), 
    OrderStatus VARCHAR2(20) DEFAULT 'COMPLETED', 
    
    CONSTRAINT orderid_pk PRIMARY KEY (OrderId),
    CONSTRAINT ordercustId_fk FOREIGN KEY (CustId) REFERENCES CUSTOMER(CustId) ON DELETE CASCADE,
    CONSTRAINT orderstaffId_fk FOREIGN KEY (StaffId) REFERENCES STAFFS(StaffId) ON DELETE SET NULL
);

CREATE TABLE ORDERDETAILS (
    OrderDetailsId NUMBER(10),
    OrderId NUMBER(10),
    FruitId NUMBER(5),
    Quantity NUMBER(10),
    
    CONSTRAINT orderdetailsId_pk PRIMARY KEY (OrderDetailsId),
    CONSTRAINT orderdetails_orderid_fk FOREIGN KEY (OrderId) REFERENCES ORDERS(OrderId) ON DELETE CASCADE,
    CONSTRAINT orderdetails_fruitid_fk FOREIGN KEY (FruitId) REFERENCES FRUITS(FruitId)
);

--TABLE STAFF
INSERT INTO STAFFS (StaffId, StaffName, StaffSalary, StaffAddress, StaffEmail, StaffPhone, ManagerId, StaffPassword, StaffRole, HireDate)
VALUES (staff_id_seq.NEXTVAL, 'Miran bin Sudie', 5000, 'Merlimau, Melaka', 'admin@fruithub.com', '012-1112222', NULL, 'admin123', 'ADMIN', SYSDATE);

INSERT INTO STAFFS (StaffId, StaffName, StaffSalary, StaffAddress, StaffEmail, StaffPhone, ManagerId, StaffPassword, StaffRole, HireDate)
VALUES (staff_id_seq.NEXTVAL, 'Izzairi bin Syafiq', 2500, 'Jasin, Melaka', 'izzairi@fruithub.com', '012-3334444', 3000, 'staffali', 'STAFF', SYSDATE);

INSERT INTO STAFFS (StaffId, StaffName, StaffSalary, StaffAddress, StaffEmail, StaffPhone, ManagerId, StaffPassword, StaffRole, HireDate)
VALUES (staff_id_seq.NEXTVAL, 'Izzat bin Abu', 2500, 'Bandar Melaka', 'izzat@fruithub.com', '012-5556666', 3000, 'staffizzat', 'STAFF', SYSDATE);

--TABLE CUSTOMER
INSERT INTO CUSTOMER (CustId, CustName, CustPhone, CustEmail, CustAddress)
VALUES (cust_id_seq.NEXTVAL, 'Redzwan', '013-1234567', 'redzwan@gmail.com', 'Taman Seri Mendapat, Melaka');

INSERT INTO CUSTOMER (CustId, CustName, CustPhone, CustEmail, CustAddress)
VALUES (cust_id_seq.NEXTVAL, 'Niel', '017-9876543', 'niel@gmail.com', 'Taman Seri Mendapat, Melaka');

--TABLE SUPPLIER
INSERT INTO SUPPLIER (SupplierId, SupplierName, SupplierContact, SupplierPhone, SupplierEmail, SupplierType)
VALUES (supp_id_seq.NEXTVAL, 'Kebun Pak Uzair', 'Uzair bin Ali', '019-1112222', 'pakuzair@farm.com', 'LOCALFARM');

INSERT INTO SUPPLIER (SupplierId, SupplierName, SupplierContact, SupplierPhone, SupplierEmail, SupplierType)
VALUES (supp_id_seq.NEXTVAL, 'DFarm Fruit', 'Mr. Awie', '03-8889999', 'contact@dfarmfruit.com', 'DISTRIBUTOR');

--TABLE Inheritance LOCALFARM DAN DISTRIBUTOR
INSERT INTO LOCALFARM (SupplierId, FarmAddress) VALUES (5000, 'Bemban, Melaka');
INSERT INTO DISTRIBUTOR (SupplierId, BusinessLicenseNo, DistributionCenterId, LogisticPartner) 
VALUES (5001, 'LLN12345', 'DC-SOUTH-01', 'Lalamove');

--TABLE FRUITS
INSERT INTO FRUITS (FruitId, FruitName, FruitPrice, QuantityStock, Category, ExpireDate, SupplierId)
VALUES (fruit_id_seq.NEXTVAL, 'Durian Musang King', 50.00, 20, 'LOCAL', '31-JAN-2026', 5000);

INSERT INTO FRUITS (FruitId, FruitName, FruitPrice, QuantityStock, Category, ExpireDate, SupplierId)
VALUES (fruit_id_seq.NEXTVAL, 'Anggur', 2.50, 100, 'IMPORTED', '30-JAN-2026', 5001);

--TABLE ORDERS
INSERT INTO ORDERS (OrderId, OrderDate, CustId, StaffId, TotalAmount, PaymentMethod, OrderStatus)
VALUES (order_id_seq.NEXTVAL, SYSDATE, 1000, 3000, 52.50, 'QR', 'COMPLETED');

--TABLE ORDERDETAILS
INSERT INTO ORDERDETAILS (OrderDetailsId, OrderId, FruitId, Quantity)
VALUES (orderdtl_id_seq .NEXTVAL, 4000, 100, 1);

INSERT INTO ORDERDETAILS (OrderDetailsId, OrderId, FruitId, Quantity)
VALUES (orderdtl_id_seq.NEXTVAL, 4000, 101, 1);

COMMIT;