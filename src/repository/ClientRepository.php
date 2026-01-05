<?php


require_once "src/config/database.php";
require_once "src/repository/BaseRepository.php";

class ClientRepository implements BaseRepository
{

    private PDO $conn;


    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
    }


    public function findAll()
    {
        $query = "SELECT * FROM clients WHERE 1=1";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_OBJ);

        $clients = [];
        foreach ($result as $obj) {

            $cl = new Client($obj->name, $obj->email);
            $cl->setId($obj->id);
            array_push($clients, $cl);
        }

        return $clients;
    }

    public function findById($id) {

        $query = "SELECT * FROM clients WHERE id = :id";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ":id" => $id
            ]);

            $row = $stmt->fetch(PDO::FETCH_OBJ);

            if (empty($row)) {
                throw new EntitySearchException(" Client search with id: ".$id." error ", 403);
            }

            $client = new Client($row->name, $row->email);
            $client->setId($id);

            return $client;

        } catch (\Throwable $th) {
            throw new EntitySearchException(" Client search with id: ".$id." error ", 403);
        }

    }

    public function create($client)
    {

        $query = "INSERT INTO clients(name,email) VALUES(:name, :email)";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ":name" => $client->name,
                ":email" => $client->email
            ]);

            (int) $id = $this->conn->lastInsertId();

            if ($id) {
                $client->setId($id);
                return $client;
            }

            throw new EntityCreationException(" Client creation error ", 403);
        } catch (\Throwable $th) {
            throw new EntityCreationException(" Client creation error ", 403);
        }
    }


    public function update($client) {

        $query = "UPDATE clients SET name =:name, email =:email WHERE id=:id";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ":id" => $client->id,
                ":name" => $client->name,
                ":email" => $client->email
            ]);

        } catch (\Throwable $th) {
            throw new EntityCreationException(" Client with id: ".$client->id. "update error", 403);
        }
    }


    public function delete($id) {

        $client = $this->findById($id);

        $query = "DELETE FROM clients WHERE id=:id";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ":id" => $client->id,
            ]);

        } catch (\Throwable $th) {
            throw new EntityCreationException(" Client with id: ".$client->id. "delete error", 403);
        }

    }
}
