<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Order;

abstract class AbstractApiController extends AbstractController
{
    
    protected function buildForm(string $type, $data = null, array $options = []): FormInterface
    {
        $options = array_merge($options, [
            'csrf_protection' => false,
        ]);

        $this->container->get('form.factory')->createNamed('', $type, $data, $options);
    }
}
