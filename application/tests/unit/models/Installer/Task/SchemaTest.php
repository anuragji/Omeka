<?php
/**
 * @version $Id$
 * @copyright Center for History and New Media, 2007-2010
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @package Omeka
 */

/**
 * 
 *
 * @package Omeka
 * @copyright Center for History and New Media, 2007-2010
 */
class Installer_Task_SchemaTest extends PHPUnit_Framework_TestCase
{
    const DB_PREFIX = 'test_';
    
    public function setUp()
    {
        $this->dbAdapter = new Zend_Test_DbAdapter;
        $this->db = new Omeka_Db($this->dbAdapter, self::DB_PREFIX);
        $this->profilerHelper = new Omeka_Test_Helper_DbProfiler($this->dbAdapter->getProfiler(),
            $this);
        $this->schemaTask = new Installer_Task_Schema;
    }
        
    public function testAddTable()
    {
        $collectionSql = CORE_DIR . DIRECTORY_SEPARATOR . 'schema' . DIRECTORY_SEPARATOR . 'collections.sql';
        $this->schemaTask->addTable('collections', $collectionSql);
        $this->assertEquals(array(
            'collections' => $collectionSql
        ),$this->schemaTask->getTables());
    }
    
    public function testAddNonExistentTable()
    {
        try {
            $this->schemaTask->addTable('foobar', '/fake/path/to/no/file.sql');
            $this->fail("Should have thrown an exception when an invalid file was given.");
        } catch (Exception $e) {
            $this->assertThat($e, $this->isInstanceOf('Installer_Task_Exception'));
            $this->assertContains("Invalid SQL file", $e->getMessage());
        }
    }
            
    public function testUseDefaultTables()
    {
        $this->assertEquals(0, count($this->schemaTask->getTables()));
        $this->schemaTask->useDefaultTables();
        $this->assertEquals(array(
            'collections' => CORE_DIR . DIRECTORY_SEPARATOR . 'schema' . DIRECTORY_SEPARATOR . 'collections.sql',
            'element_texts' => CORE_DIR . DIRECTORY_SEPARATOR . 'schema' . DIRECTORY_SEPARATOR . 'element_texts.sql',
            'entities_relations' => CORE_DIR . DIRECTORY_SEPARATOR . 'schema' . DIRECTORY_SEPARATOR . 'entities_relations.sql',
            'item_types' => CORE_DIR . DIRECTORY_SEPARATOR . 'schema' . DIRECTORY_SEPARATOR . 'item_types.sql',
            'mime_element_set_lookup' => CORE_DIR . DIRECTORY_SEPARATOR . 'schema' . DIRECTORY_SEPARATOR . 'mime_element_set_lookup.sql',
            'processes' => CORE_DIR . DIRECTORY_SEPARATOR . 'schema' . DIRECTORY_SEPARATOR . 'processes.sql',
            'tags' => CORE_DIR . DIRECTORY_SEPARATOR . 'schema' . DIRECTORY_SEPARATOR . 'tags.sql',
            'data_types' => CORE_DIR . DIRECTORY_SEPARATOR . 'schema' . DIRECTORY_SEPARATOR . 'data_types.sql',
            'elements' => CORE_DIR . DIRECTORY_SEPARATOR . 'schema' . DIRECTORY_SEPARATOR . 'elements.sql',
            'entity_relationships' => CORE_DIR . DIRECTORY_SEPARATOR . 'schema' . DIRECTORY_SEPARATOR . 'entity_relationships.sql',
            'item_types_elements' => CORE_DIR . DIRECTORY_SEPARATOR . 'schema' . DIRECTORY_SEPARATOR . 'item_types_elements.sql',
            'options' => CORE_DIR . DIRECTORY_SEPARATOR . 'schema' . DIRECTORY_SEPARATOR . 'options.sql',
            'record_types' => CORE_DIR . DIRECTORY_SEPARATOR . 'schema' . DIRECTORY_SEPARATOR . 'record_types.sql',
            'users' => CORE_DIR . DIRECTORY_SEPARATOR . 'schema' . DIRECTORY_SEPARATOR . 'users.sql',
            'element_sets' => CORE_DIR . DIRECTORY_SEPARATOR . 'schema' . DIRECTORY_SEPARATOR . 'element_sets.sql',
            'entities' => CORE_DIR . DIRECTORY_SEPARATOR . 'schema' . DIRECTORY_SEPARATOR . 'entities.sql',
            'files' => CORE_DIR . DIRECTORY_SEPARATOR . 'schema' . DIRECTORY_SEPARATOR . 'files.sql',
            'items' => CORE_DIR . DIRECTORY_SEPARATOR . 'schema' . DIRECTORY_SEPARATOR . 'items.sql',
            'plugins' => CORE_DIR . DIRECTORY_SEPARATOR . 'schema' . DIRECTORY_SEPARATOR . 'plugins.sql',
            'taggings' => CORE_DIR . DIRECTORY_SEPARATOR . 'schema' . DIRECTORY_SEPARATOR . 'taggings.sql',
            'users_activations' => CORE_DIR . DIRECTORY_SEPARATOR . 'schema' . DIRECTORY_SEPARATOR . 'users_activations.sql'
        ), $this->schemaTask->getTables());
    }
        
