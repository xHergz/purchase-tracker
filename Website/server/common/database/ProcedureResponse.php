<?php
    class ProcedureResponse {
        public $Outputs;

        public $Results;

        public $NumberOfOutputs;

        public $NumberOfResults;

        public function __construct($outputs, $results) {
            $this->Outputs = $outputs;
            $this->Results = $results;
            $this->NumberOfOutputs = count((array)$outputs);
            $this->NumberOfResults = count($results);
        }

        public function HasOutputs() {
            return empty($this->Outputs);
        }

        public function HasResults() {
            return empty($this->Results);
        }

        public function GetSingleRow() {
            if (empty($this->Results)) {
                return null;
            }
            return $this->Results[0];
        }

        public function GetSingleValue($value) {
            if (empty($this->Results)) {
                return null;
            }
            return $this->Results[0][$value];
        }

        public function GetOutput($name) {
            if (!isset($this->Outputs->$name)) {
                return null;
            }
            return $this->Outputs->$name;
        }

        public function ToString() {
            return json_encode((object) ['Outputs' => $this->Outputs, 'Results' => $this->Results]);
        }
    }
?>