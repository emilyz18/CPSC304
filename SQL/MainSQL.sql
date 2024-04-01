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

CREATE TABLE Financial_Market_Hours(
  startingHour TIMESTAMP,
  endingHour TIMESTAMP,
  PRIMARY KEY (startingHour)
);

CREATE TABLE Financial_Market_Info(
  startingHour TIMESTAMP,
  country VARCHAR2(40),
  marketDate DATE,
  PRIMARY KEY (country),
  FOREIGN KEY (startingHour) REFERENCES Financial_Market_Hours
	ON DELETE CASCADE
);

CREATE TABLE Stock_Market (
  country VARCHAR2(40),
  numberOfStocks VARCHAR2(15),
  PRIMARY KEY (country),
  FOREIGN KEY (country) REFERENCES Financial_Market_Info
	ON DELETE CASCADE
);

CREATE TABLE Bond_Market(
  country VARCHAR2(40),
  numberOfBonds VARCHAR2(15),
  PRIMARY KEY (country),
  FOREIGN KEY (country) REFERENCES Financial_Market_Info
	ON DELETE CASCADE
);

CREATE TABLE User_Address_Info (
  address VARCHAR2(60),
  phoneNumber VARCHAR2(15),
  PRIMARY KEY (address)
);

CREATE TABLE User_Contact_Info (
  email VARCHAR2(30),
  address VARCHAR2(60),
  PRIMARY KEY (email),
  FOREIGN KEY (address) REFERENCES User_Address_Info
	ON DELETE CASCADE
);

CREATE TABLE User_Info (
  email VARCHAR2(30), 
  ID CHAR(8),
  name VARCHAR2(20),
  PRIMARY KEY (ID),
  FOREIGN KEY (email) REFERENCES User_Contact_Info
	ON DELETE CASCADE
);

CREATE TABLE "In" (
  country VARCHAR2(40),
  ID CHAR(8),
  PRIMARY KEY (country, ID),
  FOREIGN KEY (country) REFERENCES Financial_Market_Info
	ON DELETE CASCADE,
  FOREIGN KEY (ID) REFERENCES User_Info
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
  FOREIGN KEY (ID) REFERENCES User_Info
	ON DELETE CASCADE
);

CREATE TABLE Public_Traded_Company (
  address VARCHAR2(60),
  name VARCHAR2(50),
  PRIMARY KEY(address)
);

CREATE TABLE Public_Traded_Company_Details (
  address VARCHAR2(60),
  companyID VARCHAR2(10),
  listingDate DATE,
  sector VARCHAR2(10),
  PRIMARY KEY (companyID),
  FOREIGN KEY (address) REFERENCES Public_Traded_Company
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
  FOREIGN KEY (companyID) REFERENCES Public_Traded_Company_Details
	ON DELETE CASCADE
);

