<?php

namespace Module\MiraklConnector\Pdf;


use Carrier;
use Context;
use Module\MiraklConnector\Grid\Filters\ProductFilters;
use PDFGenerator;
use Order;
use PhpOffice\PhpSpreadsheet\Worksheet\HeaderFooter;
use PrestaShop\PrestaShop\Adapter\Entity\OrderInvoice;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\Response;


class MyPdfGenerator
{
    private Context $context;

    private static string $PDF_BASE_PATH = '/../../../..';

    public function __construct()
    {
        $this->context = Context::getContext();
    }

    public function generatePDF(array $params): void
    {
        $myOrderObject = new Order((int)$params['id_order']);

        $myCustomInvoiceVarsForPdfContent = $this->myContentDatasPresenter($myOrderObject);
        $myCustomInvoiceVarsForPdfFooter = $this->myFooterDatasPresenter($myOrderObject);
        $myCustomInvoiceVarsForPdfHeader = $this->myHeaderDatasPresenter($myOrderObject);
        $pdfGen = new PDFGenerator(false, 'P');
        $pdfGen->setFontForLang(Context::getContext()->language->iso_code);
        $pdfGen->startPageGroup();
        $pdfGen->createHeader($this->getHeader($myCustomInvoiceVarsForPdfHeader));
        $pdfGen->createFooter($this->getFooter($myCustomInvoiceVarsForPdfFooter));
        $pdfGen->createContent($this->getPdfContent($myCustomInvoiceVarsForPdfContent));
        $pdfGen->writePage();
        $pdfGen->render('my_custom_pdf.pdf', 'D');
    }

    /**
     * Returns the template's HTML content.
     *
     * @return string HTML content
     */
    public function getPdfContent(array $myCustomInvoiceVarsForPdfContent): string
    {
        $this->context->smarty->assign($myCustomInvoiceVarsForPdfContent);

        $tpls = array(
            'addresses_tab' => $this->context->smarty->fetch(__DIR__ . MyPdfGenerator::$PDF_BASE_PATH . '/pdf/invoice.addresses-tab.tpl'),
            'note_tab' => $this->context->smarty->fetch(__DIR__ . MyPdfGenerator::$PDF_BASE_PATH . '/pdf/invoice.note-tab.tpl'),
            'payment_tab' => $this->context->smarty->fetch(__DIR__ . MyPdfGenerator::$PDF_BASE_PATH . '/pdf/invoice.payment-tab.tpl'),
            'product_tab' => $this->context->smarty->fetch(__DIR__ . MyPdfGenerator::$PDF_BASE_PATH . '/pdf/invoice.product-tab.tpl'),
            'shipping_tab' => $this->context->smarty->fetch(__DIR__ . MyPdfGenerator::$PDF_BASE_PATH . '/pdf/invoice.shipping-tab.tpl'),
            'style_tab' => $this->context->smarty->fetch(__DIR__ . MyPdfGenerator::$PDF_BASE_PATH . '/pdf/invoice.style-tab.tpl'),
            'summary_tab' => $this->context->smarty->fetch(__DIR__  . '/pdf/invoice.summary-tab.tpl'),
            'tax_tab' => $this->context->smarty->fetch(__DIR__ . MyPdfGenerator::$PDF_BASE_PATH . '/pdf/invoice.tax-tab.tpl'),
            'total_tab' => $this->context->smarty->fetch(__DIR__ . MyPdfGenerator::$PDF_BASE_PATH . '/pdf/invoice.total-tab.tpl'),
        );
        $this->context->smarty->assign($tpls);

        return $this->context->smarty->fetch(__DIR__ . MyPdfGenerator::$PDF_BASE_PATH . '/pdf/invoice.tpl');
    }

    /**
     * Returns the template's HTML footer.
     *
     * @return string HTML footer
     */
    public function getFooter(array $myCustomInvoiceVarsForPdfFooter): string
    {
        $this->context->smarty->assign($myCustomInvoiceVarsForPdfFooter);
        return $this->context->smarty->fetch(__DIR__ . MyPdfGenerator::$PDF_BASE_PATH .'/pdf/footer.tpl');
    }

