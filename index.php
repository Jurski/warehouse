<?php

require 'vendor/autoload.php';

use App\Database\Database;
use App\Log\Log;
use App\Product\Product;
use App\Product\ProductManager;
use Respect\Validation\Validator as v;

$userOptions = [
    "1" => "Add product",
    "2" => "Change amount",
    "3" => "Withdraw product",
    "4" => "Delete product",
    "5" => "Save",
    "6" => "Exit",
];


echo "---===Warehouse app===---" . PHP_EOL;

$user = strtolower(trim(readline("Enter your username: ")));

$content = file_get_contents("data/users.json");
if ($content === false) {
    echo "Unable to read users.json";
    exit;
}

$registeredUsers = json_decode($content, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo "Unable to parse users.json: " . json_last_error_msg();
    exit;
}

if (!isset($registeredUsers[$user])) {
    exit("You are not registered!");
}

$definedPassword = $registeredUsers[$user]['password'];

$password = strtolower(trim(readline("Enter your password:")));

if ($definedPassword === $password) {
    echo "Welcome to the warehouse $user!" . PHP_EOL;
} else {
    exit("Wrong password!");
}

$logger = new Log();
$productManager = new ProductManager();
$loadedProducts = Database::loadData($user);
$productManager->setProducts($loadedProducts);

function isNaturalNumber(string $number): bool
{
    $NaturalNumberValidator = v::digit()->positive();
    if (!$NaturalNumberValidator->validate($number)) {
        return false;
    }
    return true;
}

while (true) {
    echo "Options:" . PHP_EOL;
    foreach ($userOptions as $option => $value) {
        echo "- $option: $value" . PHP_EOL;
    }

    echo "Your stock - " . PHP_EOL;
    echo $productManager->displayProducts();

    $inputOption = trim(readline("Enter what you want to do:"));

    switch ($inputOption) {
        case "1":
            $id = trim(readline("Enter product id (positive integer):"));
            if (!isNaturalNumber($id)) {
                echo "Please enter a valid id!" . PHP_EOL;
                break;
            }

            $name = strtolower(trim(readline("Enter product name:")));

            if ($name === "") {
                echo "Dont leave this field empty!\n";
                break;
            }

            $amount = readline("Enter product amount (positive integer):");
            if (!isNaturalNumber($amount)) {
                echo "Please enter valid amount!" . PHP_EOL;
                break;
            }

            $product = new Product($id, $name, $amount);
            $productAdded = $productManager->addProduct($product);

            if ($productAdded) {
                $logger->logChange($product, "added", $user);
                echo "===Product added===\n";
            } else {
                echo "You entered an existing id for a product - please enter a unique id!\n";
            }

            break;
        case "2":
            $id = readline("Enter product id:");
            if (!isNaturalNumber($id)) {
                echo "Please enter a valid id!" . PHP_EOL;
                break;
            }

            $amount = readline("Enter product amount (positive integer):");
            if (!isNaturalNumber($amount)) {
                echo "Please enter valid amount!" . PHP_EOL;
                break;
            }

            $amountChanged = $productManager->changeAmount($id, $amount);

            if ($amountChanged) {
                $product = $productManager->getProduct($id);
                $logger->logChange($product, "changed amount to $amount", $user);
                echo "===Product amount changed===\n";
            } else {
                echo "Couldnt find a product with this id, enter en existing id!\n";
            }

            break;
        case "3":
            $id = readline("Enter product id:");
            if (!isNaturalNumber($id)) {
                echo "Please enter a valid id!" . PHP_EOL;
                break;
            }

            $amount = readline("Enter product amount (positive integer):");
            if (!isNaturalNumber($amount)) {
                echo "Please enter valid amount!" . PHP_EOL;
                break;
            }

            $withdrew = $productManager->withdrawAmount($id, $amount);

            if ($withdrew) {
                $product = $productManager->getProduct($id);
                $logger->logChange($product, "withdrew $amount pcs", $user);
                echo "===Product amount deducted===\n";
            } else {
                echo "Amount to withdraw is too big or id is incorrect!\n";
            }

            break;
        case "4":
            $id = readline("Enter product id:");
            if (!isNaturalNumber($id)) {
                echo "Please enter a valid id!" . PHP_EOL;
                break;
            }

            $deleted = $productManager->deleteProduct($id);

            if ($deleted) {
                $logger->logChange(null, "deleted", $user);
                echo "===Product deleted===\n";
            } else {
                echo "Couldnt find a product with this id, enter en existing id!\n";
            }

            break;
        case "5":
            $data = $productManager->getProducts();
            Database::saveData($user, $data);
            echo "===Data saved===" . PHP_EOL;
            break;
        case "6":
            exit("Goodbye!");
        default:
            echo "Unknown option: $inputOption" . PHP_EOL;
    }
}