<?php
    require_once __DIR__.'/ApiRequest.php';

    class DeleteRequest extends ApiRequest {
        public function __construct() {
            parent::__construct($_GET);
        }
    }
?>