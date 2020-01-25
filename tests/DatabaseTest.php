<?php

/*
    Asatru PHP (dnyAsatruPHP) developed by Daniel Brendel
    
    (C) 2019 - 2020 by Daniel Brendel
    
    Version: 0.1
    Contact: dbrendel1988<at>yahoo<dot>com
    GitHub: https://github.com/danielbrendel
    
    License: see LICENSE.txt
*/

use PHPUnit\Framework\TestCase;

class TestModel extends Asatru\Database\Model {
    public static function tableName() { return 'example_migration'; }
}

/**
 * TestCase for Asatru\Database
 */
final class DatabaseTest extends TestCase
{
    private $mdl = null;

    protected function setUp(): void
    {
        $objPdo = new \PDO('mysql:host=localhost;port=3306;dbname=asatru', 'root', '');

        $this->mdl = TestModel::getInstance();
        $this->mdl->__setHandle($objPdo);
    }

    public function testMigrateFresh()
    {
        migrate_fresh();
        $this->addToAssertionCount(1);
    }

    /**
     * @depends testMigrateFresh
     */
    public function testInsertEntries()
    {
        $result = TestModel::insert('text', 'text #1')->go();
        $this->assertTrue($result !== false);

        $result = TestModel::count()->get();
        $this->assertTrue($result === 1);

        $result = TestModel::insert('text', 'text #2')->go();
        $this->assertTrue($result !== false);

        $result = TestModel::count()->get();
        $this->assertTrue($result === 2);

        $result = TestModel::insert('text', 'text #3')->go();
        $this->assertTrue($result !== false);

        $result = TestModel::count()->get();
        $this->assertTrue($result === 3);
    }

    /**
     * @depends testInsertEntries
     */
    public function testUpdateEntries()
    {
        $result = TestModel::update('text', 'New text')->where('id', '=', 1)->go();
        $this->assertTrue($result !== false);

        $result = TestModel::where('id', '=', 1)->first();
        $this->assertTrue($result->get(0)->get('text') === 'New text');
    }

    /**
     * @depends testUpdateEntries
     */
    public function testQueryEntries()
    {
        $result = TestModel::where('id', '<>', '3')->orderBy('id', 'desc')->get();
        $result->each(function($ident, $item) {
            $this->assertTrue($item->get('id') !== '3');
        });

        $result = TestModel::where('text', '=', 'text #1')->whereOr('text', '=', 'text #3')->get();
        $result->each(function($ident, $item) {
            $this->assertTrue($item->get('text') !== 'New text');
        });

        $result = TestModel::all();
        $this->assertTrue($result->count() === 3);

        $result = TestModel::find(1);
        $this->assertEquals(1, $result->get(0)->get('id'));
    }

    /**
     * @depends testQueryEntries
     */
    public function testDeleteEntry()
    {
        $result = TestModel::where('id', '=', 1)->delete();
        $this->assertTrue($result);
    }
}