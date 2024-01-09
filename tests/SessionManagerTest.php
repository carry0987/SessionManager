<?php
use carry0987\SessionManager\SessionManager;
use PHPUnit\Framework\TestCase;

class SessionManagerTest extends TestCase
{
    public function testSetAndGetSession()
    {
        $sessionManager = new SessionManager();
        $sessionManager->set('foo', 'bar');

        $this->assertEquals('bar', $sessionManager->get('foo'));
    }
}
