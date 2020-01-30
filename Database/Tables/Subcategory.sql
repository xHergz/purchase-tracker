CREATE TABLE Subcategory
(
    Subcategory_Id INT NOT NULL AUTO_INCREMENT,
    Category_Id INT NOT NULL,
    Description VARCHAR(100) NOT NULL,
    PRIMARY KEY (Subcategory_Id),
    FOREIGN KEY (Category_Id) REFERENCES Category(Category_Id)
);