<?php

namespace JackSleight\LaravelRaster;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Arr;

class Cache
{
    protected Filesystem $files;

    protected ?string $directory;

    public function __construct(Filesystem $files, ?string $directory = null)
    {
        $this->files = $files;
        $this->directory = $directory;
    }

    public function get(string $name, string $id, array $params)
    {
        $path = $this->getFilePath($name, $id, $params);

        if ($this->files->exists($path)) {
            return $this->files->get($path);
        }
    }

    public function put(string $name, string $id, array $params, string $data)
    {
        $path = $this->getFilePath($name, $id, $params);

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
                $this->files->deleteDirectory($this->getDirectoryPath($name, $id));
            }
        }
    }

    public function flush()
    {
        if (! $this->files->exists($this->directory)) {
            return;
        }

        foreach ($this->files->directories($this->directory) as $directory) {
            $this->files->deleteDirectory($directory);
        }
    }

    protected function getDirectoryPath(string $name, string $id)
    {
        return $this->directory.'/'.$name.'/'.$id;
    }

    protected function getFilePath(string $name, string $id, array $params)
    {
        $extension = match ($params['type']) {
            'jpeg' => 'jpg',
            'png' => 'png',
            'pdf' => 'pdf',
        };

        return $this->directory.'/'.$name.'/'.$id.'/'.md5(serialize($params)).'.'.$extension;
    }
}
