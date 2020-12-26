<?php

namespace Anteris\ReflectionFinder\Reflection;

use Symfony\Component\Filesystem\Filesystem;

class ReflectionClass extends \ReflectionClass
{
    protected Filesystem $filesystem;

    public function __construct($class)
    {
        parent::__construct($class);
        $this->filesystem = new Filesystem;
    }

    public function copy(string $targetFile, bool $overwriteNewerFiles = false)
    {
        return $this->filesystem->copy(
            $this->getFileName(),
            $targetFile,
            $overwriteNewerFiles
        );
    }

    public function rename(string $target, bool $overwrite = false)
    {
        return $this->filesystem->rename($this->getFileName(), $target, $overwrite);
    }

    public function remove()
    {
        return $this->filesystem->remove($this->getFileName());
    }
}
