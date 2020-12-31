<?php


namespace YangjunLiu\Canal\CanalObserver;


use Com\Alibaba\Otter\Canal\Protocol\Column;
use Com\Alibaba\Otter\Canal\Protocol\RowData;
use YangjunLiu\Canal\Observer\BinlogObserver;
use YangjunLiu\Canal\Observer\EventType;

class InsertObserver extends BinlogObserver
{
    public function __construct()
    {
        // 绑定通知事件
//        $this->setEventType([EventType::INSERT, EventType::DELETE, EventType::UPDATE]);
        $this->setEventType([EventType::INSERT]);
        // 绑定数据库名称
        $this->setSchema('my_test');
        // 绑定表名称
        $this->setTable('test_canal');
    }

    /**
     * @param RowData $message
     * @return mixed|void
     */
    public function handle($message)
    {
        /** @var RowData $rowData */
        foreach ($message as $rowData) {
            echo '-------> insert before', PHP_EOL;
            self::ptColumn($rowData->getBeforeColumns());
            echo '-------> insert after', PHP_EOL;
            self::ptColumn($rowData->getAfterColumns());
        }
    }

    private function ptColumn($columns) {
        /** @var Column $column */
        foreach ($columns as $column) {
            echo sprintf("%s : %s  update= %s", $column->getName(), $column->getValue(), var_export($column->getUpdated(), true)), PHP_EOL;
        }
    }
}