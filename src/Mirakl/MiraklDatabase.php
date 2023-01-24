<?php

namespace Module\MiraklConnector\Mirakl;

use Doctrine\DBAL\Driver\Exception;
use mysqli;
use mysqli_sql_exception;

use Mirakl\MMP\Common\Domain\Order\CustomerBillingAddress;

require_once(dirname(__DIR__, 2) . '/vendor/autoload.php');

class MiraklDatabase
{
    public static function getConnection(): mysqli
    {
        $serverName = "172.18.0.2";
        $userName = "root";
        $password = "mycustompassword";
        $dbName = "PC_CompOrders";

        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $conn = new mysqli($serverName, $userName, $password, $dbName);

        //echo("Connecting to " . $dbName . " on " . $serverName . " ...\n");

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        return $conn;
    }

    public static function getOrderData(mysqli $conn, $date, $billingAddress, $title, $sku, $quantity)
    {
        /*
        $stmt = $conn->prepare(
            "SELECT * from Orders o JOIN BillingAddress ON o.billingAddress = BillingAddress.id
            WHERE o.date = ?
            AND o.billingAddress = ?
            AND o.title = ?
            AND o.sku = ?
            AND o.quantity = ?;"
        );

        $stmt->bind_param('sisii',
            $date, $billingAddress, $title, $sku, $quantity
        );*/

        $sql = "SELECT * from Orders o JOIN BillingAddress ON o.billingAddress = BillingAddress.id
            WHERE o.date LIKE '".$date."'
            AND o.billingAddress = ".$billingAddress."
            AND o.title LIKE '".$title."'
            AND o.sku = ".$sku."
            AND o.quantity = ".$quantity.";"
        ;

        try {
            return $conn->query($sql)->fetch_row();
        } catch (mysqli_sql_exception $e) {
            echo $e->getMessage() . "\n";
            return -1;
        }
    }

