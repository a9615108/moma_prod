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



namespace Mirasvit\Rma\Helper\Controller\Rma;

class NoAccessStrategy extends AbstractStrategy
{

    public function __construct(
        \Mirasvit\Rma\Api\Repository\RmaRepositoryInterface $rmaRepository
    ) {
        $this->rmaRepository = $rmaRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function isRequireCustomerAutorization()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getRmaId(\Mirasvit\Rma\Api\Data\RmaInterface $rma)
    {
        return $rma->getGuestId();
    }

    /**
     * {@inheritdoc}
     */
    public function initRma(\Magento\Framework\App\RequestInterface $request)
    {
        throw new \Magento\Framework\Exception\NoSuchEntityException();
    }

    /**
     * {@inheritdoc}
     */
    public function getRmaList(\Magento\Sales\Model\Order $order = null)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getPerformer()
    {
        // TODO: Implement getPerformer() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedOrderList()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getRmaUrl(\Mirasvit\Rma\Api\Data\RmaInterface $rma)
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getNewRmaUrl()
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getPrintUrl(\Mirasvit\Rma\Api\Data\RmaInterface $rma)
    {
        return '';
    }
}