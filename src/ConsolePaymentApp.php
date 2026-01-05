<?php

require_once "src/exception/ValidationException.php";
require_once "src/exception/EntityCreationException.php";
require_once "src/exception/ServerErrorException.php";
require_once "src/exception/EntitySearchException.php";
require_once "src/entity/Client.php";
require_once "src/entity/Order.php";
require_once "src/entity/Virement.php";
require_once "src/entity/PayPal.php";
require_once "src/entity/Carte.php";
require_once "src/repository/ClientRepository.php";
require_once "src/repository/OrderRepository.php";
require_once "src/repository/PaymentRepository.php";

class ConsolePaymentApp
{


    private $clientRepository;
    private $orderRepository;
    private $paymentRepository;


    public function __construct()
    {
        $this->clientRepository = new ClientRepository();
        $this->orderRepository = new OrderRepository();
        $this->paymentRepository = new PaymentRepository();
    }


    public function run()
    {

        echo "╔═══════════════════════════════════════════════════════════╗\n";
        echo "║     SYSTÈME DE GESTION DE PAIEMENT - CONSOLE APP          ║\n";
        echo "╚═══════════════════════════════════════════════════════════╝\n";


        try {

            while (true) {
                $this->displayMenu();
                $choix = $this->readUserInput("\nVeuiller entrez votre choix svp: \n");

                match ($choix) {
                    "1" => $this->createClient(),
                    "2" => $this->listAllClients(),
                    "3" => $this->updateClient(),
                    "4" => $this->deleteClient(),
                    "5" => $this->createOrder(),
                    "6" => $this->listAllOrders(),
                    "7" => $this->updateOrder(),
                    "8" => $this->deleteOrder(),
                    "9" => $this->createPayment(),
                    "10" => $this->updatePayment(),
                    "11" => $this->changePaymentStatus(),
                    "12" => $this->listAllPayments(),
                    "13" => $this->deletePayment(),
                    "0" => $this->exitApp()
                };
            }
        } catch (ValidationException $e) {
            echo "\n \n Code: " . $e->getCode() . " Message:" . $e->getMessage();
        } catch (EntityCreationException $e) {
            echo "\n \n Code: " . $e->getCode() . " Message:" . $e->getMessage();
        } catch (ServerErrorException $e) {
            echo "\n \n Code: " . $e->getCode() . " Message:" . $e->getMessage();
        } catch (EntitySearchException $e) {
            echo "\n \n Code: " . $e->getCode() . " Message:" . $e->getMessage();
        }
    }


    public function displayMenu()
    {
        echo "\n";
        echo "┌─────────────────────────────────────────────────────────┐\n";
        echo "│ MENU PRINCIPAL                                          │\n";
        echo "├─────────────────────────────────────────────────────────┤\n";
        echo "│ 1. Créer un client                                      │\n";
        echo "│ 2. Lister les clients                                   │\n";
        echo "│ 3. Mettre à jour un client                              │\n";
        echo "│ 4. supprimer un client                                  │\n";
        echo "│ 5. Créer une commande                                   │\n";
        echo "│ 6. Lister les commandes                                 │\n";
        echo "│ 7. Mettre à jour une commande                           │\n";
        echo "│ 8. supprimer une commande                               │\n";
        echo "│ 9. Créer un paiement                                    │\n";
        echo "│ 10. Traiter un paiement                                 │\n";
        echo "│ 11. Consulter le statut d'un paiement                   │\n";
        echo "│ 12. Lister tous les paiements                           │\n";
        echo "│ 13. supprimer un paiement                               │\n";
        echo "│ 0. Quitter                                              │\n";
        echo "└─────────────────────────────────────────────────────────┘\n";
    }


    public function readUserInput($prompt)
    {
        $input = readline($prompt);
        $input = trim($input);

        if (empty($input) && $input != 0) {
            throw new ValidationException("ERROR vient de: " . $prompt, 100);
        }
        return $input;
    }




    public function createClient()
    {
        echo "\n Demande de création d'un client: \n";

        $name = $this->readUserInput(" Entrez le nom du client: ");
        $email = $this->readUserInput("\n Entrez l'email du client: ");

        $client  = new Client($name, $email);

        return $this->clientRepository->create($client);
    }

    public function listAllClients()
    {

        $clients = $this->clientRepository->findAll();

        if (empty($clients)) {

            echo "\n Aucun client n'est présent a ce moment !\n";
            return;
        }

        printf("%-5s  %-30s  %-50s\n", "ID", "Name", "Email");

        foreach ($clients as $client) {
            printf("%-5s  %-30s  %-50s \n", $client->id, $client->name, $client->email);
        }
    }

    public function updateClient()
    {
        echo "\n Mise à jour d'un client: \n";

        $this->listAllClients();
        $id = $this->readUserInput("Veuillez choisir l'id du client: ");

        $oldclient = $this->clientRepository->findById($id);

        $name = $this->readUserInput(" Entrez le nouveau nom du client: ");
        $email = $this->readUserInput("\n Entrez le nouveau email du client: ");

        $client  = new Client($name, $email);
        $client->setId($oldclient->id);

        return $this->clientRepository->update($client);
    }

    public function deleteClient()
    {
        echo "\n Demande de suppression d'un client: \n";

        $this->listAllClients();
        $id = $this->readUserInput("Veuillez choisir l'id du client: ");

        $client = $this->clientRepository->findById($id);

        return $this->clientRepository->delete($client->id);
    }

    public function createOrder()
    {
        echo "\n Demande de création d'une commande: \n";

        $this->listAllClients();
        $id = $this->readUserInput("Veuillez choisir l'id du client: ");

        $client = $this->clientRepository->findById($id);
        $montantTotal = $this->readUserInput("\n Veuillez entrez le montant total de la commande: ");

        $order  = new Order($montantTotal);
        $order->setClient($client);

        return $this->orderRepository->create($order);
    }

