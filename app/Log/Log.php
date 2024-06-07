<?php

namespace App\Log;

use App\Product\Product;
use Carbon\Carbon;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class Log {
    private Logger $logger;

    public function __construct() {
        $this->logger = new Logger('warehouse');
        $this->logger->pushHandler(new StreamHandler(__DIR__ . "../../../logs/warehouse.log", Logger::DEBUG));
    }

    public function logChange(?Product $product, string $action, string $user): void
    {
        if($product !== null) {

            if ($action !== 'added') {
                $product->setLastUpdatedAt(Carbon::now('UTC'));
            }

            $name = $product->getName();
            $this->logger->info("$user made a change for product - $name: $action.");
        } else {
            $this->logger->info("$user did an action - $action.");
        }
    }
}