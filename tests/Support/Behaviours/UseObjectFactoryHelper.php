<?php declare(strict_types=1);

namespace App\Tests\Support\Behaviours;

use Faker\Factory;
use Faker\Generator;
use InvalidArgumentException;
use App\Tests\Support\ObjectFactoryHelper;
use function sprintf;

/**
 * Trait UseObjectFactoryHelper
 *
 * @package App\Tests\Support\Behaviours
 * @subpackage App\Tests\Support\Behaviours\UseObjectFactoryHelper
 *
 * @property-read Generator           $faker
 * @property-read ObjectFactoryHelper $factory
 */
trait UseObjectFactoryHelper
{

    /**
     * @var ObjectFactoryHelper
     */
    private $unitTester;

    public function __get($name)
    {
        switch ($name) {
            case 'faker': return $this->factory()->faker(); break;
            case 'factory': return $this->factory(); break;
        }

        throw new InvalidArgumentException(sprintf('No property found for "%s"', $name));
    }

    /**
     * @param string $locale Locale to pass to Faker when initialising, default en_US
     *
     * @return ObjectFactoryHelper
     */
    protected function factory(string $locale = Factory::DEFAULT_LOCALE): ObjectFactoryHelper
    {
        if (!$this->unitTester instanceof ObjectFactoryHelper) {
            $this->unitTester = new ObjectFactoryHelper($locale);
        }

        return $this->unitTester;
    }
}
