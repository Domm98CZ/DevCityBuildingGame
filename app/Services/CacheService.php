<?php declare(strict_types=1);
namespace App\Services;

use Nette\Caching\Cache;
use Nette\Caching\Storages\DevNullStorage;
use Nette\Caching\Storages\FileStorage;
use Nette\Utils\FileSystem;

final readonly class CacheService
{
    /**
     * @param string $tmpDir
     * @param bool $cacheEnabled
     */
    public function __construct(
        private string $tmpDir
        , private bool $cacheEnabled
    ) {
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->cacheEnabled;
    }

    /**
     * @param string $path
     * @return string
     */
    private function getCacheDir(string $path): string
    {
        return $this->tmpDir . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . $path;
    }

    /**
     * @param string $path
     * @return Cache
     */
    private function getCacheStorage(string $path): Cache
    {
        $cacheDir = $this->getCacheDir($path);
        FileSystem::createDir($cacheDir);
        return new Cache($this->isEnabled() ? new FileStorage($cacheDir) : new DevNullStorage());
    }

    /**
     * @param string $path
     * @param string $key
     * @return mixed
     */
    public function load(string $path, string $key = 'main'): mixed
    {
        return $this->getCacheStorage($path)->load($key);
    }

    /**
     * @param string $path
     * @param string $key
     * @param mixed $data
     * @param array $options
     * @return mixed
     */
    public function save(string $path, string $key = 'main', mixed $data = null, array $options = [])
    {
        return $this->getCacheStorage($path)->save($key, $data, $options);
    }

    /**
     * @param string $path
     * @param string $key
     * @return void
     */
    public function delete(string $path, string $key = 'main'): void
    {
        $this->getCacheStorage($path)->remove($key);
    }

    /**
     * @param string $path
     * @return void
     */
    public function clean(string $path): void
    {
        $this->getCacheStorage($path)->clean([Cache::All => true]);
    }
}
