<?php

namespace Biz\RewardPoint\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\RewardPoint\Service\ProductOrderService;

class ProductOrderServiceImpl extends BaseService implements ProductOrderService
{
    public function createProductOrder($fields)
    {
        $this->validateProductOrderFields($fields);
        $fields = $this->filterFields($fields);
        $fields['status'] = 'created';

        return $this->getProductOrderDao()->create($fields);
    }

    public function updateProductOrder($id, $fields)
    {
        $fields = $this->filterFields($fields);

        return $this->getProductOrderDao()->update($id, $fields);
    }

    public function deleteProductOrder($id)
    {
        $productOrder = $this->getProductOrder($id);

        if (empty($productOrder)) {
            throw $this->createNotFoundException("thread(#{$id}) not found");
        }

        return $this->getProductOrderDao()->delete($id);
    }

    public function getProductOrder($id)
    {
        return $this->getProductOrderDao()->get($id);
    }

    public function countProductOrders(array $conditions)
    {
        return $this->getProductOrderDao()->count($conditions);
    }

    public function searchProductOrders(array $conditions, array $orderBys, $start, $limit)
    {
        return $this->getProductOrderDao()->search($conditions, $orderBys, $start, $limit);
    }

    public function findProductOrdersByProductId($productId)
    {
        return $this->getProductOrderDao()->findByProductId($productId);
    }

    public function findProductOrdersByUserId($userId)
    {
        return $this->getProductOrderDao()->findByUserId($userId);
    }

    protected function validateProductOrderFields($fields)
    {
        if (!ArrayToolkit::requireds(
            $fields,
            array(
            'sn',
            'productId',
            'title',
            'price',
            'userId',
            'telephone',
            'email',
            'address',
        ))) {
            throw $this->createInvalidArgumentException('parameters is invalid');
        }
    }

    protected function filterFields($fields)
    {
        return ArrayToolkit::parts(
            $fields,
            array(
                'sn',
                'productId',
                'title',
                'price',
                'userId',
                'telephone',
                'email',
                'address',
                'sendTime',
                'message',
                'status',
            )
        );
    }

    protected function getProductOrderDao()
    {
        return $this->createDao('RewardPoint:ProductOrderDao');
    }
}
