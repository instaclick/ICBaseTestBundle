<?php
/**
 * @copyright 2012 Instaclick Inc.
 */

namespace IC\Bundle\Base\TestBundle\Test\Helper;

use Symfony\Component\BrowserKit\Cookie;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

use IC\Bundle\Base\TestBundle\Test\WebTestCase;

/**
 * Session helper class.
 * Implementation inspired by LiipFunctionalTestBundle.
 *
 * @author Guilherme Blanco <gblanco@nationalfibre.net>
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 */
class SessionHelper extends AbstractHelper
{
    /**
     * @var \Symfony\Component\HttpFoundation\Session\Session
     */
    private $session;

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(WebTestCase $testCase)
    {
        parent::__construct($testCase);

        $client    = $this->testCase->getClient();
        $cookieJar = $client->getCookieJar();
        $container = $client->getContainer();

        // Required parameter to be defined, preventing "hasPreviousSession" in Request to return false.
        $options = $container->getParameter('session.storage.options');

        if ( ! $options || ! isset($options['name'])) {
            throw new \InvalidArgumentException('Missing session.storage.options#name');
        }

        // Retrieve session
        $this->session = $container->get('session');

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

        $this->session->set('_security_' . $firewall, serialize($token));
        $this->session->save();
    }
}
