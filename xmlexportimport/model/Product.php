<?php

/**
 * Class Product
 */
class Product
{
    private $fields = [
        'visibility' => [
            'data_type' => 'int'
        ],
        'artnr' => [
            'data_type' => 'varchar'
        ],
        'artid' => [
            'data_type' => 'varchar'
        ],
        'hauptnr' => [
            'data_type' => 'varchar'
        ],
        'ean' => [
            'data_type' => 'varchar'
        ],
        'description' => [
            'data_type' => 'text'
        ],
        'short_description' => [
            'data_type' => 'text'
        ],
        'tax_class_id' => [
            'data_type' => 'int'
        ],
        'categories' => [
            'data_type' => 'varchar'
        ],
        'sku' => [
            'data_type' => 'varchar'
        ],
        'name' => [
            'data_type' => 'varchar'
        ],
        'status' => [
            'data_type' => 'int'
        ],
        'weight' => [
            'data_type' => 'decimal'
        ],
        'price' => [
            'data_type' => 'decimal'
        ],
    ];

    /**
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

}