CREATE VIEW Transaction_Details
AS
SELECT
    Transaction.Transaction_Id,
    Transaction.Transaction_Date,
    Transaction.Credit_Amount,
    Transaction.Debit_Amount,
    Transaction.Location,
    Transaction.User_Id,
	User.Username,
    Category.Category_Id,
    Category.Description AS Category_Description,
    Transaction.Subcategory_Id,
    Subcategory.Description AS Subcategory_Description,
    Transaction.Entry_Date,
    Transaction.Description
FROM
	Transaction
    INNER JOIN User ON User.User_Id = Transaction.User_Id
    INNER JOIN Subcategory ON Subcategory.Subcategory_Id = Transaction.Subcategory_Id
    INNER JOIN Category ON Category.Category_Id = Subcategory.Category_Id;
    