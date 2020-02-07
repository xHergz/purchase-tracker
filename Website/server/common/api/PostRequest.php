<?php
    require_once __DIR__.'/ApiRequest.php';

    class PostRequest extends ApiRequest {
        public function __construct() {
            parent::__construct(empty($_POST) ? GetJsonInput() : $_POST);
        }
    }
?>