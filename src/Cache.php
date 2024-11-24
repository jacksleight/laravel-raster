<?php

namespace JackSleight\LaravelRaster;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Arr;

class Cache
{
    protected Filesystem $files;

    protected ?int $filePermission;

    public function __construct(Filesystem $files, ?int $filePermission = null)
    {
        $this->files = $files;
        $this->filePermission = $filePermission;
    }

    public function get(string $name, string $id, array $params)
    {
        $path = $this->getPath($name, $id, $params);

        if ($this->files->exists($path)) {
            return $this->files->get($path);
        }
    }

    public function put(string $name, string $id, array $params, string $data)
    {
        $path = $this->getPath($name, $id, $params);

        $directory = dirname($path);
        if (! $this->files->exists($directory)) {
            $this->files->makeDirectory($directory, 0777, true, true);
        }

        return $this->files->put($path, $data);
    }

    public function forget(array|string $names, array|string $ids = '_')
    {
        foreach (Arr::wrap($names) as $name) {
            foreach (Arr::wrap($ids) as $id) {
                $directory = $name.'/'.$id;

                $this->files->deleteDirectory($directory);
            }
        }
    }

    public function flush()
    {
        foreach ($this->files->directories() as $directory) {
            $this->files->deleteDirectory($directory);
        }
    }

    protected function getPath(string $name, string $id, array $params)
    {
        $extension = match ($params['type']) {
            'jpeg' => 'jpg',
            'png' => 'png',
            'pdf' => 'pdf',
        };

        return $name.'/'.$id.'/'.md5(serialize($params)).'.'.$extension;
    }
}
