<?php declare(strict_types=1);

namespace App\Delivery\Api;

use Somnambulist\ApiBundle\Controllers\ApiController;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class IndexController
 *
 * @package App\Delivery\Api
 * @subpackage App\Delivery\Api\IndexController
 */
class IndexController extends ApiController
{

    public function __invoke()
    {
        return new JsonResponse('Service is running; customise me to add service definitions');
    }
}

