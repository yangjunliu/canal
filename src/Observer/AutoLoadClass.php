<?php


namespace YangjunLiu\Canal\Observer;


class AutoLoadClass
{
    /**
     * @var string
     */
    public $classPath;

    public function __construct(string $classPath)
    {
        $this->classPath = $classPath;
    }


}