<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\FileUploader;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use App\Events\OrderEvent;

use Knp\Component\Pager\PaginatorInterface;

use App\Entity\Order;
use App\Entity\Package;
use App\Entity\Issue;

class ShippingController extends AbstractController
{
    
    
    public function index(Request $request, PaginatorInterface $paginator, EventDispatcherinterface $eventDispatcher): Response
    {
        $ordersRepo = $this->getDoctrine()->getRepository(Order::class)->findBy([
            'status' => 'ORDER_READY_TO_SHIP'
        ]);
        $orders = $paginator->paginate(
            $ordersRepo,
            $request->query->getInt('page', 1),
            5
        );

        // $orders = $this->getDoctrine()->getRepository(Order::class)->findAll();
        
        return $this->render('users/shipper/index.html.twig', [
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

        return $this->render('users/shipper/order_details.html.twig', [
            'order' => $order,
            'package' => $package,
            'issues' => $issues
        ]);
    }

    public function submitIssue(Request $request, EventDispatcherinterface $eventDispatcher): Response
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

        // create new issue and persist to db
        $issue = new Issue();
        $issue->setOrderId($orderId);
        $issue->setIssueType($request->get('issueType'));
        $issue->setReason($request->get('reason'));
        $issue->setCreatedAt(new \DateTime("now", new \DateTimeZone("UTC")));
        $issue->setUpdatedAt(new \DateTime("now", new \DateTimeZone("UTC")));

        $eManager->persist($issue);

        $eManager->flush();

        // dispatch event
        
        $orderEvent = new OrderEvent($order, $this->getuser());
        $eventDispatcher->dispatch($orderEvent, OrderEvent::ORDER_RE_PROCESSING);

        $package = $eManager->getRepository(Package::class)->findOneBy([
            'orderId' => $orderId
        ]);

        $issues = $eManager->getRepository(Issue::class)->findBy([
            'orderId' => $orderId
        ]);

        return $this->render('users/shipper/order_details.html.twig', [
            'order' => $order,
            'package' => $package,
            'issues' => $issues
        ]);
    }

    public function shipOrder(Request $request, FileUploader $fileUploader, EventDispatcherinterface $eventDispatcher): Response
    {
        $orderId = $request->get('id');

        $eManager = $this->getDoctrine()->getManager();
        $order = $eManager->getRepository(Order::class)->find($orderId);

        if(!$order) {
            throw $this->createNotFoundException(
                'No order found for id: '.$orderId
            );
        }

        $order->setStatus('ORDER_SHIPPED');
        $order->setUpdatedAt(new \DateTime("now", new \DateTimeZone("UTC")));

        $oldPackage = $eManager->getRepository(Package::class)->findOneBy([
            'orderId' => $orderId
        ]);

        // update package
        $oldPackage->setShippingCompany($request->get('shippingCompany'));

        $file = $request->files->get('proof');
        $filenName = $fileUploader->upload($file);

        $oldPackage->setProof($filenName);
        $oldPackage->setTrackingNumber($request->get('trackingNumber'));
        $oldPackage->setShippingUserId($this->getUser()->getId());
        $oldPackage->setUpdatedAt(new \DateTime("now", new \DateTimeZone("UTC")));

        $eManager->persist($oldPackage);
        

        $eManager->flush();

        //DISPATCH EVENT
        $orderEvent = new OrderEvent($order, $this->getuser());
        $eventDispatcher->dispatch($orderEvent, OrderEvent::ORDER_SHIPPED);

        $package = $eManager->getRepository(Package::class)->findOneBy([
            'orderId' => $orderId
        ]);

        $issues = $eManager->getRepository(Issue::class)->findBy([
            'orderId' => $orderId
        ]);

        return $this->render('users/shipper/order_details.html.twig', [
            'order' => $order,
            'package' => $package,
            'issues' => $issues
        ]);
    }

    /**
     * @Route("/download/{filename}", name="download_file")
    **/
    public function downloadFileAction(Request $request){
        $fileName = $request->get('filename');
        $response = new BinaryFileResponse($this->getParameter('proof_directory').'/'.$fileName);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT);
        return $response;
    }
}