    public function listAllOrders()
    {

        $orders = $this->orderRepository->findAll();

        if (empty($orders)) {
            echo "\n Aucune commande n'est présente à ce moment !\n";
            return;
        }

        printf("%-5s  %-20s  %-20s  %-20s  \n", "ID", "Client Name", "Total Amount", "Status");

        foreach ($orders as $ord) {
            printf("%-5s  %-20s  %-20s  %-20s  \n", $ord->id, $ord->client->name, $ord->totalAmount, $ord->status);
        }
    }

    public function updateOrder()
    {
        echo "\n Demande de Mise à jour d'une commande: \n";

        $this->listAllOrders();
        $id = $this->readUserInput("Veuillez choisir l'id du commande: ");

        $oldOrder = $this->orderRepository->findById($id);
        $totalAmount = $this->readUserInput(" Entrez le nouveau montant de la commande: ");

        $order  = new Order($totalAmount, $oldOrder->status);
        $order->setId($oldOrder->id);
        $this->listAllClients();
        $id = $this->readUserInput("Veuillez choisir l'id du nouveau client: ");

        $client = $this->clientRepository->findById($id);
        $order->setClient($client);

        return $this->orderRepository->update($order);
    }

    public function deleteOrder()
    {
        echo "\n Demande de création d'un client: \n";

        $this->listAllOrders();
        $id = $this->readUserInput("Veuillez choisir l'id de la commande a supprimer: ");

        $order = $this->orderRepository->findById($id);

        return $this->orderRepository->delete($order->id);
    }

    public function createPayment()
    {
        echo "\n Demande de création d'un Payment: \n";

        $this->listAllOrders();
        $id = $this->readUserInput("Veuillez choisir l'id de la commande: ");

        $order = $this->orderRepository->findById($id);

        echo "\n\n";
        echo "┌────────────────────────────┐\n";
        echo "│ Payment  MENU              │\n";
        echo "├────────────────────────────┤\n";
        echo "│ 1. Virement                │\n";
        echo "│ 2. Carte                   │\n";
        echo "│ 3. PayPal                  │\n";
        echo "└────────────────────────────┘\n";

        $choix = $this->readUserInput("\n Veuillez choisir un mode de payment: ");

        $payment = match ($choix) {
            "1" => $this->createVirementInstance($order),
            "2" => $this->createCarteInstance($order),
            "3" => $this->createPayPalInstance($order)
        };

        $payment->setOrder($order);

        $payment = $this->paymentRepository->create($payment);

        $this->orderRepository->update($order);
    }

    public function listAllPayments()
    {
        $payments = $this->paymentRepository->findAll();

        if (empty($payments)) {

            echo "\n Aucun paiement n'est présent a ce moment !\n";
            return;
        }

        printf("%-5s  %-20s  %-40s  %-10s  %-40s  %-40s  %-40s\n", "ID", "Montant", "Status", "Order ID", "Info1", "Info2");

        foreach ($payments as $payment) {
            printf("%-5s  %-20s  %-40s  %-10s  %-40s  %-40s  %-40s\n", $payment->id, $payment->amount, $payment->status, $payment->creditCardNumber || $payment->email || $payment->rib, $payment->expirationDate || $payment->password || "-------");
        }
    }

    public function changePaymentStatus()
    {

        $this->listAllPayments();
        $id = $this->readUserInput("Veuillez choisir l'id du paiement: ");
        echo "\n\n";
        echo "┌────────────────────────────┐\n";
        echo "│ Payment Status MENU        │\n";
        echo "├────────────────────────────┤\n";
        echo "│ 1. Virement                │\n";
        echo "│ 2. Carte                   │\n";
        echo "│ 3. PayPal                  │\n";
        echo "└────────────────────────────┘\n";
    }

    public function updatePayment()
    {
        echo "\n Demande de Mise à jour d'une paiement: \n";

        $this->listAllPayments();
        $id = $this->readUserInput("Veuillez choisir l'id du paiement: ");

        $oldPayment = $this->paymentRepository->findById($id);
        $this->listAllOrders();
        $orderid = $this->readUserInput(" Entrez le nouveau commande du paiement: ");
        $order = $this->orderRepository->findById($orderid);
        $this->listAllClients();
        $id = $this->readUserInput("Veuillez choisir l'id du nouveau client: ");

        $client = $this->clientRepository->findById($id);
        $order->setClient($client);

        return $this->orderRepository->update($order);
    }

    public function deletePayment()
    {
        echo "\n Demande de suppression d'une paiement: \n";

        $this->listAllPayments();
        $id = $this->readUserInput("Veuillez choisir l'id du paiement: ");

        $oldPayment = $this->paymentRepository->findById($id);

        return $this->paymentRepository->delete($id);
    }

    public function createVirementInstance($order)
    {
        $rib = $this->readUserInput("\n Entrez le rib: ");
        $payment = new Virement($order->totalAmount, $rib);
        return $payment;
    }



    public function createCarteInstance($order)
    {
        $cardNumber = $this->readUserInput("\n Entrez le numéro de la carte: ");
        $expirationDate = $this->readUserInput("\n Entrez le date d'expiration de la carte (YYYY-MM-01):");
        $payment = new creditcard($order->totalAmount, $cardNumber, $expirationDate);
        return $payment;
    }

    public function createPayPalInstance($order)
    {
        $email = $this->readUserInput("\n Entrez l'email: ");
        $password = $this->readUserInput("\n Entrez le password: ");
        $payment = new PayPal($order->totalAmount, $email, $password);
        return $payment;
    }

    public function exitApp()
    {
        echo "\n exiting app ..... \n";
        exit(0);
    }
}
