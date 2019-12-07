<?php declare(strict_types=1);

namespace App\Tests\Support\Behaviours;

/**
 * Trait BootTestClient
 *
 * @package App\Tests\Support\Behaviours
 * @subpackage App\Tests\Support\Behaviours\BootTestClient
 *
 * @method void setKernelClass()
 * @method void setUpTests()
 */
trait BootTestClient
{

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        if (method_exists($this, 'setKernelClass')) {
            self::setKernelClass();
        }

        self::createClient();

        if (method_exists($this, 'setUpTests')) {
            $this->setUpTests();
        }
    }
}
