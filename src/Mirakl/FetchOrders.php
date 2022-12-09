<?php

declare(strict_types=1);

namespace Module\MiraklConnector\Mirakl;

require_once(dirname(__DIR__,2) . '/vendor/autoload.php');

use Mirakl\MMP\Shop\Client\ShopApiClient as Client;
use Mirakl\MCI\Shop\Request\Hierarchy\GetHierarchiesRequest;
use Mirakl\MMP\Shop\Domain\Collection\Order\ShopOrderCollection;
use Mirakl\MMP\Shop\Request\Order\Get\GetOrdersRequest;
use Mirakl\MMP\Shop\Domain\Order\ShopOrder;

class FetchOrders
{
    public static function fetchOrders(){

        #region read api key
        echo sprintf(dirname(__DIR__,2).'/apikey.json');
        echo sprintf("\n");

        $json = file_get_contents(dirname(__DIR__,2) . '/apikey.json');
        $json_data = json_decode($json, true);

        $apiUrl = $json_data["apiUrl"];
        $apiKey = $json_data["apiKey"];
        #endregion

        #region create client and send orders request
        $client = new Client($apiUrl, $apiKey);

        $request = new GetHierarchiesRequest();
        echo sprintf('%d hierarchy found', count($client->getHierarchies($request)));

        echo sprintf("\n");
        $request = new GetOrdersRequest();
        //echo sprintf($request);
        echo sprintf('%d orders found', $client->getOrders($request)->count());
        echo sprintf("\n");
        #endregion

        #region iterate orders and print info
        $orderIterator = $client->getOrders($request)->getIterator();
        foreach ($orderIterator as $key=>$order){

            //$array = json_decode(($val), true);
            //new ShopOrder($array);

            $shopOrderLine = $order->getOrderLines()->first();
            $productInfo = $shopOrderLine->getOffer()->getProduct();
            $title = $productInfo->getTitle();
            echo sprintf("ID: %d, ", $shopOrderLine->getId());
            echo sprintf("Product sku: %d, ", $productInfo->getSku());
            echo sprintf("Price: %f€, ", $shopOrderLine->getTotalPrice());
            echo sprintf("Title: %s", $title);

            echo "\n";
            //uncomment to print whole content
            //echo $key." ".$title.":".$order."\n";
        }
        #endregion
    }
}

FetchOrders::fetchOrders();