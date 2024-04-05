# CPSC304 Project
Two assertions are needed for our project but we did not implement: 
CREATE ASSERTION totalStock CHECK (NOT EXISTS ((SELECT stockID FROM Stock) EXCEPT (SELECT stockID FROM Have_Stock)));
CREATE ASSERTION totalBond CHECK (NOT EXISTS ((SELECT bondID FROM Bond) MINUS (SELECT bondID FROM Have_Bond)));

The first assertion checks the participation constraint for the entity set Stock and the other one checks the participation constraint for the entity set Bond.
