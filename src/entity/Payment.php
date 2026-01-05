<?php

abstract class Payment
{


    public const UNPAID = "Unpaid";
    public const PAID = "Paid";


    protected $id;
    protected $paymentDate;
    protected $status;
    protected $amount;
    protected $order;


    public function __construct($montant)
    {
        $this->amount = $montant;
        $this->paymentDate = date('Y-m-d');;
        $this->status = self::UNPAID;
    }


    abstract public function pay();


    public function setId($id)
    {

        if (!is_numeric($id) || (int) $id <= 0) {
            throw new ValidationException("ID doit etre un entier positif");
        }

        $this->id = (int) $id;
    }


    public function setOrder($commande)
    {

        if (!($commande instanceof Order) && is_null($commande->id)) {
            throw new ValidationException("l'objet commande passé au payment non valide!!");
        }

        $this->order = $commande;
    }

    public function setStatus($status)
    {
        $status_array = [self::PAID, self::UNPAID];

        if (!in_array($status, $status_array)) {

            throw new ValidationException("status doit etre dans la plage suivante: " . self::UNPAID . "," . self::PAID);
        }

        $this->status = $status;
    }

}
