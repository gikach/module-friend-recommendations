<?php

namespace SwiftOtter\FriendRecommendations\Model\Resolver\DataProvider;

use Magento\Catalog\Model\ProductRepository;
use Magento\Customer\Model\Group;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use SwiftOtter\FriendRecommendations\Api\Data\RecommendationListInterface;
use SwiftOtter\FriendRecommendations\Api\RecommendationListRepositoryInterface;

use SwiftOtter\FriendRecommendations\Model\RecommendationListProductRepository;

class RecommendationList
{
    private RecommendationListRepositoryInterface $recommendationListRepository;
    private SearchCriteriaBuilder $searchCriteriaBuilder;
    private RecommendationListProductRepository $recommendationListProductRepository;
    private ProductRepository $product;

    public function __construct (
        RecommendationListRepositoryInterface $recommendationListRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RecommendationListProductRepository $recommendationListProductRepository,
        ProductRepository $product
    ) {

        $this->recommendationListRepository = $recommendationListRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->recommendationListProductRepository = $recommendationListProductRepository;

        $this->product = $product;
    }
    public function getRecommendationList (string $email): array
    {

        $this->searchCriteriaBuilder->addFilter('email', $email);
        $recommendation_lists = $this->recommendationListRepository->getList($this->searchCriteriaBuilder->create())->getItems();

        if (empty($recommendation_lists)) {
            throw new GraphQlNoSuchEntityException(__("No Recommendation List for this Email"));
        }

        return $this->formatRecommendationListData($recommendation_lists);

    }

    /**
     * @throws NoSuchEntityException
     */
    private function formatRecommendationListData(array $recommendation_lists): array
    {
        $result = [];

        foreach ($recommendation_lists as $recommendation_list ) {
            $products_items = $this->getRecommendationListProducts($recommendation_list->getId());
            $recommendationListProduct = [];

            if (!empty($products_items)) {
                foreach ($products_items as $products_item) {
                    $name = $this->product->get($products_item->getSku())->getName();

                    $thumbnailUrl = $this->product->get($products_item->getSku())->getData('thumbnail');

                    $recommendationListProduct[$products_item->getId()]['sku'] = $products_item->getSku();
                    $recommendationListProduct[$products_item->getId()]['name'] = $name;
                    $recommendationListProduct[$products_item->getId()]['thumbnailUrl'] = $thumbnailUrl ?? '';
                }
            }

            $result[] = [
                'friendName' => $recommendation_list->getFriendName(),
                'title' =>  $recommendation_list->getTitle(),
                'note' =>  $recommendation_list->getNote(),
                'products' => $recommendationListProduct
            ];

        }

        return $result;
    }

    private function getRecommendationListProducts($list_id): array
    {
        if (empty($list_id)) {
            return [];
        }

        $this->searchCriteriaBuilder->addFilter('list_id', $list_id);
        $recommendation_list_products_skus = $this->recommendationListProductRepository->getList($this->searchCriteriaBuilder->create())->getItems();

        return $recommendation_list_products_skus;
    }

}
