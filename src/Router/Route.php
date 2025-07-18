<?php

namespace HGeS\Router;

class Route
{
    private string $httpMethod;
    private string $actionMethod;
    private string $class;
    private bool $isAdmin;

    public function __construct(string $httpMethod, string $class, string $actionMethod, bool $isAdmin = false)
    {
        $this->httpMethod = $httpMethod;
        $this->class = $class;
        $this->actionMethod = $actionMethod;
        $this->isAdmin = $isAdmin;
    }

    public function getHttpMethod(): string
    {
        return $this->httpMethod;
    }

    public function getActionMethod(): string
    {
        return $this->actionMethod;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function isAdmin(): bool
    {
        return $this->isAdmin;
    }
}