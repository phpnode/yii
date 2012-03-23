<?php
Yii::import("system.web.*");
require_once __DIR__."/db/data/models.php";
class CDataProviderIteratorPerformanceTest extends CTestCase {
	/**
	 * @var CDbConnection
	 */
	private $_connection;

	protected function setUp()
	{
		if(!extension_loaded('pdo') || !extension_loaded('pdo_sqlite'))
			$this->markTestSkipped('PDO and SQLite extensions are required.');

		$this->_connection=new CDbConnection('sqlite::memory:');
		$this->_connection->active=true;
		$this->_connection->pdoInstance->exec(file_get_contents(dirname(__FILE__).'/db/data/sqlite.sql'));
		CActiveRecord::$db=$this->_connection;
		$transaction = $this->_connection->beginTransaction();
		for($i = 6; $i <= 10000; $i++) {
			$this->_connection->getCommandBuilder()->createInsertCommand(
				"posts",
				array(
					"title" => "post ".$i,
					"content" => "content ".$i,
					"create_time" => 100000 + $i,
					"author_id" => 1
				)
			)->execute();
		}
		$transaction->commit();
	}

	protected function tearDown()
	{
		$this->_connection->active=false;
	}

	public function testSpeed() {
		$startTime = microtime(true);
		$dataProvider = new CActiveDataProvider("Post");
		$iterator = new CDataProviderIterator($dataProvider);
		foreach($iterator as $i => $post) {
			$this->assertEquals("post ".($i + 1),"$post->title");
		}
		$endTime = microtime(true);
		echo __CLASS__." in ".($endTime - $startTime)." seconds (".memory_get_peak_usage()." bytes)\n";
	}
}