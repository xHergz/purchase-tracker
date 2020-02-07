<?php
    require_once __DIR__.'/../../common/database/DatabaseConnection.php';

    class AuthorizationDal extends DatabaseConnection {
        public const IS_API_KEY_PERMITTED = 'IsApiKeyPermitted';

        public function IsApiKeyPermitted($apiKey, $permissionId) {
            $parameterArray = array(
                new InputParameter('_apiKey', $apiKey, PDO::PARAM_STR)
            );
            return $this->ExecuteFunction(self::IS_API_KEY_PERMITTED, $parameterArray);
        }
    }
?>
