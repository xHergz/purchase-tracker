<?php
	require_once __DIR__.'/InputParameter.php';
	require_once __DIR__.'/OutputParameter.php';
	require_once __DIR__.'/ProcedureResponse.php';
	require_once __DIR__.'/../logging/Log.php';
	require_once __DIR__.'/../utilities/StringUtilities.php';
	require_once __DIR__.'/../utilities/SystemUtilities.php';
	
	define("WindowsDbConnectionConfig", "C:\Users\Justin\Documents\Development\Website\HouseHold\DatabaseConnection.config");
	define("LinuxDbConnectionConfig", "/etc/hergbot/MyJustinHergott/DatabaseConnection.config");
	define("ServerTag", "Server");
	define("UsernameTag", "Username");
	define("PasswordTag", "Password");
	define("DatabaseTag", "Database");
	define("InputParameterMarker", "?");
	define("OutputParameterMarker", "!");
	
	class DatabaseConnection {
		public const STATUS = "@status";

		private $_server;

		private $_username;

		private $_password;

		private $_database;

		private $_mysqlConnection;

		private $_connectionId;

		private $_transactionId;

		private $_log;
		
		public $IsConnected;
		
		public function __construct(){
			$IsConnected = false;
			$this->_log = Log::CreateLog(Log::DATABASE_LOG_KEY);
			$this->_connectionId = uniqid("DBConnection:");
			$this->_transactionId = null;
		}
		
		// Initializes the database connection
		public function Initialize(){
			$this->_log->QueueInformation("Initializing Database Connection: {$this->_connectionId}");
			//Get the connection info
			if (!$this->ParseConnectionInfo()) {
				$this->_log->QueueError('Parsing database connection info failed');
				$this->_log->LogQueuedMessages();
				return false;
			}
			
			$connectionString = "mysql:host={$this->_server};dbname={$this->_database};";
			$withPassword = ($this->_password == null) ? 'NO' : 'YES';
			$this->_log->QueueInformation("Connecting as user '{$this->_username}' to '{$connectionString}' (with password: {$withPassword})");
			try {
				$this->_mysqlConnection = new PDO($connectionString, $this->_username, $this->_password);
			}
			catch(PDOException $exception){
				//Log the error
				$this->_log->QueueError('Failed to Initialize Database Connection.');
				$this->_log->QueueError($exception->getMessage());
				$this->_log->LogQueuedMessages();
				return false;
			}
			
			$this->_log->LogQueuedMessages();
			return true;
		}

		// Closes the database connection
		public function Close() {
			// Setting the PDO Connection to null closes the connection
			$this->_mysqlConnection = null;
			$this->_log->LogInformation("Closing Database Connection: {$this->_connectionId}");
		}

		public function BeginTransaction() {
			if (!$this->IsConnected) {
				$this->_log->LogError("Could not begin transaction, database connection not connected");
				return false;
			}

			$this->_transactionId = uniqid("Transaction:");
			$this->_log->LogInformation("Beginning Transaction: {$this->_transactionId}");
			return $this->_mysqlConnection->beginTransaction();
		}

		public function CommitTransaction() {
			if (!$this->IsConnected) {
				$this->_log->LogError("Could not commit transaction '{$this->_transactionId}', database connection not connected");
				return false;
			}

			$this->_log->LogInformation("Commiting Transaction: {$this->_transactionId}");
			$this->_transactionId = null;
			return $this->_mysqlConnection->commit();
		}

		public function RollBackTransaction() {
			if (!$this->IsConnected) {
				$this->_log->LogError("Could not roll back transaction '{$this->_transactionId}', database connection not connected");
				return false;
			}

			$this->_log->LogInformation("Rolling Back Transaction: {$this->_transactionId}");
			$this->_transactionId = null;
			return $this->_mysqlConnection->rollBack();
		}

		// Execute a stored procedure with no parameters
		protected function ExecuteStoredProcedure($procedureName, $parameters = array()) {
			$this->_log->QueueInformation("Executing Stored Procedure {$procedureName} on {$this->_connectionId} {$this->GetTransactionIdString()}");
			$this->LogParameters($parameters);
			$inputParameters = array_filter($parameters, [$this, 'FilterInputParameters']);
			$outputParameters = array_filter($parameters, [$this, 'FilterOutputParameters']);

			$parameterString = $this->CreateParameterString($inputParameters, $outputParameters);
			$this->BindOutputParameters($parameterString, $outputParameters);
			$statement = $this->_mysqlConnection->prepare("CALL {$procedureName}({$parameterString});");
			$this->BindInputParameters($statement, $inputParameters);
			$this->_log->QueueInformation("Statement Query String: {$statement->queryString}");

			$beginExecutionTime = microtime(true);
			$results = $this->ExecutePdoStatement($statement);
			$statement->closeCursor();
			$outputs = $this->GetOutputParameters($outputParameters);
			$executionTime = microtime(true) - $beginExecutionTime;
			$this->_log->QueueInformation("Execution Time: {$executionTime}");

			$response = new ProcedureResponse($outputs, $results);
			$this->_log->QueueInformation("Response: {$response->ToString()}");
			$this->_log->LogQueuedMessages();
			return $response;
		}

		// Execute a function
		protected function ExecuteFunction($functionName, $parameters) {
			$this->_log->QueueInformation("Executing Function {$functionName} on {$this->_connectionId} {$this->GetTransactionIdString()}");
			$this->LogParameters($parameters);
			$parameterString = $this->CreateParameterString($parameters);
			$statement = $this->_mysqlConnection->prepare("SELECT {$functionName}({$parameterString});");
			$this->BindInputParameters($statement, $parameters);
			$this->_log->QueueInformation("Statement Query String: {$statement->queryString}");
			$beginExecutionTime = microtime(true);
			$response = $this->ExecutePdoStatement($statement);
			$executionTime = microtime(true) - $beginExecutionTime;
			$this->_log->QueueInformation("Execution Time: {$executionTime}");

			$returnValue = end($response[0]);
			$this->_log->QueueInformation("Return Value: {$returnValue}");
			$this->_log->LogQueuedMessages();
			return $returnValue;
		}

		protected function ExecuteQuery($queryString) {
			$this->_log->QueueInformation("Executing Query '{$queryString}' on {$this->_connectionId} {$this->GetTransactionIdString()}");
			$statement = $this->_mysqlConnection->prepare($queryString);
			$beginExecutionTime = microtime(true);
			$response = $this->ExecutePdoStatement($statement);
			$executionTime = microtime(true) - $beginExecutionTime;
			$jsonResponse = json_encode($response);
			$this->_log->QueueInformation("Execution Time: {$executionTime}");
			$this->_log->QueueInformation("Response: {$jsonResponse}");
			$this->_log->LogQueuedMessages();
			return $response;
		}

		// Parses the connection info from the config file
		private function ParseConnectionInfo(){
			$connectionConfigPath = "";
			if (IsWindows()) {
				$connectionConfigPath = WindowsDbConnectionConfig;
			} else {
				$connectionConfigPath = LinuxDbConnectionConfig;
			}

			$this->_log->QueueInformation("Parsing Database Connection Information from '{$connectionConfigPath}'");
			$beginParsingTime = microtime(true);
			$connectionInfo = simplexml_load_file($connectionConfigPath) or die("Unable to open connection info file");

			if (!isset($connectionInfo->Server)) {
				$this->_log->QueueError('No server tag present');
				return false;
			}
			else if(!isset($connectionInfo->Username)) {
				$this->_log->QueueError('No username tag present');
				return false;
			}
			else if(!isset($connectionInfo->Password)) {
				$this->_log->QueueError('No password tag present');
				return false;
			}
			else if(!isset($connectionInfo->Database)) {
				$this->_log->QueueError('No database tag present');
				return false;
			}

			$this->_server = $connectionInfo->Server;
			$this->_username = $connectionInfo->Username;
			$this->_password = $connectionInfo->Password;
			$this->_database = $connectionInfo->Database;
			$parsingTime = microtime(true) - $beginParsingTime;
			$this->_log->QueueInformation("Parsing Time: {$parsingTime}");
			return true;
		}

		private function FilterInputParameters($parameter) {
			if (isset($parameter->Direction) && $parameter->Direction == ParameterDirection::IN) {
				return true;
			}
			return false;
		}

		private function FilterOutputParameters($parameter) {
			if (isset($parameter->Direction) && $parameter->Direction == ParameterDirection::OUT) {
				return true;
			}
			return false;
		}

		private function CreateParameterString($inputParameters, $outputParameters = null) {
			// Create a comma separated string of markers for input parameters to be bound to a PDO statement and markers
			// for output parameters to be replaced with their names.
			$parameterString = join(', ', array_fill(0, count($inputParameters), InputParameterMarker));
			if ($outputParameters != null) {
				$outputParameterString = join(', ', array_fill(0, count($outputParameters), OutputParameterMarker));
				// Check if the parameter string is empty (no input params)
				if (count($inputParameters) == 0) {
					$parameterString = $outputParameterString;
				}
				else {
					$parameterString = $parameterString . ', ' . $outputParameterString;
				}
			}
			return $parameterString;
		}

		private function BindOutputParameters(&$parameterString, $outputParameters) {
			// Replace the !'s in the parameter string with the output parameters
			foreach($outputParameters as $param) {
				$parameterString = str_replace_first(OutputParameterMarker, $param->Value, $parameterString);
			}
		}

		private function BindInputParameters(&$statement, $inputParameters) {
			// Bind the parameters given to the statement
			$numberOfParameters = count($inputParameters);
			for($paramPos = 0; $paramPos < $numberOfParameters; $paramPos++){
				// Add one to paramPos because binding params is 1 indexed
				$statement->bindParam($paramPos + 1, $inputParameters[$paramPos]->Value, $inputParameters[$paramPos]->Type);
			}
		}

		private function ExecutePdoStatement($statement) {
			// Execute a statement and return the appropriate response
			if (!$statement->execute()) {
				$this->_log->QueueError('Failed to execute PDO Statement: '.implode(" | ", $statement->errorInfo()));
			}

			return $statement->fetchAll(PDO::FETCH_CLASS, 'stdClass');
		}

		private function GetOutputParameters($outputParameters) {
			if (count($outputParameters) == 0) {
				return array();
			}
			$queryString = "SELECT " . join(', ', array_column($outputParameters, 'Value')) . ";";
			return $this->ExecuteQuery($queryString)[0];
		}

		private function LogParameters($parameters) {
			$this->_log->QueueInformation("With Parameters:");
			if (empty($parameters)) {
				$this->_log->QueueInformation("-> None");
				return;
			}

			foreach($parameters as $parameter) {
				$paramString = $parameter->ToString();
				$this->_log->QueueInformation("-> {$paramString}");
			}
		}

		private function GetTransactionIdString() {
			if ($this->_transactionId == null) {
				return '';
			}
			return $this->_transactionId;
		}
	}
?>