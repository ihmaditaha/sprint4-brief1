<?php

include_once "src/entity/Payment.php";


class Creditcard extends Payment
{

    private $creditCardNumber;
    private $expirationDate;

    public function __construct($amount, $creditCardNumber, $expirationDate, $paymentDate = null)
    {
        parent::__construct($amount, $paymentDate);
        $this->creditCardNumber = $creditCardNumber;
        $this->expirationDate = DATE('Y-m-d', strtotime($expirationDate));
    }

    public function __get($property)
    {
        return $this->$property;
    }

    public function pay()
    {
        $this->status = self::PAID;
    }
}
