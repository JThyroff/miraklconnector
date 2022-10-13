<?php
require_once(dirname(__FILE__) . '/../vendor/autoload.php');

use Mirakl\MMP\Shop\Client\ShopApiClient as Client;
use Mirakl\MCI\Shop\Request\Hierarchy\GetHierarchiesRequest;
use Mirakl\MMP\Shop\Domain\Collection\Order\ShopOrderCollection;
use Mirakl\MMP\Shop\Request\Order\Get\GetOrdersRequest;
use Mirakl\MMP\Shop\Domain\Order\ShopOrder;

#region read api key
$json = file_get_contents(dirname(__FILE__).'/apikey.json');
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
foreach ($orderIterator as $key=>$val){
    $array = json_decode(($val), true);
    $order = new ShopOrder($array);
    
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