<?php


namespace YangjunLiu\Canal\Observer;


use Com\Alibaba\Otter\Canal\Protocol\RowData;

abstract class BinlogObserver
{
    /**
     * 数据库修改事件
     * @var array
     */
    protected $eventType;

    /**
     * 数据名称
     * @var string
     */
    protected $schema;

    /**
     * 数据库表明
     * @var string
     */
    protected $table;

    /**
     * @return array
     */
    public function getEventType()
    {
        return $this->eventType;
    }

    /**
     * @param array $eventType
     */
    public function setEventType($eventType)
    {
        $this->eventType = $eventType;
    }

    /**
     * @return string
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * @param string $schema
     */
    public function setSchema($schema)
    {
        $this->schema = $schema;
    }

    /**
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @param string $table
     */
    public function setTable($table)
    {
        $this->table = $table;
    }

    /**
     * @param RowData $message
     * @return mixed
     */
    abstract public function handle($message);
}