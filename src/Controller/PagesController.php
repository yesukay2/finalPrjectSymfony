<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use Symfony\Component\HttpFoundation\RedirectResponse;


class PagesController extends AbstractController
{
    
    
    public function index(): Response
    {
        return new Response('<h1>hello</h1>');
    }

    public function handleLogin(Request $request): RedirectResponse
    {
       
        $userRole = $this->getUser()->getRoles()[0];
        
        //  die($userRole);

        if($userRole == 'ROLE_MANAGER') {
            // return $this->forward('App\Controller\ManagementController::index');
            return $this->redirectToRoute('management_index');
        } else if ($userRole == 'ROLE_SHIPPER') {
            // return $this->forward('App\Controller\ShippingController::index');
            return $this->redirectToRoute('shipping_index');
        } else {
            // return $this->forward('App\Controller\PickingController::index');
            return $this->redirectToRoute('picking_index');
        }

    }
}
