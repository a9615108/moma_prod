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
 * @package   mirasvit/module-report
 * @version   1.2.3
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\Report\Service;

use Mirasvit\Report\Api\Repository\Email\BlockRepositoryInterface;
use Mirasvit\Report\Api\Service\EmailServiceInterface;
use Mirasvit\Report\Api\Data\EmailInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Backend\App\Area\FrontNameResolver;
use Mirasvit\Report\Api\Repository\EmailRepositoryInterface;

class EmailService implements EmailServiceInterface
{
    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var EmailRepositoryInterface
     */
    protected $emailRepository;

    public function __construct(
        TransportBuilder $transportBuilder,
        EmailRepositoryInterface $emailRepository
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->emailRepository = $emailRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function send(EmailInterface $email)
    {
        $vars = [
            'email'  => $email,
            'blocks' => "",
        ];

        $definedBlocks = $this->emailRepository->getBlocks();

        foreach ($email->getBlocks() as $data) {
            $identifier = $data['identifier'];

            if (key_exists($identifier, $definedBlocks)) {
                /** @var BlockRepositoryInterface $repo */
                $repo = $definedBlocks[$identifier]['repository'];

                $vars['blocks'] .= $repo->getContent($identifier, $data);
            }
        }

        $emails = explode(',', $email->getRecipient());

        foreach ($emails as $mail) {
            if (!trim($mail)) {
                continue;
            }

            /** @var \Magento\Framework\Mail\Transport $transport */
            $transport = $this->transportBuilder
                ->setTemplateIdentifier('report_email')
                ->setTemplateOptions([
                    'area'  => FrontNameResolver::AREA_CODE,
                    'store' => 0,
                ])
                ->setTemplateVars($vars)
                ->addTo($mail)
                ->getTransport();
            $transport->sendMessage();
        }
    }
}