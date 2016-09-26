<?php

declare(strict_types = 1);

namespace PSR7SessionTest\Storage;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit_Framework_TestCase;
use PSR7Session\Session\DefaultSessionData;
use PSR7Session\Session\StorableSession;
use PSR7Session\Session\StorableSessionInterface;
use PSR7Session\Storage\FileStorage;

class FileStorageTest extends PHPUnit_Framework_TestCase
{
    /** @var vfsStreamDirectory */
    private $fileSystem;
    /** @var FileStorage */
    private $storage;

    public function setUp()
    {
        $this->fileSystem = vfsStream::setup();
        $this->storage = new FileStorage($this->fileSystem->url());
    }

    public function testSaveNewSession()
    {
        $session = $this->createSession();
        $session->set('test', 'foo');

        $this->storage->save($session);

        $loadedSession = $this->storage->load($session->getId());
        $this->assertEquals($session->get('test'), $loadedSession->get('test'));
    }

    public function testDestroy()
    {
        $session = $this->createSession();
        $session->set('foo', 'bar');
        $this->storage->save($session);

        $this->storage->destroy($session->getId());

        $loaded = $this->storage->load($session->getId());
        $this->assertFalse($loaded->has('foo'));
    }

    private function createSession():StorableSessionInterface
    {
        $innerSession = DefaultSessionData::newEmptySession();
        return new StorableSession($innerSession, $this->storage);
    }
}
