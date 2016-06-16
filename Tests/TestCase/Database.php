<?php
declare(strict_types = 1);
namespace Remembrall\TestCase;

use Dibi;
use Tester;

abstract class Database extends Mockery {
    /** @var Dibi\Connection */
    protected $database;

    protected function setUp() {
        parent::setUp();
        Tester\Environment::lock('database', __DIR__ . '/../Temporary');
        $credentials = parse_ini_file(__DIR__ . '/.database.ini');
        $this->database = new Dibi\Connection($credentials);
        $this->prepareDatabase();
    }

    protected function prepareDatabase() {
        /** Template method, suitable for overriding */
    }
}