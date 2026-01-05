<?php



class Order
{

    private $id;
    private $totalAmount;
    private $status;
    private $client;


    public const STATUS_AWAITING  = "awaiting payment";
    public const STATUS_IN_DELIVERY  = "in delivery";
    public const STATUS_DELIVERED  = "delivered";



    public function __construct($totalAmount, $status = self::STATUS_AWAITING)
    {
        $this->totalAmount = $totalAmount;
        $this->status  = $status;
    }


    public function __get($property)
    {
        return $this->$property;
    }




    public function setId($id)
    {

        if (!is_numeric($id) || (int) $id <= 0) {
            throw new ValidationException("ID doit etre un entier positif");
        }
        $this->id = (int) $id;
    }


     public function setClient($client)
    {

        if (!($client instanceof Client) && is_null($client->id) ) {
            throw new ValidationException("l'objet client passé à la commande non valide!!");
        }

        $this->client =$client;
    }

    public function setStatus($status)
    {
        $status_array = [self::STATUS_IN_DELIVERY, self::STATUS_AWAITING, self::STATUS_DELIVERED];

        if (!in_array($status, $status_array)) {

            throw new ValidationException("status doit etre dans la plage suivante: " . self::STATUS_IN_DELIVERY . "," . self::STATUS_AWAITING . "," .  self::STATUS_DELIVERED);
        }

        $this->status = $status;
    }
}
