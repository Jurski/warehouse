<?php

namespace App;

use Carbon\Carbon;
use Exception;
use Monolog\Logger;
use \InitPHP\CLITable\Table;

class ProductManager
{
    private array $products = [];
    private Logger $logger;
    private string $currentUser;

    public function __construct(Logger $logger, string $currentUser)
    {
        $this->currentUser = $currentUser;
        $this->logger = $logger;
    }

    public function getCurrentUser(): string
    {
        return $this->currentUser;
    }

    public function addProduct(Product $product): string
    {
        $id = $product->getId();

        if ($this->isUniqueId($id)) {
            $this->products[$id] = $product;
            $this->logChange($id, "added");
            return "===Product added===\n";
        }

        return "You entered an existing id for a product - please enter a unique id!\n";
    }

    public function changeAmount(int $id, int $amount): string
    {
        if (!$this->isUniqueId($id)) {
            $productToAdjust = $this->products[$id];
            $productToAdjust->setAmount($amount);

            $this->logChange($id, "changed amount to $amount");
            return "===Product amount changed===\n";
        }
        return "Couldnt find a product with this id, enter en existing id!\n";

    }

    public function withdrawAmount(int $id, int $amount): string
    {

        if (!$this->isUniqueId($id)) {
            $productToAdjust = $this->products[$id];
            $currentAmount = $productToAdjust->getAmount();
            $updatedAmount = $currentAmount - $amount;


            if ($updatedAmount <= 0) {
                return "Amount to withdraw is too big, withdraw cant result in amount of 0 or < 0!\n";
            }

            $productToAdjust->setAmount($updatedAmount);
            $this->logChange($id, "withdrew $amount pcs");
            return "===Product amount deducted===\n";
        }
        return "Couldnt find a product with this id\n";

    }

    public function deleteProduct(int $id): string
    {
        if (!$this->isUniqueId($id)) {
            $this->logChange($id, "deleted");
            unset($this->products[$id]);
            return "===Product deleted===" . PHP_EOL;
        }
        return "Couldnt find a product with this id, enter en existing id!" . PHP_EOL;;

    }

    public function displayProducts(): string
    {
        if (count($this->products) !== 0) {
            $table = new Table();

            foreach ($this->products as $product) {
                $createdAtUTC = $product->getCreatedAt();
                $lastUpdatedAtUTC = $product->getLastUpdatedAt();

                $createdAtLocal = $createdAtUTC->setTimezone('Europe/Riga')->format('Y-m-d H:i:s');
                if ($lastUpdatedAtUTC !== null) {
                    $lastUpdatedAtLocal = $lastUpdatedAtUTC->setTimezone('Europe/Riga')->format('Y-m-d H:i:s');
                } else {
                    $lastUpdatedAtLocal = '[NULL]';
                }

                $table->row([
                    'id' => $product->getId(),
                    'name' => $product->getName(),
                    'amount' => $product->getAmount(),
                    'created at' => $createdAtLocal,
                    'last updated at' => $lastUpdatedAtLocal,
                ]);


            }

            return $table;
        }
        return "No products to display!\n";
    }

    public function saveData(): void
    {
        $userName = $this->getCurrentUser();

        file_put_contents("data/$userName.json", json_encode($this->products, JSON_PRETTY_PRINT));
    }

    public function loadData(): void
    {
        $userName = $this->getCurrentUser();

        try {
            if (file_exists("data/$userName.json")) {
                $productData = json_decode(file_get_contents("data/$userName.json"), true);
                foreach ($productData as $product) {
                    $this->products[$product["id"]] = new Product(
                        $product["id"],
                        $product["name"],
                        $product["amount"],
                        $product["createdAt"],
                        $product["lastUpdatedAt"]
                    );
                }
            } else {
                throw new Exception("Products not found, add a product first!");
            }
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage() . PHP_EOL;
        }
    }

    private function isUniqueId(int $id): bool
    {
        if (in_array($id, array_keys($this->products), true)) return false;

        return true;
    }

    private function logChange(int $id, string $action): void
    {
        $affectedProduct = $this->products[$id];
        $name = $affectedProduct->getName();
        $currentUser = $this->getCurrentUser();

        if ($action !== 'added') {
            $affectedProduct->setLastUpdatedAt(Carbon::now('UTC'));
        }

        $this->logger->info("$currentUser made a change for product - $name: $action.");
    }
}