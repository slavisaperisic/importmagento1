<?php

/**
 * Class Helper
 */
class Helper
{
    /**
     * @param $item
     *
     * @return array
     *
     * @throws Exception
     */
    public function extractValuesCustomer($item)
    {
        $dataValues = [
            'firstname' => 'tester',
            'lastname' => 'tester',
            'password_hash' => md5('tester'),
            'entity_id' => (int)$item->NUMMER,
            'email' => (string)$item->CONTACT->EMAIL,
            'increment_id' => (int)$item->ID,
            'customerurl' => (string)$item->CONTACT->URL,
            'accountno' => (string)$item->ACCOUNTDETAILS->ACCOUNTNO,
            'bankidentification' => (string)$item->ACCOUNTDETAILS->BANKIDENTIFICATION,
            'bank' => (string)$item->ACCOUNTDETAILS->BANK,
            'iban' => (string)$item->ACCOUNTDETAILS->IBAN,
            'bic' => (string)$item->ACCOUNTDETAILS->BIC,
            'isolk' => 'awaiting value'
        ];

        if ($result = $this->validEntries($dataValues)) {
            return $dataValues;
        } else {
            throw new \Exception('Data not valid: ' . $result);
        }
    }

    /**
     * @param $item
     *
     * @return array
     *
     * @throws Exception
     */
    public function extractValuesAddress($item)
    {
        $dataValues = [
            'firstname' => 'tester',
            'lastname' => 'tester',
            'address' => (string)$item->ADDRESS->LINE1,
            'street' => (string)$item->ADDRESS->HOME->STREET,
            'country' => (string)$item->ADDRESS->HOME->COUNTRY,
            'postcode' => (string)$item->ADDRESS->HOME->ZIP,
            'city' => (string)$item->ADDRESS->HOME->CITY,
            'telephone' => (string)$item->CONTACT->PHONE,
            'fax' => (string)$item->CONTACT->FAX
        ];

        if ($result = $this->validEntries($dataValues)) {
            return $dataValues;
        } else {
            throw new \Exception('Data not valid: ' . $result);
        }
    }

    /**
     * @param $item
     *
     * @return array
     *
     * @throws Exception
     */
    public function extractValuesCategory($item)
    {
        $attributes = $item->attributes();

        $dataValues = [
            'name' => (string)$item,
            'meta_description' => (string)$attributes['short'],
            'is_active' => 1
        ];

        if ($result = $this->validEntries($dataValues)) {
            return $dataValues;
        } else {
            throw new \Exception('Data not valid: ' . $result);
        }
    }

    /**
     * @param $order
     * @param $billingAddressId
     * @param $customerGroupId
     * @param $quoteId
     * @param $shippingAddressId
     * @param $weight
     * @param $incrementId
     * @param $shippingMethod
     * @param $customerId
     *
     * @return array
     *
     * @throws Exception
     */
    public function extractValuesOrder(
        $order,
        $billingAddressId,
        $customerGroupId,
        $quoteId,
        $shippingAddressId,
        $weight,
        $incrementId,
        $shippingMethod,
        $customerId
    )
    {
        $dataValues = [
            'entity_id' => (int)$order->ORDER_NO,
            'shipping_description' => (string)$order->SHIPPING,
            'base_grand_total' => (double)$order->PAYMENT_AMOUNT + (double)$order->SHIPPING_COST,
            'base_shipping_amount' => (double)$order->SHIPPING_COST,
            'base_subtotal' => (double)$order->PAYMENT_AMOUNT,
            'grand_total' => (double)$order->PAYMENT_AMOUNT + (double)$order->SHIPPING_COST,
            'shipping_amount' => (double)$order->SHIPPING_COST,
            'subtotal' => (double)$order->PAYMENT_AMOUNT,
            'total_qty_ordered' => count($order->ITEMS->ITEM),
            'billing_address_id' => $billingAddressId,
            'customer_group_id' => $customerGroupId,
            'customer_id' => $customerId,
            'quote_id' => $quoteId,
            'shipping_address_id' => $shippingAddressId,
            'base_subtotal_incl_tax' => (double)$order->PAYMENT_AMOUNT,
            'subtotal_incl_tax' => (double)$order->PAYMENT_AMOUNT,
            'weight' => $weight,
            'increment_id' => $incrementId,
            'customer_email' => (string)$order->ADDRESS[0]->EMAIL,
            'customer_firstname' => (string)$order->ADDRESS[0]->FIRSTNAME,
            'customer_lastname' => (string)$order->ADDRESS[0]->LASTNAME,
            'shipping_method' => $shippingMethod,
            'total_item_count' => count($order->ITEMS->ITEM),
            'shipping_incl_tax' => (double)$order->SHIPPING_COST,
            'base_shipping_incl_tax' => (double)$order->SHIPPING_COST,
        ];

        if ($result = $this->validEntries($dataValues)) {
            return $dataValues;
        } else {
            throw new \Exception('Data not valid: ' . $result);
        }
    }

