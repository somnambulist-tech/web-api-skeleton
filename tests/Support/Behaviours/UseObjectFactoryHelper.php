<?php declare(strict_types=1);

namespace App\Tests\Support\Behaviours;

use App\Tests\Support\ObjectFactoryHelper;
use Faker\Factory;

trait UseObjectFactoryHelper
{
    private ?ObjectFactoryHelper $factory = null;

    /**
     * @param string $locale Locale to pass to Faker when initialising, default en_US
     *
     * @return ObjectFactoryHelper
     */
    protected function factory(string $locale = Factory::DEFAULT_LOCALE): ObjectFactoryHelper
    {
        if (!$this->factory instanceof ObjectFactoryHelper) {
            $this->factory = new ObjectFactoryHelper($locale);
        }

        return $this->factory;
    }
}
