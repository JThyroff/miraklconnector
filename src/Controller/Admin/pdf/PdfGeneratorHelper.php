<?php

namespace Module\MiraklConnector\Controller\Admin\pdf;

class PdfGeneratorHelper
{
    /**
     * Builds an Address String from the given params array.
     *
     * @param $params $params array(23) from MyPdfGeneratorController
     *                  with order (index 0 - 13) and address (index 14 - 22) information
     * @return string
     */
    public static function invoiceAddressStringBuilder($params){
        /*"city" => $res[14],
        "civility" => $res[15],
        "country" => $res[16],
        "firstname" => $res[17],
        "lastname" => $res[18],
        "phone" => $res[19],
        "state" => $res[20],
        "street" => $res[21],
        "zip_code" => $res[22],*/

        return $params['firstname']." ".$params['lastname']."<br>"
            .$params['street']."<br>"
            .$params['city'].", ".$params['civility']." ".$params['zip_code']."<br>"
            .$params['country']."<br>"
            .$params['phone'];
    }


    /**
     * Generates a unique number string for a given order.
     *
     * @param $params $params array(23) from MyPdfGeneratorController
     *                  with order (index 0 - 13) and address (index 14 - 22) information
     * @return string
     */
    public static function invoiceNumberGenerator($params){

        $invoice_date = $params['date'];
        $y = date('Y',strtotime($invoice_date));

        return 'PC_'.$y.'_'.self::orderIDBuilder($params);
    }

    /**
     * Generates a unique order id string for a given order.
     * @param $params $params array(23) from MyPdfGeneratorController
     *                  with order (index 0 - 13) and address (index 14 - 22) information
     * @return string
     */
    public static function orderIDBuilder($params){
        $invoice_date = $params['date'];
        $md = date('md',strtotime($invoice_date));

        return $params['sku'].$params['lastname'][0].$md.$params['firstname'][0];
    }
}