<?php

namespace SwiftOtter\FriendRecommendations\Model;

use SwiftOtter\FriendRecommendations\Model\Service\CreateRecommendationListService;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;


class CreateRecommendationList implements ResolverInterface
{
    private CreateRecommendationListService $createRecommendationListService;

    public function __construct(
        CreateRecommendationListService $createRecommendationListService
    )
    {
        $this->createRecommendationListService = $createRecommendationListService;
    }

    /**
     * {@inheritdoc}
     * @param ContextInterface $context
     * @throws GraphQlInputException
     * @throws GraphQlNoSuchEntityException
     * @throws CouldNotSaveException
     */
    public function resolve(
        Field $field,
              $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($args['email'])) {
            throw new GraphQlInputException(__('Email is required to create recommendation list'));
        }

        $contextExt = $context->getExtensionAttributes();

        return $this->createRecommendationListService->execute($args);
    }

}
