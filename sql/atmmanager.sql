SET foreign_key_checks = 0;

-- Create Bank table
DROP TABLE IF EXISTS Bank;
CREATE TABLE Bank (
    Bank_ID INT NOT NULL AUTO_INCREMENT,
    Name VARCHAR(50) NOT NULL,
    Hours TIME NOT NULL,
    Phone VARCHAR(15) NOT NULL,
    PRIMARY KEY (Bank_ID)
);

-- Create ATM table
DROP TABLE IF EXISTS ATM;
CREATE TABLE ATM (
    ATM_ID INT NOT NULL AUTO_INCREMENT,
    Hours TIME NOT NULL,
    Cash_Stored DECIMAL(10,2) NOT NULL,
    Location VARCHAR(50) NOT NULL,
    Branch_ID INT NOT NULL,
    Bank_ID INT NOT NULL,
    PRIMARY KEY (ATM_ID),
    FOREIGN KEY (Branch_ID) REFERENCES Branch(Branch_ID),
    FOREIGN KEY (Bank_ID) REFERENCES Bank(Bank_ID)
);

-- Create User table
DROP TABLE IF EXISTS User;
CREATE TABLE User (
    User_ID INT NOT NULL AUTO_INCREMENT,
    Email VARCHAR(50) NOT NULL,
    Zip_code VARCHAR(5) NOT NULL,
    Phone VARCHAR(15) NOT NULL,
    Name VARCHAR(50) NOT NULL,
    PRIMARY KEY (User_ID)
);

-- Create Employee table
DROP TABLE IF EXISTS Employee;
CREATE TABLE Employee (
    Employee_ID INT NOT NULL AUTO_INCREMENT,
    Job_title VARCHAR(50) NOT NULL,
    Department VARCHAR(50) NULL,
    User_ID INT NOT NULL,
    PRIMARY KEY (Employee_ID),
    FOREIGN KEY (User_ID) REFERENCES User(User_ID)
);

-- Create Branch table
DROP TABLE IF EXISTS Branch;
CREATE TABLE Branch (
    Branch_ID INT NOT NULL AUTO_INCREMENT,
    Hours TIME NOT NULL,
    Phone VARCHAR(15) NOT NULL,
    Location VARCHAR(50) NOT NULL,
    Name VARCHAR(50) NOT NULL,
    Bank_ID INT NOT NULL,
    PRIMARY KEY (Branch_ID),
    FOREIGN KEY (Bank_ID) REFERENCES Bank(Bank_ID)
);

-- Create Account_Information table
DROP TABLE IF EXISTS Account_Information;
CREATE TABLE Account_Information (
    Routing_Number INT NOT NULL,
    CVC INT NOT NULL,
    Account_Number INT NOT NULL,
    Tokenization INT NOT NULL AUTO_INCREMENT,
    Bank_ID INT NOT NULL,
    User_ID INT NOT NULL,
    PRIMARY KEY (Tokenization),
    FOREIGN KEY (Bank_ID) REFERENCES Bank(Bank_ID),
    FOREIGN KEY (User_ID) REFERENCES User(User_ID)
);

-- Create Transaction table
DROP TABLE IF EXISTS Transaction;
CREATE TABLE Transaction (
    Transaction_ID INT NOT NULL AUTO_INCREMENT,
    Amount DECIMAL(10,2) NOT NULL,
    Transaction_Type VARCHAR(50) NOT NULL,
    Transaction_Date DATE NOT NULL,
    PRIMARY KEY (Transaction_ID)
);

SET foreign_key_checks = 1;