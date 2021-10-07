<?php

namespace App\Utilities;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class GpxStorage
{
    /**
     * @var \Illuminate\Contracts\Filesystem\Filesystem
     */
    private $storage;

    public function __construct()
    {
        $this->storage = Storage::disk('local');
    }

    /**
     * @param UploadedFile $file
     *
     * @return string|null
     */
    public function store(UploadedFile $file): ?string
    {
        $fileName = $this->generateGpxFileName();

        if ($this->storage->put($this->generateGpxFilePath($fileName), $file->getContent())) {
            return $fileName;
        }

        return null;
    }

    /**
     * @param string $fileName
     * @return string|null
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function get(string $fileName): ?string
    {
        $path = $this->generateGpxFilePath($fileName);

        if ($this->storage->exists($path)) {
            return $this->storage->get($path);
        }

        return null;
    }

    /**
     * @param string $fileName
     * @return string
     */
    public function getFullPath(string $fileName): string
    {
        return storage_path('app') . DIRECTORY_SEPARATOR . $this->generateGpxFilePath($fileName);
    }

    /**
     * @param string|null $fileName
     *
     * @return string
     */
    private function generateGpxFilePath(string $fileName = null): string
    {
        return implode(DIRECTORY_SEPARATOR, [
            'activities',
            'user_' . auth()->user()->id,
            date('Y'),
            'gpx',
            $fileName ?? $this->generateGpxFileName()
        ]);
    }

    /**
     * @return string
     */
    private function generateGpxFileName(): string
    {
        return auth()->user()->id . '_' . date('Y_m_d_H_m_i') . '.gpx';
    }
}
