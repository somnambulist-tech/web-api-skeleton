<?php declare(strict_types=1);

namespace App;

use IlluminateAgnostic\Str\Support\Str;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

use function get_class;
use function get_parent_class;
use function php_sapi_name;
use function preg_match;
use function sprintf;
use function str_replace;
use function ucfirst;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function getLogDir(): string
    {
        return $this->getProjectDir().'/var/logs';
    }

    protected function getContainerClass(): string
    {
        $class = get_class($this);
        $class = 'c' === $class[0] && str_starts_with($class, "class@anonymous\0") ? get_parent_class($class) . str_replace('.', '_', ContainerBuilder::hash($class)) : $class;
        $class = str_replace('\\', '_', $class).Str::studly(php_sapi_name()).ucfirst($this->environment).($this->debug ? 'Debug' : '').'Container';

        if (!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $class)) {
            throw new InvalidArgumentException(sprintf('The environment "%s" contains invalid characters, it can only contain characters allowed in PHP class names.', $this->environment));
        }

        return $class;
    }
}
