DELIMITER $$
CREATE PROCEDURE CreateTransaction
(
	IN _userId INT,
    IN _subcategoryId INT,
    IN _location VARCHAR(100),
    IN _creditAmount DOUBLE(8, 2),
    IN _debitAmount DOUBLE(8, 2),
    IN _transactionDate DATE,
    IN _description VARCHAR(250),
    OUT _status SMALLINT
)
BEGIN
    CreateTransaction:BEGIN
        INSERT INTO Transaction (User_Id, Subcategory_Id, Location, Credit_Amount, Debit_Amount, Transaction_Date, Entry_Date, Description) VALUES
        (_userId, _subcategoryId, _location, _creditAmount, _debitAmount, _transactionDate, CURRENT_TIMESTAMP, _description);
        
        SET _status = 0;
    END;
END
$$
DELIMITER ;
