<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use App\Events\OrderEvent;

use Knp\Component\Pager\PaginatorInterface;

use App\Entity\Order;
use App\Entity\Package;
use App\Entity\Issue;

class PickingController extends AbstractController
{
    
    
    public function index(Request $request, PaginatorInterface $paginator, EventDispatcherinterface $eventDispatcher): Response
    {

        $ordersRepo = $this->getDoctrine()->getRepository(Order::class)->findBy([
            'status' => ['ORDER_RECEIVED', 'ORDER_PROCESSING'],
        ]);
        $orders = $paginator->paginate(
            $ordersRepo,
            $request->query->getInt('page', 1),
            5
        );
        
        return $this->render('users/picker/index.html.twig', [
            'orders' => $orders
        ]);
    }

    public function viewOrder(Request $request): Response
    {
        $orderId = $request->get('id');

        $eManager = $this->getDoctrine()->getManager();
        $order = $eManager->getRepository(Order::class)->find($orderId);

        $package = $eManager->getRepository(Package::class)->findOneBy([
            'orderId' => $orderId
        ]);

        $issues = $eManager->getRepository(Issue::class)->findBy([
            'orderId' => $orderId
        ]);

        return $this->render('users/picker/order_details.html.twig', [
            'order' => $order,
            'package' => $package,
            'issues' => $issues
        ]);
    }

    public function processingOrder(Request $request, EventDispatcherinterface $eventDispatcher): Response
    {
        $orderId = $request->get('id');

        $eManager = $this->getDoctrine()->getManager();
        $order = $eManager->getRepository(Order::class)->find($orderId);

        if(!$order) {
            throw $this->createNotFoundException(
                'No order found for id: '.$orderId
            );
        }

        $order->setStatus('ORDER_PROCESSING');
        $order->setUpdatedAt(new \DateTime("now", new \DateTimeZone("UTC")));
        $eManager->flush();

        //DISPATCH EVENT
        $orderEvent = new OrderEvent($order, $this->getuser());
        $eventDispatcher->dispatch($orderEvent, OrderEvent::ORDER_PROCESSING);

        $package = $eManager->getRepository(Package::class)->findOneBy([
            'orderId' => $orderId
        ]);

        $issues = $eManager->getRepository(Issue::class)->findBy([
            'orderId' => $orderId
        ]);

        return $this->render('users/picker/order_details.html.twig', [
            'order' => $order,
            'package' => $package,
            'issues' => $issues
        ]);

    }

    public function orderProcessingComplete(Request $request, EventDispatcherinterface $eventDispatcher): Response
    {

        $boxId = $request->get('boxId');

        $orderId = $request->get('id');

        $eManager = $this->getDoctrine()->getManager();
        $order = $eManager->getRepository(Order::class)->find($orderId);

        if(!$order) {
            throw $this->createNotFoundException(
                'No order found for id: '.$orderId
            );
        }

        // update status and timestamp
        $order->setStatus('ORDER_READY_TO_SHIP');
        $order->setUpdatedAt(new \DateTime("now", new \DateTimeZone("UTC")));
        $eManager->persist($order);

        // check if package already exist
        // if a package already exists with the same orderId, proceed to update, else; create new record

        $oldPackage = $eManager->getRepository(Package::class)->findOneBy([
            'orderId' => $orderId
        ]);

        if($oldPackage) {
            // update old package
            $oldPackage->setBoxId($boxId);
            $oldPackage->setUpdatedAt(new \DateTime("now", new \DateTimeZone("UTC")));

            $eManager->persist($oldPackage);
        } else {
            // create new package and persist to db
            $package = new Package();
            $package->setOrderId($orderId);
            $package->setBoxId($boxId);
            $package->setPackingUserId($this->getUser()->getId());
            $package->setCreatedAt(new \DateTime("now", new \DateTimeZone("UTC")));
            $package->setUpdatedAt(new \DateTime("now", new \DateTimeZone("UTC")));

            $eManager->persist($package);
        }
        
        

        $eManager->flush();

        //DISPATCH EVENT
        $orderEvent = new OrderEvent($order, $this->getuser());
        $eventDispatcher->dispatch($orderEvent, OrderEvent::ORDER_READY_TO_SHIP);

        $package = $eManager->getRepository(Package::class)->findOneBy([
            'orderId' => $orderId
        ]);

        $issues = $eManager->getRepository(Issue::class)->findBy([
            'orderId' => $orderId
        ]);

        return $this->render('users/picker/order_details.html.twig', [
            'order' => $order,
            'package' => $package,
            'issues' => $issues
        ]);

    }
}
