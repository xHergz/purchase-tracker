<?php
    require_once __DIR__.'/../../common/database/DatabaseConnection.php';

    define("CreateTransaction", "CreateTransaction");

    class TransactionDal extends DatabaseConnection {
        public function CreateTransaction($userId, $subcategoryId, $location, $creditAmount, $debitAmount, $transactionDate, $description) {
            $parameterArray = array(
                new InputParameter('_userId', $userId, PDO::PARAM_INT),
                new InputParameter('_subcategoryId', $subcategoryId, PDO::PARAM_INT),
                new InputParameter('_location', $location, PDO::PARAM_STR),
                new InputParameter('_creditAmount', $creditAmount, PDO::PARAM_STR),
                new InputParameter('_debitAmount', $debitAmount, PDO::PARAM_STR),
                new InputParameter('_transactionDate', $transactionDate, PDO::PARAM_STR),
                new InputParameter('_description', $description, PDO::PARAM_STR),
                new OutputParameter('_status', DatabaseConnection::STATUS, PDO::PARAM_STR)
            );
            return $this->ExecuteStoredProcedure(CreateTransaction, $parameterArray);
        }
    }
?>
