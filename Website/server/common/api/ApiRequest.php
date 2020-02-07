<?php
    require_once __DIR__.'/../enum/HttpStatus.php';
    require_once __DIR__.'/../logging/Log.php';
    require_once __DIR__.'/../utilities/HttpUtilities.php';

    define("EndpointKey", "endpoint");
    define("UniqueIdKey", "uid");

    class ApiRequest {
        const STATUS_LABEL = "status";

        private $_requestParameters;

        private $_endpoint;

        private $_uniqueId;

        private $_httpMethod;

        private $_queryString;

        private $_apiKey;

        private $_log;

        public function __construct($requestParameters) {
            $this->_requestParameters = $requestParameters;
            $this->_httpMethod = $_SERVER['REQUEST_METHOD'];
            $this->_queryString = $_SERVER['QUERY_STRING'];
            // Endpoint and unique id will always come from the query string
            $queryStringVars = ParseQueryString($this->_queryString);
            $this->_endpoint = isset($queryStringVars[EndpointKey]) ? $queryStringVars[EndpointKey] : null;
            $this->_uniqueId = isset($queryStringVars[UniqueIdKey]) ? $queryStringVars[UniqueIdKey] : null;
            $this->RemoveKey(EndpointKey);
            $this->RemoveKey(UniqueIdKey);
            $this->_apiKey = GetBearerToken();
            $this->_log = Log::CreateLog(Log::API_LOG_KEY);
            $this->LogRequest();
        }

        public function AuthorizeRequest(callable $authorizationFunction, $permissionNeeded) {
            if (!$authorizationFunction($this->_apiKey, $permissionNeeded)) {
                $this->EndRequest(HttpStatus::UNAUTHORIZED, "Authorization failed for permission '{$permissionNeeded}'");
            }
        }

        public function CompleteRequestWithStatus($statusLabel, $status) {
            $response = (object) [
                $statusLabel => $status
            ];
            $this->CompleteRequest(HttpStatus::OK, $response);
        }

        public function CompleteRequestWithResult($statusLabel, $status, $resultLabel, $results) {
            $response = (object) [
                $statusLabel => $status,
                $resultLabel => $results
            ];
            $this->CompleteRequest(HttpStatus::OK, $response);
        }

        public function InvalidRequest($errorCode, $errorMessage) {
            $response = (object) [
                'error' => (object) [
                    'code' => $errorCode,
                    'message' => $errorMessage
                ]
            ];
            $this->CompleteRequest(HttpStatus::UNPROCESSABLE_ENTITY, $response);
        }

        public function EndRequest($status, $reason) {
            $this->_log->QueueError($reason);
            $this->LogResponse($status);
            $this->_log->LogQueuedMessages();
            BadRequest($status);
        }

        public function GetEndpoint() {
            return $this->_endpoint;
        }

        public function GetHttpMethod() {
            return $this->_httpMethod;
        }

        public function GetKey($key) {
            if (!$this->IsKeySet($key)) {
                return null;
            }
            return $this->_requestParameters[$key];
        }

        public function GetUniqueId() {
            return $this->_uniqueId;
        }

        public function InitializeDal($dalClass) {
            $newDal = new $dalClass;
            if (!$newDal->Initialize()) {
                $this->EndRequest(HttpStatus::INTERNAL_SERVER_ERROR, "{$dalClass} DAL could not be initialized.");
            }
            return $newDal;
        }

        public function IsEmpty() {
            return empty($this->_requestParameters) && !$this->IsForUniqueId();
        }

        public function IsForUniqueId() {
            return $this->_uniqueId != null;
        }

        public function ValidateInput(callable $validationFunction, $entity) {
            $validationReturn = $validationFunction($entity);
            if ($validationReturn != Errors::SUCCESS) {
                $this->EndRequest(HttpStatus::UNPROCESSABLE_ENTITY, "Invalid Input for {$entity->ToString()} ({$validationReturn})");
            }
        }

        public function ValidateRequest($canBeEmpty, $canBeByUniqueId, $requiredParameters) {
            if ($this->IsEmpty() && !$canBeEmpty) {
                $this->EndRequest(HttpStatus::NOT_FOUND, "Request is empty when marked can't be empty.");
            }
            else if ($this->IsForUniqueId() && !$canBeByUniqueId) {
                $this->EndRequest(HttpStatus::NOT_FOUND, "Request is for unique id when marked can't be by unique id.");
            }
            else if (!$this->IsEmpty() && !$this->IsForUniqueId() && !$this->HasRequiredParameters($requiredParameters)) {
                $this->EndRequest(HttpStatus::BAD_REQUEST, "Request does not have all required parameters.");
            }
        }

        private function CompleteRequest($status, $response) {
            $jsonResponse = $this->EncodeResponse($response);
            $this->LogResponse($status, $jsonResponse);
            $this->_log->LogQueuedMessages();
            echo $jsonResponse;
        }

        private function HasRequiredParameters($keys) {
            $parametersReceived = $this->NumberOfParameters();
            $parametersRequired = count($keys);
            if ($parametersReceived < $parametersRequired) {
                $this->_log->QueueError("Required {$parametersRequired} parameter(s) but received: {$parametersReceived}");
                return false;
            }
            foreach ($keys as $key) {
                if (!$this->IsKeySet($key)) {
                    $this->_log->QueueError("Required Parameter '{$key}' not found");
                    return false;
                }
            }
            return true;
        }

        private function IsKeySet($key) {
            return isset($this->_requestParameters[$key]);
        }

        private function LogRequest() {
            $this->_log->QueueInformation("{$this->_httpMethod} request made on end point: {$this->_endpoint}");

            $responseString = $this->_uniqueId == null ? 'N/A' : $this->_uniqueId;
            $this->_log->QueueInformation("UID: {$responseString}");

            $apiKeyString = $this->_apiKey == null ? 'None' : $this->_apiKey;
            $this->_log->QueueInformation("API Key: {$apiKeyString}");

            $parameters = json_encode($this->_requestParameters);
            $this->_log->QueueInformation("Parameters: {$parameters}");
        }

        private function LogResponse($status, $response = null) {
            $this->_log->QueueInformation("Status: {$status}");
            $responseString = $response == null ? 'N/A' : $response;
            $this->_log->QueueInformation("Response: {$responseString}");
        }

        private function NumberOfParameters() {
            return count($this->_requestParameters);
        }

        private function RemoveKey($key) {
            if ($this->IsKeySet($key)) {
                unset($this->_requestParameters[$key]);
            }
        }

        private function EncodeResponse($response) {
            if (is_array($response)) {
                // Convert all keys with Some_Key case to someKey for javascript
                $convertedResponse = $this->ConvertArrayKeys($response);
                return json_encode($convertedResponse, JSON_NUMERIC_CHECK);
            }
            return $this->EncodeResponse(json_decode(json_encode($response), true));
        }

        private function ConvertArrayKeys($array) {
            // Convert all array keys to lower camelCase for nicer json output
            $newArray = array();
            foreach($array as $key => $value) {
                if (is_array($value)) {
                    $value = $this->ConvertArrayKeys($value);
                }
                $newKey = lcfirst(str_replace("_", "", $key));
                $newArray[$newKey] = $value;
            }
            return $newArray;
        }
    }
?>