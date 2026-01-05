<?php


require_once "src/config/Database.php";
require_once "src/repository/BaseRepository.php";

class PaymentRepository implements BaseRepository
{

    private $orderRepository;

    private PDO $conn;


    public function __construct()
    {
        $db = Database::getInstance();
        $this->conn = $db->getConnection();
        $this->orderRepository = new OrderRepository();
    }


    public function findAll()
    {
        $query = "SELECT
    p.id,
    p.amount,
    p.status,
    p.payment_date,
    p.order_id,
    CASE WHEN cc.id IS NOT NULL THEN cc.card_number WHEN pp.id IS NOT NULL THEN pp.email WHEN bt.id IS NOT NULL THEN bt.rib
END 'info1',
CASE WHEN cc.id IS NOT NULL THEN cc.expiration_date WHEN pp.id IS NOT NULL THEN pp.password ELSE NULL
END 'info2',
CASE WHEN cc.id IS NOT NULL THEN 'Credit Card' WHEN pp.id IS NOT NULL THEN 'PayPal' WHEN bt.id IS NOT NULL THEN 'Bank Transfer'
END 'type'
FROM
    payments p
LEFT JOIN creditcards cc ON
    p.id = cc.id
LEFT JOIN paypals pp ON
    p.id = pp.id
LEFT JOIN banktransfers bt ON
    p.id = bt.id;";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_OBJ);

        $payments = [];
        foreach ($result as $obj) {
            $payment = $this->findById($obj->id);
            array_push($payments, $payment);
        }

        return $payments;
    }

    public function findById($id)
    {

        $query = "SELECT
    p.id,
    p.amount,
    p.status,
    p.payment_date,
    CASE WHEN cc.id IS NOT NULL THEN cc.card_number WHEN pp.id IS NOT NULL THEN pp.email WHEN bt.id IS NOT NULL THEN bt.rib END 'info1',
    CASE WHEN cc.id IS NOT NULL THEN cc.expiration_date WHEN pp.id IS NOT NULL THEN pp.password ELSE NULL END 'info2',
    CASE WHEN cc.id IS NOT NULL THEN 'Credit Card' WHEN pp.id IS NOT NULL THEN 'PayPal' WHEN bt.id IS NOT NULL THEN 'Bank Transfer' END 'type'
FROM
    payments p
LEFT JOIN creditcards cc ON
    p.id = cc.id
LEFT JOIN paypals pp ON
    p.id = pp.id
LEFT JOIN banktransfers bt ON
    p.id = bt.id
WHERE p.id = :id";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ":id" => $id
            ]);

            $obj = $stmt->fetch(PDO::FETCH_OBJ);

            if (empty($row)) {
                throw new EntitySearchException(" payment search with id: " . $id . " error ", 403);
            }
            if ($obj->type == 'Bank Transfer') {
                $payment = new Virement($obj->amount, $obj->rib);
                $payment->setId($obj->id);

                $order = $this->orderRepository->findById($obj->order_id);

                $payment->setOrder($order);
            } else if ($obj->type == 'Credit Card') {
                $payment = new Creditcard($obj->amount, $obj->card_number, $obj->payment_date);
                $payment->setId($obj->id);

                $order = $this->orderRepository->findById($obj->order_id);

                $payment->setOrder($order);
            } else if ($obj->type == 'PayPal') {
                $payment = new paypal($obj->amount, $obj->email, $obj->password);
                $payment->setId($obj->id);

                $order = $this->orderRepository->findById($obj->order_id);

                $payment->setOrder($order);
            }

            return $payment;
        } catch (\Throwable $th) {
            throw new EntitySearchException("Commande search with id: " . $id . " error ", 403);
        }
    }

    public function create($payment)
    {

        $query = "insert into payments(amount, status, payment_date, order_id) 
        values(:montant,:status, :paymentDate,:order_id)";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ":montant" => $payment->amount,
                ":status" => $payment->status,
                ":paymentDate" => $payment->paymentDate,
                ":order_id" => $payment->order->id,
            ]);

            (int) $id = $this->conn->lastInsertId();

            if ($id) {
                $payment->setId($id);

                if ($payment instanceof CreditCard) {
                    $query = "insert into creditcards(id, card_number, expiration_date) 
                      values(:paiment_id, :creditCardNumber, :expiration_date)";
                    $stmt = $this->conn->prepare($query);
                    $stmt->execute([
                        ":id" => $payment->id,
                        ":creditCardNumber" => $payment->creditCardNumber,
                        ":expiration_date" => $payment->expirationDate
                    ]);
                } else if ($payment instanceof PayPal) {

                    $query = "insert into paypals(id, email, password) 
                      values(:id, :email, :password)";
                    $stmt = $this->conn->prepare($query);
                    $stmt->execute([
                        ":id" => $payment->id,
                        ":email" => $payment->paymentEmail,
                        ":password" => $payment->paymentPassword
                    ]);
                } else {

                    $query = "insert into banktransfers(id, rib) 
                      values(:id, :rib)";
                    $stmt = $this->conn->prepare($query);
                    $stmt->execute([
                        ":id" => $payment->id,
                        ":rib" => $payment->rib
                    ]);
                }
            }
        } catch (\Throwable $th) {
            throw new EntityCreationException(" Payment creation error " . $th->getMessage(), 403);
        }
    }


    public function update($payment)
    {
        $query = "UPDATE payments SET amount=:montant, status=:status, payment_date=:paymentDate, order_id=:order_id WHERE id =:id";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ":montant" => $payment->amount,
                ":status" => $payment->status,
                ":paymentDate" => $payment->paymentDate,
                ":order_id" => $payment->order->id,
                ":id" => $payment->id
            ]);

            if ($payment instanceof CreditCard) {
                $query = "UPDATE creditcards SET card_number=:creditCardNumber, expiration_date=:expiration_date WHERE id=:id";
                $stmt = $this->conn->prepare($query);
                $stmt->execute([
                    ":id" => $payment->id,
                    ":creditCardNumber" => $payment->creditCardNumber,
                    ":expiration_date" => $payment->expirationDate
                ]);
            } else if ($payment instanceof PayPal) {

                $query = "UPDATE paypals SET email=:email, password=:password WHERE id=:id";

                $stmt = $this->conn->prepare($query);
                $stmt->execute([
                    ":id" => $payment->id,
                    ":email" => $payment->paymentEmail,
                    ":password" => $payment->paymentPassword
                ]);
            } else {

                $query = "UPDATE banktransfers SET rib=:rib WHERE id=:id";

                $stmt = $this->conn->prepare($query);
                $stmt->execute([
                    ":id" => $payment->id,
                    ":rib" => $payment->rib
                ]);
            }
        } catch (\Throwable $th) {
            throw new EntitySearchException(" Payment update error " . $th->getMessage(), 403);
        }
    }


    public function delete($id)
    {
        $payment = $this->findById($id);

        $query = "DELETE FROM payments WHERE id=:id";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ":id" => $payment->id,
            ]);

        } catch (\Throwable $th) {
            throw new EntitySearchException(" Client with id: ".$payment->id. "delete error", 403);
        }
    }
}
