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


namespace Mirasvit\Rma\Service\Resolution;
use Mirasvit\Rma\Api\Data\RmaInterface;

/**
 *  We put here only methods directly connected with Resolution properties
 */
class ResolutionManagement implements \Mirasvit\Rma\Api\Service\Resolution\ResolutionManagementInterface
{
    public function __construct(
        \Mirasvit\Rma\Api\Repository\ResolutionRepositoryInterface $resolutionRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->resolutionRepository  = $resolutionRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getResolutionByCode($code)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('code', $code)
        ;

        return $this->resolutionRepository->getList($searchCriteria->create())->getItems();
    }
}