    /**
     * @param mysqli $conn database connection
     * @param array $order prepared order array from GridPrepare::extractInvoiceFields()
     * @return int ID of the new entry
     */
    public static function insertBillingAddress(mysqli $conn, array $order): int
    {
        $billingAddr = $order['billingAddress'];

        $stmt = $conn->prepare(
            "INSERT INTO `BillingAddress` (
                `ID`, `city`, `civility`, `country`, `firstname`, `lastname`, `phone`, `state`, `street`, `zip_code`
            ) 
            VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?);"
        );

        $city = $billingAddr->getCity();
        $civility = $billingAddr->getCivility();
        $country = $billingAddr->getCountry();
        $firstname = $billingAddr->getFirstname();
        $lastname = $billingAddr->getLastname();
        $phone = $billingAddr->getPhone();
        $state = $billingAddr->getState();
        $street1 = $billingAddr->getStreet1();
        $zipCode = $billingAddr->getZipCode();

        $stmt->bind_param('sssssssss',
            $city, $civility, $country, $firstname, $lastname, $phone, $state, $street1, $zipCode
        );

        try {
            if ($stmt->execute()) {
                echo("BillingAddress : Insertion successfully.\n");
            }
        } catch (mysqli_sql_exception $e) {
            echo $e->getMessage() . "\n";
            return -1;
        }
        $sql = "SELECT LAST_INSERT_ID();";

        return $conn->query($sql)->fetch_row()[0];
    }

    public static function insertOrder(mysqli $conn, array $order)
    {
        $billingAddress = MiraklDatabase::insertBillingAddress($conn, $order);

        #INSERT INTO `Orders` (`date`, `billingAddress`, `title`, `sku`, `quantity`, `basePricePerUnit`, `basePrice`, `totalBasePrice`, `taxes`, `commissionTaxRate`, `shippingPrice`, `shippingTaxes`, `totalPrice`) VALUES ('2022-05-12 08:14:35.000000', '1', 'Bresser Arcturus Telescopio Refractor 60/700 AZ con Filtro Solar y Adaptador para Móvil', '267523', '2', '92.55', '185.1', '189.19', '38.88', '21.00', '4.09', '0.86', '228.93');

        $stmt = $conn->prepare(
            "INSERT INTO `Orders` (
                      `date`, `billingAddress`, `title`, `sku`, `quantity`, `basePricePerUnit`, `basePrice`
                      , `totalBasePrice`, `taxes`, `commissionTaxRate`, `shippingPrice`, `shippingTaxes`, `totalPrice`
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);"
        );

        $date = $order['date']->format('Y-m-d H:i:s');
        $title = $order['title'];
        $sku = $order['sku'];
        $quantity = $order['quantity'];
        $basePricePerUnit = $order['basePricePerUnit'];
        $basePrice = $order['basePrice'];
        $totalBasePrice = $order['totalBasePrice'];
        $taxAmount = $order['taxes']->first()->getAmount();
        $commissionTaxRate = $order['commissionTaxRate'];
        $shippingPrice = $order['shippingPrice'];
        $shippingTaxAmount = $order['shippingTaxes']->first()->getAmount();
        $totalPrice = $order['totalPrice'];

        $stmt->bind_param('sissidddddddd',
            $date, $billingAddress, $title, $sku, $quantity, $basePricePerUnit, $basePrice, $totalBasePrice
            , $taxAmount, $commissionTaxRate, $shippingPrice, $shippingTaxAmount, $totalPrice
        );

        try {
            if ($stmt->execute()) {
                echo("Order : Insertion successfully.\n");
            }
        } catch (mysqli_sql_exception $e) {
            echo $e->getMessage() . "\n";
            return -1;
        }
        $sql = "SELECT LAST_INSERT_ID();";

        return $conn->query($sql)->fetch_row()[0];
    }

    public static function createTables(mysqli $conn): void
    {
        echo "Creating tables ...\n";
        $sql = "CREATE TABLE `PC_CompOrders`.`BillingAddress` (`ID` INT NOT NULL , `city` VARCHAR(255) NOT NULL , `civility` VARCHAR(255) NOT NULL , `country` VARCHAR(32) NOT NULL , `firstname` VARCHAR(255) NOT NULL , `lastname` VARCHAR(255) NOT NULL , `phone` VARCHAR(32) NOT NULL , `state` VARCHAR(255) NOT NULL , `street` VARCHAR(255) NOT NULL , `zip_code` VARCHAR(16) NOT NULL ) ENGINE = InnoDB;";
        try {
            if ($conn->query($sql)) {
                echo("BillingAddress : Table created successfully.\n");
            }
        } catch (mysqli_sql_exception $e) {
            echo $e->getMessage() . "\n";
        }

        $sql = "ALTER TABLE `BillingAddress` ADD PRIMARY KEY(`ID`);";
        try {
            if ($conn->query($sql)) {
                echo("BillingAddress : Set ID as primary key successfully.\n");
            }
        } catch (mysqli_sql_exception $e) {
            echo $e->getMessage() . "\n";
        }

        $sql = "ALTER TABLE `BillingAddress` CHANGE `ID` `ID` INT(11) NOT NULL AUTO_INCREMENT;";
        try {
            if ($conn->query($sql)) {
                echo("BillingAddress : Set ID to auto increment successfully.\n");
            }
        } catch (mysqli_sql_exception $e) {
            echo $e->getMessage() . "\n";
        }

        $sql = "CREATE TABLE `PC_CompOrders`.`Orders` (
            `date` DATETIME NOT NULL , `billingAddress` INT NOT NULL , `title` VARCHAR(255) NOT NULL 
            , `sku` VARCHAR(12) NOT NULL , `quantity` INT NOT NULL , `basePricePerUnit` FLOAT NOT NULL 
            , `basePrice` FLOAT NOT NULL , `totalBasePrice` FLOAT NOT NULL , `taxes` FLOAT NOT NULL 
            , `commissionTaxRate` FLOAT NOT NULL , `shippingPrice` FLOAT NOT NULL , `shippingTaxes` FLOAT NOT NULL 
            , `totalPrice` FLOAT NOT NULL
            , FOREIGN KEY (billingAddress) REFERENCES `PC_CompOrders`.`BillingAddress`(ID) ON DELETE CASCADE 
        ) ENGINE = InnoDB;";

        try {
            if ($conn->query($sql)) {
                echo("Orders : Table created successfully.\n");
            }
        } catch (mysqli_sql_exception $e) {
            echo $e->getMessage() . "\n";
        }


        $sql = "ALTER TABLE `Orders` ADD PRIMARY KEY(`date`, `billingAddress`, `title`, `sku`, `quantity`);";
        try {
            if ($conn->query($sql)) {
                echo("Orders : Set primary key successfully.\n");
            }
        } catch (mysqli_sql_exception $e) {
            echo $e->getMessage() . "\n";
        }
        echo "Tables created.\n";
    }
}

if (false) {
    $mysqli = MiraklDatabase::getConnection();
    MiraklDatabase::createTables($mysqli);

    list($client, $request) = FetchOrders::getOrdersRequest();
    $response = $client->getOrders($request);
    var_dump($response[0]);
    $invoiceFields = GridPrepare::extractInvoiceFields(...$response);
    foreach ($invoiceFields as $order) {
        var_dump(MiraklDatabase::insertOrder($mysqli, $order));
    }
}