    /**
     * @param $order
     * @param $reservedOrderId
     *
     * @return array
     *
     * @throws Exception
     */
    public function extractValuesQuote($order, $reservedOrderId)
    {
        $numberOfItems = count($order->ITEMS->ITEM);

        $total = (double)$order->PAYMENT_AMOUNT;
        $shippingCost = (double)$order->SHIPPING_COST;
        $grandTotal = $total + $shippingCost;

        $dataValues = [
            'items_count' => $numberOfItems,
            'grand_total' => $grandTotal,
            'base_grand_total' => $grandTotal,
            'checkout_method' => 'guest',
            'customer_email' => (string)$order->ADDRESS[0]->EMAIL,
            'customer_firstname' => (string)$order->ADDRESS[0]->FIRSTNAME,
            'customer_lastname' => (string)$order->ADDRESS[0]->LASTNAME,
            'reserved_order_id' => (string)$reservedOrderId,
            'subtotal' => $total,
            'base_subtotal' => $total,
            'subtotal_with_discount' => $total,
            'base_subtotal_with_discount' => $total
        ];

        if ($result = $this->validEntries($dataValues)) {
            return $dataValues;
        } else {
            throw new \Exception('Data not valid: ' . $result);
        }
    }

    /**
     * @param $productData
     * @param $orderId
     * @param $quoteItemId
     * @param $product
     *
     * @return array
     *
     * @throws Exception
     */
    public function extractItemValuesOrder($productData, $orderId, $quoteItemId, $product)
    {
        $dataValues = [
            'order_id' => $orderId,
            'quote_item_id' => $quoteItemId,
            'product_id' => $productData['product_id'],
            'weight' => $productData['weight'],
            'sku' => $productData['sku'],
            'name' => $productData['name'],
            'price' => (double)$product->PRICE,
            'base_price' => (double)$product->PRICE,
            'original_price' => (double)$product->PRICE,
            'base_original_price' => (double)$product->PRICE,
            'row_total' => (double)$product->PRICE,
            'base_row_total' => (double)$product->PRICE,
            'price_incl_tax' => (double)$product->PRICE,
            'base_price_incl_tax' => (double)$product->PRICE,
            'row_total_incl_tax' => (double)$product->PRICE,
            'base_row_total_incl_tax' => (double)$product->PRICE
        ];

        if ($result = $this->validEntries($dataValues)) {
            return $dataValues;
        } else {
            throw new \Exception('Data not valid: ' . $result);
        }
    }

    /**
     * @param $productData
     * @param $quoteId
     * @param $product
     *
     * @return array
     *
     * @throws Exception
     */
    public function extractItemValuesQuote($productData, $quoteId, $product)
    {
        $dataValues = [
            'quote_id' => $quoteId,
            'product_id' => $productData['product_id'],
            'weight' => $productData['weight'],
            'sku' => $productData['sku'],
            'name' => $productData['name'],
            'price' => (double)$product->PRICE,
            'base_price' => (double)$product->PRICE,
            'original_price' => (double)$product->PRICE,
            'base_original_price' => (double)$product->PRICE,
            'row_total' => (double)$product->PRICE,
            'base_row_total' => (double)$product->PRICE,
            'price_incl_tax' => (double)$product->PRICE,
            'base_price_incl_tax' => (double)$product->PRICE,
            'row_total_incl_tax' => (double)$product->PRICE,
            'base_row_total_incl_tax' => (double)$product->PRICE
        ];

        if ($result = $this->validEntries($dataValues)) {
            return $dataValues;
        } else {
            throw new \Exception('Data not valid: ' . $result);
        }
    }

