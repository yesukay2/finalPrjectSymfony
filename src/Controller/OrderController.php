<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Order;
use App\Form\Type\OrderType;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use App\Events\OrderEvent;

class OrderController extends AbstractApiController
{
    
    public function index(Request $request): Response
    {
        $orders = $this->getDoctrine()->getRepository(Order::class)->findAll();

        // return $this->render('order/index.html.twig', [
        //     'controller_name' => 'OrderController',
        // ]);
        return $this->json($orders);
    }

    public function newOrders(Request $request): Response
    {
        $orders = $this->getDoctrine()->getRepository(Order::class)->findBy([
            'status' => 'ORDER_RECEIVED'
        ]);
        return $this->json($orders);
    }

    public function canceledOrders(Request $request): Response
    {
        $orders = $this->getDoctrine()->getRepository(Order::class)->findBy([
            'status' => 'ORDER_CANCELED'
        ]);
        return $this->json($orders);
    }

    public function createeOrder(Request $request): Response
    {
        $orderForm = $this->buildForm(OrderType::class);

        $orderForm->handleRequest($request);

        if(!$orderForm->isSubmitted() || !$orderForm->isValid())
        {
            print 'Form is not valid';
            exit;
        }
        
        /** @var Order $order */
        $order = $orderForm->getData();

        $this->getDoctrine()->getManager()->persist($order);
        $this->getDoctrine()->getManager()->flush();

        return $this->json($order);
    }

    public function createOrder(Request $request, ValidatorInterface $validator, EventDispatcherinterface $eventDispatcher): Response
    {
        $requestData = $request->toArray();

        $eManager = $this->getDoctrine()->getManager();
        
        $order = new Order();
        $order->setTotal($requestData['total']);
        $order->setDiscount($requestData['discount']);
        $order->setItems($requestData['items']);
        $order->setShippingDetails($requestData['shipping_details']);
        $order->setStatus('ORDER_RECEIVED');
        $order->setCreatedAt(new \DateTime("now", new \DateTimeZone("UTC")));
        $order->setUpdatedAt(new \DateTime("now", new \DateTimeZone("UTC")));

        $errors = $validator->validate($order);

        // if (count($errors) > 0) {
        //     return new Response((string) $errors, 400);
        // }

        $eManager->persist($order);
        $eManager->flush();

        $orderEvent = new OrderEvent($order, null);
        $eventDispatcher->dispatch($orderEvent, OrderEvent::ORDER_CREATED);

        return $this->json($order);
    }

    public function cancelOrder(Request $request): Response
    {
        $requestData = $request->toArray();
        $orderId = $requestData['id'];

        $eManager = $this->getDoctrine()->getManager();
        $order = $eManager->getRepository(Order::class)->find($orderId);

        if(!$order) {
            throw $this->createNotFoundException(
                'No order found for id: '.$orderId
            );
        }

        $order->setStatus('ORDER_CANCELED');
        $order->setUpdatedAt(new \DateTime("now", new \DateTimeZone("UTC")));
        $eManager->flush();

        return $this->json($order);

    }
}