    public function testAddTables()
    {
        $expectedTables = array(
            'collections' => CORE_DIR . DIRECTORY_SEPARATOR . 'schema' . DIRECTORY_SEPARATOR . 'collections.sql',
            'items' => CORE_DIR . DIRECTORY_SEPARATOR . 'schema' . DIRECTORY_SEPARATOR . 'items.sql'
        );
        $this->schemaTask->addTables($expectedTables);
        $this->assertEquals($expectedTables, $this->schemaTask->getTables());
    }
    
    public function testSetTables()
    {
        $expectedTables = array(
            'collections' => CORE_DIR . DIRECTORY_SEPARATOR . 'schema' . DIRECTORY_SEPARATOR . 'collections.sql',
            'items' => CORE_DIR . DIRECTORY_SEPARATOR . 'schema' . DIRECTORY_SEPARATOR . 'items.sql'
        );
        $this->schemaTask->setTables($expectedTables);
        $this->assertEquals($expectedTables, $this->schemaTask->getTables());
    }
    
    public function testRemoveTable()
    {
        $someTables = array(
            'collections' => CORE_DIR . DIRECTORY_SEPARATOR . 'schema' . DIRECTORY_SEPARATOR . 'collections.sql',
            'items' => CORE_DIR . DIRECTORY_SEPARATOR . 'schema' . DIRECTORY_SEPARATOR . 'items.sql'
        );
        $this->schemaTask->addTables($someTables);
        $this->schemaTask->removeTable('collections');
        $this->assertEquals(array(
            'items' => $someTables['items']
        ), $this->schemaTask->getTables());
    }
    
    public function testInstallFailsWithNoTables()
    {
        $task = new Installer_Task_Schema();        
        try {
            $task->install($this->db);
            $this->fail("Task should have thrown an exception when not given a valid schema file.");
        } catch (Exception $e) {
            $this->assertContains("No SQL files were given to create the schema.", $e->getMessage());
        }
    } 
    
    public function testInstall()
    {
        $task = new Installer_Task_Schema();
        $schemaFile = dirname(__FILE__) . '/_files/schema.sql';
        $task->addTable('test_table', $schemaFile);
        $task->install($this->db);
        $this->profilerHelper->assertDbQuery("CREATE TABLE `test_table` (`id` int(11), `name` varchar(20))");
    }
    
    public function testLoadsDefaultOmekaSchema()
    {
        $task = new Installer_Task_Schema();
        $task->useDefaultTables();
        $task->install($this->db);
        $expectedTables = array(
            'test_collections',
            'test_data_types',
            'test_elements',
            'test_element_sets',
            'test_element_texts',
            'test_entities',
            'test_entities_relations',
            'test_entity_relationships',
            'test_files',
            'test_items',
            'test_item_types',
            'test_item_types_elements',
            'test_mime_element_set_lookup',
            'test_options',
            'test_plugins',
            'test_processes',
            'test_record_types',
            'test_tags',
            'test_taggings',
            'test_users',
            'test_users_activations'
        );
        foreach ($expectedTables as $tableName) {
            $this->profilerHelper->assertDbQuery("CREATE TABLE IF NOT EXISTS `$tableName`");
        }        
    }
}