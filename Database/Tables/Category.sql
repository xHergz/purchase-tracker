CREATE TABLE Category
(
    Category_Id INT NOT NULL AUTO_INCREMENT,
    Description VARCHAR(100) NOT NULL UNIQUE,
    PRIMARY KEY (Category_Id)
);
