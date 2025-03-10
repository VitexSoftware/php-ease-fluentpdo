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

namespace Test\Ease\Logger;

use Ease\Logger\LogToSQL;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2021-10-03 at 12:26:22.
 */
class LogToSQLTest extends \PHPUnit\Framework\TestCase
{
    protected LogToSQL $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->object = new LogToSQL();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown(): void
    {
    }

    /**
     * @covers \Ease\Logger\LogToSQL::singleton
     */
    public function testsingleton(): void
    {
        $this->assertEquals('', $this->object->singleton());
    }

    /**
     * @covers \Ease\Logger\LogToSQL::setUser
     */
    public function testsetUser(): void
    {
        $this->object->setUser(100);
        $this->assertEquals(100, $this->object->userId);
    }

    /**
     * @covers \Ease\Logger\LogToSQL::addToLog
     */
    public function testaddToLog(): void
    {
        $this->assertEquals(1, $this->object->addToLog($this->object, 'phpunit'));
    }
}
