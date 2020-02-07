<?php
    require_once __DIR__.'/../../common/utilities/StringUtilities.php';
    require_once __DIR__.'/../../common/utilities/ValidationUtilities.php';

    class Transaction {
        const LOCATION_MIN_LENGTH = 1;

        const LOCATION_MAX_LENGTH = 100;

        const DESCRIPTION_MIN_LENGTH = 1;

        const DESCRIPTION_MAX_LENGTH = 250;

        private $_userId;

        private $_subcategoryId;

        private $_location;

        private $_creditAmount;

        private $_debitAmount;

        private $_transactionDate;

        private $_description;

        public function __construct($userId, $subcategoryId, $location, $creditAmount, $debitAmount, $transactionDate, $description) {
            $this->_userId = $userId;
            $this->_subcategoryId = $subcategoryId;
            $this->_location = IsNullOrEmptyString($location) ? null : $location;
            $this->_creditAmount = IsNullOrEmptyString($creditAmount) ? null : $creditAmount;
            $this->_debitAmount = IsNullOrEmptyString($debitAmount) ? null : $debitAmount;
            $this->_transactionDate = $transactionDate;
            $this->_description = IsNullOrEmptyString($description) ? null : $description;
        }

        public function GetUserId() {
            return $this->_userId;
        }

        public function GetSubcategoryId() {
            return $this->_subcategoryId;
        }

        public function GetLocation() {
            return $this->_location;
        }

        public function GetCreditAmount() {
            return $this->_creditAmount;
        }

        public function GetDebitAmount() {
            return $this->_debitAmount;
        }

        public function GetTransactionDate() {
            return $this->_transactionDate;
        }

        public function GetDescription() {
            return $this->_description;
        }

        public function ToString() {
            return "Transaction {User ID: {$this->_userId}, SubcategoryId: {$this->_subcategoryId}, Location: {$this->_location}, Credit Amount: {$this->_creditAmount}, Debit Amount: {$this->_debitAmount}, Transaction Date: {$this->_transactionDate}, Description: {$this->_description}}";
        }

        public function ValidateUserId() {
            return ValidateNumber($this->_userId);
        }

        public function ValidateSubcategoryId() {
            return ValidateNumber($this->_subcategoryId);
        }

        public function ValidateLocation() {
            return ValidateStringLength($this->_location, self::LOCATION_MIN_LENGTH, self::LOCATION_MAX_LENGTH);
        }

        public function ValidateCreditAmount() {
            if ($this->_creditAmount == null) {
                return true;
            }
            return ValidateNumber($this->_creditAmount);
        }

        public function ValidateDebitAmount() {
            if ($this->_debitAmount == null) {
                return true;
            }
            return ValidateNumber($this->_debitAmount);
        }

        public function ValidateTransactionDate() {
            return ValidateDate($this->_transactionDate, 'Y-m-d');
        }

        public function ValidateDescription() {
            if ($this->_description == null) {
                return true;
            }
            return ValidateStringLength($this->_description, self::DESCRIPTION_MIN_LENGTH, self::DESCRIPTION_MAX_LENGTH);
        }
    }
?>