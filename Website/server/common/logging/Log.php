<?php
    require_once __DIR__. "/../utilities/SystemUtilities.php";

    define("WindowsLogDirectory", "C:\\xampp\\apache\\logs\\");
    define("LinuxLogDirectory", "/var/hergbot/MyJustinHergott/logs/");
    define("LogExtension", ".log");
    define("ApiLogKey", "API_LOG");
    define("ApiFileName", "HouseHoldApi");
    define("DatabaseLogKey", "DATABASE_LOG");
    define("DatabaseFileName", "HouseHoldDatabase");

    class Log {
        public const API_LOG_KEY = ApiLogKey;

        public const DATABASE_LOG_KEY = DatabaseLogKey;

        private $_logPath;

        private $_logQueue;

        private $_fileName;

        public static function CreateLog($key) {
            switch($key) {
                case ApiLogKey:
                    return new Log(ApiFileName);
                case DatabaseLogKey:
                    return new Log(DatabaseFileName);
                default:
                    throw new Exception("Log Key '" . $key . "' not implemented");
            }
        }

        public function __construct($fileName) {
            $this->CheckIfFolderExists();
            $this->_logPath = $this->GetLogFilePath($fileName);
            $this->_logQueue = array();
            $this->_fileName = $fileName;
        }

        public function LogDebug($message) {
            $message = $this->GetTimestamp() . "[DBG] " . $message . "\r\n";
            $this->LogMessage($message);
        }

        public function QueueDebug($message) {
            $message = "[DBG] " . $message . "\r\n";
            $this->QueueMessage($message);
        }

        public function LogError($message) {
            $message = $this->GetTimestamp() . "[ERR] " . $message . "\r\n";
            $this->LogMessage($message);
        }
        
        public function QueueError($message) {
            $message = "[ERR] " . $message . "\r\n";
            $this->QueueMessage($message);
        }

        public function LogInformation($message) {
            $message = $this->GetTimestamp() . "[INF] " . $message . "\r\n";
            $this->LogMessage($message);
        }

        public function QueueInformation($message) {
            $message = "[INF] " . $message . "\r\n";
            $this->QueueMessage($message);
        }

        public function LogWarning($message) {
            $message = $this->GetTimestamp() . "[WRN] " . $message . "\r\n";
            $this->LogMessage($message);
        }

        public function QueueWarning($message) {
            $message = "[WRN] " . $message . "\r\n";
            $this->QueueMessage($message);
        }

        public function LogQueuedMessages() {
            $queueLength = count($this->_logQueue);
            $fullMessage = "";
            for($count = 0; $count < $queueLength; $count++) {
                $fullMessage .= ($this->GetTimestamp() . array_shift($this->_logQueue));
            }
            $this->LogMessage($fullMessage);
        }

        private function GetDirectory() {
            if (IsWindows()) {
                return WindowsLogDirectory;
            }
            return LinuxLogDirectory;
        }

        private function GetLogFilePath($fileName) {
            return $this->GetDirectory() . $fileName . "_" . date("Y-m-d") . LogExtension;
        }

        private function CheckIfFolderExists() {
            $directory = $this->GetDirectory();
            if(!file_exists($directory) && is_dir($directory)) {
                mkdir($directory, 0755, true);
            }
        }

        private function GetTimestamp() {
            return '['.date("Y-m-d H:i:s").']';
        }

        private function LogMessage($message) {
            file_put_contents($this->GetLogFilePath($this->_fileName), $message, FILE_APPEND | LOCK_EX);
        }

        private function QueueMessage($message) {
            array_push($this->_logQueue, $message);
        }
    }
?>