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
    private $pdo = null;
    private $mdl = null;

    protected function setUp(): void
    {
        $this->pdo = new \PDO('mysql:host=' . $_ENV['DB_HOST'] . ';port=' . $_ENV['DB_PORT'] . ';dbname=' . $_ENV['DB_DATABASE'], $_ENV['DB_USER'], $_ENV['DB_PASSWORD']);

        $this->mdl = TestModel::getInstance();
        $this->mdl->__setHandle($this->pdo);
    }

    public function testMigration()
    {
        $mig = new Asatru\Database\Migration('example_migration', $this->pdo);
        $this->addToAssertionCount(1);

        $mig->drop();
        $this->addToAssertionCount(1);

        $mig->add('id INT NOT NULL AUTO_INCREMENT PRIMARY KEY');
        $mig->add('text VARCHAR(260) NULL DEFAULT \'Test\'');
        $mig->add('created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP');
        $mig->create();
        $this->addToAssertionCount(4);

        $mig->append('test VARCHAR(255) NULL');
        $result = $this->mdl->raw('INSERT INTO ' . TestModel::tableName() . ' (text, test) VALUES(\'foo\', \'bar\')');
        $this->assertTrue($result !== false);

        $result = TestModel::where('text', '=', 'foo')->where('test', '=', 'bar')->first();
        $this->assertEquals(1, $result->get(0)->get('id'));
    }

    /**
     * @depends testMigration
     */
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

        $result = TestModel::insert('text', 'text same')->go();
        $this->assertTrue($result !== false);

        $result = TestModel::count()->get();
        $this->assertTrue($result === 4);

        $result = TestModel::insert('text', 'text same')->go();
        $this->assertTrue($result !== false);

        $result = TestModel::count()->get();
        $this->assertTrue($result === 5);
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
        $this->assertTrue($result->count() === 5);

        $result = TestModel::find(1);
        $this->assertEquals(1, $result->get(0)->get('id'));

        $result = TestModel::aggregate('max', 'id')->get();
        $this->assertEquals(5, $result->get(0)->get('id'));

        $result = TestModel::whereBetween('id', 1, 3)->orderBy('id', 'desc')->get();
        $this->assertEquals(3, $result->get(2)->get('id'));
        $this->assertEquals(2, $result->get(1)->get('id'));
        $this->assertEquals(1, $result->get(0)->get('id'));

        $result = TestModel::where('id', '<>', 4)->whereOr('id', '<>', 5)->limit(2)->get();
        $this->assertEquals(2, $result->count());
        $result->each(function($ident, $item) {
            $this->assertNotEquals(4, $item->get('id'));
            $this->assertNotEquals(5, $item->get('id'));
        });

        $result = TestModel::whereBetween('id', 1, 2)->whereBetweenOr('id', 4, 5)->get();
        $this->assertEquals(4, $result->count());
        $result->each(function($ident, $item) {
            $this->assertNotEquals(3, $item->get('id'));
        });
    }

    /**
     * @depends testQueryEntries
     */
    public function testDeleteEntry()
    {
        $result = TestModel::where('id', '=', 1)->delete();
        $this->assertTrue($result);

        $result = TestModel::whereBetween('id', 2, 3)->whereBetweenOr('id', 3, 4)->delete();
        $this->assertTrue($result);
    }
}