<?php
    require_once __DIR__.'/ApiRequest.php';

    class GetRequest extends ApiRequest {
        public function __construct() {
            parent::__construct($_GET);
        }
    }
?>