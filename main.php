<?php
/**
 * 
 */


/*
example:

env('remote.name','orasolvinfo');
env('remote.path','/mnt/persist/www/orasolv.info');
env('remote.ssh','root@c6889.cloudnet.se');
env('remote.database','orasolvinfo');
env('remote.domain','orasolv.info');
env('local.domain','intra.dev');
env('local.is_elastic',true);

 */
include_once 'common.php';
include_once 'init.php';
include_once 'pull.php';