    /**
     * Returns the template's HTML header.
     *
     * @return string HTML header
     */
    public function getHeader(array $myCustomInvoiceVarsForPdfHeader): string
    {
        $this->context->smarty->assign($myCustomInvoiceVarsForPdfHeader);
        return $this->context->smarty->fetch(__DIR__ .MyPdfGenerator::$PDF_BASE_PATH . '/pdf/header.tpl');
    }


    /**
     * Format your order data here for pdf content : ['tpl_var_name'=>'tpl_value']
     *
     * @return array
     */
    public function myContentDatasPresenter(Order $myOrderObject): array
    {
        // TODO : implement it
        $example_order_detail = [
            'product_reference' => 'product_reference',
            'image' => 'image',
            'image_tag' => 'image_tag',
            'product_name' => 'product_name',
            'order_detail_tax_label' => 'order_detail_tax_label',
            'unit_price_tax_excl_before_specific_price' => 40,
            'unit_price_tax_excl_including_ecotax' => 30,
            'total_price_tax_excl_including_ecotax' => 25,
            'ecotax_tax_excl' => 0.1,
            'product_quantity' => 2,
            'customizedDatas' => [],
        ];
        $order_details = [
            $example_order_detail,
        ];
        $layout = [
            'product' => [
                'width' => 50,
            ],
            'tax_code' => [
                'width' => 50,
            ],
            'reference' => [
                'width' => 50,
            ],
            'before_discount' => true,
            'unit_price_tax_excl' => [
                'width' => 50,
            ],
            'quantity' => [
                'width' => 50,
            ],
            '_colCount' => 10,
            'total_tax_excl' => [
                'width' => 50,
            ],
        ];

        $footer = [
            'products_before_discounts_tax_excl' => 0.01,
            'product_discounts_tax_excl' => 0.01,
            'shipping_tax_excl' => 0.01,
            'wrapping_tax_excl' => 0.01,
            'total_paid_tax_excl' => 0.01,
            'total_taxes' => 0.01,
            'total_paid_tax_incl' => 0.01,
        ];

        $orderInvoice = new OrderInvoice();
        $carrier = new Carrier('carrie name');

        return [
            'delivery_address' => 'delivery_address',
            'invoice_address' => 'invoice_address',
            'addresses' => [
                'invoice' => [
                    'vat_number' => '123456789',
                    'address_1' => 'address 1',
                    'address_2' => 'address 2',
                    'postcode' => 'postcode',
                    'city' => 'city',
                    'country' => 'country',
                ],
            ],
            'order' => $myOrderObject,
            'invoice' => [],
            'order_invoice' => $orderInvoice,
            'layout' => $layout,
            'order_details' => $order_details,
            'display_product_images' => false,
            'cart_rules' => [],
            'carrier' => $carrier,
            'isTaxEnabled' => false,
            'footer' => $footer,
            'legal_free_text' => 'legal_free_text',
            #'addresses'                 => ['invoice' => ['vat_number' => 2]],
        ];
    }

    /**
     * Format your order data here for pdf footer : ['tpl_var_name'=>'tpl_value']
     *
     * @return array
     */
    public function myFooterDatasPresenter(Order $myOrderObject): array
    {
        // TODO : implement it
        return [
            'shop_address' => 'shop_address',
        ];
    }

    /**
     * Format your order data here for pdf header : ['tpl_var_name'=>'tpl_value']
     *
     * @return array
     */
    public function myHeaderDatasPresenter(Order $myOrderObject): array
    {
        // TODO : implement it
        return [
            'logo_path' => 'logo.png',
            'width_logo' => 50,
            'height_logo' => 50,
            'date' => '18.01.2000',
            'title' => 'Invoice',
            'available_in_your_account' => 'available_in_your_account',
        ];
    }
}