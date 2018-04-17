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



namespace Mirasvit\Rma\Helper\Message;


class Option extends \Magento\Framework\App\Helper\AbstractHelper
{

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Mirasvit\Core\Helper\ParseVariables $parser,
        \Mirasvit\Rma\Api\Repository\QuickResponseRepositoryInterface $quickResponseRepository,
        \Mirasvit\Rma\Model\QuickResponseFactory $responseFactory,
        \Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface $rmaManagement,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->registry                = $registry;
        $this->searchCriteriaBuilder   = $searchCriteriaBuilder;
        $this->parser                  = $parser;
        $this->quickResponseRepository = $quickResponseRepository;
        $this->responseFactory         = $responseFactory;
        $this->rmaManagement           = $rmaManagement;
        $this->context                 = $context;

        parent::__construct($context);
    }

    /**
     * @return \Mirasvit\Rma\Api\Data\RmaInterface
     */
    public function getRma()
    {
        return $this->registry->registry('current_rma');
    }

    /**
     * @return \Mirasvit\Rma\Api\Data\QuickResponseInterface[]
     */
    public function getOptionsList()
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('is_active', 1)
        ;

        $items = [
            $this->responseFactory->create()->setId(0)->setName(__('-- Please Select --'))
        ];
        $items = array_merge($items, $this->quickResponseRepository->getList($searchCriteria->create())->getItems());
        foreach ($items as $response) {
            $response->setTemplate($this->parseTemplate($response));
        }

        return $items;
    }

    /**
     * @param \Mirasvit\Rma\Model\QuickResponse $response
     * @return string
     */
    public function parseTemplate(\Mirasvit\Rma\Model\QuickResponse $response)
    {
        $template = $response->getTemplate();
        $rma = $this->getRma();
        if ($rma) {
            $data = [
                'rma'   => $rma,
                'store' => $this->rmaManagement->getStore($rma),
                'user'  => $this->rmaManagement->getCustomer($rma),
            ];
            $template = $this->parser->parse($template, $data);
        }

        return $template;
    }
}