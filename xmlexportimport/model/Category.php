<?php

/**
 * Class Category
 */
class Category
{
    private $fields = [
        'name' => [
            'data_type' => 'varchar'
        ],
        'meta_description' => [
            'data_type' => 'text'
        ],
        'is_active' => [
            'data_type' => 'int'
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