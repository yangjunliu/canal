<?php

//use xingwenge\canal_php\CanalConnectorFactory;
//use xingwenge\canal_php\Fmt;

use YangjunLiu\Canal\Observer\Subject;

require_once './vendor/autoload.php';

$autoloaderPath = __DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'CanalObserver';
$autoloaderNamespace = 'YangjunLiu\Canal\CanalObserver';

$subject = new Subject($autoloaderPath, $autoloaderNamespace);
$subject->deamainSevice();


//try {
//    $client = CanalConnectorFactory::createClient(CanalConnectorFactory::CLIENT_SOCKET);
//    # $client = CanalConnectorFactory::createClient(CanalClient::TYPE_SWOOLE);
//
//    $client->connect("127.0.0.1", 11111);
//    $client->checkValid();
//    $client->subscribe("1001", "example", ".*\\..*");
//    # $client->subscribe("1001", "example", "db_name.tb_name"); # 设置过滤
//
//    while (true) {
//        $message = $client->get(100);
//        if ($entries = $message->getEntries()) {
//            foreach ($entries as $entry) {
//                Fmt::println($entry);
//            }
//        }
//        sleep(1);
//    }
//
//    $client->disConnect();
//} catch (\Exception $e) {
//    echo $e->getMessage(), PHP_EOL;
//}