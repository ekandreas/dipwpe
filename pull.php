<?php

task( 'pull:database', function () {

    writeln( 'Creating a new database dump on the remote server' );
    run( 'mysqldump {{pull.name}} > /tmp/{{pull.name}}.sql' );
    writeln( 'Getting database dump from the remote server' );
    runLocally( 'scp {{pull.remote_ssh}}:/tmp/{{pull.name}}.sql {{pull.name}}.sql' );
    writeln( 'Restore remote database to local database' );
    runLocally( 'cd web && wp db import ../{{pull.name}}.sql' );
    writeln( 'Search and replace urls in the imported database to local urls' );
    runLocally( 'cd web && wp search-replace www.{{pull.remote_domain}} {{pull.local}}' );
    runLocally( 'cd web && wp search-replace {{pull.remote_domain}} {{pull.local}}' );

} );

task( 'pull:files', function () {

    writeln( 'Getting uploads, long duration first time! (approx. 60s)' );
    runLocally( 'rsync -re ssh {{pull.remote_ssh}}:{{pull.remote_path}}/shared/web/app/uploads web/app' );

} );

task( 'pull:elastic', function () {

    writeln( 'Setup elasticsearch and elasticpress' );
    runLocally( 'cd web && wp elasticpress index --setup' );

} );

task( 'pull:cleanup', function () {

    writeln( 'Cleaning up locally...' );
    runLocally( 'rm {{pull.name}}.sql' );
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
