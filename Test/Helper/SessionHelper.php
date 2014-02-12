<?php
/**
 * @copyright 2012 Instaclick Inc.
 */

namespace IC\Bundle\Base\TestBundle\Test\Helper;

use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\SecurityEvents;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use IC\Bundle\Base\TestBundle\Test\Functional\WebTestCase;

/**
 * Session helper class.
 * Implementation inspired by LiipFunctionalTestBundle.
 *
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 * @author John Cartwright <jcartdev@gmail.com>
 */
class SessionHelper extends AbstractHelper
{
    /**
     * @var \Symfony\Component\HttpFoundation\Session\Session
     */
    private $session;

    /**
     * \Symfony\Component\DependencyInjection\Container
     */
    private $container;

    /**
     * \Symfony\Bundle\FrameworkBundle\Client
     */
    private $client;

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(WebTestCase $testCase)
    {
        parent::__construct($testCase);

        $this->client    = $this->testCase->getClient();
        $this->container = $this->client->getContainer();
        $this->session   = $this->container->get('session');
        $cookieJar       = $this->client->getCookieJar();

        // Required parameter to be defined, preventing "hasPreviousSession" in Request to return false.
        $options = $this->container->getParameter('session.storage.options');

        if ( ! $options || ! isset($options['name'])) {
            throw new \InvalidArgumentException('Missing session.storage.options#name');
        }

        $this->session->setId(uniqid());

        // Assign session cookie information referring to session id, allowing consecutive requests session recovering
        $cookieJar->set(new Cookie($options['name'], $this->session->getId()));
    }

    /**
     * Retrieve the associated client session.
     *
     * @return \Symfony\Component\HttpFoundation\Session\Session
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * Authenticate a given User on a security firewall.
     *
     * @param \Symfony\Component\Security\Core\User\UserInterface $user     User to be authenticated
     * @param string                                              $firewall Security firewall name
     */
    public function authenticate(UserInterface $user, $firewall)
    {
        $token = new UsernamePasswordToken($user, null, $firewall, $user->getRoles());

        $this->dispatchInteractiveLoginEvent($token);

        $this->session->set('_security_' . $firewall, serialize($token));
        $this->session->save();

        $this->client->getCookieJar()->set(new Cookie($this->session->getName(), $this->session->getId()));
    }

    /**
     * Dispatch an interactive login event
     *
     * @param \Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken $token
     */
    private function dispatchInteractiveLoginEvent(UsernamePasswordToken $token)
    {
        $request    = $this->client->getRequest() ?: new Request();
        $loginEvent = new InteractiveLoginEvent($request, $token);

        $this->container->get('security.context')->setToken($token);
        $this->container->get('event_dispatcher')->dispatch(SecurityEvents::INTERACTIVE_LOGIN, $loginEvent);
    }
}
