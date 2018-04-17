<?php
namespace Astralweb\ShippingStorePickUp\Api;

use Astralweb\ShippingStorePickUp\Api\Data\shopInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface shopRepositoryInterface 
{
    public function save(shopInterface $page);

    public function getById($id);

    public function getList(SearchCriteriaInterface $criteria);

    public function delete(shopInterface $page);

    public function deleteById($id);
}
