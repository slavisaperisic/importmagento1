<?php

/**
 * Class Address
 */
class Address
{
    private $fields = [
        'firstname' => [
            'data_type' => 'varchar'
        ],
        'lastname' => [
            'data_type' => 'varchar'
        ],
        'address' => [
            'data_type' => 'varchar'
        ],
        'street' => [
            'data_type' => 'text'
        ],
        'country' => [
            'data_type' => 'varchar'
        ],
        'postcode' => [
            'data_type' => 'varchar'
        ],
        'city' => [
            'data_type' => 'varchar'
        ],
        'telephone' => [
            'data_type' => 'varchar'
        ],
        'fax' => [
            'data_type' => 'varchar'
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