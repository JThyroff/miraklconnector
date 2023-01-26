<?php

declare(strict_types=1);

namespace Module\MiraklConnector\Mirakl;

use Mirakl\MMP\Shop\Domain\Order\ShopOrder;
use Mirakl\MMP\Shop\Domain\Collection\Order\ShopOrderCollection;

/**
 * Processes the mirakl sdk shop orders result json, filters necessary fields and converts it to an array.
 */
class GridPrepare
{
    /**
     * Unused.
     *
     * Extract fields from shopOrders.
     *
     * @param ShopOrder ...$shopOrders
     * @return array
     */
    public static function processJSON(ShopOrder ...$shopOrders): array
    {
        $array = array();
        foreach ($shopOrders as $order) {
            $shopOrderLine = $order->getOrderLines()->first();
            $productInfo = $shopOrderLine->getOffer()->getProduct();
            $title = $productInfo->getTitle();

            $id = $shopOrderLine->getId();
            $sku = $productInfo->getSku();
            $price = $shopOrderLine->getTotalPrice();

            $array[] = [
                "id_product" => $id,
                "reference" => $title,
                "price_tax_excluded" => $price,
                "active" => $sku,
                "name" => $title,
                "link_rewrite" => $title,
                "category" => "Art",
                "id_image" => "5",
                "id_tax_rules_group" => "9",
                "quantity" => $sku
            ];
        }
        return $array;
    }

    /**
     *  Extract fields from shopOrders used for Invoices.
     *  TODO extract data for billing address.
     *
     * @param ShopOrder ...$shopOrders
     * @return array
     */
    public static function extractInvoiceFields(ShopOrder ...$shopOrders): array
    {
        $array = array();
        foreach ($shopOrders as $order) {
            $date = $order->getAcceptanceDecisionDate();
            $customer = $order->getCustomer();
            $billingAddress = $customer->getBillingAddress();
            $firstName = $billingAddress->getFirstname();
            $lastName = $billingAddress->getLastname();

            #
            $shopOrderLine = $order->getOrderLines()->first();
            $offer = $shopOrderLine->getOffer();
            $title = $offer->getProduct()->getTitle();
            $sku = $offer->getProduct()->getSku();
            $basePricePerUnit = $offer->getPrice();
            $basePrice = $shopOrderLine->getPrice();

            $quantity = $shopOrderLine->getQuantity();
            $totalBasePrice = $shopOrderLine->getTotalPrice();//total base price with shipping
            $shippingTaxes = $shopOrderLine->getShippingTaxes();
            $shippingPrice = $shopOrderLine->getShippingPrice();
            $taxes = $shopOrderLine->getTaxes();
            $commission = $shopOrderLine->getCommission();
            $commissionTaxRate = $commission->getCommissionTaxes()->first()->getRate();

            $totalTax = $shippingTaxes->first()->getAmount() + $taxes->first()->getAmount();
            $totalPrice = $totalBasePrice + $totalTax;

            $id = $shopOrderLine->getId();

            $array[] = [
                "date" => $date,
                "lastname" => $lastName,
                "firstname" => $firstName,
                "billingAddress" => $billingAddress,
                "title" => $title,
                "sku" => $sku,
                "quantity" => $quantity,
                "basePricePerUnit" => $basePricePerUnit,
                "basePrice" => $basePrice,
                "totalBasePrice" => $totalBasePrice,
                "taxes"=>$taxes,
                "commissionTaxRate"=>$commissionTaxRate,
                "shippingPrice"=>$shippingPrice,
                "shippingTaxes"=>$shippingTaxes,
                "totalPrice"=>$totalPrice
            ];
        }
        return $array;
    }
}