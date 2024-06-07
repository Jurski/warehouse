<?php


namespace App\Database;

use App\Product\Product;
use Exception;

class Database
{
    public static function saveData(string $user, array $data): void
    {
        file_put_contents("data/$user.json", json_encode($data, JSON_PRETTY_PRINT));
    }

    public static function loadData(string $user): array
    {
        try {
            if (file_exists("data/$user.json")) {
                $productData = json_decode(file_get_contents("data/$user.json"), true);
                $data = [];

                foreach ($productData as $product) {
                    $data[$product["id"]] = new Product(
                        $product["id"],
                        $product["name"],
                        $product["amount"],
                        $product["createdAt"],
                        $product["lastUpdatedAt"]
                    );
                }
                return $data;
            } else {
                throw new Exception("Products not found, add a product first!");
            }
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage() . PHP_EOL;
        }
        return [];
    }
}