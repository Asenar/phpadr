<?php declare(strict_types=1);

namespace ADR\Filesystem;

use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Filesystem;

class AutoDiscoverConfig
{
    private Filesystem $filesystem;
    private string $defaultConfigFile;

    private const DEFAULT_CONFIG = 'adr.yml.dist';

    public function __construct(
        string $root,
        ?string $configFile = null
    ) {

        $this->rootPath = isset($GLOBALS['_composer_autoload_path'])
            ? dirname($GLOBALS['_composer_autoload_path'], 2) . '/'
            : rtrim($root, '/') . '/';

        $this->filesystem = new Filesystem();
        $this->defaultConfigFile = $configFile ?? $this->rootPath . self::DEFAULT_CONFIG;
    }

    private function getRootConfig(): ?string
    {
        if ($this->filesystem->exists($this->rootPath . 'adr.yml')) {
            return $this->rootPath . 'adr.yml';
        }

        return null;
    }

    public function getConfig(?string $configLocation = null): string
    {
        if (null === $configLocation) {
            $configLocation = $this->getRootConfig() ?? $this->defaultConfigFile;
        }

        if (str_ends_with($configLocation, '.dist')) {
            $customConfig = substr($configLocation, 0, -5);
            if ($this->filesystem->exists($customConfig)) {
                return $this->getConfigPath($customConfig);
            }
        }
        return $this->getConfigPath($configLocation);
    }

    private function getConfigPath(string $configLocation): string
    {
        if (!$this->filesystem->exists($configLocation)) {
            throw new FileNotFoundException(sprintf(
                'Config file "%s" does not exists',
                $configLocation
            ));
        };
        return  $configLocation;
    }
}
