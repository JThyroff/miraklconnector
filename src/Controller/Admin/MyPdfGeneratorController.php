<?php

namespace Module\MiraklConnector\Controller\Admin;


use Carrier;
use Context;
use http\Params;
use Module\MiraklConnector\Controller\Admin\pdf\PdfGeneratorHelper;
use Module\MiraklConnector\Grid\Filters\ProductFilters;
use Module\MiraklConnector\Mirakl\MiraklDatabase;
use PDFGenerator;
use Order;
use PhpOffice\PhpSpreadsheet\Writer\Pdf;
use PrestaShop\PrestaShop\Core\Grid\Action\Row\Type\LinkRowAction;
use Symfony\Component\HttpFoundation\Request;
use PhpOffice\PhpSpreadsheet\Worksheet\HeaderFooter;
use PrestaShop\PrestaShop\Adapter\Entity\OrderInvoice;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\Response;
use function GuzzleHttp\Promise\queue;
use function Sodium\add;


/**
 * Controller to generate a Pdf for an invoice in the table. The order details are passed in the url on button click.
 * @ProductGridDefinitionFactory->getColumns()-> ... -> add((new LinkRowAction('invoice'))
 *
 * https://devdocs.prestashop-project.org/8/modules/concepts/pdf/#customize-the-pdf-files-templates
 */
class MyPdfGeneratorController extends FrameworkBundleAdminController
{
    private Context $context;

    private static string $PDF_BASE_PATH = '/../../../../..';
    private string $shop_address;

    public function __construct()
    {
        $this->context = Context::getContext();

        #region read shop address
        $json = file_get_contents(dirname(__DIR__, 3) . '/invoicefooter.json');
        $json_data = json_decode($json, true);

        $this->shop_address = $json_data["shop_address"];
        #endregion
    }


    /**
     * Index action of the controller. Parameter processing and initiating the pdf generation.
     * @param Request $request holds order information
     */
    public function indexAction(Request $request)
    {
        $date = $request->query->get('0');
        $billingAddress = $request->query->get('1');
        $title = $request->query->get('2');
        $sku = $request->query->get('3');
        $quantity = $request->query->get('4');

        $mysqli = MiraklDatabase::getConnection();
        $res = MiraklDatabase::getOrderData($mysqli, $date, $billingAddress, $title, $sku, $quantity);

        $params = array(

            //Order

            "date" => $date,
            "billingAddress" => $billingAddress,
            "title" => $title,
            "sku" => $sku,
            "quantity" => $quantity,
            "basePricePerUnit" => $res[5],
            "basePrice" => $res[6],
            "totalBasePrice" => $res[7],
            "taxes" =>$res[8],
            "commissionTaxRate" => $res[9],
            "shippingPrice" =>$res[10],
            "shippingTaxes" => $res[11],
            "totalPrice" =>$res[12],

            // JOIN ON "id" => $res[13]

            //Address

            "city" => $res[14],
            "civility" => $res[15],
            "country" => $res[16],
            "firstname" => $res[17],
            "lastname" => $res[18],
            "phone" => $res[19],
            "state" => $res[20],
            "street" => $res[21],
            "zip_code" => $res[22],
        );

        $this->generatePDF($params);

        return $this->redirectToRoute('ps_controller_mirakl_sell_manual_tab_index', []);
    }

    /**
     * Generate the pdf for an order passed in a prepared params array
     *
     * @param array $params order params prepared in the indexAction
     * @return string
     * @throws \PrestaShopException
     */
    public function generatePDF(array $params): string
    {
        $myCustomInvoiceVarsForPdfContent = $this->myContentDatasPresenter($params);
        $myCustomInvoiceVarsForPdfFooter = $this->myFooterDatasPresenter($params);
        $myCustomInvoiceVarsForPdfHeader = $this->myHeaderDatasPresenter($params);
        $pdfGen = new PDFGenerator(false, 'P');
        $pdfGen->setFontForLang(Context::getContext()->language->iso_code);
        $pdfGen->startPageGroup();
        $pdfGen->createHeader($this->getHeader($myCustomInvoiceVarsForPdfHeader));
        $pdfGen->createFooter($this->getFooter($myCustomInvoiceVarsForPdfFooter));
        $pdfGen->createContent($this->getPdfContent($myCustomInvoiceVarsForPdfContent));
        $pdfGen->writePage();
        return $pdfGen->render('Invoice_'.PdfGeneratorHelper::invoiceNumberGenerator($params).'.pdf', 'D');
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
            'addresses_tab' => $this->context->smarty->fetch(__DIR__ . MyPdfGeneratorController::$PDF_BASE_PATH . '/pdf/invoice.addresses-tab.tpl'),
            'note_tab' => $this->context->smarty->fetch(__DIR__ . MyPdfGeneratorController::$PDF_BASE_PATH . '/pdf/invoice.note-tab.tpl'),
            'payment_tab' => $this->context->smarty->fetch(__DIR__ . MyPdfGeneratorController::$PDF_BASE_PATH . '/pdf/invoice.payment-tab.tpl'),
            'product_tab' => $this->context->smarty->fetch(__DIR__ . MyPdfGeneratorController::$PDF_BASE_PATH . '/pdf/invoice.product-tab.tpl'),
            'shipping_tab' => $this->context->smarty->fetch(__DIR__ . MyPdfGeneratorController::$PDF_BASE_PATH . '/pdf/invoice.shipping-tab.tpl'),
            'style_tab' => $this->context->smarty->fetch(__DIR__ . MyPdfGeneratorController::$PDF_BASE_PATH . '/pdf/invoice.style-tab.tpl'),
            'summary_tab' => $this->context->smarty->fetch(__DIR__ . '/pdf/invoice.summary-tab.tpl'),
            'tax_tab' => $this->context->smarty->fetch(__DIR__ . MyPdfGeneratorController::$PDF_BASE_PATH . '/pdf/invoice.tax-tab.tpl'),
            'total_tab' => $this->context->smarty->fetch(__DIR__ . MyPdfGeneratorController::$PDF_BASE_PATH . '/pdf/invoice.total-tab.tpl'),
        );
        $this->context->smarty->assign($tpls);

