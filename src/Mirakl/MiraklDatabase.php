<?php

namespace Module\MiraklConnector\Mirakl;

use mysqli;

require_once(dirname(__DIR__, 2) . '/vendor/autoload.php');

class MiraklDatabase
{
    public static function main()
    {

        #region debug script code
        if (true) {
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


        /*
         * CREATE TABLE `PC_CompOrders`.`BillingAddress` (`ID` INT NOT NULL , `city` VARCHAR(255) NOT NULL , `civility` VARCHAR(255) NOT NULL , `country` VARCHAR(32) NOT NULL , `firstname` VARCHAR(255) NOT NULL , `lastname` VARCHAR(255) NOT NULL , `phone` VARCHAR(32) NOT NULL , `state` VARCHAR(255) NOT NULL , `street` VARCHAR(255) NOT NULL , `zip_code` VARCHAR(16) NOT NULL ) ENGINE = InnoDB;
         *
         * CREATE TABLE `PC_CompOrders`.`Orders` (`date` DATETIME NOT NULL , `billingAddress` INT NOT NULL , `title` VARCHAR(255) NOT NULL , `sku` INT NOT NULL , `quantity` INT NOT NULL , `basePricePerUnit` FLOAT NOT NULL , `basePrice` FLOAT NOT NULL , `totalBasePrice` FLOAT NOT NULL , `taxes` FLOAT NOT NULL , `commissionTaxRate` FLOAT NOT NULL , `shippingPrice` FLOAT NOT NULL , `shippingTaxes` FLOAT NOT NULL , `totalPrice` FLOAT NOT NULL ) ENGINE = InnoDB;
         *
         * ALTER TABLE `BillingAddress` ADD PRIMARY KEY(`ID`);
         *
         * ALTER TABLE `BillingAddress` CHANGE `ID` `ID` INT(11) NOT NULL AUTO_INCREMENT;
         *
         * ALTER TABLE `Orders` ADD PRIMARY KEY(`date`, `billingAddress`, `title`, `sku`, `quantity`);
         *
         * INSERT INTO `BillingAddress` (`ID`, `city`, `civility`, `country`, `firstname`, `lastname`, `phone`, `state`, `street`, `zip_code`) VALUES (NULL, 'ARROYOMOLINOS DE LEÓN', '', 'ES', 'Reyes Carmen', 'Vázquez Carballar', '618042506', 'Huelva', 'Calle Juan Ramón Jiménez, 45', '21280');
         *
         * INSERT INTO `Orders` (`date`, `billingAddress`, `title`, `sku`, `quantity`, `basePricePerUnit`, `basePrice`, `totalBasePrice`, `taxes`, `commissionTaxRate`, `shippingPrice`, `shippingTaxes`, `totalPrice`) VALUES ('2022-05-12 08:14:35.000000', '1', 'Bresser Arcturus Telescopio Refractor 60/700 AZ con Filtro Solar y Adaptador para Móvil', '267523', '2', '92.55', '185.1', '189.19', '38.88', '21.00', '4.09', '0.86', '228.93');
         */
    }

    public static function getConnection(): mysqli
    {
        $serverName = "172.18.0.2";
        $userName = "root";
        $password = "mycustompassword";
        $dbName = "PC_CompOrders";

        $conn = new mysqli($serverName, $userName, $password, $dbName);

        echo("Connecting to " . $dbName . " on " . $serverName . " ...\n");

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        echo("Connected successfully\n");
        return $conn;
    }

    public static function createTables($conn): void
    {
        $sql = "CREATE TABLE `PC_CompOrders`.`BillingAddress` (`ID` INT NOT NULL , `city` VARCHAR(255) NOT NULL , `civility` VARCHAR(255) NOT NULL , `country` VARCHAR(32) NOT NULL , `firstname` VARCHAR(255) NOT NULL , `lastname` VARCHAR(255) NOT NULL , `phone` VARCHAR(32) NOT NULL , `state` VARCHAR(255) NOT NULL , `street` VARCHAR(255) NOT NULL , `zip_code` VARCHAR(16) NOT NULL ) ENGINE = InnoDB;";
        if ($conn->query($sql)) {
            echo("BillingAddress : Table created successfully.\n");
        }

        $sql = "CREATE TABLE `PC_CompOrders`.`Orders` (`date` DATETIME NOT NULL , `billingAddress` INT NOT NULL , `title` VARCHAR(255) NOT NULL , `sku` INT NOT NULL , `quantity` INT NOT NULL , `basePricePerUnit` FLOAT NOT NULL , `basePrice` FLOAT NOT NULL , `totalBasePrice` FLOAT NOT NULL , `taxes` FLOAT NOT NULL , `commissionTaxRate` FLOAT NOT NULL , `shippingPrice` FLOAT NOT NULL , `shippingTaxes` FLOAT NOT NULL , `totalPrice` FLOAT NOT NULL ) ENGINE = InnoDB;";
        if ($conn->query($sql)) {
            echo("Orders : Table created successfully.\n");
        }

        $sql = "ALTER TABLE `BillingAddress` ADD PRIMARY KEY(`ID`);";
        if ($conn->query($sql)) {
            echo("BillingAddress : Set ID as primary key successfully.\n");
        }

        $sql = "ALTER TABLE `BillingAddress` CHANGE `ID` `ID` INT(11) NOT NULL AUTO_INCREMENT;";
        if ($conn->query($sql)) {
            echo("BillingAddress : Set ID to auto increment successfully.\n");
        }

        $sql = "ALTER TABLE `Orders` ADD PRIMARY KEY(`date`, `billingAddress`, `title`, `sku`, `quantity`);";
        if ($conn->query($sql)) {
            echo("Orders : Set primary key successfully.\n");
        }
    }
}

$mysqli = MiraklDatabase::getConnection();
MiraklDatabase::createTables($mysqli);