<?php
    require_once __DIR__.'/ApiRequest.php';

    class PutRequest extends ApiRequest {
        public function __construct() {
            parent::__construct(GetPutInput());
        }
    }
?>