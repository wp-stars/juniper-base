<?php

namespace bmd;

use DOMDocument;
use \SimpleXMLElement;

class Exporter
{

    public array $data = [];
    public \WC_Order $order;
    public string $xml = '';
    public string $uploadDir = 'bmd-exports/export';

    /**
     * @param \WC_Order $order
     * @return $this
     */
    public function readOrder(\WC_Order $order): self
    {
        $this->order = $order;
        $this->data = $this->getOrderData();

        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function createXML(): self
    {
        $this->xml = $this->toXML();
        return $this;
    }

    public function getSalutation(): string
    {
        global $wpdb;

        $salutation = "";
        $orderID = (int) sanitize_text_field($this->order->get_id());

        if(!is_int($orderID) && $orderID <= 0){
            return $salutation;
        }

        $sql = "SELECT meta_value 
                FROM {$wpdb->prefix}wc_orders_meta 
                WHERE order_id = {$orderID} 
                AND meta_key = '_billing_title' 
                LIMIT 1";

        $salutation = $wpdb->get_var($sql);

        switch ($salutation){
            case 1:
                $salutation = 'Herr';
                break;
            case 2:
                $salutation = 'Frau';
                break;
            default:
                $salutation = '';
        }

        return $salutation;

    }

    /**
     * create an array of order data out of the WooCommerce order for the xml creation
     * @return array
     */
    public function getOrderData(): array
    {

        // get vat number
        $billing_address = $this->order->get_address('billing');
        $vat_id = $billing_address['vat_id'] ?? '';

        $salutation = $this->getSalutation();

        // orderingParty address
        $orderingPartyAddress = [];
        $orderingPartyAddress['bmd:Salutation'] = $salutation;
        $orderingPartyAddress['bmd:FirstName'] = $this->order->get_billing_first_name();
        $orderingPartyAddress['bmd:LastName'] = $this->order->get_billing_last_name();
        $orderingPartyAddress['bmd:AdditionalName'] = '';
        $orderingPartyAddress['bmd:AcademicTitle'] = '';
        $orderingPartyAddress['bmd:Street'] = $this->order->get_billing_address_1();
        $orderingPartyAddress['bmd:Town'] = $this->order->get_billing_city();
        $orderingPartyAddress['bmd:ZIP'] = $this->order->get_billing_postcode();
        $orderingPartyAddress['bmd:Country'] = $this->order->get_billing_country();

        // invoiceRecipientAddress
        $invoiceRecipientAddress = [];
        $invoiceRecipientAddress['bmd:FirstName'] = $this->order->get_billing_first_name();
        $invoiceRecipientAddress['bmd:LastName'] = $this->order->get_billing_last_name();
        $invoiceRecipientAddress['bmd:Street'] = $this->order->get_billing_address_1();
        $invoiceRecipientAddress['bmd:Town'] = $this->order->get_billing_city();
        $invoiceRecipientAddress['bmd:ZIP'] = $this->order->get_billing_postcode();
        $invoiceRecipientAddress['bmd:Country'] = $this->order->get_billing_country();

        // deliveryRecipientAddress
        $deliveryRecipientAddress = [];
        $deliveryRecipientAddress['bmd:FirstName'] = $this->order->get_shipping_first_name();
        $deliveryRecipientAddress['bmd:LastName'] = $this->order->get_shipping_last_name();
        $deliveryRecipientAddress['bmd:Street'] = $this->order->get_shipping_address_1();
        $deliveryRecipientAddress['bmd:Town'] = $this->order->get_shipping_city();
        $deliveryRecipientAddress['bmd:ZIP'] = $this->order->get_shipping_postcode();
        $deliveryRecipientAddress['bmd:Country'] = $this->order->get_shipping_country();

        // orderingParty
        $orderingParty = [];
        //$orderingParty['bmd:ExternalOrderingPartyID'] = '';//get_current_user_id();
        $orderingParty['bmd:VATIdentificationNumber'] = $vat_id;
        $orderingParty['bmd:Email'] = $this->order->get_billing_email();
        $orderingParty['bmd:Address'] = $orderingPartyAddress;

        // invoiceRecipient
        $invoiceRecipient = [];
        //$invoiceRecipient['bmd:ExternalInvoiceRecipientID'] = get_current_user_id();
        $invoiceRecipient['bmd:VATIdentificationNumber'] = '';
        $invoiceRecipient['bmd:Email'] = $this->order->get_billing_email();
        $invoiceRecipient['bmd:Address'] = $invoiceRecipientAddress;

        // deliveryRecipient
        $deliveryRecipient = [];
        //$deliveryRecipient['bmd:ExternalDeliveryRecipientID'] = get_current_user_id();
        $deliveryRecipient['bmd:VATIdentificationNumber'] = $vat_id;
        $deliveryRecipient['bmd:Email'] = $this->order->get_billing_email();
        $deliveryRecipient['bmd:Address'] = $deliveryRecipientAddress;

        // items
        $itemList = [];
        $itemPos = 0;
        foreach ($this->order->get_items() as $item_id => $item) {
            $itemPos++;

            // get product name for bmd
            $itemName = $item->get_name();
            if(function_exists('get_field')){
                $bmdArticleName = get_field('wps_sp_bmd_product_name', $item->get_product_id()) ?? '';
                if(!!$bmdArticleName){
                    $itemName = $bmdArticleName;
                }
            }

            // get sku if available
            $sku = $item->get_product()->get_sku() ?? $item->get_product_id() ?? '';

            $itemList[] = [
                'n1:OrderPosNumber' => $itemPos,
                'n1:DistributorsArticleNo' => $sku,
                'n1:DistributorsArticleName' => $itemName,
                'n1:OrderQuantity' => $item->get_quantity(),
                'n1:Price' => $item->get_total(),
                'n1:DiscountAmount' => 0.00,
                'n1:DeliveryDate' => ''
            ];
        }

        // append shipping item to order
        $shippingItems = $this->order->get_items('shipping');
        if(is_array($shippingItems) && count($shippingItems) > 0){
            foreach ($shippingItems as $shippingItem){
                $itemPos++;
                $itemList[] = [
                    'n1:OrderPosNumber' => $itemPos,
                    'n1:DistributorsArticleNo' => '9600200',
                    'n1:DistributorsArticleName' => $shippingItem->get_name(),
                    'n1:OrderQuantity' => 1,
                    'n1:Price' => $shippingItem->get_total(),
                    'n1:DiscountAmount' => 0.00,
                    'n1:DeliveryDate' => ''
                ];
            }
        }

        $this->getOrderDate();

        return $this->data = [
            'n1:OrderingParty' => $orderingParty,
            'n1:InvoiceRecipient' => $invoiceRecipient,
            'n1:DeliveryRecipient' => $deliveryRecipient,
            'n1:OrderReference' => $this->order->get_id(),
            'n1:OrderDate' => $this->getOrderDate(),
            'n1:DeliveryDateRequested' => '',
            'n1:GrossPrice' => 'false',
            'n1:DiscountPercentage' => '',
            'n1:ItemList' => $itemList
        ];
    }

    public function getOrderDate(): string
    {
        $date = $this->order->get_date_created();

        if($date instanceof \WC_DateTime){
            return $date->date('Y-m-d');
        }

        return '';
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function toXML(): string
    {
        //
        // version 1
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" standalone="no"?><n1:Order xmlns:n1="http://www.bmd.at/xsd/ORDER/2.0/"></n1:Order>',
            LIBXML_NOERROR | LIBXML_ERR_NONE | LIBXML_ERR_FATAL);

        $xml->registerXPathNamespace('n1', 'http://www.bmd.at/xsd/ORDER/2.0/');
        $xml->registerXPathNamespace('bmd', 'http://www.bmd.at/xsd/COMMONTYPES/2.0/');
        $xml->registerXPathNamespace('xsi', 'http://www.w3.org/2001/XMLSchema-instance');

        $xml->addAttribute('n1:Language', 'ger');
        $xml->addAttribute('n1:GeneratingSystem', 'WooCommerce');
        $xml->addAttribute('n1:DocumentType', 'Order');
        $xml->addAttribute('n1:DocumentTitle', 'Order');
        $xml->addAttribute('xsi:schemaLocation', 'http://www.bmd.at/xsd/ORDER/2.0/ http://www.bmd.at/xsd/ORDER/2.0/ORDER.xsd http://www.bmd.at/xsd/COMMONTYPES/2.0/ http://www.bmd.at/xsd/COMMONTYPES/2.0/COMMONTYPES.xsd');

        // Ordering Party
        $ordering_party = $xml->addChild('n1:OrderingParty');

        $ordering_party->addChild('bmd:ExternalOrderingPartyID', $this->data['n1:OrderingParty']['bmd:ExternalOrderingPartyID'], 'bmd');
        $ordering_party->addChild('bmd:VATIdentificationNumber', $this->data['n1:OrderingParty']['bmd:VATIdentificationNumber'], 'bmd');
        $ordering_party->addChild('bmd:Email', $this->data['n1:OrderingParty']['bmd:Email'], 'bmd');

        $address = $ordering_party->addChild('bmd:Address', null, 'bmd');
        $address->addChild('bmd:Salutation', $this->data['n1:OrderingParty']['bmd:Address']['bmd:Salutation']);
        $address->addChild('bmd:FirstName', $this->data['n1:OrderingParty']['bmd:Address']['bmd:FirstName']);
        $address->addChild('bmd:LastName', $this->data['n1:OrderingParty']['bmd:Address']['bmd:LastName']);
        $address->addChild('bmd:AdditionalName', $this->data['n1:OrderingParty']['bmd:Address']['bmd:AdditionalName']);
        $address->addChild('bmd:AcademicTitle', $this->data['n1:OrderingParty']['bmd:Address']['bmd:AcademicTitle']);
        $address->addChild('bmd:Street', $this->data['n1:OrderingParty']['bmd:Address']['bmd:Street']);
        $address->addChild('bmd:Town', $this->data['n1:OrderingParty']['bmd:Address']['bmd:Town']);
        $address->addChild('bmd:ZIP', $this->data['n1:OrderingParty']['bmd:Address']['bmd:ZIP']);
        $address->addChild('bmd:Country', $this->data['n1:OrderingParty']['bmd:Address']['bmd:Country']);

        // Invoice Recipient
        $invoice_recipient = $xml->addChild('n1:InvoiceRecipient');

        $invoice_recipient->addChild('bmd:ExternalInvoiceRecipientID', $this->data['n1:InvoiceRecipient']['bmd:ExternalInvoiceRecipientID'], 'bmd');
        $invoice_recipient->addChild('bmd:VATIdentificationNumber', $this->data['n1:InvoiceRecipient']['bmd:VATIdentificationNumber'], 'bmd');
        $invoice_recipient->addChild('bmd:Email', $this->data['n1:InvoiceRecipient']['bmd:Email'], 'bmd');

        $invoiceAddress = $invoice_recipient->addChild('bmd:Address', null, 'bmd');
        $invoiceAddress->addChild('bmd:FirstName', $this->data['n1:InvoiceRecipient']['bmd:Address']['bmd:FirstName']);
        $invoiceAddress->addChild('bmd:LastName', $this->data['n1:InvoiceRecipient']['bmd:Address']['bmd:LastName']);
        $invoiceAddress->addChild('bmd:Street', $this->data['n1:InvoiceRecipient']['bmd:Address']['bmd:Street']);
        $invoiceAddress->addChild('bmd:Town', $this->data['n1:InvoiceRecipient']['bmd:Address']['bmd:Town']);
        $invoiceAddress->addChild('bmd:ZIP', $this->data['n1:InvoiceRecipient']['bmd:Address']['bmd:ZIP']);
        $invoiceAddress->addChild('bmd:Country', $this->data['n1:InvoiceRecipient']['bmd:Address']['bmd:Country']);

        // Delivery Recipient
        $delivery_recipient = $xml->addChild('n1:DeliveryRecipient');

        $delivery_recipient->addChild('bmd:ExternalDeliveryRecipientID', $this->data['n1:DeliveryRecipient']['bmd:ExternalDeliveryRecipientID'], 'bmd');
        $delivery_recipient->addChild('bmd:VATIdentificationNumber', $this->data['n1:DeliveryRecipient']['bmd:VATIdentificationNumber'], 'bmd');
        $delivery_recipient->addChild('bmd:Email', $this->data['n1:DeliveryRecipient']['bmd:Email'], 'bmd');

        $deliveryAddress = $delivery_recipient->addChild('bmd:Address', null, 'bmd');
        $deliveryAddress->addChild('bmd:FirstName', $this->data['n1:DeliveryRecipient']['bmd:Address']['bmd:FirstName']);
        $deliveryAddress->addChild('bmd:LastName', $this->data['n1:DeliveryRecipient']['bmd:Address']['bmd:LastName']);
        $deliveryAddress->addChild('bmd:Street', $this->data['n1:DeliveryRecipient']['bmd:Address']['bmd:Street']);
        $deliveryAddress->addChild('bmd:Town', $this->data['n1:DeliveryRecipient']['bmd:Address']['bmd:Town']);
        $deliveryAddress->addChild('bmd:ZIP', $this->data['n1:DeliveryRecipient']['bmd:Address']['bmd:ZIP']);
        $deliveryAddress->addChild('bmd:Country', $this->data['n1:DeliveryRecipient']['bmd:Address']['bmd:Country']);

        // Order Reference, Order Date, Delivery Date Requested, Gross Price, Discount Percentage
        $xml->addChild('n1:OrderReference', $this->data['n1:OrderReference']);
        $xml->addChild('n1:OrderDate', $this->data['n1:OrderDate']);
        $xml->addChild('n1:DeliveryDateRequested', $this->data['n1:DeliveryDateRequested']);
        $xml->addChild('n1:GrossPrice', $this->data['n1:GrossPrice']);
        $xml->addChild('n1:DiscountPercentage', $this->data['n1:DiscountPercentage']);

        // Item List
        $item_list = $xml->addChild('n1:ItemList');

        foreach ($this->data['n1:ItemList'] as $lineItem){

            $line_item = $item_list->addChild('n1:ListLineItem');
            $line_item->addChild('n1:OrderPosNumber', $lineItem['n1:OrderPosNumber']);
            $line_item->addChild('n1:DistributorsArticleNo', $lineItem['n1:DistributorsArticleNo']);
            $line_item->addChild('n1:DistributorsArticleName', $lineItem['n1:DistributorsArticleName']);

            // quantitiy
            $quantity = $line_item->addChild('n1:OrderQuantity', $lineItem['n1:OrderQuantity']);
            $quantity->addAttribute('bmd:Unit', '');

            // price
            $price = $line_item->addChild('n1:Price', $lineItem['n1:Price']);
            $price->addAttribute('bmd:PricePer', $lineItem['n1:OrderQuantity']);
            $price->addAttribute('bmd:Unit', '');
            $price->addAttribute('bmd:Currency', 'EUR');

            $line_item->addChild('n1:DiscountAmount', $lineItem['n1:DiscountAmount']);
            $line_item->addChild('n1:DeliveryDate', $lineItem['n1:DeliveryDate']);

        }

        return $xml->asXML();
    }

    /**
     * @return false|string
     */
    public function formatXML(): false|string
    {

        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($this->xml);

        return $dom->saveXML();
    }

    public function getUploadDir(): string
    {
        return ABSPATH . $this->uploadDir . '/';
    }

    public function storeXML(): void
    {

        $filename = 'order-' . $this->order->get_id() . '.xml';
        $file = ABSPATH . '/' . $this->uploadDir . '/' . $filename;

        // create a directory if it does not exist
        if(!file_exists(ABSPATH . '/' . $this->uploadDir)){
            if (!mkdir($concurrentDirectory = ABSPATH . '/' . $this->uploadDir, 0777, true) && !is_dir($concurrentDirectory)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
            }
        }

        // create a silence is golden php file if it does not exist
        if(!file_exists(ABSPATH . '/' . $this->uploadDir . '/index.php')){
            file_put_contents(ABSPATH . '/' . $this->uploadDir . '/index.php', '<?php // Silence is golden.');
        }

        // create a .htaccess file if it does not exist
        if(!file_exists(ABSPATH . '/' . $this->uploadDir . '/.htaccess')){
            file_put_contents(ABSPATH . '/' . $this->uploadDir . '/.htaccess', 'deny from all');
        }

        file_put_contents($file, $this->formatXML());
    }
}