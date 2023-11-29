<?php

namespace SwiftOtter\FriendRecommendations\Model\Service;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;

use SwiftOtter\FriendRecommendations\Api\Data\RecommendationListInterface;
use SwiftOtter\FriendRecommendations\Api\Data\RecommendationListInterfaceFactory;
use SwiftOtter\FriendRecommendations\Api\RecommendationListRepositoryInterface;

class CreateRecommendationListService
{
    private RecommendationListRepositoryInterface $recommendationListRepositoryInterface;
    private RecommendationListInterfaceFactory $recommendationListInterfaceFactory;

    public function __construct(
        RecommendationListRepositoryInterface $recommendationListRepositoryInterface,
        RecommendationListInterfaceFactory $recommendationListInterfaceFactory
    )
    {
        $this->recommendationListRepositoryInterface = $recommendationListRepositoryInterface;
        $this->recommendationListInterfaceFactory = $recommendationListInterfaceFactory;

    }


    /**
     * @throws GraphQlNoSuchEntityException
     * @throws CouldNotSaveException
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

}