CREATE TABLE Have_Stock (
  country VARCHAR2(40),
  stockID VARCHAR2(10),
  PRIMARY KEY (country, stockID),
  FOREIGN KEY (country) REFERENCES Financial_Market_Info
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
  FOREIGN KEY (country) REFERENCES Financial_Market_Info
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
  FOREIGN KEY (companyID) REFERENCES Public_Traded_Company_Details
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




---- INSERT DATA ----

INSERT INTO Financial_Market_Hours (startingHour, endingHour) VALUES (TO_TIMESTAMP('1970-01-01 08:00:00', 'YYYY-MM-DD HH24:MI:SS'), TO_TIMESTAMP('1970-01-01 16:00:00', 'YYYY-MM-DD HH24:MI:SS'));
INSERT INTO Financial_Market_Hours (startingHour, endingHour) VALUES (TO_TIMESTAMP('1970-01-01 09:00:00', 'YYYY-MM-DD HH24:MI:SS'), TO_TIMESTAMP('1970-01-01 17:00:00', 'YYYY-MM-DD HH24:MI:SS'));
INSERT INTO Financial_Market_Hours (startingHour, endingHour) VALUES (TO_TIMESTAMP('1970-01-01 07:00:00', 'YYYY-MM-DD HH24:MI:SS'), TO_TIMESTAMP('1970-01-01 15:00:00', 'YYYY-MM-DD HH24:MI:SS'));
INSERT INTO Financial_Market_Hours (startingHour, endingHour) VALUES (TO_TIMESTAMP('1970-01-01 10:00:00', 'YYYY-MM-DD HH24:MI:SS'), TO_TIMESTAMP('1970-01-01 18:00:00', 'YYYY-MM-DD HH24:MI:SS'));
INSERT INTO Financial_Market_Hours (startingHour, endingHour) VALUES (TO_TIMESTAMP('1970-01-01 06:00:00', 'YYYY-MM-DD HH24:MI:SS'), TO_TIMESTAMP('1970-01-01 14:00:00', 'YYYY-MM-DD HH24:MI:SS'));


INSERT INTO Financial_Market_Info (startingHour, country, marketDate) VALUES (TO_TIMESTAMP('1970-01-01 08:00:00', 'YYYY-MM-DD HH24:MI:SS'), 'USA', TO_DATE('2020-02-25', 'YYYY-MM-DD'));
INSERT INTO Financial_Market_Info (startingHour, country, marketDate) VALUES (TO_TIMESTAMP('1970-01-01 09:00:00', 'YYYY-MM-DD HH24:MI:SS'), 'China', TO_DATE('2012-03-27', 'YYYY-MM-DD'));
INSERT INTO Financial_Market_Info (startingHour, country, marketDate) VALUES (TO_TIMESTAMP('1970-01-01 07:00:00', 'YYYY-MM-DD HH24:MI:SS'), 'Germany', TO_DATE('2013-09-18', 'YYYY-MM-DD'));
INSERT INTO Financial_Market_Info (startingHour, country, marketDate) VALUES (TO_TIMESTAMP('1970-01-01 10:00:00', 'YYYY-MM-DD HH24:MI:SS'), 'Japan', TO_DATE('2016-01-07', 'YYYY-MM-DD'));
INSERT INTO Financial_Market_Info (startingHour, country, marketDate) VALUES (TO_TIMESTAMP('1970-01-01 06:00:00', 'YYYY-MM-DD HH24:MI:SS'), 'Canada', TO_DATE('2018-08-22', 'YYYY-MM-DD'));


INSERT into Stock_Market (country, numberOfStocks ) VALUES ('USA','396');
INSERT into Stock_Market (country, numberOfStocks ) VALUES ('China','789');
INSERT into Stock_Market (country, numberOfStocks ) VALUES ('Germany','147');
INSERT into Stock_Market (country, numberOfStocks ) VALUES ('Japan','258');
INSERT into Stock_Market (country, numberOfStocks ) VALUES ('Canada','357');

INSERT into Bond_Market (country, numberOfBonds ) VALUES ('USA','951');
INSERT into Bond_Market (country, numberOfBonds ) VALUES ('China','725');
INSERT into Bond_Market (country, numberOfBonds ) VALUES ('Germany','345');
INSERT into Bond_Market (country, numberOfBonds ) VALUES ('Japan','943');
INSERT into Bond_Market (country, numberOfBonds ) VALUES ('Canada','486');

INSERT INTO User_Address_Info (address, phoneNumber) VALUES ('44 Park Road', '4435792534');
INSERT INTO User_Address_Info (address, phoneNumber) VALUES ('806 E. Brown St', '7238865678');
INSERT INTO User_Address_Info (address, phoneNumber) VALUES ('789 Oak St', '5522109012');
INSERT INTO User_Address_Info (address, phoneNumber) VALUES ('99 Marine Drive', '5877972252');
INSERT INTO User_Address_Info (address, phoneNumber) VALUES ('654 Maple St', '6205477810');

INSERT INTO User_Contact_Info (email, address) VALUES ('JohnKent01@yahoo.com', '44 Park Road');
INSERT INTO User_Contact_Info (email, address) VALUES ('AliceG23@gmail.com', '806 E. Brown St');
INSERT INTO User_Contact_Info (email, address) VALUES ('BobWayne34@gmail.com', '789 Oak St');
INSERT INTO User_Contact_Info (email, address) VALUES ('KateL09@hotmail.com', '99 Marine Drive');
INSERT INTO User_Contact_Info (email, address) VALUES ('MikeDo98@msn.com', '654 Maple St');

INSERT INTO User_Info (email, ID, name) VALUES ('JohnKent01@yahoo.com', 'U5H9P2Z3', 'John Kent');
INSERT INTO User_Info (email, ID, name) VALUES ('AliceG23@gmail.com', 'U8F4Q1W7', 'Alice Green');
INSERT INTO User_Info (email, ID, name) VALUES ('BobWayne34@gmail.com', 'U2T6K9Y4', 'Bob Wayne');
INSERT INTO User_Info (email, ID, name) VALUES ('KateL09@hotmail.com', 'U3V7M1X8', 'Kate Lee');
INSERT INTO User_Info (email, ID, name) VALUES ('MikeDo98@msn.com', 'U6N2D5B9', 'Michael Do');

INSERT INTO "In" (country, ID) VALUES ('USA', 'U5H9P2Z3');
INSERT INTO "In" (country, ID) VALUES ('China', 'U8F4Q1W7');
INSERT INTO "In" (country, ID) VALUES ('Germany', 'U2T6K9Y4');
INSERT INTO "In" (country, ID) VALUES ('Japan', 'U3V7M1X8');
INSERT INTO "In" (country, ID) VALUES ('Canada', 'U6N2D5B9');

INSERT INTO Has_Account (accountID, balance, accountName, since, ID) VALUES ('ACCa2B3cD4', '5020', 'JohnKent01', TO_DATE('2020-01-15', 'YYYY-MM-DD'), 'U5H9P2Z3');
INSERT INTO Has_Account (accountID, balance, accountName, since, ID) VALUES ('ACC1eFg2H3', '10810', 'AliceG23', TO_DATE('2021-07-20', 'YYYY-MM-DD'), 'U8F4Q1W7');
INSERT INTO Has_Account (accountID, balance, accountName, since, ID) VALUES ('ACCiJ4k5Lm', '7500', 'BobWayne34', TO_DATE('2023-03-10', 'YYYY-MM-DD'), 'U2T6K9Y4');
INSERT INTO Has_Account (accountID, balance, accountName, since, ID) VALUES ('ACC6Nop7Qr', '124100', 'KateL09', TO_DATE('2022-12-05', 'YYYY-MM-DD'), 'U3V7M1X8');
INSERT INTO Has_Account (accountID, balance, accountName, since, ID) VALUES ('ACC8S9tUvW', '9600', 'MikeDo98', TO_DATE('2021-02-10', 'YYYY-MM-DD'), 'U6N2D5B9');

INSERT INTO Public_Traded_Company(address, name) VALUES ('1 Apple Park Way', 'Apple');
INSERT INTO Public_Traded_Company(address, name) VALUES ('No. 33, Haitian Second Road', 'Tencent');
INSERT INTO Public_Traded_Company(address, name) VALUES ('Petuelring 130, 80809 München', 'Bayerische Motoren Werke AG');
INSERT INTO Public_Traded_Company(address, name) VALUES ('1600 Amphitheatre Parkway', 'Google');
INSERT INTO Public_Traded_Company(address, name) VALUES ('1 Toyota-Cho', 'Toyota Motor');

INSERT INTO Public_Traded_Company_Details(address, companyID, listingDate, sector) VALUES ('1 Apple Park Way', '12321', TO_DATE('1980-12-12', 'YYYY-MM-DD'), 'technology');
INSERT INTO Public_Traded_Company_Details(address, companyID, listingDate, sector) VALUES ('No. 33, Haitian Second Road', '12331', TO_DATE('2004-06-16', 'YYYY-MM-DD'), 'technology');
INSERT INTO Public_Traded_Company_Details(address, companyID, listingDate, sector) VALUES ('Petuelring 130, 80809 München', '12341', TO_DATE('1918-08-13', 'YYYY-MM-DD'), 'technology');
INSERT INTO Public_Traded_Company_Details(address, companyID, listingDate, sector) VALUES ('1600 Amphitheatre Parkway', '12351', TO_DATE('2004-08-19', 'YYYY-MM-DD'), 'technology');
INSERT INTO Public_Traded_Company_Details(address, companyID, listingDate, sector) VALUES ('1 Toyota-Cho', '12361', TO_DATE('1959-05-01', 'YYYY-MM-DD'), 'technology');

INSERT INTO Owns_PTC_Stock_Stock(stockID, price, dividend, tradedQuantity, PEratio, companyID) VALUES ('AAPL', '21897', '2.1', '123128', '1.2', '12321');
INSERT INTO Owns_PTC_Stock_Stock(stockID, price, dividend, tradedQuantity, PEratio, companyID) VALUES ('TCEHY', '31497', '2.0', '123128', '1.1', '12331');
INSERT INTO Owns_PTC_Stock_Stock(stockID, price, dividend, tradedQuantity, PEratio, companyID) VALUES ('BMW', '12897', '2.1', '123128', '1.3', '12341');
INSERT INTO Owns_PTC_Stock_Stock(stockID, price, dividend, tradedQuantity, PEratio, companyID) VALUES ('GOOGL', '21837', '2.4', '123128', '1.2', '12351');
INSERT INTO Owns_PTC_Stock_Stock(stockID, price, dividend, tradedQuantity, PEratio, companyID) VALUES ('TM', '22299', '1.8', '123128', '1.4', '12361');


INSERT INTO Have_Stock(country, stockID) VALUES ('USA', 'AAPL');
INSERT INTO Have_Stock(country, stockID) VALUES ('China', 'TCEHY');
INSERT INTO Have_Stock(country, stockID) VALUES ('Germany', 'BMW');
INSERT INTO Have_Stock(country, stockID) VALUES ('USA', 'GOOGL');
INSERT INTO Have_Stock(country, stockID) VALUES ('Japan', 'TM');

INSERT INTO Bond(bondID, faceValue, maturityDate) VALUES('1273809090', '1000', TO_DATE('2024-02-11', 'YYYY-MM-DD'));
INSERT INTO Bond(bondID, faceValue, maturityDate) VALUES('1284984092', '1000', TO_DATE('2024-02-12', 'YYYY-MM-DD'));
INSERT INTO Bond(bondID, faceValue, maturityDate) VALUES('8298390002', '1000', TO_DATE('2024-02-13', 'YYYY-MM-DD'));
INSERT INTO Bond(bondID, faceValue, maturityDate) VALUES('9029883888', '1000', TO_DATE('2024-02-14', 'YYYY-MM-DD'));
INSERT INTO Bond(bondID, faceValue, maturityDate) VALUES('4564729000', '1000', TO_DATE('2024-02-15', 'YYYY-MM-DD'));

INSERT INTO Have_Bond(country, bondID) VALUES ('USA', '1273809090');
INSERT INTO Have_Bond(country, bondID) VALUES ('Germany', '1284984092');
INSERT INTO Have_Bond(country, bondID) VALUES ('Japan', '8298390002');
INSERT INTO Have_Bond(country, bondID) VALUES ('China', '9029883888');
INSERT INTO Have_Bond(country, bondID) VALUES ('Canada', '4564729000');

INSERT INTO Create_Watchlist(listID, name, since, accountID) VALUES ('2345', 'a1', TO_DATE('2022-01-01', 'YYYY-MM-DD'), 'ACCa2B3cD4');
INSERT INTO Create_Watchlist(listID, name, since, accountID) VALUES ('2346', 'a2', TO_DATE('2022-01-02', 'YYYY-MM-DD'), 'ACC8S9tUvW');
INSERT INTO Create_Watchlist(listID, name, since, accountID) VALUES ('2347', 'a3', TO_DATE('2022-01-03', 'YYYY-MM-DD'), 'ACC8S9tUvW');
INSERT INTO Create_Watchlist(listID, name, since, accountID) VALUES ('2348', 'a4', TO_DATE('2022-01-04', 'YYYY-MM-DD'), 'ACC6Nop7Qr');
INSERT INTO Create_Watchlist(listID, name, since, accountID) VALUES ('2349', 'a5', TO_DATE('2022-01-05', 'YYYY-MM-DD'), 'ACC6Nop7Qr');

INSERT INTO Issue_PTC_Bond_CorporateBond(bondID, issueDate, companyID) VALUES ('1273809090', TO_DATE('2024-01-11', 'YYYY-MM-DD'), '12321');
INSERT INTO Issue_PTC_Bond_CorporateBond(bondID, issueDate, companyID) VALUES ('1284984092', TO_DATE('2024-01-12', 'YYYY-MM-DD'), '12331');
INSERT INTO Issue_PTC_Bond_CorporateBond(bondID, issueDate, companyID) VALUES ('8298390002', TO_DATE('2024-01-13', 'YYYY-MM-DD'), '12341');
INSERT INTO Issue_PTC_Bond_CorporateBond(bondID, issueDate, companyID) VALUES ('9029883888', TO_DATE('2024-01-14', 'YYYY-MM-DD'), '12351');
INSERT INTO Issue_PTC_Bond_CorporateBond(bondID, issueDate, companyID) VALUES ('4564729000', TO_DATE('2024-01-15', 'YYYY-MM-DD'), '12361');

INSERT INTO Government(country, president) VALUES ('USA', 'Joe Biden');
INSERT INTO Government(country, president) VALUES ('China', 'JinPing Xi');
INSERT INTO Government(country, president) VALUES ('Germany', 'Frank-Walter Steinmeier');
INSERT INTO Government(country, president) VALUES ('Canada', 'Justin Trudeau');
INSERT INTO Government(country, president) VALUES ('Japan', 'Fumio Kishida');

INSERT INTO Issue_Government_Bond_GovernmentBond (bondID, issueDate, country) VALUES ('1273809090', TO_DATE('2024-01-11', 'YYYY-MM-DD'), 'USA');
INSERT INTO Issue_Government_Bond_GovernmentBond (bondID, issueDate, country) VALUES ('1284984092', TO_DATE('2024-01-12', 'YYYY-MM-DD'), 'China');
INSERT INTO Issue_Government_Bond_GovernmentBond (bondID, issueDate, country) VALUES ('8298390002', TO_DATE('2024-01-13', 'YYYY-MM-DD'), 'Germany');
INSERT INTO Issue_Government_Bond_GovernmentBond (bondID, issueDate, country) VALUES ('9029883888', TO_DATE('2024-01-14', 'YYYY-MM-DD'), 'USA');
INSERT INTO Issue_Government_Bond_GovernmentBond (bondID, issueDate, country) VALUES ('4564729000', TO_DATE('2024-01-15', 'YYYY-MM-DD'), 'Japan');

INSERT INTO Operates_Stock(stockID, accountID, type, operateTime) VALUES ('AAPL', 'ACCa2B3cD4', 'buy', TO_TIMESTAMP('1970-01-01 13:00:00', 'YYYY-MM-DD HH24:MI:SS'));
INSERT INTO Operates_Stock(stockID, accountID, type, operateTime) VALUES ('TCEHY', 'ACC8S9tUvW', 'sell', TO_TIMESTAMP('1970-01-01 13:01:00', 'YYYY-MM-DD HH24:MI:SS'));
INSERT INTO Operates_Stock(stockID, accountID, type, operateTime) VALUES ('BMW', 'ACC8S9tUvW', 'buy', TO_TIMESTAMP('1970-01-01 13:00:00', 'YYYY-MM-DD HH24:MI:SS'));
INSERT INTO Operates_Stock(stockID, accountID, type, operateTime) VALUES ('GOOGL', 'ACC6Nop7Qr', 'sell', TO_TIMESTAMP('1970-01-01 13:01:00', 'YYYY-MM-DD HH24:MI:SS'));
INSERT INTO Operates_Stock(stockID, accountID, type, operateTime) VALUES ('TM', 'ACC6Nop7Qr', 'buy', TO_TIMESTAMP('1970-01-01 13:02:00', 'YYYY-MM-DD HH24:MI:SS'));

INSERT INTO Operates_Bond(bondID, accountID, type, operateTime) VALUES ('1273809090', 'ACCa2B3cD4', 'buy', TO_TIMESTAMP('1970-01-01 14:00:00', 'YYYY-MM-DD HH24:MI:SS'));
INSERT INTO Operates_Bond(bondID, accountID, type, operateTime) VALUES ('1284984092', 'ACC8S9tUvW', 'sell', TO_TIMESTAMP('1970-01-01 14:01:00', 'YYYY-MM-DD HH24:MI:SS'));
INSERT INTO Operates_Bond(bondID, accountID, type, operateTime) VALUES ('8298390002', 'ACC8S9tUvW', 'buy', TO_TIMESTAMP('1970-01-01 14:00:00', 'YYYY-MM-DD HH24:MI:SS'));
INSERT INTO Operates_Bond(bondID, accountID, type, operateTime) VALUES ('9029883888', 'ACC6Nop7Qr', 'sell', TO_TIMESTAMP('1970-01-01 14:01:00', 'YYYY-MM-DD HH24:MI:SS'));
INSERT INTO Operates_Bond(bondID, accountID, type, operateTime) VALUES ('4564729000', 'ACC6Nop7Qr', 'buy', TO_TIMESTAMP('1970-01-01 14:02:00', 'YYYY-MM-DD HH24:MI:SS'));


INSERT INTO Includes_Stock(stockID, listID, accountID) VALUES ('AAPL', '2345', 'ACCa2B3cD4');
INSERT INTO Includes_Stock(stockID, listID, accountID) VALUES ('TCEHY', '2346', 'ACC8S9tUvW');
INSERT INTO Includes_Stock(stockID, listID, accountID) VALUES ('BMW', '2347', 'ACC8S9tUvW');
INSERT INTO Includes_Stock(stockID, listID, accountID) VALUES ('GOOGL', '2348', 'ACC6Nop7Qr');
INSERT INTO Includes_Stock(stockID, listID, accountID) VALUES ('TM', '2349', 'ACC6Nop7Qr');

INSERT INTO Includes_Bond(bondID, listID, accountID) VALUES ('1273809090', '2345', 'ACCa2B3cD4');
INSERT INTO Includes_Bond(bondID, listID, accountID) VALUES ('1284984092', '2346', 'ACC8S9tUvW');
INSERT INTO Includes_Bond(bondID, listID, accountID) VALUES ('8298390002', '2347', 'ACC8S9tUvW');
INSERT INTO Includes_Bond(bondID, listID, accountID) VALUES ('9029883888', '2348', 'ACC6Nop7Qr');
INSERT INTO Includes_Bond(bondID, listID, accountID) VALUES ('4564729000', '2349', 'ACC6Nop7Qr');