    /**
     * @param $order
     * @param $orderId
     * @param $reservedOrderId
     * @param $customerId
     *
     * @return array
     *
     * @throws Exception
     */
    public function extractValuesOrderGrid(
        $order,
        $orderId,
        $reservedOrderId,
        $customerId
    )
    {
        $total = (double)$order->PAYMENT_AMOUNT;
        $shippingCost = (double)$order->SHIPPING_COST;

        $grandTotal = $total + $shippingCost;

        $shippingName = (string)$order->ADDRESS[0]->FIRSTNAME . ' ' . (string)$order->ADDRESS[0]->LASTNAME;
        $billing_name = (string)$order->ADDRESS[0]->FIRSTNAME . ' ' . (string)$order->ADDRESS[0]->LASTNAME;

        $dataValues = [
            'entity_id' => $orderId,
            'customer_id' => $customerId,
            'base_grand_total' => $grandTotal,
            'grand_total' => $grandTotal,
            'increment_id' => $reservedOrderId,
            'shipping_name' => $shippingName,
            'billing_name' => $billing_name,
        ];

        if ($result = $this->validEntries($dataValues)) {
            return $dataValues;
        } else {
            throw new \Exception('Data not valid: ' . $result);
        }
    }

    /**
     * @param $item
     *
     * @return array
     *
     * @throws Exception
     */
    public function extractValuesProduct($item)
    {
        $dataValues = [
            'visibility' => 4,
            'artnr' => (string)$item->ARTNO,
            'artid' => (string)$item->ARTID,
            'hauptnr' => (string)$item->HAUPTNR,
            'ean' => (string)$item->EAN,
            'description' => $this->getDescription($item),
            'short_description' => $this->getShortDescription($item),
            'tax_class_id' => 4,
            'categories' => $this->getCategories($item),
            'sku' => (string)$item->ARTID,
            'status' => 1,
            'price' => 0,
            'weight' => (float)$item->GEWICHT,
            'name' => $this->getName($item),
            'files' => $item->FILES,
        ];

        if ($result = $this->validEntries($dataValues)) {
            return $dataValues;
        } else {
            throw new \Exception('Data not valid: ' . $result);
        }
    }

    /**
     * @param $item
     *
     * @return array
     *
     * @throws Exception
     */
    public function extractValuesPrice($item)
    {
        $dataValues = [
            'artnr' => (string)$item->ARTNR,
            'artid' => (string)$item->ARTID,
            'price' => $this->priceWithDiscount((string)$item->DISCOUNT, (string)$item->PRICE),
            'discount' => (string)$item->DISCOUNT,
            'from' => (string)$item->FROM,
            'unit' => (string)$item->UNIT,
        ];

        if ($result = $this->validEntries($dataValues)) {
            return $dataValues;
        } else {
            throw new \Exception('Data not valid: ' . $result);
        }
    }

    /**
     * @param $discount
     * @param $price
     *
     * @return mixed
     */
    public function priceWithDiscount($discount, $price)
    {
        if ($discount == 0) {
            return $price;
        }

        return $price - ($price * $discount) / 100;
    }

    /**
     * @param $item
     * @return string
     */
    public function getDescription($item)
    {
        $desc = (string)$item->TEXTS->LANG->TEXT[1];
        if (trim($desc) == '') {
            return $this->getShortDescription($item);
        }

        return $desc;
    }

    public function getShortDescription($item)
    {
        return (string)$item->TEXTS->LANG->TEXT[2];
    }

    public function getName($item)
    {
        $name = (string)$item->TEXTS->LANG->TEXT[2];
        if (strlen($name) > 255) {
            $name = substr($name, 0, 254);
        }

        return $name;
    }

    public function getCategories($item)
    {
        $cats = [
            (string)$item->CATEGORIES->CAT1
        ];

        if (isset($item->CATEGORIES->CAT2) && trim($item->CATEGORIES->CAT2) != '') {
            $cats[] = (string)$item->CATEGORIES->CAT2;
        }
        if (isset($item->CATEGORIES->CAT3) && trim($item->CATEGORIES->CAT3) != '') {
            $cats[] = (string)$item->CATEGORIES->CAT3;
        }
        if (isset($item->CATEGORIES->CAT4) && trim($item->CATEGORIES->CAT4) != '') {
            $cats[] = (string)$item->CATEGORIES->CAT4;
        }
        if (isset($item->CATEGORIES->CAT5) && trim($item->CATEGORIES->CAT5) != '') {
            $cats[] = (string)$item->CATEGORIES->CAT5;
        }
        return $cats;
    }

    /**
     * @param $dataValues
     *
     * @return bool|string
     */
    public function validEntries($dataValues)
    {
        $notValid = '';
        foreach ($dataValues as $dataKey => $value) {
            if (!isset($value)) {
                $notValid .= $dataKey . ' // ';
            }
        }

        return ($notValid == '') ? true : $notValid;
    }

}