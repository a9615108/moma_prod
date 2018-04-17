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



namespace Mirasvit\Rma\Controller\Adminhtml\Field;

use Magento\Framework\Controller\ResultFactory;

class MassDelete extends \Mirasvit\Rma\Controller\Adminhtml\Field
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $ids = $this->getRequest()->getParam('field_id');
        if (!is_array($ids)) {
            $this->messageManager->addError(__('Please select Field(s)'));
        } else {
            try {
                foreach ($ids as $id) {
                    /** @var \Mirasvit\Rma\Model\Field $field */
                    $field = $this->fieldFactory->create()
                        ->setIsMassDelete(true)
                        ->load($id);
                    $field->delete();
                }
                $this->messageManager->addSuccess(
                    __(
                        'Total of %d record(s) were successfully deleted',
                        count($ids)
                    )
                );
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }

        return $resultRedirect->setPath('*/*/index');
    }
}
