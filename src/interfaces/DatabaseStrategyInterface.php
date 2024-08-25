<?php
namespace Imccc\Snail\Interfaces;

interface DatabaseStrategyInterface
{
    public function backupDatabase($backupFilePath);
    public function restoreDatabase($backupFilePath);
    public function createView($viewName, $selectStatement);
    public function dropView($viewName);
    public function createTrigger($triggerName, $timing, $event, $table, $statement);
    public function dropTrigger($triggerName);
    public function createProcedure($procedureName, $procedureDefinition);
    public function dropProcedure($procedureName);
}
