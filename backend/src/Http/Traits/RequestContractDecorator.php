<?php

namespace TestingTimes\Http\Traits;

use TestingTimes\Http\Request\Request;

/**
 * Trait RequestContractDecorator
 * @package TestingTimes\Http\Traits
 */
trait RequestContractDecorator
{
    /**
     * @var Request
     */
    private Request $request;

    /**
     * @param  string  $key
     * @param  mixed|null  $fallback
     * @return mixed
     */
    public function get(string $key, mixed $fallback = null): mixed
    {
        return $this->request->get($key, $fallback);
    }

    /**
     * @param  string  $key
     * @param  mixed|null  $fallback
     * @return mixed
     */
    public function post(string $key, mixed $fallback = null): mixed
    {
        return $this->request->post($key, $fallback);
    }

    /**
     * @param  string  $key
     * @param  mixed|null  $fallback
     * @return mixed
     */
    public function query(string $key, mixed $fallback = null): mixed
    {
        return $this->request->query($key, $fallback);
    }

    /**
     * @param  string  $key
     * @param  mixed|null  $fallback
     * @return mixed
     */
    public function header(string $key, mixed $fallback = null): mixed
    {
        return $this->request->header($key, $fallback);
    }
}