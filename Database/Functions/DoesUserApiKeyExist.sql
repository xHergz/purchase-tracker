DELIMITER $$
CREATE FUNCTION DoesUserApiKeyExist
(
	_apiKey VARCHAR(256)
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
                User.Api_Key
            FROM
                User
            WHERE
                User.Api_Key = _apiKey
        )
    ) THEN 
        RETURN FALSE;
    END IF;

    RETURN TRUE;
END
$$
DELIMITER ;
