<?php


namespace YangjunLiu\Canal\Observer;


use Com\Alibaba\Otter\Canal\Protocol\EntryType;
use Com\Alibaba\Otter\Canal\Protocol\RowChange;
use Com\Alibaba\Otter\Canal\Protocol\RowData;
use xingwenge\canal_php\CanalConnectorFactory;

class Subject
{
    /**
     * 自动加载观察者类路径
     * @var string
     */
    protected $autoLoadPath;

    /**
     * 自动加载canal观察者类匹配key
     * @var string
     */
    protected $autoLoadClassKey;

    /**
     * @var array BinlogObserver
     */
    private $obs = array();

    public function __construct()
    {
        $this->init();
    }

    /**
     * 初始化类
     */
    protected function init()
    {
        $this->autoLoadClassKey = 'CanalObserver';
        $this->autoLoadPath = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . $this->autoLoadClassKey;
        $this->autoLoadObserver();
    }

    /**
     * 自动加载观察者类
     */
    protected function autoLoadObserver()
    {
        // 加载类文件
        $allFiles = $this->scanObserverFile();
        foreach ($allFiles as $file) {
            require_once $file;
        }

        foreach (get_declared_classes() as $class) {
            if (strstr($class, $this->autoLoadClassKey)) {
                // 反射实例化类
                $class = new \ReflectionClass($class);
                $obsObj = $class->newInstance();
                array_push($this->obs, $obsObj);
            }
        }
    }

    private function scanObserverFile()
    {
        $files = glob($this->autoLoadPath . "/*");
        $ret = [];
        foreach ($files as $file) {
            if (is_dir($file)) {
                $ret = array_merge($ret, $this->scanObserverFile($file));
            } elseif (pathinfo($file)["extension"] == "php") {
                $ret[] = $file;
            }
        }

        return $ret;
    }

    public function deamainSevice()
    {
        try {
            $client = CanalConnectorFactory::createClient(CanalConnectorFactory::CLIENT_SOCKET);
            # $client = CanalConnectorFactory::createClient(CanalClient::TYPE_SWOOLE);

            $client->connect("127.0.0.1", 11111);
            $client->checkValid();
            $client->subscribe("1001", "example", ".*\\..*");
            # $client->subscribe("1001", "example", "db_name.tb_name"); # 设置过滤

            while (true) {
                $message = $client->get(100);
                if ($entries = $message->getEntries()) {
                    foreach ($entries as $entry) {
                        $entryType = $entry->getEntryType();
                        if ($entryType == EntryType::TRANSACTIONBEGIN) {
                            continue;
                        }

                        if ($entryType == EntryType::TRANSACTIONEND) {
                            continue;
                        }

                        $rowChange = new RowChange();
                        $rowChange->mergeFromString($entry->getStoreValue());
                        $header = $entry->getHeader();

                        echo sprintf("================> binlog[%s : %d],name[%s,%s], eventType: %s", $header->getLogfileName(), $header->getLogfileOffset(), $header->getSchemaName(), $header->getTableName(), $header->getEventType()), PHP_EOL;
                        $this->notifyObserver($header->getSchemaName(), $header->getTableName(), $header->getEventType(), $rowChange->getRowDatas());
                    }
                }
                sleep(1);
            }

            $client->disConnect();
        } catch (\Exception $e) {
            echo $e->getMessage(), PHP_EOL;
        }
    }

    /**
     * @param $schemaName
     * @param $tableName
     * @param $eventType
     * @param RowData $rowData
     */
    private function notifyObserver($schemaName, $tableName, $eventType, $rowData)
    {
        /** @var BinlogObserver $ob */
        foreach ($this->obs as $ob) {
            // 匹配数据库名称
            if (!empty($ob->getSchema()) && (strcmp($schemaName, $ob->getSchema()) == 0)
                && !empty($ob->getTable()) && (strcmp($tableName, $ob->getTable()) == 0)
                && (empty($ob->getEventType()) || in_array($eventType, $ob->getEventType()))) {
                $ob->handle($rowData);
                continue;
            }

            if (!empty($ob->getSchema()) && (strcmp($schemaName, $ob->getSchema()) == 0)
                && !empty($ob->getEventType()) && in_array($eventType, $ob->getEventType())
                && (empty($ob->getTable()) || (strcmp($tableName, $ob->getTable()) == 0))) {
                $ob->handle($rowData);
                continue;
            }

            if (!empty($ob->getTable()) && (strcmp($tableName, $ob->getTable()) == 0)
                && !empty($ob->getEventType()) && in_array($eventType, $ob->getEventType())
                && (empty($ob->getSchema()) || (strcmp($schemaName, $ob->getSchema()) == 0))) {
                $ob->handle($rowData);
                continue;
            }
        }
    }
}