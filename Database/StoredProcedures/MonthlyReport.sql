DELIMITER $$
CREATE PROCEDURE MonthlyReport
(
    IN _userId INT,
    IN _subcategories VARCHAR(1024),
    IN _startDate DATE,
    IN _endDate DATE,
    OUT _status SMALLINT
)
BEGIN
    MonthlyReport:BEGIN
        SELECT
            EXTRACT(YEAR_MONTH FROM Transaction_Date) AS Month_Year,
            Subcategory_Id,
            Subcategory_Description,
            SUM(Debit_Amount) AS Debit_Sum,
            SUM(Credit_Amount) AS Credit_Sum
        FROM
            Transaction_Details
        WHERE
            Transaction_Date >= _startDate
            AND Transaction_Date <= _endDate
        GROUP BY
            Month_Year,
            Subcategory_Id
        ORDER BY
            Month_Year,
            Subcategory_Id;
        
        SET _status = 0;
    END;
END
$$
DELIMITER ;
