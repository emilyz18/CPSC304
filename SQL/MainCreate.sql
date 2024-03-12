-- Delete all current table

BEGIN
   FOR cur_rec IN (SELECT object_name, object_type
                   FROM user_objects
                   WHERE object_type IN
                             ('TABLE',
                              'VIEW',
                              'MATERIALIZED VIEW',
                              'PACKAGE',
                              'PROCEDURE',
                              'FUNCTION',
                              'SEQUENCE',
                              'SYNONYM',
                              'PACKAGE BODY'
                             ))
   LOOP
      BEGIN
         IF cur_rec.object_type = 'TABLE'
         THEN
            EXECUTE IMMEDIATE 'DROP '
                              || cur_rec.object_type
                              || ' "'
                              || cur_rec.object_name
                              || '" CASCADE CONSTRAINTS';
         ELSE
            EXECUTE IMMEDIATE 'DROP '
                              || cur_rec.object_type
                              || ' "'
                              || cur_rec.object_name
                              || '"';
         END IF;
      EXCEPTION
         WHEN OTHERS
         THEN
            DBMS_OUTPUT.put_line ('FAILED: DROP '
                                  || cur_rec.object_type
                                  || ' "'
                                  || cur_rec.object_name
                                  || '"'
                                 );
      END;
   END LOOP;
   FOR cur_rec IN (SELECT * 
                   FROM all_synonyms 
                   WHERE table_owner IN (SELECT USER FROM dual))
   LOOP
      BEGIN
         EXECUTE IMMEDIATE 'DROP PUBLIC SYNONYM ' || cur_rec.synonym_name;
      END;
   END LOOP;
END;
/

-- Start Create Table

CREATE TABLE Financial_Market1(
  startingHour TIMESTAMP,
  endingHour TIMESTAMP,
  PRIMARY KEY (startingHour)
);

CREATE TABLE Financial_Market2(
  startingHour TIMESTAMP,
  country VARCHAR2(40),
  marketDate DATE,
  PRIMARY KEY (country),
  FOREIGN KEY (startingHour) REFERENCES Financial_Market1
	ON DELETE CASCADE
);

CREATE TABLE Stock_Market (
  country VARCHAR2(40),
  numberOfStocks VARCHAR2(15),
  PRIMARY KEY (country),
  FOREIGN KEY (country) REFERENCES Financial_Market2
	ON DELETE CASCADE
);

CREATE TABLE Bond_Market(
  country VARCHAR2(40),
  numberOfBonds VARCHAR2(15),
  PRIMARY KEY (country),
  FOREIGN KEY (country) REFERENCES Financial_Market2
	ON DELETE CASCADE
);

CREATE TABLE User1 (
  address VARCHAR2(60),
  phoneNumber VARCHAR2(15),
  PRIMARY KEY (address)
);

CREATE TABLE User3 (
  email VARCHAR2(30),
  address VARCHAR2(60),
  PRIMARY KEY (email),
  FOREIGN KEY (address) REFERENCES User1
	ON DELETE CASCADE
);

CREATE TABLE User4 (
  email VARCHAR2(30), 
  ID CHAR(8),
  name VARCHAR2(20),
  PRIMARY KEY (ID),
  FOREIGN KEY (email) REFERENCES User3
	ON DELETE CASCADE
);

CREATE TABLE "In" (
  country VARCHAR2(40),
  ID CHAR(8),
  PRIMARY KEY (country, ID),
  FOREIGN KEY (country) REFERENCES Financial_Market2
	ON DELETE CASCADE,
  FOREIGN KEY (ID) REFERENCES User4
	ON DELETE CASCADE
);

CREATE TABLE Has_Account (
  accountID CHAR(10),
  balance VARCHAR2(15),
  accountName VARCHAR2(30),
  since DATE,
  ID CHAR(8) NOT NULL,
  UNIQUE (ID),
  PRIMARY KEY (accountID),
  FOREIGN KEY (ID) REFERENCES User4
	ON DELETE CASCADE
);

CREATE TABLE Public_Traded_Company1 (
  address VARCHAR2(60),
  name VARCHAR2(50),
  PRIMARY KEY(address)
);

CREATE TABLE Public_Traded_Company2 (
  address VARCHAR2(60),
  companyID VARCHAR2(10),
  listingDate DATE,
  sector VARCHAR2(10),
  PRIMARY KEY (companyID),
  FOREIGN KEY (address) REFERENCES Public_Traded_Company1
	ON DELETE CASCADE
);

