<?php

namespace SwiftOtter\FriendRecommendations\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextExtensionInterface;

use SwiftOtter\FriendRecommendations\Model\Resolver\DataProvider\RecommendationList as RecommendationListProvider;

use Magento\Customer\Api\CustomerRepositoryInterface;

class RecommendationList implements ResolverInterface
{

    private RecommendationListProvider $recommendationListProvider;
    private CustomerRepositoryInterface $customerRepository;


    public function __construct(
        RecommendationListProvider $recommendationListProvider,
        CustomerRepositoryInterface $customerRepository,
    ) {
        $this->recommendationListProvider = $recommendationListProvider;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @inheirtDoc
     * @param ContextInterface $context
     * @throws GraphQlNoSuchEntityException
     */
    public function resolve(
        Field $field,
              $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {

        $userId = $context->getUserId();
        try {
            $email = $this->getUserEmail($userId);
        } catch (NoSuchEntityException|LocalizedException $e) {
        }


        if (empty($email)) {
            throw new GraphQlNoSuchEntityException(__('No Email for user'));
        }


        return $this->recommendationListProvider->getRecommendationList($email);


    }

    /**
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getUserEmail($userId): string
    {
        $customer = $this->customerRepository->getById($userId);

        return $customer->getEmail();

    }






}
