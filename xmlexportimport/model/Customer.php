<?php

/**
 * Class Customer
 */
class Customer
{
    private $fields = [
        'firstname' => [
            'data_type' => 'varchar'
        ],
        'lastname' => [
            'data_type' => 'varchar'
        ],
        'password_hash' => [
            'data_type' => 'varchar'
        ],
        'bankidentification' => [
            'data_type' => 'varchar'
        ],
        'accountno' => [
            'data_type' => 'varchar'
        ],
        'bank' => [
            'data_type' => 'varchar'
        ],
        'iban' => [
            'data_type' => 'varchar'
        ],
        'bic' => [
            'data_type' => 'varchar'
        ],
        'customerurl' => [
            'data_type' => 'varchar'
        ],
        'isolk' => [
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