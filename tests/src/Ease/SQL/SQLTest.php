<?php

namespace Test\Ease\SQL;

use Ease\SQL\SQL;

class SQLTester extends SQL {

    public $myTable = 'test';

}

/**
 * Generated by PHPUnit_SkeletonGenerator on 2020-05-10 at 14:53:22.
 */
class SQLTest extends \PHPUnit\Framework\TestCase {

    /**
     * @var SQL
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void {

        $this->object = new SQLTester(['myTable' => 'test']);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown(): void {
        
    }

    /**
     * @covers Ease\SQL\SQL::setUp
     */
    public function testSetUp() {
        $this->assertEquals('', $this->object->SetUp());
    }

    /**
     * @covers Ease\SQL\SQL::connect
     */
    public function testConnect() {
        $this->assertEquals('', $this->object->Connect());
    }

    /**
     * @covers Ease\SQL\SQL::selectDB
     */
    public function testSelectDB() {
        $this->assertEquals('', $this->object->SelectDB());
    }

    /**
     * @covers Ease\SQL\SQL::getInsertID
     */
    public function testGetInsertID() {
        $this->assertEquals('', $this->object->GetInsertID());
    }

    /**
     * @covers Ease\SQL\SQL::ping
     */
    public function testPing() {
        $this->assertEquals('', $this->object->Ping());
    }

    /**
     * @covers Ease\SQL\SQL::sanitizeQuery
     */
    public function testSanitizeQuery() {
        $this->assertEquals('', $this->object->SanitizeQuery(''));
    }

    /**
     * @covers Ease\SQL\SQL::setTable
     */
    public function testSetTable() {
        $this->assertEquals($this->object->tableName, $this->object->setTable('test'));
    }

    /**
     * @covers Ease\SQL\SQL::getNumRows
     */
    public function testGetNumRows() {
        $this->assertEquals(0, $this->object->GetNumRows());
    }

    /**
     * @covers Ease\SQL\SQL::getLastQuery
     */
    public function testGetLastQuery() {
        $this->assertEquals('', $this->object->GetLastQuery());
    }

    /**
     * @covers Ease\SQL\SQL::getlastInsertID
     */
    public function testGetlastInsertID() {
        $this->assertEquals('', $this->object->GetlastInsertID());
    }

    /**
     * @covers Ease\SQL\SQL::getLastError
     */
    public function testGetLastError() {
        $this->assertEquals([], $this->object->GetLastError());
    }

    /**
     * @covers Ease\SQL\SQL::__sleep
     */
    public function test__sleep() {
        $this->assertEquals('', $this->object->__sleep());
    }

    /**
     * @covers Ease\SQL\SQL::__destruct
     */
    public function test__destruct() {
        $this->assertEquals('', $this->object->__destruct());
    }

    /**
     * @covers Ease\SQL\SQL::queryTo2DArray
     */
    public function testQueryTo2DArray() {
        $this->assertEquals('', $this->object->QueryTo2DArray('SELECT * from test where id=1'));
    }

    /**
     * @covers Ease\SQL\SQL::queryToValue
     */
    public function testQueryToValue() {
        $this->assertEquals('', $this->object->QueryToValue('SELECT id FROM test WHERE id=1'));
    }

    /**
     * @covers Ease\SQL\SQL::queryToCount
     */
    public function testQueryToCount() {
        $this->assertEquals('', $this->object->QueryToCount('SELECT * FROM test'));
    }

    /**
     * @covers Ease\SQL\SQL::getColumnComma
     */
    public function testGetColumnComma() {
        $this->assertEquals('', $this->object->GetColumnComma());
    }

    /**
     * @covers Ease\SQL\SQL::isConnected
     */
    public function testIsConnected() {
        $this->assertTrue($this->object->IsConnected());
    }

    /**
     * @covers Ease\SQL\SQL::arrayToSetQuery
     */
    public function testArrayToSetQuery() {
        $this->assertEquals('', $this->object->ArrayToSetQuery([]));
    }

    /**
     * @covers Ease\SQL\SQL::logSqlError
     */
    public function testLogSqlError() {
        $this->assertEquals('', $this->object->LogSqlError());
    }

    /**
     * @covers Ease\SQL\SQL::easeAddSlashes
     */
    public function testEaseAddSlashes() {
        $this->assertEquals('', $this->object->EaseAddSlashes(''));
    }

}