        return $this->context->smarty->fetch(__DIR__ . MyPdfGeneratorController::$PDF_BASE_PATH . '/pdf/invoice.tpl');
    }

    /**
     * Returns the template's HTML footer.
     *
     * @return string HTML footer
     */
    public function getFooter(array $myCustomInvoiceVarsForPdfFooter): string
    {
        $this->context->smarty->assign($myCustomInvoiceVarsForPdfFooter);
        return $this->context->smarty->fetch(__DIR__ . MyPdfGeneratorController::$PDF_BASE_PATH . '/pdf/footer.tpl');
    }

    /**
     * Returns the template's HTML header.
     *
     * @return string HTML header
     */
    public function getHeader(array $myCustomInvoiceVarsForPdfHeader): string
    {
        $this->context->smarty->assign($myCustomInvoiceVarsForPdfHeader);
        return $this->context->smarty->fetch(__DIR__ . MyPdfGeneratorController::$PDF_BASE_PATH . '/pdf/header.tpl');
    }


    /**
     * Format your order data here for pdf content : ['tpl_var_name'=>'tpl_value']
     *
     * @return array
     */
    public function myContentDatasPresenter(array $params): array
    {
        // TODO : implement it
        $example_order_detail = [
            'product_reference' => $params['sku'],
            'image' => 'image',
            'image_tag' => 'image_tag',
            'product_name' => $params['title'],
            'order_detail_tax_label' => 'order_detail_tax_label',
            'unit_price_tax_excl_before_specific_price' => $params['totalBasePrice'],
            'unit_price_tax_excl_including_ecotax' => $params['basePricePerUnit'],
            'total_price_tax_excl_including_ecotax' => $params['totalBasePrice'],
            'ecotax_tax_excl' => 0.0,
            'product_quantity' => $params['quantity'],
            'customizedDatas' => [],
        ];
        $order_details = [
            $example_order_detail,
        ];
        $layout = [
            'product' => [
                'width' => 40,
            ],
            'tax_code' => [
                'width' => 10,
            ],
            'reference' => [
                'width' => 10,
            ],
            'before_discount' => false,
            'unit_price_tax_excl' => [
                'width' => 10,
            ],
            'quantity' => [
                'width' => 10,
            ],
            '_colCount' => 6,
            'total_tax_excl' => [
                'width' => 10,
            ],
        ];

        $footer = [
            'products_before_discounts_tax_excl' => $params['totalBasePrice'],
            'product_discounts_tax_excl' => 0.00,
            'shipping_tax_excl' => $params['shippingPrice'],
            'wrapping_tax_excl' => $params['taxes'],
            'total_paid_tax_excl' => $params['totalPrice'],
            'total_taxes' => $params['taxes'],
            'total_paid_tax_incl' => $params['totalPrice'],
        ];

        $orderInvoice = new OrderInvoice();
        $carrier = new Carrier('carrier name');
        $order = new Order();
        $order->invoice_date = date('d/m/Y', time());
        $order->date_add = $params['date'];

        return [
            'delivery_address' => 'delivery_address',
            'invoice_address' => PdfGeneratorHelper::invoiceAddressStringBuilder($params),
            'addresses' => [
                'invoice' => [
                    'vat_number' => '123456789',
                    'address_1' => 'address 1',
                    'address_2' => 'address 2',
                    'postcode' => $params['zip_code'],
                    'city' => $params['city'],
                    'country' => $params['country'],
                    'invoice_date' => date('d/m/Y', time()),
                ],
            ],
            'invoiceNumber' => PdfGeneratorHelper::invoiceNumberGenerator($params),
            'orderID' => PdfGeneratorHelper::orderIDBuilder($params),
            'order' => $order,
            'invoice' => [],
            'order_invoice' => $orderInvoice,
            'layout' => $layout,
            'order_details' => $order_details,
            'display_product_images' => false,
            'cart_rules' => [],
            'carrier' => $carrier,
            'isTaxEnabled' => false,
            'footer' => $footer,
            'legal_free_text' => 'Please note that this invoice has been generated automatically by our software, which is currently in the alpha testing phase. If you notice any errors or discrepancies, we kindly request that you initiate contact with our customer support team to resolve the issue. We apologize for any inconvenience this may cause and appreciate your understanding and cooperation.',
        ];
    }

    /**
     * Format your order data here for pdf footer : ['tpl_var_name'=>'tpl_value']
     *
     * @return array
     */
    public function myFooterDatasPresenter(array $params): array
    {
        return [
            'shop_address' => $this->shop_address,
        ];
    }

    /**
     * Format your order data here for pdf header : ['tpl_var_name'=>'tpl_value']
     *
     * @return array
     */
    public function myHeaderDatasPresenter(array $params): array
    {
        // TODO : implement it
        return [
            'logo_path' => 'logo.png',
            'width_logo' => 50,
            'height_logo' => 50,
            'date' => date('d M. Y', time()),
            'title' => PdfGeneratorHelper::invoiceNumberGenerator($params),
            'available_in_your_account' => 'available_in_your_account',
        ];
    }
}