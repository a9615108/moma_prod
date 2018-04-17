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



namespace Mirasvit\Rma\Controller\Adminhtml\Resolution;

use Magento\Framework\Controller\ResultFactory;
use \Mirasvit\Rma\Api\Data\ResolutionInterface;

class MassDelete extends \Mirasvit\Rma\Controller\Adminhtml\Resolution
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $ids = $this->getRequest()->getParam('resolution_id');
        if (!is_array($ids)) {
            $this->messageManager->addErrorMessage(__('Please select Resolution(s)'));
            return $resultRedirect->setPath('*/*/index');
        }
        try {
            $resolutionAmount = count($ids);
            foreach ($ids as $id) {
                if (in_array($id, ResolutionInterface::RESERVED_IDS)) {
                    $this->messageManager->addWarningMessage(
                        __(
                            'This resolution "%1" is reserved. You can only deactivate it',
                            $this->resolutionFactory->create()->load($id)->getName()
                        )
                    );
                    $resolutionAmount--;
                    continue;
                }
                $this->resolutionRepository->deleteById($id);
            }
            if ($resolutionAmount) {
                $this->messageManager->addSuccessMessage(
                    __(
                        'Total of %1 record(s) were successfully deleted',
                        $resolutionAmount
                    )
                );
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $resultRedirect->setPath('*/*/index');
    }
}
