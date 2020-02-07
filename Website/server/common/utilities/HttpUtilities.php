<?php
    require_once __DIR__.'/StringUtilities.php';

    function BadRequest($httpStatus) {
        http_response_code($httpStatus);
        die();
    }
    
    // Source: https://stackoverflow.com/a/40582472/8070411
    function GetAuthorizationHeader(){
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        }
        else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        return $headers;
    }

    // Source: https://stackoverflow.com/a/40582472/8070411
    function GetBearerToken() {
        $headers = GetAuthorizationHeader();
        // HEADER: Get the access token from the header
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }

    function GetJsonInput() {
      $rest_json = file_get_contents("php://input");
      return json_decode($rest_json, true);
  }
  
  function GetPutInput() {
      $input = file_get_contents('php://input');
      if (is_json($input)) {
          return json_decode($input, true);
      }
      parse_str($input, $parsed);
      return $parsed;
  }

    function ParseQueryString($queryString) {
        $queryStringVariables = array();
        $variables = explode('&', $queryString);
      
        foreach ($variables as $variable) {
          list($name,$value) = explode('=', $variable, 2);
          
          // Check if the name already exists as variables dont have to be unqiue in the query string
          if( isset($queryStringVariables[$name]) ) {
            if( is_array($queryStringVariables[$name]) ) {
              $queryStringVariables[$name][] = $value;
            }
            else {
              $queryStringVariables[$name] = array($queryStringVariables[$name], $value);
            }
          }
          else {
            $queryStringVariables[$name] = $value;
          }
        }
      
        return $queryStringVariables;
    }
?>