<?php

namespace App\EventListener;


use Doctrine\ORM\Event\LifecycleEventArgs;
use App\Events\OrderEvent;

use App\Entity\Order;
use App\Entity\Package;
use App\Entity\Issue;

class OrderListener
{

    public function orderCreated(OrderEvent $orderEvent)
    {
        $order = $orderEvent->getOrder();
        $logMessage = 'Order #'.$order->getId().' has been received by the system';
        echo ($logMessage);
    }

    public function orderProcessing(OrderEvent $orderEvent)
    {
        $order = $orderEvent->getOrder();
        $logMessage = 'Order #'.$order->getId().' has been changed to PROCESSING by '.$this->getUser()->getusername().' ('.$this->getUser()->getId().')';
        echo ($logMessage);
    }

    public function orderReadyToShip(OrderEvent $orderEvent)
    {
        $order = $orderEvent->getOrder();
        $package = $eManager->getRepository(Package::class)->findOneBy([
            'orderId' => $order->getId()
        ]);
        $logMessage = 'Order #'.$order->getId().' has been changed to READY TO SHIP by '.$this->getUser()->getusername().' ('.$this->getUser()->getId().') with BOX_ID: '.$package->getBoxId();
        echo ($logMessage);
    }

    public function orderReprocessing(OrderEvent $orderEvent)
    {
        $order = $orderEvent->getOrder();
        $issue = $eManager->getRepository(Issue::class)->findOneBy([
            'orderId' => $order->getId()
        ]);
        $logMessage = 'Order #'.$order->getId().' has been changed to PROCESSING by '.$this->getUser()->getusername().' ('.$this->getUser()->getId().') with reason: '.$issue->getIssueType().' ('.$issue->getReason().')';
        echo ($logMessage);
    }

    public function orderShipped(OrderEvent $orderEvent)
    {
        $order = $orderEvent->getOrder();
        $package = $eManager->getRepository(Package::class)->findOneBy([
            'orderId' => $order->getId()
        ]);
        $logMessage = 'Order #'.$order->getId().' has been changed to SHIPPED by '.$this->getUser()->getusername().' ('.$this->getUser()->getId().') with AWB: #'.$package->getTrackingNumber().' by '.$package->getShippingCompany().' [View Label]';
        echo ($logMessage);
    }


}