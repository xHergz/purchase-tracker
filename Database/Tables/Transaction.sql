CREATE TABLE Transaction (
    Transaction_Id INT NOT NULL AUTO_INCREMENT,
    User_Id INT NOT NULL,
    Subcategory_Id INT NOT NULL,
    Location VARCHAR(100) NOT NULL,
    Credit_Amount DOUBLE(8, 2),
    Debit_Amount DOUBLE(8, 2),
    Transaction_Date DATE NOT NULL,
    Entry_Date DATETIME NOT NULL,
    Description VARCHAR(250),
    PRIMARY KEY (Transaction_Id),
    FOREIGN KEY (User_Id) REFERENCES User(User_Id),
    FOREIGN KEY (Subcategory_Id) REFERENCES Subcategory(Subcategory_Id)
);
