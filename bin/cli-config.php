#!/usr/bin/env php
<?php

require_once __DIR__.'/../app/bootstrap.php';

$app = new \Core\Application();
$app->init();
$em = $app['orm.em'];

$helpers = new Symfony\Component\Console\Helper\HelperSet(array(
    'db' => new \Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper($em->getConnection()),
    'em' => new \Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper($em)
));
