<?php declare(strict_types=1);

namespace App\Delivery\Api;

use Somnambulist\Bundles\ApiBundle\Controllers\ApiController;
use Symfony\Component\HttpFoundation\JsonResponse;

class IndexController extends ApiController
{
    public function __invoke()
    {
        return new JsonResponse('Service is running; customise me to add service definitions');
    }
}
