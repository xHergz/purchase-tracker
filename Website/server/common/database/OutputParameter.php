<?php
    require_once __DIR__.'/DatabaseParameter.php';
    require_once __DIR__.'/ParameterDirection.php';

    class OutputParameter extends DatabaseParameter {
        public function __construct($name, $value, $type) {
            $this->Name = $name;
            $this->Value = $value;
            $this->Type = $type;
            $this->Direction = ParameterDirection::OUT;
        }
    }
?>