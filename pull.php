<?php

task( 'pull:database', function () {

    writeln( 'Creating a new database dump on the remote server' );
    run( 'mysqldump {{remote.database}} > /tmp/{{remote.name}}.sql' );
    writeln( 'Getting database dump from the remote server' );
    runLocally( 'scp {{remote.ssh}}:/tmp/{{remote.name}}.sql {{remote.name}}.sql' );
    writeln( 'Restore remote database to local database' );
    runLocally( 'cd web && wp db import ../{{remote.name}}.sql' );
    writeln( 'Search and replace urls in the imported database to local urls' );
    runLocally( 'cd web && wp search-replace www.{{remote.domain}} {{local.domain}}' );
    runLocally( 'cd web && wp search-replace {{remote.domain}} {{local.domain}}' );

} );

task( 'pull:files', function () {

    writeln( 'Getting uploads, long duration first time! (approx. 60s)' );
    runLocally( 'rsync -re ssh {{remote.ssh}}:{{remote.path}}/shared/web/app/uploads web/app' );

} );

task( 'pull:elastic', function () {

    if( env('local.is_elastic') ) {
        writeln( 'Setup elasticsearch and elasticpress' );
        runLocally( 'cd web && wp elasticpress index --setup' );
    }

} );

task( 'pull:cleanup', function () {

    writeln( 'Cleaning up locally...' );
    runLocally( 'rm {{remote.name}}.sql' );
    writeln( 'Permalinks rewrite/flush' );
    runLocally( 'cd web && wp rewrite flush' );
    writeln( 'Activate query monitor' );
    runLocally( 'cd web && wp plugin activate query-monitor' );

} );

task( 'pull', [
    'pull:database',
    'pull:files',
    'pull:elastic',
    'pull:cleanup',
] );
