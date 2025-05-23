local Pipeline(omekaVersion, phpVersion, dbImage) = {
    kind: 'pipeline',
    type: 'docker',
    name: 'omeka:' + omekaVersion + ' php:' + phpVersion + ' ' + dbImage,
    workspace: {
        path: 'omeka-s/modules/Taxonomy',
    },
    steps: [
        {
            name: 'test',
            image: 'git.biblibre.com/omeka-s/omeka-s-ci:' + omekaVersion + '-php' + phpVersion,
            commands: [
                'cp -rT /usr/src/omeka-s ../..',
                "echo 'host = \"db\"\\nuser = \"root\"\\npassword = \"root\"\\ndbname = \"omeka_test\"\\n' > ../../application/test/config/database.ini",
                'bash -c "cd ../.. && php /usr/local/libexec/wait-for-db.php"',
                '../../vendor/bin/phpunit',
                '../../node_modules/.bin/gulp test:module:cs',
            ],
        },
    ],
    services: [
        {
            name: 'db',
            image: dbImage,
            environment: {
                MYSQL_ROOT_PASSWORD: 'root',
                MYSQL_DATABASE: 'omeka_test',
            },
        },
    ],
};

local DocumentationPipeline() = {
    kind: 'pipeline',
    type: 'docker',
    name: 'documentation',
    steps: [
        {
            name: 'build',
            image: 'python:3',
            commands: [
                'sh .drone/documentation-build.sh',
            ],
        },
        {
            name: 'push',
            image: 'alpine',
            commands: [
                'apk add git openssh',
                'sh .drone/documentation-push.sh',
            ],
            environment: {
                GH_DEPLOY_KEY: {
                    from_secret: 'GH_DEPLOY_KEY',
                },
            },
        },
    ],
    trigger: {
        branch: ['master'],
        event: ['push'],
    },
};

[
    Pipeline('3.1.2', '8.0', 'mariadb:10'),
    Pipeline('3.2.3', '8.0', 'mariadb:10'),
    Pipeline('4.0.1', '8.1', 'mariadb:10'),
    Pipeline('4.0.1', '8.2', 'mariadb:10'),
    Pipeline('4.1.1', '8.1', 'mariadb:10'),
    Pipeline('4.1.1', '8.2', 'mariadb:10'),
    Pipeline('4.1.1', '8.2', 'mariadb:11'),
    DocumentationPipeline(),
]
