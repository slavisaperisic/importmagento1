<?php

/**
 * Class Price
 */
class Price
{
    private $fields = [
        'artnr' => [
            'data_type' => 'varchar'
        ],
        'artid' => [
            'data_type' => 'int'
        ],
        'price' => [
            'data_type' => 'decimal'
        ],
        'discount' => [
            'data_type' => 'varchar'
        ],
        'from' => [
            'data_type' => 'varchar'
        ],
        'unit' => [
            'data_type' => 'varchar'
        ]
    ];

    /**
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

}