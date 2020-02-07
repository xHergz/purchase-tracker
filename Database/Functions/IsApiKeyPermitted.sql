DELIMITER $$
CREATE FUNCTION IsApiKeyPermitted
(
	_userApiKey VARCHAR(256)
)
RETURNS SMALLINT
READS SQL DATA
NOT DETERMINISTIC
BEGIN
    IF
    (
        NOT EXISTS
        (
            SELECT
                User.User_Id
            FROM
                User
            WHERE
                User.Api_Key = _userApiKey
        )
    ) THEN 
        RETURN FALSE;
    END IF;

    RETURN TRUE;
END
$$
DELIMITER ;
