<?php

namespace SwiftOtter\FriendRecommendations\Model\Service;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;

use SwiftOtter\FriendRecommendations\Api\Data\RecommendationListInterface;
use SwiftOtter\FriendRecommendations\Api\Data\RecommendationListInterfaceFactory;
use SwiftOtter\FriendRecommendations\Api\Data\RecommendationListProductInterface;
use SwiftOtter\FriendRecommendations\Api\RecommendationListProductRepositoryInterface;
use SwiftOtter\FriendRecommendations\Api\RecommendationListRepositoryInterface;
use Magento\Catalog\Model\Product;
use SwiftOtter\FriendRecommendations\Api\Data\RecommendationListProductFactory;
use SwiftOtter\FriendRecommendations\Model\RecommendationListProductRepository;


class CreateRecommendationListService
{
    private RecommendationListRepositoryInterface $recommendationListRepositoryInterface;
    private RecommendationListInterfaceFactory $recommendationListInterfaceFactory;
    private Product $product;
    private RecommendationListProductFactory $recommendationListProduct;
    private RecommendationListProductRepository $recommendationListProductRepository;

    public function __construct(
        RecommendationListRepositoryInterface $recommendationListRepositoryInterface,
        RecommendationListInterfaceFactory $recommendationListInterfaceFactory,
        Product $product,
        RecommendationListProductFactory $recommendationListProduct,
        RecommendationListProductRepository $recommendationListProductRepository
    )
    {
        $this->recommendationListRepositoryInterface = $recommendationListRepositoryInterface;
        $this->recommendationListInterfaceFactory = $recommendationListInterfaceFactory;

        $this->product = $product;

        $this->recommendationListProduct = $recommendationListProduct;
        $this->recommendationListProductRepository = $recommendationListProductRepository;

    }


    /**
     * @throws GraphQlNoSuchEntityException
     * @throws CouldNotSaveException
     * @throws \Exception
     */
    public function execute(array $args): array
    {
        $email = $args['email'];
        $friendName = $args['friendName'];
        $title = ($args['title']) ?? '';
        $note = ($args['note']) ?? '';

        /** @var RecommendationListInterface $recommend_list */
        $recommend_list = $this->recommendationListInterfaceFactory->create();

        $recommend_list->setEmail($email)->setFriendName($friendName)->setTitle($title)->setNote($note);

        $savedRecommendList = $this->recommendationListRepositoryInterface->save($recommend_list);

        if ($list_id = $savedRecommendList->getId()) {
            // product list SKUs
            if (!empty($args['productSkus'])) {
                $this->saveProductSkus($args['productSkus'], $list_id);
            }
        }

        return $this->formatRecommendationListData(
            $savedRecommendList
        );
    }

    public function formatRecommendationListData(RecommendationListInterface $savedRecommendList): array
    {
        return [
            'email' => $savedRecommendList->getEmail(),
            'friendName' => $savedRecommendList->getFriendName(),
            'title' => $savedRecommendList->getTitle(),
            'note' => $savedRecommendList->getNote()
        ];
    }

    /**
     * @throws CouldNotSaveException
     */
    public function saveProductSkus($skus, $list_id) {

        // check if the product exists in Magento
        foreach ($skus as $product_sku) {
            if (!$this->product->getIdBySku($product_sku)) {
                throw new \Exception('Invalid product');
            }
        }

        // save product list with sku and list id
        foreach ($skus as $product_sku) {

            $product_list = $this->recommendationListProduct->create();

            $product_list->setListId($list_id)->setSku($product_sku);

            $this->recommendationListProductRepository->save($product_list);
        }
    }

}
