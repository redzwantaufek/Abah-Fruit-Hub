CREATE TABLE CUSTOMER (
    CustId NUMBER(5),
    CustName VARCHAR2(100),
    CustPhone VARCHAR2(20),
    CustEmail VARCHAR2(100),
    CustAddress VARCHAR2(255),
    CustPassword VARCHAR2(15),
    
    CONSTRAINT custid_pk PRIMARY KEY (CustId),
    CONSTRAINT custname_nn CHECK (CustName IS NOT NULL),
    CONSTRAINT custemail_nn CHECK (CustEmail IS NOT NULL),
    CONSTRAINT custemail_uk UNIQUE (CustEmail),
    CONSTRAINT custaddress_nn CHECK (CustAddress IS NOT NULL),
    CONSTRAINT custpass_nn CHECK (CustPassword IS NOT NULL)
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
    CONSTRAINT suppliertype_type CHECK (SupplierType IN ( 'LOCALFARM', 'DISTRIBUTOR'))
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
    QuantityStock NUMBER(10) DEFAULT 0,
    Category VARCHAR2(50),
    ExpireDate DATE,
    ImageURL VARCHAR2(255),
    SupplierId NUMBER(5),
    
    CONSTRAINT fruitid_pk PRIMARY KEY (FruitId),
    CONSTRAINT fruitname_nn CHECK (FruitName IS NOT NULL),
    CONSTRAINT quantitystock_ck CHECK (QuantityStock >= 0),
    CONSTRAINT expiredate_nn CHECK (ExpireDate IS NOT NULL),
    CONSTRAINT supplierid_fk3 FOREIGN KEY (SupplierId) REFERENCES SUPPLIER(SupplierId)
);

CREATE TABLE ORDERS (
    OrderId NUMBER(10),
    OrderDate DATE DEFAULT SYSDATE,
    CustId NUMBER(5),
    StaffId NUMBER(5),
    TotalAmount NUMBER(10, 2) DEFAULT 0, -- Store the final total for faster reporting
    PaymentMethod VARCHAR2(50), -- 'CASH', 'QR', 'CARD'
    OrderStatus VARCHAR2(20) DEFAULT 'COMPLETED', -- 'COMPLETED', 'CANCELLED'
    
    CONSTRAINT orderid_pk PRIMARY KEY (OrderId),
    CONSTRAINT ordercustId_fk FOREIGN KEY (CustId) REFERENCES CUSTOMER(CustId),
    CONSTRAINT orderstaffId_fk FOREIGN KEY (StaffId) REFERENCES STAFFS(StaffId)
);

CREATE TABLE ORDERDETAILS (
    OrderDetailsId NUMBER(10),
    OrderId NUMBER(10),
    FruitId NUMBER(5),
    Quantity NUMBER,
    CONSTRAINT orderdetailsId_pk PRIMARY KEY (OrderDetailsId),
    CONSTRAINT orderdetails_orderid_fk FOREIGN KEY (OrderId) REFERENCES ORDERS(OrderId),
    CONSTRAINT orderdetails_fruitid_fk FOREIGN KEY (FruitId) REFERENCES FRUITS(FruitId)
);