<?php


namespace Astralweb\MassPrintInvoicePlus\Controller\Adminhtml\Order\Invoice;

use Eadesigndev\Pdfgenerator\Controller\Adminhtml\Order\Abstractpdf;
use Eadesigndev\Pdfgenerator\Model\ResourceModel\Pdfgenerator\CollectionFactory as templateCollectionFactory;
use Eadesigndev\Pdfgenerator\Helper\Pdf;
use Eadesigndev\Pdfgenerator\Helper\Data;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Eadesigndev\Pdfgenerator\Model\Source\TemplateActive;
use Eadesigndev\Pdfgenerator\Model\Source\AbstractSource;
use Magento\Email\Model\Template\Config;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Eadesigndev\Pdfgenerator\Model\PdfgeneratorRepository;
use Magento\Sales\Model\Order\InvoiceRepository;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;

class MassPrintInvoiceOrderGrid extends Abstractpdf
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::sales_invoice';

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var FileFactory
     */

    private $fileFactory;
    /**
     * @var ForwardFactory
     */

    private $resultForwardFactory;

    protected $_invoiceCollectionFactory;

    /**
     * @var Pdf
     */
    private $helper;

    /**
     * @var PdfgeneratorRepository
     */
    private $pdfGeneratorRepository;

    /**
     * @var InvoiceRepository
     */
    private $invoiceRepository;

    /**
     * Printpdf constructor.
     * @param Context $context
     * @param Registry $coreRegistry
     * @param Config $emailConfig
     * @param JsonFactory $resultJsonFactory
     * @param Pdf $helper
     * @param DateTime $dateTime
     * @param FileFactory $fileFactory
     * @param ForwardFactory $resultForwardFactory
     * @param PdfgeneratorRepository $pdfGeneratorRepository
     * @param InvoiceRepository $invoiceRepository
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        Config $emailConfig,
        JsonFactory $resultJsonFactory,
        Pdf $helper,
        DateTime $dateTime,
        FileFactory $fileFactory,
        ForwardFactory $resultForwardFactory,
        PdfgeneratorRepository $pdfGeneratorRepository,
        InvoiceRepository $invoiceRepository,
        Data $dataHelper,
        Filter $filter,
        \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory $invoiceCollectionFactory,
        templateCollectionFactory $_templateCollection,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory

    ) {
        $this->fileFactory = $fileFactory;
        $this->helper = $helper;
        parent::__construct($context, $coreRegistry, $emailConfig, $resultJsonFactory);
        $this->resultForwardFactory = $resultForwardFactory;
        $this->dateTime = $dateTime;
        $this->pdfGeneratorRepository = $pdfGeneratorRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->dataHelper = $dataHelper;
        $this->filter = $filter;
        $this->_invoiceCollectionFactory = $invoiceCollectionFactory;
        $this->templateCollection = $_templateCollection;
        $this->_orderCollectionFactory = $orderCollectionFactory;
    }

    /**
     * @return object
     */
    public function execute()
    {
        $helper = $this->helper;

        if ($this->getRequest()->getParam('namespace') == 'sales_order_grid') {
            /** @var \Magento\Sales\Model\ResourceModel\Order\Invoice\Collection $invoiceCollection */
            $invoiceCollection = $this->_invoiceCollectionFactory->create();

            /** @var \Magento\Sales\Model\ResourceModel\Order\Collection $orderCollection */
            $orderCollection = $this->filter->getCollection($this->_orderCollectionFactory->create());

            $invoiceIds = [];
            foreach ($orderCollection as $order) {
                /** @var \Magento\Sales\Model\Order $order */
                $invoiceIds = array_merge($invoiceIds, $order->getInvoiceCollection()->getAllIds());
            }

            $invoiceCollection->addFieldToFilter('entity_id', ['in' => $invoiceIds]);

        } else {
            //set collection to have not any item
        }

        $templateId = $this->getTemplateId();

        if (!$templateId) {
            return $this->returnNoRoute();
        }

        $templateModel = $this->pdfGeneratorRepository
            ->getById($templateId);

        if (!$templateModel) {
            return $this->returnNoRoute();
        }

        $helper->setTemplate($templateModel);

        $pdf = $helper->_eaPDFSettingsMass($templateModel);

        foreach ($invoiceCollection as  $value) {
            $invoiceId  = $value->getId();
            $invoice = $this->invoiceRepository
                ->get($invoiceId);
            $helper->setInvoice($invoice);

            $pdfFileData = $helper->_transport();
            $pdf->AddPage();
            $pdf->WriteHTML('<body >' . html_entity_decode($pdfFileData['body']) . '</body>');
            // \Zend_Debug::dump($pdfFileData);

        }
        // $pdfToOutput = $pdf->Output('', 'S');

        $pdfFileData = $this->template2PdfMass($pdf);
        $date = $this->dateTime->date('Y-m-d_H-i-s');

        $fileName = $pdfFileData['filename'] . $date . '.pdf';

        return $this->fileFactory->create(
            $fileName,
            $pdfFileData['filestream'],
            DirectoryList::VAR_DIR,
            'application/pdf'
        );
    }

    /**
     * @return $this
     */
    private function returnNoRoute()
    {
        return $this->resultForwardFactory->create()->forward('noroute');
    }




    public function template2PdfMass($applySettings)
    {
        /**transport use to get the variables $order object, $invoice object and the template model object*/
        // $parts = $this->_transport();

        /** instantiate the mPDF class and add the processed html to get the pdf*/
        // $applySettings = $this-> _eaPDFSettingsMass($parts);
        $applySettings = $applySettings->Output('', 'S');

        $fileParts = [
            'filestream' => $applySettings,
            'filename' => filter_var($parts['filename'], FILTER_SANITIZE_URL)
        ];

        return $fileParts;
    }
    public function getTemplateId(){
        $collection = $this->templateCollection->create();
        $collection->addFieldToFilter('is_active', TemplateActive::STATUS_ENABLED);
        $collection->addFieldToFilter('template_default', AbstractSource::IS_DEFAULT);
        $collection->getLastItem();

        return $collection->getData()[($collection->getSize() - 1)]['template_id'];
    }
}