<?php

namespace wps\bmd;

use \SimpleXMLElement;

class Exporter
{

    public array $data = [];
    public \WC_Order $order;

    public function processOrder(\WC_Order $order)
    {
        $this->order = $order;
        $this->data = $this->getOrderData();
        //dump($this->toXML($order));
        dump($this->toXML2($order));
        die();
    }

    /**
     * create an array of order data out of the WooCommerce order for the xml creation
     * @return array
     */
    public function getOrderData(): array
    {

        // orderingParty address
        $orderingPartyAddress = [];
        $orderingPartyAddress['bmd:Salutation'] = '';
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
        $orderingParty['bmd:ExternalOrderingPartyID'] = get_current_user_id();
        $orderingParty['bmd:VATIdentificationNumber'] = 'atu0815';
        $orderingParty['bmd:Email'] = $this->order->get_billing_email();
        $orderingParty['bmd:Address'] = $orderingPartyAddress;

        // invoiceRecipient
        $invoiceRecipient = [];
        $invoiceRecipient['bmd:ExternalInvoiceRecipientID'] = get_current_user_id();
        $invoiceRecipient['bmd:VATIdentificationNumber'] = 'atu0815';
        $invoiceRecipient['bmd:Email'] = $this->order->get_billing_email();
        $invoiceRecipient['bmd:Address'] = $invoiceRecipientAddress;

        // deliveryRecipient
        $deliveryRecipient = [];
        $deliveryRecipient['bmd:ExternalDeliveryRecipientID'] = get_current_user_id();
        $deliveryRecipient['bmd:VATIdentificationNumber'] = 'atu0815';
        $deliveryRecipient['bmd:Email'] = $this->order->get_billing_email();
        $deliveryRecipient['bmd:Address'] = $deliveryRecipientAddress;

        // items
        $itemList = [];
        $itemPos = 0;
        foreach ($this->order->get_items() as $item_id => $item) {
            $itemPos++;
            $itemList[] = [
                'n1:OrderPosNumber' => $itemPos,
                'n1:DistributorsArticleNo' => $item->get_product_id(),
                'n1:DistributorsArticleName' => $item->get_name(),
                'n1:OrderQuantity' => $item->get_quantity(),
                'n1:Price' => $item->get_total(),
                'n1:DiscountAmount' => 0.00,
                'n1:DeliveryDate' => ''
            ];
        }

        return $this->data = [
            'n1:OrderingParty' => $orderingParty,
            'n1:InvoiceRecipient' => $invoiceRecipient,
            'n1:DeliveryRecipient' => $deliveryRecipient,
            'n1:OrderReference' => $this->order->get_id(),
            'n1:OrderDate' => 'Y-m-d',
            'n1:DeliveryDateRequested' => '',
            'n1:GrossPrice' => 'false',
            'n1:DiscountPercentage' => '',
            'n1:ItemList' => $itemList
        ];
    }

    public function toXML(\WC_Order $order) {

        // version 1
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" standalone="no"?><n1:Order></n1:Order>',
            LIBXML_NOERROR | LIBXML_ERR_NONE | LIBXML_ERR_FATAL);

        $xml->registerXPathNamespace('n1', 'http://www.bmd.at/xsd/ORDER/2.0/');
        $xml->registerXPathNamespace('bmd', 'http://www.bmd.at/xsd/COMMONTYPES/2.0/');
        $xml->registerXPathNamespace('xsi', 'http://www.w3.org/2001/XMLSchema-instance');

        $order_data = $order->get_data();

        $xml->addAttribute('n1:Language', 'ger');
        $xml->addAttribute('n1:GeneratingSystem', 'WooCommerce');
        $xml->addAttribute('n1:DocumentType', 'Order');
        $xml->addAttribute('n1:DocumentTitle', 'Order');
        $xml->addAttribute('xsi:schemaLocation', 'http://www.bmd.at/xsd/ORDER/2.0/ http://www.bmd.at/xsd/ORDER/2.0/ORDER.xsd http://www.bmd.at/xsd/COMMONTYPES/2.0/ http://www.bmd.at/xsd/COMMONTYPES/2.0/COMMONTYPES.xsd');

        // Ordering Party
        $ordering_party = $xml->addChild('n1:OrderingParty');

