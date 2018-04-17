<?php


namespace Astralweb\FixPdfBrokenFont\Model\Order\Pdf;


class PackagingReWrite extends \Magento\Shipping\Model\Order\Pdf\Packaging
{
    /**
     * {@inheritDoc}
     */
    protected function _setFontRegular($object, $size = 7)
    {
        $font = \Zend_Pdf_Font::fontWithPath(
            $this->_rootDirectory->getAbsolutePath('lib/internal/DFKai-SB/kaiu.ttf')
        );
        $object->setFont($font, $size);
        return $font;
    }

    /**
     * {@inheritDoc}
     */
    protected function _setFontBold($object, $size = 7)
    {
        $font = \Zend_Pdf_Font::fontWithPath(
            $this->_rootDirectory->getAbsolutePath('lib/internal/DFKai-SB/kaiu.ttf')
        );
        $object->setFont($font, $size);
        return $font;
    }

    /**
     * {@inheritDoc}
     */
    protected function _setFontItalic($object, $size = 7)
    {
        $font = \Zend_Pdf_Font::fontWithPath(
            $this->_rootDirectory->getAbsolutePath('lib/internal/DFKai-SB/kaiu.ttf')
        );
        $object->setFont($font, $size);
        return $font;
    }
}