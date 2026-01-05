<?php


include_once "src/entity/Payment.php";


class PayPal extends Payment
{

    private $paymentEmail;
    private $paymentPassword;

    public function __construct($montant, $paymentEmail, $paymentPassword, $paymentDate = null)
    {
        parent::__construct($montant, $paymentDate);
        $this->paymentEmail = $paymentEmail;
        $this->paymentPassword = $paymentPassword;
    }

    public function __get($property)
    {
        return $this->$property;
    }

    public function pay()
    {
        $this->status = self::PAID;
        $this->commande->setStatus(Order::STATUS_IN_DELIVERY);
    }
}
