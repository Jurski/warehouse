<?php

require 'vendor/autoload.php';

use App\Product;
use App\ProductManager;
use Respect\Validation\Validator as v;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$log = new Logger('warehouse');
$log->pushHandler(new StreamHandler(__DIR__ . "/logs/warehouse.log", Logger::DEBUG));

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
    echo "Unable to parse products.json: " . json_last_error_msg();
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

$productManager = new ProductManager($log, $user);

$productManager->loadData();

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

            echo $productManager->addProduct(new Product($id, $name, $amount));

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

            echo $productManager->changeAmount($id, $amount);

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

            echo $productManager->withdrawAmount($id, $amount);

            break;
        case "4":
            $id = readline("Enter product id:");
            if (!isNaturalNumber($id)) {
                echo "Please enter a valid id!" . PHP_EOL;
                break;
            }

            echo $productManager->deleteProduct($id);

            break;
        case "5":
            $productManager->saveData();
            echo "===Data saved===" . PHP_EOL;
            break;
        case "6":
            exit("Goodbye!");
        default:
            echo "Unknown option: $inputOption" . PHP_EOL;
    }
}