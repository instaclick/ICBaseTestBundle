<?php
/**
 * @copyright 2012 Instaclick Inc.
 */

namespace IC\Bundle\Base\TestBundle\Test\Helper;

use Symfony\Component\HttpFoundation\Request;
use IC\Bundle\Base\TestBundle\Test\Functional\WebTestCase;

/**
 * Controller helper class.
 *
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 */
class ControllerHelper extends AbstractHelper
{
    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    private $request;

    /**
     * {@inheritdoc}
     */
    public function __construct(WebTestCase $testCase)
    {
        parent::__construct($testCase);

        $container = $testCase->getClient()->getContainer();

        $this->request = Request::create('/_internal');
        $this->request->setSession($container->get('session'));
    }

    /**
     * Retrieve the associated request
     *
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Execute an internal request.
     *
     * @param string $controller The controller to execute the request
     * @param array  $options    Request Options
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function render($controller, array $options = array())
    {
        $client     = $this->testCase->getClient();
        $container  = $client->getContainer();
        $httpKernel = $container->get('http_kernel');
        $attributes = isset($options['attributes']) ? $options['attributes'] : array();

        $container->set('request', $this->request);

        return $httpKernel->forward($controller, $attributes);
    }
}
