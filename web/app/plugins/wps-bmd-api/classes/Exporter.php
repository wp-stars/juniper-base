<?php

namespace wps\bmd;

use \SimpleXMLElement;

class Exporter
{
    public function processOrder(\WC_Order $order)
    {
        //$this->data = $this->getOrderData($order);
        dump($this->toXML($order));
        die();
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

        return $xml->asXML();

        // beautify xml in vsc
        // shift + option + f
    }
}