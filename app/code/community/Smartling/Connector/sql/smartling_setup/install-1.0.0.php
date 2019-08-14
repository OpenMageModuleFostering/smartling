<?php

$installer = $this;

$installer->startSetup();

$installer->run("DROP TABLE IF EXISTS smartling_content_types");
$installer->run("DROP TABLE IF EXISTS smartling_localization_files_index");
$installer->run("DROP TABLE IF EXISTS smartling_projects");
$installer->run("DROP TABLE IF EXISTS smartling_projects_locales");
$installer->run("DROP TABLE IF EXISTS smartling_translate_attributes");
$installer->run("DROP TABLE IF EXISTS smartling_translate_content");
$installer->run("DROP TABLE IF EXISTS smartling_translate_fields");

$indexColumns = array ('origin_content_id', 'type', 'project_id', 'store_id');
$processTable = $installer->getConnection()
        ->newTable($installer->getTable('connector/translate_content'))
        ->addColumn('content_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
             'unsigned' => true,
             'nullable' => false,
             'primary'  => true,
             'identity' => true,
         ), 'Content Id')
        ->addColumn('type', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned' => true,
        ), 'Content type')
        ->addColumn('translated_content_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'nullable'  => true,
        ), 'Translated Content ID')        
        ->addColumn('source_store_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned' => true,
            'nullable' => false,
        ), 'Id of source store view')
        ->addColumn('origin_content_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned' => true,
            'nullable' => false,
        ), 'Origin Content Id')
        ->addColumn('content_title', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
            'nullable' => false,
        ), 'Content Title')
        ->addColumn('filename', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
            'nullable' => false, 
        ), 'Filename')
        ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'nullable' => false,
            'unsigned' => true,
        ), 'Store Id')
        ->addColumn('project_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'nullable' => false,
            'unsigned' => true
        ), 'Magento Project Id')
        ->addColumn('submitter', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned' => false
        ), 'Username Id')
        ->addColumn('submitted_time', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
            'nullable' => false,
            'default'  => Varien_Db_Ddl_Table::TIMESTAMP_INIT,
       ), 'Submitted for translate')
        ->addColumn('percent', Varien_Db_Ddl_Table::TYPE_FLOAT, '4,2', array(
            'nullable' => true
       ), 'Translated content percent')
        ->addColumn('status', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
            'nullable' => true
       ), 'Status of translated content')
      ->addIndex($installer->getIdxName('connector/translate_content', $indexColumns, Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE),
        $indexColumns, array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE));
        
$installer->getConnection()->createTable($processTable);

$typesTable = $installer->getConnection()
        ->newTable($installer->getTable('connector/content_types'))
        ->addColumn('type_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
             'unsigned' => true,
             'nullable' => false,
             'primary'  => true,
             'identity' => true,
         ), 'Content Id')        
        ->addColumn('type_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
            'nullable' => false, 
        ), 'Type name')
        ->addColumn('model', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
            'nullable' => false,
        ), 'Model')
        ->addColumn('title', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable' => false,
        ), 'Title');        

$installer->getConnection()->createTable($typesTable);

$projectsTable = $installer->getTable('connector/projects');
$projectsLocalesTable = $installer->getTable('connector/projects_locales');

$table_projects = $installer->getConnection()->newTable($projectsTable)
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Identity')
    ->addColumn('website_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
        'unsigned'  => true
        ), 'Identity')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
        'nullable' => false
        ), 'Project Name')
    ->addColumn('active', Varien_Db_Ddl_Table::TYPE_TINYINT, null, array(
        'unsigned' => true,
        'nullable' => false
        ), 'Is Active')    
    ->addColumn('api_url', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
        'nullable' => false
        ), 'API url')
    ->addColumn('callback_url', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
        'nullable' => true
        ), 'Callback url')
    ->addColumn('project_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
        'nullable' => false
        ), 'Project identity')
    ->addColumn('key', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
        'nullable' => false
        ), 'API Key')
    ->addColumn('retrieval_type', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
        'nullable' => false
        ), 'Retrieval Type')
    ->setComment('List of Smartling Projects');
$installer->getConnection()->createTable($table_projects);


$table_projects_locale = $installer->getConnection()->newTable($projectsLocalesTable)
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Identity')
    ->addColumn('parent_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'nullable' => false
        ), 'Parent Id')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'default' => 0
        ), 'Store View')
    ->addColumn('locale_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
        'nullable' => false
        ), 'Smartling Locale Code')
    ->setComment('List of Smartling Projects Locales')
    ->addForeignKey(
        $installer->getFkName('connector/projects_locales', 'projects_locales_id', 'connector/projects', 'id'),
        'parent_id', $projectsTable, 'id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE   
    );

