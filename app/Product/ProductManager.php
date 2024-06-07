<?php

namespace App\Product;

use InitPHP\CLITable\Table;

class ProductManager
{
    private array $products = [];

    public function getProduct(int $id): ?Product
    {
        if ($id > count($this->products)) {
            return null;
        } else {
            return $this->products[$id];
        }
    }

    /**
     * @return Product[]
     */
    public function getProducts(): array
    {
        return $this->products;
    }

    public function setProducts(array $products): void
    {
        $this->products = $products;
    }

    public function addProduct(Product $product): bool
    {
        $id = $product->getId();

        if ($this->isUniqueId($id)) {
            $this->products[$id] = $product;
            return true;
        }
        return false;
    }


    public function changeAmount(int $id, int $amount): bool
    {
        $product = $this->getProduct($id);

        if (!$this->isUniqueId($id)) {
            $product->setAmount($amount);
            return true;
        }
        return false;
    }

    public function withdrawAmount(int $id, int $amount): bool
    {
        $product = $this->getProduct($id);

        if (!$this->isUniqueId($id)) {
            $currentAmount = $product->getAmount();
            $updatedAmount = $currentAmount - $amount;


            if ($updatedAmount <= 0) {
                return false;
            }

            $product->setAmount($updatedAmount);
            return true;
        }
        return false;

    }

    public function deleteProduct(int $id): bool
    {
        if (!$this->isUniqueId($id)) {
            unset($this->products[$id]);
            return true;
        }
        return false;
    }

    public function displayProducts(): string
    {
        if (count($this->products) !== 0) {
            $table = new Table();

            foreach ($this->products as $product) {
                $createdAtUTC = $product->getCreatedAt();
                $lastUpdatedAtUTC = $product->getLastUpdatedAt();

                $createdAtLocal = $createdAtUTC->setTimezone('Europe/Riga')->format('d-m-Y H:i:s');
                if ($lastUpdatedAtUTC !== null) {
                    $lastUpdatedAtLocal = $lastUpdatedAtUTC->setTimezone('Europe/Riga')->format('d-m-Y H:i:s');
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


    private function isUniqueId(int $id): bool
    {
        if (in_array($id, array_keys($this->products), true)) return false;

        return true;
    }
}