<?php
namespace Astralweb\Creditmemo\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
class AccessActions extends Column
{


    protected $groupquestionFactory;
    protected $_resource;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UserFactory $userFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        \Magento\Framework\App\Filesystem\DirectoryList $directory_list,
        UiComponentFactory $uiComponentFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Item\Collection $ShipmentFactory,
        array $components = [],
        array $data = []
    ) {
      $this->directory_list = $directory_list;
        $this->_resource = $resourceConnection;
        $this->groupquestionFactory = $ShipmentFactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                if($item['product_custom']) {
                    $a =explode("||",$item['product_custom']);
                    $html = '';
                    $html .= '<table style="width:100%" data-bind="css: getFieldClass($row()), click: getFieldHandler($row()), template: getBody()">
                                 <tr>
                                    <th>Sku</th>
                                    <th>Qty</th> 
                                    <th>Price</th>
                                  </tr>';

                    for($i = 0 ;$i<count($a) -1;$i++){
                        $b =explode(",",$a[$i]);
                        $html .= '<tr>
                                    <td>'.explode(":",$b[0])[1].'</td>
                                    <td>'.explode(":",$b[1])[1].'</td>
                                    <td>'.explode(":",$b[2])[1].'</td>
                                </tr>';
                    }
                    
                    $html .= '</table>';
                    $item[$this->getData('name')] = $html;

                 // $item[$this->getData('name')] = $this->prepareItem($item['product_custom']);
                }
            }
        }
        return $dataSource;
    }


}