$installer->getConnection()->createTable($table_projects_locale);
        
$filesIndexTable = $installer->getConnection()
    ->newTable($installer->getTable('connector/localization_files_index'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Identity')
    ->addColumn('dir_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 16, array(
        'nullable' => false
        ), 'Localization files dir name')
    ->addColumn('file_path', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable' => false
        ), 'File Path')
    ->addColumn('file_exists', Varien_Db_Ddl_Table::TYPE_SMALLINT, 1, array(
        'default'  => 1
        ), 'Flag if file exists')    
    ->addColumn('has_changed', Varien_Db_Ddl_Table::TYPE_SMALLINT, 1, array(
        'default'  => 0
        ), 'Flag if file has changed since previous indexing')    
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Update Time')
    ->addIndex(
        $installer->getIdxName(
            'connector/localization_files_index',
            array('dir_name', 'file_path'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ),
        array('dir_name', 'file_path'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->setComment('List of locale files');
$installer->getConnection()->createTable($filesIndexTable);

$installer->getConnection()
        ->addColumn($installer->getTable('core_store'), 'store_localization_dir',  
                    array(
                          'comment' => 'Localization dir name',
                          'nullable' => true,
                          'column_type' => Varien_Db_Ddl_Table::TYPE_TEXT,
                          'length' => 16
                       )
                );

$translateAttributesTable = $installer->getConnection()->newTable($installer->getTable('connector/translate_attributes'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Record ID')
    ->addColumn('attribute_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'nullable' => false
        ), 'Attribute ID')
    ->addColumn('entity_type_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'nullable' => false
        ), 'Entity Type Id')
    ->addIndex(
            $installer->getIdxName(
            'connector/translate_attributes',
            array('attribute_id', 'entity_type_id')
        ),
         array('attribute_id', 'entity_type_id'),
         array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
      )
    ->setComment('List of attributes which should not be translated');
$installer->getConnection()->createTable($translateAttributesTable);

$translateFieldsTable = $installer->getConnection()->newTable($installer->getTable('connector/translate_fields'))
        ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned' => true,
            'nullable' => false,
            'primary'  => true,
            'identity' => true,
         ), 'Field Id')        
        ->addColumn('field_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 64, array(
            'nullable' => false, 
        ), 'Field Name')
        ->addColumn('entity_type_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned' => true,
            'nullable' => false
        ), 'Smartling Entity Type Id')
        ->addIndex(
                $installer->getIdxName(
                'connector/translate_fields',
                array('id', 'entity_type_id')
            ),
             array('id', 'entity_type_id'),
             array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
          )
        ->setComment('List of columns in table which should not be translated');
        
$installer->getConnection()->createTable($translateFieldsTable);

$table = $installer->getTable('connector/content_types');
$data = array(
    array(
        'type_id' => 1,
        'type_name' => Smartling_Connector_Model_Content_CmsPage::CONTENT_TYPE,
        'model'     => 'connector/content_cmsPage',
        'title'   => 'Pages'
    ),
    array(
        'type_id' => 2,
        'type_name' => Smartling_Connector_Model_Content_CmsBlock::CONTENT_TYPE,
        'model'     => 'connector/content_cmsBlock',
        'title'   => 'Static Blocks'
    ),
    array(
        'type_id' => 3,
        'type_name' => Smartling_Connector_Model_Content_Product::CONTENT_TYPE,
        'model'     => 'connector/content_product',
        'title'   => 'Products'
    ),
    array(
        'type_id' => 4,
        'type_name' => Smartling_Connector_Model_Content_Category::CONTENT_TYPE,
        'model'     => 'connector/content_category',
        'title'   => 'Categories'
    ),
    array(
        'type_id' => 5,
        'type_name' => Smartling_Connector_Model_Content_Attribute::CONTENT_TYPE,
        'model'     => 'connector/content_attribute',
        'title'     => 'Attributes'
    ),
    array(
        'type_id' => 6,
        'type_name' => Smartling_Connector_Model_Content_Localization::CONTENT_TYPE,
        'model'     => 'connector/content_localization',
        'title'     => 'Localization Files'
    )
);

$installer->getConnection()->insertMultiple($table, $data);

$installer->endSetup();

