<?php

namespace App\Events;

use App\Entity\Order;
use App\Entity\UserEntity;
use Symfony\Contracts\EventDispatcher\Event;

class OrderEvent extends Event
{

    public const ORDER_CREATED = 'order_created';
    public const ORDER_PROCESSING = 'order_processing';
    public const ORDER_READY_TO_SHIP = 'order_ready_to_ship';
    public const ORDER_RE_PROCESSING = 'order_re_processing';
    public const ORDER_SHIPPED = 'order.shipped';

    protected $order;
    protected $userEntity;

    public function __construct(Order $order, UserEntity $userEntity = null)
    {
        $this->order = $order;
        $this->userEntity = $userEntity;
    }

    public function getOrder()
    {
        return $this->order;
    }

    public function getuserEntity()
    {
        return $this->userEntity;
    }


}