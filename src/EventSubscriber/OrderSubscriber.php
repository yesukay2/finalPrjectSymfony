<?php

namespace App\EventSubscriber;

use App\Entity\Order;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Doctrine\ORM\EntityManagerInterface;

use App\Events\OrderEvent;
use App\Entity\Log;
use App\Entity\Package;
use App\Entity\Issue;

class OrderSubscriber implements EventSubscriberInterface
{

    private $eManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->eManager = $entityManager;
    }

    public static function getSubscribedEvents()
    {
        return [
            OrderEvent::ORDER_CREATED => 'orderCreated',
            OrderEvent::ORDER_PROCESSING => 'orderProcessing',
            OrderEvent::ORDER_READY_TO_SHIP => 'orderReadyToShip',
            OrderEvent::ORDER_RE_PROCESSING => 'orderReprocessing',
            OrderEvent::ORDER_SHIPPED => 'orderShipped',
        ];
    }

    public function orderCreated(OrderEvent $orderEvent)
    {
        $order = $orderEvent->getOrder();
        $logMessage = 'Order #'.$order->getId().' has been received by the system';
        // echo ($logMessage);

        // create new log and persist to db
        $log = new Log();
        $log->setOrderId($order->getId());
        $log->setMessage($logMessage);
        $log->setStatus($order->getStatus());
        // $log->setProof();
        $log->setCreatedAt(new \DateTime("now", new \DateTimeZone("UTC")));
        $this->eManager->persist($log);
        $this->eManager->flush();
    }

    public function orderProcessing(OrderEvent $orderEvent)
    {
        $order = $orderEvent->getOrder();
        $user = $orderEvent->getUserEntity();
        $logMessage = 'Order #'.$order->getId().' has been changed to PROCESSING by '.$user->getusername().' ('.$user->getId().')';
        // echo ($logMessage);

        $log = new Log();
        $log->setOrderId($order->getId());
        $log->setMessage($logMessage);
        $log->setStatus($order->getStatus());
        // $log->setProof();
        $log->setCreatedAt(new \DateTime("now", new \DateTimeZone("UTC")));
        $this->eManager->persist($log);
        $this->eManager->flush();
    }

    public function orderReadyToShip(OrderEvent $orderEvent)
    {
        $order = $orderEvent->getOrder();
        $user = $orderEvent->getUserEntity();
        $package = $this->eManager->getRepository(Package::class)->findOneBy([
            'orderId' => $order->getId()
        ]);
        $logMessage = 'Order #'.$order->getId().' has been changed to READY TO SHIP by '.$user->getusername().' ('.$user->getId().') with BOX_ID: '.$package->getBoxId();
        // echo ($logMessage);

        $log = new Log();
        $log->setOrderId($order->getId());
        $log->setMessage($logMessage);
        $log->setStatus($order->getStatus());
        // $log->setProof();
        $log->setCreatedAt(new \DateTime("now", new \DateTimeZone("UTC")));
        $this->eManager->persist($log);
        $this->eManager->flush();
    }

    public function orderReprocessing(OrderEvent $orderEvent)
    {
        $order = $orderEvent->getOrder();
        $user = $orderEvent->getUserEntity();
        $issue = $this->eManager->getRepository(Issue::class)->findOneBy([
            'orderId' => $order->getId()
        ]);
        $logMessage = 'Order #'.$order->getId().' has been changed to PROCESSING by '.$user->getusername().' ('.$user->getId().') with reason: '.$issue->getIssueType().' ('.$issue->getReason().')';
        // echo ($logMessage);

        $log = new Log();
        $log->setOrderId($order->getId());
        $log->setMessage($logMessage);
        $log->setStatus($order->getStatus());
        // $log->setProof();
        $log->setCreatedAt(new \DateTime("now", new \DateTimeZone("UTC")));
        $this->eManager->persist($log);
        $this->eManager->flush();
    }

    public function orderShipped(OrderEvent $orderEvent)
    {
        $order = $orderEvent->getOrder();
        $user = $orderEvent->getUserEntity();
        $package = $this->eManager->getRepository(Package::class)->findOneBy([
            'orderId' => $order->getId()
        ]);
        $logMessage = 'Order #'.$order->getId().' has been changed to SHIPPED by '.$user->getusername().' ('.$user->getId().') with AWB: #'.$package->getTrackingNumber().' by '.$package->getShippingCompany().' [View Label]';
        // echo ($logMessage);

        $log = new Log();
        $log->setOrderId($order->getId());
        $log->setMessage($logMessage);
        $log->setStatus($order->getStatus());
        $log->setProof($package->getProof());
        $log->setCreatedAt(new \DateTime("now", new \DateTimeZone("UTC")));
        $this->eManager->persist($log);
        $this->eManager->flush();
    }
}