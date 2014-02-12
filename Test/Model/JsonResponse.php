<?php
/**
 * @copyright 2013 Instaclick Inc.
 */

namespace IC\Bundle\Base\TestBundle\Test\Model;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Provides accessor to properties of a JSON encoded response.
 *
 * @author Danilo Cabello <danilo.cabello@gmail.com>
 */
class JsonResponse
{
    /**
     * Original response.
     *
     * @var \Symfony\Component\HttpFoundation\Response
     */
    private $response;

    /**
     * Decoded JSON response into StdClass object.
     *
     * @var StdClass|null
     */
    private $responseObject = null;

    /**
     * Constructor needs the original response and creates a property accessor.
     *
     * @param \Symfony\Component\HttpFoundation\Response $response
     */
    public function __construct(Response $response)
    {
        $this->response       = $response;
        $this->responseObject = json_decode($this->response->getContent());
        $this->accessor       = PropertyAccess::getPropertyAccessor();
    }

    /**
     * If method is not implemented here, try calling method from original response.
     *
     * @param string $methodName
     * @param array  $argumentList
     *
     * @return mixed Return type will be the same from original method.
     */
    public function __call($methodName, $argumentList)
    {
        return call_user_func_array(array($this->response, $methodName), $argumentList);
    }

    /**
     * Retrieve decoded JSON response into StdClass object.
     *
     * @return StdClass
     */
    public function getResponseObject()
    {
        return $this->responseObject;
    }

    /**
     * Retrieve property from decoded object.
     *
     * @param string $propertyPath
     *
     * @return mixed
     */
    public function getProperty($propertyPath)
    {
        return $this->accessor->getValue($this->getResponseObject(), $propertyPath);
    }
}
