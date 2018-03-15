<?php
use React\EventLoop\Factory;
use React\ChildProcess\Process;

require __DIR__ . '/vendor/autoload.php';

const PERIODIC = 5;
const CLEAN = PERIODIC * 8;

date_default_timezone_set ('America/Sao_Paulo');

function con () {
    try {
        $pdo = new \PDO('sqlite:' . __DIR__ . '/db.sqlite');
    }
    catch(\PDOException $e) {
        echo $e->getMessage();
        die;
    }

    return $pdo;
}

function crons()
{
    $pdo = con();
    $sql = " SELECT * from  crons ";

    $stm = $pdo->prepare($sql);

    $stm->execute();

    $result = ($stm->fetchAll(\PDO::FETCH_ASSOC));
    return $result;
}

function running($id, $run)
{
    $pdo = con();
    $sql = "update crons set run = ?  where id = ?";

    $stm = $pdo->prepare($sql);

    $stm->bindParam(1, $run);
    $stm->bindParam(2, $id);

    $stm->execute();

    return;
}

function active()
{
    $pdo = con();
    $sql = "update crons set run = 0 where run = 1";

    $stm = $pdo->prepare($sql);

    $stm->execute();

    return;
}


$loop = Factory::create();

$loop->addPeriodicTimer(PERIODIC, function () use ($loop) {

    $cronJobs = crons();

    foreach($cronJobs as $job) {
            if ((new DateTime())->format('H:i') == $job['time'] && $job['run'] == 0) {
                echo 'running...' . PHP_EOL;
                running((int)$job['id'], 1);

                $proc = new Process($job['command'], null);
                $proc->start($loop);

                $proc->on('exit', function ($code)  use ($job){
                    $date = new DateTime();
                    echo $job['name'] . ' EXIT with code ' . $code . 'at:' . $date->format('Y-m-d H:i:s') . PHP_EOL;
                });

                $proc->stdout->on('data', function ($chunk) {
                    echo $chunk;
                });
            } else {
                echo 'do nothing...' . (new DateTime())->format('H:i:s')  . PHP_EOL;
            }
    }
});

$loop->addPeriodicTimer(CLEAN, function () use ($loop) {

    echo 'Active crons...' . PHP_EOL;
    active();
});

echo 'CronLoop Init: ' . (new DateTime())->format('Y-m-d H:i:s') . PHP_EOL;

$loop->run();
