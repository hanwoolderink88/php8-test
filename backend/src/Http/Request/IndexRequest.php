<?php

namespace TestingTimes\Http\Request;

use Exception;
use Psr\Http\Message\RequestInterface;
use TestingTimes\Http\Contracts\RequestContract;
use TestingTimes\Http\Traits\RequestContractDecorator;
use TestingTimes\Http\Traits\ServerRequestDecoratorTrait;

/**
 * Class IndexRequest
 * @package TestingTimes\Http\Request
 */
class IndexRequest implements RequestInterface, RequestContract
{
    use ServerRequestDecoratorTrait, RequestContractDecorator;

    /**
     * @var Request
     */
    private Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->validate();
    }

    private function validate()
    {
        $criteria = $this->request->query('criteria', []);
        if (!is_array($criteria)) {
            throw new Exception('Criteria in querystring should be an array like ?criteria[name]=value');
        }
    }
}
