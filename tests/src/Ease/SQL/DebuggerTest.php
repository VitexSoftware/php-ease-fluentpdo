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

use Ease\SQL\Debugger;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2022-10-03 at 23:41:45.
 */
class DebuggerTest extends \PHPUnit\Framework\TestCase
{
    public \Envms\FluentPDO\Queries\Select $query;
    protected Debugger $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $sqlHelper = new \Ease\SQL\Engine();
        $this->query = $sqlHelper->getFluentPDO()->from('test');
        $this->object = new Debugger($this->query, 'test pdo');
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown(): void
    {
    }

    /**
     * Test Constructor.
     *
     * @covers \Ease\SQL\Debugger::__construct
     */
    public function testConstructor(): void
    {
        $classname = \get_class($this->object);
        // Get mock, without the constructor being called
        $mock = $this->getMockBuilder($classname)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $pdo = new \Ease\SQL\PDO();
        $mock->__construct($this->query, 'pdo test');
    }
}
