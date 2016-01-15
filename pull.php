<?php

task( 'pull:create_database_dump', function () {
    writeln( 'Creating a new database dump on the remote server' );
    run( 'mysqldump {{remote.database}} > /tmp/{{remote.name}}.sql', 999 );
} );

task( 'pull:get_database_dump', function () {
    writeln( 'Getting database dump from the remote server via scp' );
    runLocally( 'scp {{remote.ssh}}:/tmp/{{remote.name}}.sql {{remote.name}}.sql', 999 );
} );

task( 'pull:restore_database', function () {
    writeln( 'Restore remote database backup to local database' );
    runLocally( 'cd web && wp db import ../{{remote.name}}.sql', 999 );
} );

task( 'pull:search_and_replace_database', function () {
    writeln( 'Search and replace urls in the imported database to local urls' );
    runLocally( 'cd web && wp search-replace www.{{remote.domain}} {{local.domain}}', 999 );
    runLocally( 'cd web && wp search-replace {{remote.domain}} {{local.domain}}', 999 );
} );

task( 'pull:files', function () {
    writeln( 'Getting uploads, long duration first time! (approx. 60s)' );
    runLocally( 'rsync -re ssh {{remote.ssh}}:{{remote.path}}/shared/web/app/uploads web/app', 999 );
} );

task( 'pull:elastic', function () {
    if( env('local.is_elastic') ) {
        writeln( 'Setup elasticsearch and elasticpress' );
        runLocally( 'cd web && wp elasticpress index --setup', 999 );
    }
} );

task( 'pull:cleanup', function () {
    writeln( 'Cleaning up locally...' );
    runLocally( 'rm {{remote.name}}.sql' );
    writeln( 'Permalinks rewrite/flush' );
    runLocally( 'cd web && wp rewrite flush' );
    writeln( 'Activate query monitor' );
    runLocally( 'cd web && wp plugin activate query-monitor' );
    if( file_exists('web/app/uploads/.cache/') ) {
        writeln( 'Empty Bladerunner cache' );
        array_map('unlink', glob("web/app/uploads/.cache/*.*"));
    }
} );

task( 'pull', [
    'pull:create_database_dump',
    'pull:get_database_dump',
    'pull:restore_database',
    'pull:search_and_replace_database',
    'pull:files',
    'pull:elastic',
    'pull:cleanup',
] );
