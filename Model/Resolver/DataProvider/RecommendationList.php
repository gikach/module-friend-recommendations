<?php

namespace SwiftOtter\FriendRecommendations\Model\Resolver\DataProvider;

use Magento\Customer\Model\Group;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use SwiftOtter\FriendRecommendations\Api\Data\RecommendationListInterface;
use SwiftOtter\FriendRecommendations\Api\RecommendationListRepositoryInterface;

class RecommendationList
{
    private RecommendationListRepositoryInterface $recommendationListRepository;
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    public function __construct (
        RecommendationListRepositoryInterface $recommendationListRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {

        $this->recommendationListRepository = $recommendationListRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
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

    private function formatRecommendationListData(array $recommendation_lists): array
    {
        $result = [];

        foreach ($recommendation_lists as $recommendation_list ) {
            $result[] = [
                'friendName' => $recommendation_list->getFriendName(),
                'title' =>  $recommendation_list->getTitle(),
                'note' =>  $recommendation_list->getNote(),
                'products' => [
                ]
            ];

        }

        return $result;
    }

}
