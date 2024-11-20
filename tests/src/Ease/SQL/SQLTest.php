<?php

declare(strict_types=1);

/**
 * This file is part of the EaseFluentPDO package
 *
 * https://github.com/VitexSoftware/php-ease-fluentpdo
 *
 * (c) Vítězslav Dvořák <http://vitexsoftware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Test\Ease\SQL;

use Ease\SQL\SQL;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2020-05-10 at 14:53:22.
 */
class SQLTest extends \PHPUnit\Framework\TestCase
{
    protected SQL $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->object = new \SQLTester(['myTable' => 'test']);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown(): void
    {
    }

    /**
     * @covers \Ease\SQL\SQL::setUp
     */
    public function testSetUp(): void
    {
        $this->assertEquals('', $this->object->SetUp());
    }

    /**
     * @covers \Ease\SQL\SQL::connect
     */
    public function testConnect(): void
    {
        $this->assertEquals('', $this->object->Connect());
    }

    /**
     * @covers \Ease\SQL\SQL::selectDB
     */
    public function testSelectDB(): void
    {
        $this->assertEquals('', $this->object->SelectDB());
    }

    /**
     * @covers \Ease\SQL\SQL::getInsertID
     */
    public function testGetInsertID(): void
    {
        $this->assertEquals('', $this->object->GetInsertID());
    }

    /**
     * @covers \Ease\SQL\SQL::ping
     */
    public function testPing(): void
    {
        $this->assertEquals('', $this->object->Ping());
    }

    /**
     * @covers \Ease\SQL\SQL::sanitizeQuery
     */
    public function testSanitizeQuery(): void
    {
        $this->assertEquals('', $this->object->SanitizeQuery(''));
    }

    /**
     * @covers \Ease\SQL\SQL::setTable
     */
    public function testSetTable(): void
    {
        $this->assertEquals($this->object->tableName, $this->object->setTable('test'));
    }

    /**
     * @covers \Ease\SQL\SQL::getNumRows
     */
    public function testGetNumRows(): void
    {
        $this->assertEquals(0, $this->object->GetNumRows());
    }

    /**
     * @covers \Ease\SQL\SQL::getLastQuery
     */
    public function testGetLastQuery(): void
    {
        $this->assertEquals('', $this->object->GetLastQuery());
    }

    /**
     * @covers \Ease\SQL\SQL::getlastInsertID
     */
    public function testGetlastInsertID(): void
    {
        $this->assertEquals('', $this->object->GetlastInsertID());
    }

    /**
     * @covers \Ease\SQL\SQL::getLastError
     */
    public function testGetLastError(): void
    {
        $this->assertNull($this->object->getLastError());
    }

    /**
     * @covers \Ease\SQL\SQL::__sleep
     */
    public function testSleep(): void
    {
        $this->assertEquals([], $this->object->__sleep());
    }

    /**
     * @covers \Ease\SQL\SQL::__destruct
     */
    public function testDestruct(): void
    {
        $this->assertEquals('', $this->object->__destruct());
    }

    /**
     * @covers \Ease\SQL\SQL::isConnected
     */
    public function testIsConnected(): void
    {
        $this->assertTrue($this->object->IsConnected());
    }
}
