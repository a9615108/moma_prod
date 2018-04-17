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


abstract class AbstractStrategy
{

    /**
     * @return bool
     */
    public abstract function isRequireCustomerAutorization();

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @return \Mirasvit\Rma\Api\Data\RmaInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public abstract function initRma(\Magento\Framework\App\RequestInterface $request);


    /**
     * @param \Mirasvit\Rma\Api\Data\RmaInterface $rma
     * @return int
     */
    public abstract function getRmaId(\Mirasvit\Rma\Api\Data\RmaInterface $rma);

    /**
     * @param \Magento\Sales\Model\Order|null $order
     * @return \Mirasvit\Rma\Api\Data\RmaInterface[]
     */
    public abstract function getRmaList(\Magento\Sales\Model\Order $order = null);

    /**
     * @return \Mirasvit\Rma\Api\Service\Performer\PerformerInterface
     */
    public abstract function getPerformer();

    /**
     * @return \Magento\Sales\Model\Order[]
     */
    public abstract function getAllowedOrderList();

    /**
     * @param \Mirasvit\Rma\Api\Data\RmaInterface $rma
     *
     * @return string
     */
    public abstract function getRmaUrl(\Mirasvit\Rma\Api\Data\RmaInterface $rma);

    /**
     * @return string
     */
    public abstract function getNewRmaUrl();

    /**
     * @param \Mirasvit\Rma\Api\Data\RmaInterface $rma
     *
     * @return string
     */
    public abstract function getPrintUrl(\Mirasvit\Rma\Api\Data\RmaInterface $rma);
}