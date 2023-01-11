<?php

declare(strict_types=1);

namespace Module\MiraklConnector\Mirakl;

require_once(dirname(__DIR__, 2) . '/vendor/autoload.php');

use Mirakl\MMP\Shop\Client\ShopApiClient as Client;
use Mirakl\MCI\Shop\Request\Hierarchy\GetHierarchiesRequest;
use Mirakl\MMP\Shop\Domain\Collection\Order\ShopOrderCollection;
use Mirakl\MMP\Shop\Request\Order\Get\GetOrdersRequest;
use Mirakl\MMP\Shop\Domain\Order\ShopOrder;

class FetchOrders
{
    /**
     * @return array
     */
    public static function getOrdersRequest(): array
    {
        #region read api key
        $json = file_get_contents(dirname(__DIR__, 2) . '/apikey.json');
        $json_data = json_decode($json, true);

        $apiUrl = $json_data["apiUrl"];
        $apiKey = $json_data["apiKey"];
        #endregion

        $client = new Client($apiUrl, $apiKey);
        $request = new GetOrdersRequest();
        return array($client, $request);
    }

    public static function fetchOrders()
    {
        list($client, $request) = self::getOrdersRequest();

        $processJSON = GridPrepare::processJSON(...$client->getOrders($request));

        return $processJSON;
    }

    public static function printToConsole(ShopOrder $shopOrder)
    {
        $shopOrderLine = $shopOrder->getOrderLines()->first();
        $productInfo = $shopOrderLine->getOffer()->getProduct();
        $title = $productInfo->getTitle();
        echo sprintf("ID: %d, ", $shopOrderLine->getId());
        echo sprintf("Product sku: %d, ", $productInfo->getSku());
        echo sprintf("Price: %f€, ", $shopOrderLine->getTotalPrice());
        echo sprintf("Title: %s", $title);

        echo "\n";
    }
}

#region debug script code
if (false){
    FetchOrders::fetchOrders();
    list($client, $request) = FetchOrders::getOrdersRequest();
    $response = $client->getOrders($request);
    //var_dump($response[0]);
    $invoiceFields = GridPrepare::extractInvoiceFields(...$response);
    foreach ($invoiceFields as $order) {
        var_dump($order);
        //FetchOrders::printToConsole($order);
    }
}
#endregion