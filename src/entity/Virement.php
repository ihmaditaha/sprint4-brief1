<?php

include_once "src/entity\Payment.php";

class Virement extends Payment
{

    private $rib;

    public function __construct($montant, $rib, $paymentDate = null)
    {
        parent::__construct($montant, $paymentDate);
        $this->rib = $rib;
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
