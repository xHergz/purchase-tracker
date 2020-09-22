<?php
    require_once __DIR__.'/../../common/api/DeleteRequest.php';
    require_once __DIR__.'/../../common/api/GetRequest.php';
    require_once __DIR__.'/../../common/api/PostRequest.php';
    require_once __DIR__.'/../../common/api/PutRequest.php';
    require_once __DIR__.'/../../common/api/IApiEndpoint.php';
    require_once __DIR__.'/../data/Transaction.php';
    require_once __DIR__.'/../dataaccesslayers/AuthorizationDal.php';
    require_once __DIR__.'/../dataaccesslayers/TransactionDal.php';
    require_once __DIR__.'/../enum/Errors.php';

    class TransactionEndpoint implements IApiEndpoint {
        const USER_ID_KEY = 'userId';

        const SUBCATEGORY_ID_KEY = 'subcategoryId';

        const LOCATION_KEY = 'location';

        const CREDIT_AMOUNT_KEY = 'creditAmount';

        const DEBIT_AMOUNT_KEY = 'debitAmount';

        const TRANSACTION_DATE_KEY = 'transactionDate';

        const DESCRIPTION_KEY = 'description';

        const VALIDATE_TRANSACTION_FOR_CREATE = 'ValidateTransactionForCreate';

        public function get() {
            BadRequest(HttpStatus::METHOD_NOT_ALLOWED);
        }

        public function post() {
            // Endpoint configuration
            $canBeEmpty = false;
            $canBeByUniqueId = false;
            $requiredParameters = [
                self::USER_ID_KEY,
                self::SUBCATEGORY_ID_KEY,
                self::LOCATION_KEY,
                self::CREDIT_AMOUNT_KEY,
                self::DEBIT_AMOUNT_KEY,
                self::TRANSACTION_DATE_KEY,
                self::DESCRIPTION_KEY
            ];
            $permissionNeeded = null;
            $request = new PostRequest();

            // Set up endpoint
            $request->ValidateRequest($canBeEmpty, $canBeByUniqueId, $requiredParameters);
            $authorizationDal = $request->InitializeDal(AuthorizationDal::class);
            $request->AuthorizeRequest([$authorizationDal, AuthorizationDal::IS_API_KEY_PERMITTED], $permissionNeeded);
            $transactionDal = $request->InitializeDal(TransactionDal::class);

            // Perform endpoint specific logic
            $transaction = new Transaction(
                $request->GetKey(self::USER_ID_KEY),
                $request->GetKey(self::SUBCATEGORY_ID_KEY),
                $request->GetKey(self::LOCATION_KEY),
                $request->GetKey(self::CREDIT_AMOUNT_KEY),
                $request->GetKey(self::DEBIT_AMOUNT_KEY),
                $request->GetKey(self::TRANSACTION_DATE_KEY),
                $request->GetKey(self::DESCRIPTION_KEY)
            );
            $request->ValidateInput([$this, self::VALIDATE_TRANSACTION_FOR_CREATE], $transaction);
            $response = $transactionDal->CreateTransaction($transaction->GetUserId(), $transaction->GetSubcategoryId(), $transaction->GetLocation(),
                $transaction->GetCreditAmount(), $transaction->GetDebitAmount(), $transaction->GetTransactionDate(), $transaction->GetDescription());
            
            // Complete the request
            $request->CompleteRequestWithStatus(
                ApiRequest::STATUS_LABEL,
                $response->GetOutput(TransactionDal::STATUS)
            );
            $transactionDal->Close();
        }

        public function put() {
            BadRequest(HttpStatus::METHOD_NOT_ALLOWED);
        }

        public function delete() {
            BadRequest(HttpStatus::METHOD_NOT_ALLOWED);
        }

        public function options() {
            header('Allow: GET, POST, PUT, DELETE, OPTIONS');
        }

        public function ValidateTransactionForCreate($transaction) {
            if ($transaction->GetUserId() == null) {
                return Errors::USER_ID_IS_REQUIRED;
            }
            else if (!$transaction->ValidateUserId()) {
                return Errors::USER_ID_IS_INVALID;
            }
            else if ($transaction->GetSubcategoryId() == null) {
                return Errors::SUBCATEGORY_ID_IS_REQUIRED;
            }
            else if (!$transaction->ValidateSubcategoryId()) {
                return Errors::SUBCATEGORY_ID_IS_INVALID;
            }
            else if ($transaction->GetLocation() == null) {
                return Errors::LOCATION_IS_REQUIRED;
            }
            else if (!$transaction->ValidateLocation()) {
                return Errors::LOCATION_IS_INVALID;
            }
            else if ($transaction->GetCreditAmount() == null && $transaction->GetDebitAmount() == null) {
                return Errors::CREDIT_OR_DEBIT_AMOUNT_IS_REQUIRED;
            }
            else if (!$transaction->ValidateCreditAmount()) {
                return Errors::CREDIT_AMOUNT_IS_INVALID;
            }
            else if (!$transaction->ValidateDebitAmount()) {
                return Errors::DEBIT_AMOUNT_IS_INVALID;
            }
            else if ($transaction->GetTransactionDate() == null) {
                return Errors::TRANSACTION_DATE_IS_REQUIRED;
            }
            else if (!$transaction->ValidateTransactionDate()) {
                return Errors::TRANSACTION_DATE_IS_INVALID;
            }
            else if (!$transaction->ValidateDescription()) {
                return Errors::DESCRIPTION_IS_INVALID;
            }
            else {
                return Errors::SUCCESS;
            }
        }
    }
?>