CREATE TABLE Owns_PTC_Stock_Stock (
  stockID VARCHAR2(10),
  price VARCHAR2(10),
  dividend VARCHAR2(15),
  tradedQuantity VARCHAR2(20),
  PEratio FLOAT,
  companyID VARCHAR2(10) NOT NULL,
  PRIMARY KEY (stockID),
  FOREIGN KEY (companyID) REFERENCES Public_Traded_Company2
	ON DELETE CASCADE
);

CREATE TABLE Have_Stock (
  country VARCHAR2(40),
  stockID VARCHAR2(10),
  PRIMARY KEY (country, stockID),
  FOREIGN KEY (country) REFERENCES Financial_Market2
	ON DELETE CASCADE,
  FOREIGN KEY (stockID) REFERENCES Owns_PTC_Stock_Stock
	ON DELETE CASCADE
);

CREATE TABLE Bond (
  bondID CHAR(10),
  faceValue VARCHAR2(20),
  maturityDate DATE,
  PRIMARY KEY (bondID)
);

CREATE TABLE Have_Bond ( 
  country VARCHAR2(40),
  bondID CHAR(10),
  PRIMARY KEY (country, bondID),
  FOREIGN KEY (country) REFERENCES Financial_Market2
	ON DELETE CASCADE,
  FOREIGN KEY (bondID) REFERENCES Bond
	ON DELETE CASCADE
);

CREATE TABLE Create_Watchlist (
  listID CHAR(4),
  name VARCHAR2(20),
  since DATE,
  accountID CHAR(10),
  PRIMARY KEY (accountID, listID),
  FOREIGN KEY (accountID) REFERENCES Has_Account
	ON DELETE CASCADE
);

CREATE TABLE Issue_PTC_Bond_CorporateBond (
  bondID CHAR(10),
  issueDate DATE,
  companyID VARCHAR2(10) NOT NULL,
  PRIMARY KEY (bondID),
  FOREIGN KEY (bondID) REFERENCES Bond
	ON DELETE CASCADE,
  FOREIGN KEY (companyID) REFERENCES Public_Traded_Company2
	ON DELETE CASCADE
);

CREATE TABLE Government (
  country VARCHAR2(40),
  president VARCHAR2(30),
  PRIMARY KEY (country)
);

CREATE TABLE Issue_Government_Bond_GovernmentBond (
  bondID CHAR(10),
  issueDate DATE,
  country VARCHAR2(40) NOT NULL,
  PRIMARY KEY (bondID),
  FOREIGN KEY (bondID) REFERENCES Bond
	ON DELETE CASCADE, 
  FOREIGN KEY (country) REFERENCES Government
	ON DELETE CASCADE
);

CREATE TABLE Operates_Stock (
  stockID VARCHAR2(10),
  accountID CHAR(10),
  type VARCHAR2(4),
  operateTime TIMESTAMP, 
  PRIMARY KEY (stockID, accountID),
  FOREIGN KEY (stockID) REFERENCES Owns_PTC_Stock_Stock
	ON DELETE CASCADE,
  FOREIGN KEY (accountID) REFERENCES Has_Account
);

CREATE TABLE Operates_Bond (
  bondID CHAR(10),
  accountID CHAR(10),
  type VARCHAR2(4),
  operateTime TIMESTAMP, 
  PRIMARY KEY (bondID, accountID),
  FOREIGN KEY (bondID) REFERENCES Bond
	ON DELETE CASCADE,
  FOREIGN KEY (accountID) REFERENCES Has_Account
);

CREATE TABLE Includes_Stock(
  stockID VARCHAR2(10),
  listID CHAR(4), 
  accountID CHAR(10),
  PRIMARY KEY (stockID, listID, accountID),
  FOREIGN KEY (stockID) REFERENCES Owns_PTC_Stock_Stock
	ON DELETE CASCADE,
  FOREIGN KEY (accountID, listID) REFERENCES Create_Watchlist(accountID, listID)
	ON DELETE CASCADE
);

CREATE TABLE Includes_Bond(
  bondID CHAR(10),
  listID CHAR(4), 
  accountID CHAR(10),
  PRIMARY KEY (bondID, listID, accountID),
  FOREIGN KEY (bondID) REFERENCES Bond
	ON DELETE CASCADE,
  FOREIGN KEY (accountID, listID) REFERENCES Create_Watchlist
	ON DELETE CASCADE
);
