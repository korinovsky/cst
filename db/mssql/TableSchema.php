<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 App Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace cst\db\mssql;

/**
 * TableSchema represents the metadata of a database table.
 */
class TableSchema extends \cst\db\TableSchema
{
    /**
     * @var string name of the catalog (database) that this table belongs to.
     * Defaults to null, meaning no catalog (or the current database).
     */
    public $catalogName;
}
