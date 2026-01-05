<?php


require_once "src/config/Database.php";
require_once "src/repository/BaseRepository.php";

class OrderRepository implements BaseRepository
{

    private $clientRepository; 

    private PDO $conn;


    public function __construct()
    {
        $db = Database::getInstance();
        $this->conn = $db->getConnection();
        $this->clientRepository = new ClientRepository();
    }



    public function findAll()
    {
        $query = "SELECT * FROM orders WHERE 1=1";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_OBJ);

        $orders = [];
        foreach ($result as $obj) {

            $client = $this->clientRepository->findById($obj->client_id);

            $ord = new Order($obj->total_amount, $obj->status);
            $ord->setId($obj->id);
            $ord->setClient($client);
            array_push($orders, $ord);
        }

        return $orders;
    }

    public function findById($id)
    {

        $query = "SELECT id, total_amount, status, client_id FROM orders WHERE id = :id";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ":id" => $id
            ]);

            $row = $stmt->fetch(PDO::FETCH_OBJ);

            if (empty($row)) {
                throw new EntitySearchException(" Commande search with id: " . $id . " error ", 403);
            }

            $client = $this->clientRepository->findById($row->client_id);

            $ord = new Order($row->total_amount, $row->status);
            $ord->setId($row->id);
            $ord->setClient($client);

            return $ord;

        } catch (\Throwable $th) {
            throw new EntitySearchException("Commande search with id: " . $id . " error ", 403);
        }
    }

    public function create($order)
    {
    
        $query = "INSERT INTO orders(total_amount, status, client_id) 
        VALUES(:montantTotal, :status, :client_id)";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ":montantTotal" => $order->totalAmount,
                ":status" => $order->status,
                ":client_id" => $order->client->id,
            ]);

            (int) $id = $this->conn->lastInsertId();

            if ($id) {
                $order->setId($id);
                return $order;
            }

            throw new EntityCreationException(" Commande creation error ", 403);
        } catch (\Throwable $th) {
            throw new EntityCreationException(" Commande creation error ", 403);
        }
    }


    public function update($order)
    {

        $query = "UPDATE orders SET total_amount= :montantTotal, status= :status, client_id= :client_id WHERE id=:id";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ":id" => $order->id,
                ":montantTotal" => $order->totalAmount,
                ":status" => $order->status,
                ":client_id" => $order->client->id
            ]);

            return $order;

        } catch (\Throwable $th) {
            throw new EntityCreationException(" commande with id: " . $order->id . "update error: ".$th->getMessage(), 403);
        }
    }


    public function delete($id) {

        $order = $this->findById($id);

        $query = "DELETE FROM orders WHERE id=:id";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ":id" => $order->id,
            ]);

        } catch (\Throwable $th) {
            throw new EntityCreationException(" Client with id: ".$order->id. "delete error", 403);
        }

    }
}