        $ordering_party->addChild('bmd:ExternalOrderingPartyID', $this->data['n1:OrderingParty']['bmd:ExternalOrderingPartyID']);
        $ordering_party->addChild('bmd:VATIdentificationNumber', $this->data['n1:OrderingParty']['bmd:VATIdentificationNumber']);
        $ordering_party->addChild('bmd:Email', $this->data['n1:OrderingParty']['bmd:Email']);

        $address = $ordering_party->addChild('bmd:Address');
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

        $invoice_recipient->addChild('bmd:ExternalInvoiceRecipientID', $this->data['n1:InvoiceRecipient']['bmd:ExternalInvoiceRecipientID']);
        $invoice_recipient->addChild('bmd:VATIdentificationNumber', $this->data['n1:InvoiceRecipient']['bmd:VATIdentificationNumber']);
        $invoice_recipient->addChild('bmd:Email', $this->data['n1:InvoiceRecipient']['bmd:Email']);

        $invoiceAddress = $invoice_recipient->addChild('bmd:Address');
        $invoiceAddress->addChild('bmd:FirstName', $this->data['n1:InvoiceRecipient']['bmd:Address']['bmd:FirstName']);
        $invoiceAddress->addChild('bmd:LastName', $this->data['n1:InvoiceRecipient']['bmd:Address']['bmd:LastName']);
        $invoiceAddress->addChild('bmd:Street', $this->data['n1:InvoiceRecipient']['bmd:Address']['bmd:Street']);
        $invoiceAddress->addChild('bmd:Town', $this->data['n1:InvoiceRecipient']['bmd:Address']['bmd:Town']);
        $invoiceAddress->addChild('bmd:ZIP', $this->data['n1:InvoiceRecipient']['bmd:Address']['bmd:ZIP']);
        $invoiceAddress->addChild('bmd:Country', $this->data['n1:InvoiceRecipient']['bmd:Address']['bmd:Country']);

        // Delivery Recipient
        $delivery_recipient = $xml->addChild('n1:DeliveryRecipient');

        $delivery_recipient->addChild('bmd:ExternalDeliveryRecipientID', $this->data['n1:DeliveryRecipient']['bmd:ExternalDeliveryRecipientID']);
        $delivery_recipient->addChild('bmd:VATIdentificationNumber', $this->data['n1:DeliveryRecipient']['bmd:VATIdentificationNumber']);
        $delivery_recipient->addChild('bmd:Email', $this->data['n1:DeliveryRecipient']['bmd:Email']);

        $deliveryAddress = $delivery_recipient->addChild('bmd:Address');
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
            $quantity->addAttribute('bmd:Unit', 'Stk');

            // price
            $price = $line_item->addChild('n1:Price', $lineItem['n1:Price']);
            $price->addAttribute('bmd:PricePer', $lineItem['n1:OrderQuantity']);
            $price->addAttribute('bmd:Unit', 'Stk');
            $price->addAttribute('bmd:Currency', 'EUR');

