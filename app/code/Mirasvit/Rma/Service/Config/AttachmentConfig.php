<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-rma
 * @version   1.1.22
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Rma\Service\Config;

class AttachmentConfig implements \Mirasvit\Rma\Api\Config\AttachmentConfigInterface
{
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function getFileAllowedExtensions($store = null)
    {
        if (!$extensions = $this->scopeConfig->getValue(
            'rma/general/file_allowed_extensions',
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $store
        )) {
            return [];
        }
        $extensions = explode(',', $extensions);
        $extensions = array_map('trim', $extensions);

        return $extensions;
    }

    /**
     * {@inheritdoc}
     */
    public function getFileSizeLimit($store = null)
    {
        return $this->scopeConfig->getValue(
            'rma/general/file_size_limit',
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $store
        );
    }
}
