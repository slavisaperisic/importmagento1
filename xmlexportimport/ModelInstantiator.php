<?php

/**
 * Class ModelInstantiator
 */
class ModelInstantiator
{
    /**
     * @var CategoryORM
     */
    private $categoryORM;

    /**
     * @var ProductORM
     */
    private $productORM;

    /**
     * @var CustomerORM
     */
    private $customerORM;

    /**
     * @var AddressORM
     */
    private $addressORM;

    /**
     * @var OrderORM
     */
    private $orderORM;

    /**
     * @var AttributeORM
     */
    private $attributeORM;

    /**
     * @var PriceORM
     */
    private $priceORM;

    /**
     * @var
     */
    private $pdo;

    public function __construct(
        PDO $pdo
    )
    {
        $this->pdo = $pdo;
        $this->categoryORM = new CategoryORM($pdo);
        $this->productORM = new ProductORM($pdo);
        $this->priceORM = new PriceORM($pdo);
        $this->customerORM = new CustomerORM($pdo);
        $this->addressORM = new AddressORM($pdo);
        $this->orderORM = new OrderORM($pdo);
        $this->attributeORM = new AttributeORM($pdo);
    }

    /**
     * @return CategoryORM
     */
    public function getCategoryORM()
    {
        return $this->categoryORM;
    }

    /**
     * @param CategoryORM $categoryORM
     */
    public function setCategoryORM($categoryORM)
    {
        $this->categoryORM = $categoryORM;
    }

    /**
     * @return ProductORM
     */
    public function getProductORM()
    {
        return $this->productORM;
    }

    /**
     * @param ProductORM $productORM
     */
    public function setProductORM($productORM)
    {
        $this->productORM = $productORM;
    }

    /**
     * @return CustomerORM
     */
    public function getCustomerORM()
    {
        return $this->customerORM;
    }

    /**
     * @param CustomerORM $customerORM
     */
    public function setCustomerORM($customerORM)
    {
        $this->customerORM = $customerORM;
    }

    /**
     * @return AddressORM
     */
    public function getAddressORM()
    {
        return $this->addressORM;
    }

    /**
     * @param AddressORM $addressORM
     */
    public function setAddressORM($addressORM)
    {
        $this->addressORM = $addressORM;
    }

    /**
     * @return OrderORM
     */
    public function getOrderORM()
    {
        return $this->orderORM;
    }

    /**
     * @return PriceORM
     */
    public function getPriceORM()
    {
        return $this->priceORM;
    }

    /**
     * @param OrderORM $orderORM
     */
    public function setOrderORM($orderORM)
    {
        $this->orderORM = $orderORM;
    }

    /**
     * @return AttributeORM
     */
    public function getAttributeORM()
    {
        return $this->attributeORM;
    }

    /**
     * @param AttributeORM $attributeORM
     */
    public function setAttributeORM($attributeORM)
    {
        $this->attributeORM = $attributeORM;
    }

    /**
     * @return mixed
     */
    public function getPdo()
    {
        return $this->pdo;
    }

    /**
     * @param mixed $pdo
     */
    public function setPdo($pdo)
    {
        $this->pdo = $pdo;
    }
}