            $line_item->addChild('n1:DiscountAmount', $lineItem['n1:DiscountAmount']);
            $line_item->addChild('n1:DeliveryDate', $lineItem['n1:DeliveryDate']);

        }

        return $xml->asXML();
    }

    public function toXML2(\WC_Order $order) {

        // version 1
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" standalone="no"?><n1:Order></n1:Order>',
            LIBXML_NOERROR | LIBXML_ERR_NONE | LIBXML_ERR_FATAL);

        $xml->registerXPathNamespace('n1', 'http://www.bmd.at/xsd/ORDER/2.0/');
        $xml->registerXPathNamespace('bmd', 'http://www.bmd.at/xsd/COMMONTYPES/2.0/');
        $xml->registerXPathNamespace('xsi', 'http://www.w3.org/2001/XMLSchema-instance');

        $order_data = $order->get_data();

        $xml->addAttribute('n1:Language', 'ger');
        $xml->addAttribute('n1:GeneratingSystem', 'WooCommerce');
        $xml->addAttribute('n1:DocumentType', 'Order');
        $xml->addAttribute('n1:DocumentTitle', 'Order');
        $xml->addAttribute('xsi:schemaLocation', 'http://www.bmd.at/xsd/ORDER/2.0/ http://www.bmd.at/xsd/ORDER/2.0/ORDER.xsd http://www.bmd.at/xsd/COMMONTYPES/2.0/ http://www.bmd.at/xsd/COMMONTYPES/2.0/COMMONTYPES.xsd');

        // Ordering Party
        $ordering_party = $xml->addChild('n1:OrderingParty');

        $ordering_party->addChild('bmd:ExternalOrderingPartyID', get_current_user_id());
        $ordering_party->addChild('bmd:VATIdentificationNumber', 'atu0815');
        $ordering_party->addChild('bmd:Email', $order_data['billing']['email']);

        $address = $ordering_party->addChild('bmd:Address');

        $address->addChild('bmd:Salutation', 'Frau');
        $address->addChild('bmd:FirstName', $order_data['billing']['first_name']);
        $address->addChild('bmd:LastName', $order_data['billing']['last_name']);
        $address->addChild('bmd:AdditionalName', '');
        $address->addChild('bmd:AcademicTitle', '');
        $address->addChild('bmd:Street', $order_data['billing']['address_1']);
        $address->addChild('bmd:Town', $order_data['billing']['city']);
        $address->addChild('bmd:ZIP', $order_data['billing']['postcode']);
        $address->addChild('bmd:Country', $order_data['billing']['country']);

        // Invoice Recipient
        $invoice_recipient = $xml->addChild('n1:InvoiceRecipient');

        $invoice_recipient->addChild('bmd:ExternalInvoiceRecipientID', get_current_user_id());
        $invoice_recipient->addChild('bmd:VATIdentificationNumber', 'atu0815');
        $invoice_recipient->addChild('bmd:Email', $order_data['billing']['email']);

        $invoiceAddress = $invoice_recipient->addChild('bmd:Address');
        $invoiceAddress->addChild('bmd:FirstName', $order_data['billing']['first_name']);
        $invoiceAddress->addChild('bmd:LastName', $order_data['billing']['last_name']);
        $invoiceAddress->addChild('bmd:Street', $order_data['billing']['address_1']);
        $invoiceAddress->addChild('bmd:Town', $order_data['billing']['city']);
        $invoiceAddress->addChild('bmd:ZIP', $order_data['billing']['postcode']);
        $invoiceAddress->addChild('bmd:Country', $order_data['billing']['country']);

        // Delivery Recipient
        $delivery_recipient = $xml->addChild('n1:DeliveryRecipient');

        $delivery_recipient->addChild('bmd:ExternalDeliveryRecipientID', get_current_user_id());
        $delivery_recipient->addChild('bmd:VATIdentificationNumber', 'atu0815');
        $delivery_recipient->addChild('bmd:Email', $order_data['billing']['email']);

        $deliveryAddress = $delivery_recipient->addChild('bmd:Address');
        $deliveryAddress->addChild('bmd:FirstName', $order_data['shipping']['first_name']);
        $deliveryAddress->addChild('bmd:LastName', $order_data['shipping']['last_name']);
        $deliveryAddress->addChild('bmd:Street', $order_data['shipping']['address_1']);
        $deliveryAddress->addChild('bmd:Town', $order_data['shipping']['city']);
        $deliveryAddress->addChild('bmd:ZIP', $order_data['shipping']['postcode']);
        $deliveryAddress->addChild('bmd:Country', $order_data['shipping']['country']);

        // Order Reference, Order Date, Delivery Date Requested, Gross Price, Discount Percentage
        $xml->addChild('n1:OrderReference', $order_data['id']);
        $xml->addChild('n1:OrderDate', date('Y-m-d'));
        $xml->addChild('n1:DeliveryDateRequested', '');
        $xml->addChild('n1:GrossPrice', 'false');
        $xml->addChild('n1:DiscountPercentage', '');

        // Item List
        $item_list = $xml->addChild('n1:ItemList');
        $itemPos = 0;

        foreach ($order->get_items() as $item_id => $item) {

            $itemPos++;
            $line_item = $item_list->addChild('n1:ListLineItem');
            $line_item->addChild('n1:OrderPosNumber', $itemPos);
            $line_item->addChild('n1:DistributorsArticleNo', $item->get_product_id());
            $line_item->addChild('n1:DistributorsArticleName', $item->get_name());

            // quantitiy
            $quantity = $line_item->addChild('n1:OrderQuantity', $item->get_quantity());
            $quantity->addAttribute('bmd:Unit', 'Stk');

            // price
            $price = $line_item->addChild('n1:Price', $item->get_total());
            $price->addAttribute('bmd:PricePer', $item->get_quantity());
            $price->addAttribute('bmd:Unit', 'Stk');
            $price->addAttribute('bmd:Currency', 'EUR');

            $line_item->addChild('n1:DiscountAmount', 0.00);
            $line_item->addChild('n1:DeliveryDate', '');
        }
    }
}