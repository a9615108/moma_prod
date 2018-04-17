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



namespace Mirasvit\Rma\Controller\Adminhtml\Rma;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Filesystem\DirectoryList;

class ExportCsv extends \Mirasvit\Rma\Controller\Adminhtml\Rma
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        //throw new \Exception('Not implemented');
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $fileFactory = $objectManager->get('Magento\Framework\App\Response\Http\FileFactory');
        $fileName = 'rma.csv';
        $content = $resultPage->getLayout()->createBlock('\Mirasvit\Rma\Block\Adminhtml\Rma\Grid')
            ->getCsvFile();

         // var_dump(file_get_contents('http://moma.astralweb.com.tw/var/'.$contentData['value']));die;
        // $content = file_put_contents($fileName, file_get_contents('http://moma.astralweb.com.tw/var/'.$contentData['value']));
        //var_dump($content);die;
        //$this->_sendUploadResponse($fileName, $content);
            // var_dump($content) ; die();
        return $fileFactory->create($fileName,$content, DirectoryList::VAR_DIR);      
    }
    protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream') {

     $this->_response->setHttpResponseCode(200)
        ->setHeader('Pragma', 'public', true)
        ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
        ->setHeader('Content-type', $contentType, true)
        ->setHeader('Content-Length', strlen($content), true)
        ->setHeader('Content-Disposition', 'attachment; filename="' . $fileName . '"', true)
        ->setHeader('Last-Modified', date('r'), true)
        ->setBody($content)
        ->sendResponse();

 }


}
