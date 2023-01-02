<?php

namespace Module\MiraklConnector\Mirakl;

use mysqli;

require_once(dirname(__DIR__, 2) . '/vendor/autoload.php');

#region debug script code
if (true){
    FetchOrders::fetchOrders();
    list($client, $request) = FetchOrders::getOrdersRequest();
    $response = $client->getOrders($request);
    //var_dump($response[0]);
    $invoiceFields = GridPrepare::extractInvoiceFields(...$response);
    foreach ($invoiceFields as $order) {
        var_dump($order);
        break;
        //FetchOrders::printToConsole($order);
    }
}
#endregion

$serverName = "172.18.0.2";
$userName = "root";
$password = "mycustompassword";
$dbName = "PC_CompOrders";

$conn = new mysqli($serverName, $userName, $password,$dbName);

echo( "Connecting to ".$dbName." on ".$serverName." ...\n");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo("Connected successfully\n");

/*
 * CREATE TABLE `PC_CompOrders`.`Orders` (`date` DATETIME NOT NULL , `billingAddress` INT NOT NULL , `title` VARCHAR(255) NOT NULL , `sku` INT NOT NULL , `quantity` INT NOT NULL , `basePricePerUnit` FLOAT NOT NULL , `basePrice` FLOAT NOT NULL , `totalBasePrice` FLOAT NOT NULL , `taxes` FLOAT NOT NULL , `commissionTaxRate` FLOAT NOT NULL , `shippingPrice` FLOAT NOT NULL , `shippingTaxes` FLOAT NOT NULL , `totalPrice` FLOAT NOT NULL ) ENGINE = InnoDB;
 *
 * CREATE TABLE `PC_CompOrders`.`BillingAddress` (`ID` INT NOT NULL , `city` VARCHAR(255) NOT NULL , `civility` VARCHAR(255) NOT NULL , `country` VARCHAR(32) NOT NULL , `firstname` VARCHAR(255) NOT NULL , `lastname` VARCHAR(255) NOT NULL , `phone` VARCHAR(32) NOT NULL , `state` VARCHAR(255) NOT NULL , `street` VARCHAR(255) NOT NULL , `zip_code` VARCHAR(16) NOT NULL ) ENGINE = InnoDB;
 */