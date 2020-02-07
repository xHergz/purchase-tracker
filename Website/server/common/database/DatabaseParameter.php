<?php
    require_once __DIR__.'/ParameterDirection.php';

    class DatabaseParameter {
        public $Name;

        public $Value;

        public $Type;

        public $Direction;

        public function __construct($name, $value, $type, $direction = ParameterDirection::IN) {
            $this->Name = $name;
            $this->Value = $value;
            $this->Type = $type;
            $this->Direction = $direction;
        }

        public function ToString() {
            return "Name: '" . $this->Name . "', Value:'" . $this->Value . "', Type: '" . $this->GetTypeString()
                . "', Direction: '" . $this->GetDirectionString() . "'";
        }

        private function GetTypeString() {
            switch($this->Type) {
                case PDO::PARAM_BOOL:
                    return 'BOOL';
                case PDO::PARAM_INT:
                    return 'INT';
                case PDO::PARAM_LOB:
                    return 'LOB';
                case PDO::PARAM_NULL:
                    return 'NULL';
                case PDO::PARAM_STR:
                    return 'STR';
                case PDO::PARAM_STR_NATL:
                    return 'STR_NATL';
                case PDO::PARAM_STR_CHAR:
                    return 'STR_CHAR';
                case PDO::PARAM_STMT:
                    return 'STMT';
                default:
                    throw new Exception("Unknown Database PDO Parameter Type: '" . $this->Type . "'");
            }
        }

        private function GetDirectionString() {
            switch($this->Direction) {
                case ParameterDirection::IN:
                    return 'IN';
                case ParameterDirection::OUT:
                    return 'OUT';
                default:
                    throw new Exception("Unknown Database Parameter Direction for ${$this->Name}: '" . $this->Direction . "'");
            }
        }
    }
?>