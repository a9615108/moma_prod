<?php
namespace Astralweb\RewriteSitemap\Model\ResourceModel\Cms;

class Page extends \Magento\Sitemap\Model\ResourceModel\Cms\Page
{
    protected function _prepareObject(array $data)
    {
        $object = new \Magento\Framework\DataObject();
        $object->setId($data[$this->getIdFieldName()]);
        if ($data['url'] == 'home') {
            $data['url'] = '';
        }
        $object->setUrl($data['url']);
        $object->setUpdatedAt($data['updated_at']);

        return $object;
    }

}