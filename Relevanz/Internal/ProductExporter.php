<?php
namespace Releva\Retargeting\Shopware\Internal;

use Releva\Retargeting\Base\Exception\RelevanzException;
use Releva\Retargeting\Base\Export\ProductCsvExporter;
use Releva\Retargeting\Base\Export\ProductJsonExporter;
use Releva\Retargeting\Base\Export\Item\ProductExportItem;

use Shopware\Bundle\SearchBundle\Condition\CategoryCondition;

class ProductExporter {
    
    const FORMAT_CSV = 'csv';
    
    const FORMAT_JSON = 'json';
    
    /**
     * @param \Shopware\Bundle\StoreFrontBundle\Struct\ShopContext $context
     */
    public function export($context, $criteria, $format = 'csv', $limit = null, $offset = 0) {
        $criteria->addCondition(new CategoryCondition(array($context->getShop()->getCategory()->getId())));
        $criteria->limit($limit);
        $criteria->offset($offset);
        /* @var $productResult \Shopware\Bundle\SearchBundle\ProductSearchResult */
        $productResult = \Shopware()->Container()->get('shopware_search.product_search')->search($criteria, $context);
        $products = $productResult->getProducts();
        if (count($products) === 0) {
            throw new RelevanzException("No products found.", 1585554289);
        }
        $exporter = $format === 'json' ? new ProductJsonExporter() : new ProductCsvExporter();
        foreach ($products as $product) {
            $exporter->addItem($this->getProductExportItem($product, $context));
        }
        return $exporter;
    }
    
    /**
     * @param \Shopware\Bundle\StoreFrontBundle\Struct\ListProduct $product
     * @param \Shopware\Bundle\StoreFrontBundle\Struct\ShopContext $context
     * @return \Releva\Retargeting\Shopware\Internal\ProductExportItem
     */
    private function getProductExportItem ($product, $context) {
        $categoryIds = array();
        foreach ($product->getCategories() as $category) {
            if (in_array($context->getShop()->getCategory()->getId(), $category->getPath())) {
                $categoryIds[] = $category->getId();
            }
        }
        return new ProductExportItem(
            (string) $product->getId(),
            (array) $categoryIds,
            (string) $product->getName(),
            (string) $product->getShortDescription(),
            (string) $product->getLongDescription(),
            (float) $product->getCheapestPrice()->getCalculatedPseudoPrice(),
            (float) $product->getCheapestPrice()->getCalculatedPrice(),
            (string) \Shopware()->Front()->Router()->assemble(array('module' => 'frontend', 'sViewport' => 'detail', 'sArticle' => $product->getId())),
            (string) $product->getCover()->getFile()
         );
    }
    
}
