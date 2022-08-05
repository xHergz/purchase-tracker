DELIMITER $$
CREATE PROCEDURE RegenerateUserApiKey
(
    IN _userId INT,
    OUT _status SMALLINT
)
BEGIN
    DECLARE USER_ID_DOES_NOT_EXIST SMALLINT DEFAULT 1001;
    DECLARE API_KEY_COLLISION SMALLINT DEFAULT 1002;

    DECLARE newApiKey VARCHAR(256) DEFAULT NULL;

    RegenerateUserApiKey:BEGIN
        IF (!DoesUserIdExist(_userId)) THEN
            SET _status = USER_ID_DOES_NOT_EXIST;
            LEAVE RegenerateUserApiKey;
        END IF;

        SET newApiKey = UUID();

        IF (DoesUserApiKeyExist(newApiKey)) THEN
            SET _status = API_KEY_COLLISION;
            LEAVE RegenerateUserApiKey;
        END IF;

        UPDATE
            User
        SET
            Api_Key = newApiKey
        WHERE
            User.User_Id = _userId;
        
        SET _status = 0;
    END;
END
$$
DELIMITER ;
