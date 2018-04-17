<?php
/*
    php /var/www/html/as_moma/bin/magento ps:IguangCateFull

    全部目錄檔案
*/
namespace Astralweb\Cronjob\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;

class IguangCateFull extends Command
{
    protected function configure()
    {
        $this->setName("ps:IguangCateFull");
        $this->setDescription("A command the programmer was too lazy to enter a description for.");
        parent::configure();
    }

    public function __construct(
        \Magento\Framework\App\State $state
    )
    {
        $this->state  = $state;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output){

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $model = $objectManager->get('Magento\Variable\Model\Variable')->loadByCode('DEBUG_MODEL');
        $DEBUG_MODEL = $model->getName(); 
        if( $DEBUG_MODEL ){
            $this->state->setAreaCode('adminhtml');
        }

        $product_model      = $objectManager->get('Magento\Catalog\Model\Product');
        $productCollection  = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Collection');
        $Category_model     = $objectManager->create('Magento\Catalog\Model\Category');
        //$ProductRepository  = $objectManager->create('Magento\Catalog\Model\ProductRepository');
        $Configurable_model = $objectManager->create('Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable');
        $storeManager       = $objectManager->get('Magento\Store\Model\StoreManagerInterface');
        $currentStore       = $storeManager->getStore();
        $mediaUrl           = $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $baseUrl            = $currentStore->getBaseUrl();

        $directoryList      = $objectManager->get('Magento\Framework\App\Filesystem\DirectoryList');
        $file               = $objectManager->get('Magento\Framework\Filesystem\Io\File');

        $categoryFactory = $objectManager->create('Magento\Catalog\Model\ResourceModel\Category\CollectionFactory');
        $categories = $categoryFactory->create()
            ->addAttributeToSelect('*')
            //->setProductStoreId($store->getId());
            ->addAttributeToFilter(
                [
                    [
                        'attribute' => 'level',
                        'gt' => '1'
                    ]
                ]
            )
            ->setStore($storeManager->getStore())
            ->load();

        $fileContent = array();
        foreach ($categories as $category){
            $obj = array();
            $obj['category_title']          = $category->getName();
            $obj['category_value']          = $category->getId();
            $obj['category_value_parent']   = $category->getParentId();
            $obj['category_flag']           = $category->getIsActive();

            // $a = get_class_methods( $category );
            // $output->writeln("getName :". json_encode($a) );
            // break;

            // $output->writeln( $category->getName() .' : '. $category->getId() .' : '. $category->getParentId() .' : '. $category->getIsActive()  );

            $fileContent[] = $obj;
        }
//*
        $fileContent = json_encode($fileContent);

        $filePath = "/iguang/";
        $pdfPath = $directoryList->getPath('pub').$filePath;
        if (!is_dir($pdfPath)) {
            $file->mkdir($pdfPath, 0775);
        }

        $fileName = 'cate_full';
        $file->open(array('path'=>$pdfPath));
        $file->write($fileName, $fileContent, 0666);
//*/
    }
}