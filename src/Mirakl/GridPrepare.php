<?php

declare(strict_types=1);

namespace Module\MiraklConnector\Mirakl;

use Mirakl\MMP\Shop\Domain\Order\ShopOrder;
use Mirakl\MMP\Shop\Domain\Collection\Order\ShopOrderCollection;

class GridPrepare
{
    public static function processJSON(ShopOrder $shopOrder)
    {
        $shopOrderLine = $shopOrder->getOrderLines()->first();
        $productInfo = $shopOrderLine->getOffer()->getProduct();
        $title = $productInfo->getTitle();

        $id = $shopOrderLine->getId();
        $sku = $productInfo->getSku();
        $price = $shopOrderLine->getTotalPrice();

        $array = [
            "id_product" => $id,
            "reference" => $sku,
            "price_tax_excluded" => $price,
            "active" => $sku,
            "name" => $title,
            "link_rewrite" => $title,
            "category" => "Art",
            "id_image" => "5",
            "id_tax_rules_group" => "9",
            "quantity" => $sku
        ];
        return $array;
    }
}