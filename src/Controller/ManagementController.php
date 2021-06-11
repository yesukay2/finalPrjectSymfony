<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use Knp\Component\Pager\PaginatorInterface;

use App\Entity\Order;
use App\Entity\Package;
use App\Entity\Issue;
use App\Entity\Log;

class ManagementController extends AbstractController
{
    
    
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        $userRole = $this->getUser()->getRoles()[0];

        $ordersRepo = $this->getDoctrine()->getRepository(Order::class)->findAll();
        $orders = $paginator->paginate(
            $ordersRepo,
            $request->query->getInt('page', 1),
            5
        );

        return $this->render('users/manager/index.html.twig', [
            'orders' => $orders
        ]);
    }

    public function filterOrder(Request $request, PaginatorInterface $paginator): Response
    {

        $status = $request->get('status');

        $ordersRepo = $this->getDoctrine()->getRepository(Order::class)->findBy([
            'status' => $status
        ]);
       

        if($status == "ALL") {
            $ordersRepo = $this->getDoctrine()->getRepository(Order::class)->findAll(); 
        }

        $orders = $paginator->paginate(
            $ordersRepo,
            $request->query->getInt('page', 1),
            5
        );
        
        return $this->render('users/manager/index.html.twig', [
            'orders' => $orders
        ]);
    }

    public function searchOrder(Request $request, PaginatorInterface $paginator): Response
    {

        $orderId = $request->get('orderId');

        $ordersRepo = $this->getDoctrine()->getRepository(Order::class)->findBy([
            'id' => $orderId
        ]);

        $orders = $paginator->paginate(
            $ordersRepo,
            $request->query->getInt('page', 1),
            5
        );
        
        return $this->render('users/manager/index.html.twig', [
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

        $logs = $this->getDoctrine()->getRepository(Log::class)->findBy([
            'orderId' => $orderId
        ]);

        return $this->render('users/manager/order_details.html.twig', [
            'order' => $order,
            'package' => $package,
            'issues' => $issues,
            'logs' => $logs
        ]);
    }
}
