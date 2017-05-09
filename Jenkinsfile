#!groovy

def phpVersion = "5.6"
def mysqlVersion = "5.5"
def launchUnitTests = "yes"
def launchIntegrationTests = "no"

class Globals {
    static pimVersion = "1.7"
    static extensionBranch = "dev-master"
}

stage("Checkout") {
    milestone 1
    if (env.BRANCH_NAME =~ /^PR-/) {
        userInput = input(message: 'Launch tests?', parameters: [
            choice(choices: 'yes\nno', description: 'Run unit tests', name: 'launchUnitTests'),
            choice(choices: 'yes\nno', description: 'Run integration tests', name: 'launchIntegrationTests'),
        ])

        launchUnitTests = userInput['launchUnitTests']
        launchIntegrationTests = userInput['launchIntegrationTests']
    }
    milestone 2

    node {
        deleteDir()
        checkout scm
        stash "icecat_extension"

        checkout([$class: 'GitSCM',
             branches: [[name: "${Globals.pimVersion}"]],
             userRemoteConfigs: [[credentialsId: 'github-credentials', url: 'https://github.com/akeneo/pim-community-dev.git']]
        ])
        stash "pim_community"

       checkout([$class: 'GitSCM',
         branches: [[name: "${Globals.pimVersion}"]],
         userRemoteConfigs: [[credentialsId: 'github-credentials', url: 'https://github.com/akeneo/pim-enterprise-dev.git']]
       ])
       stash "pim_enterprise"
   }
}

if (launchUnitTests.equals("yes")) {
    stage("Unit tests") {
        def tasks = [:]

        tasks["phpspec-5.6"] = {runPhpSpecTest("5.6")}
        tasks["php-cs-fixer-5.6"] = {runPhpCsFixerTest("5.6")}

        parallel tasks
    }
}

if (launchIntegrationTests.equals("yes")) {
    stage("Integration tests") {
        buildApplication()

        def tasks = [:]

        tasks["phpunit-5.6-ce"] = {runIntegrationTestCe("5.6")}
        tasks["phpunit-5.6-ee"] = {runIntegrationTestEe("5.6")}

        parallel tasks
    }
}

def runPhpSpecTest(version) {
    node('docker') {
        deleteDir()
        try {
            docker.image("carcel/php:${version}").inside("-v /home/akeneo/.composer:/home/akeneo/.composer -e COMPOSER_HOME=/home/akeneo/.composer") {
                unstash "icecat_extension"

                if (version != "5.6") {
                    sh "composer require --no-update alcaeus/mongo-php-adapter"
                }

                sh "php -d memory_limit=3G /usr/local/bin/composer install --optimize-autoloader --no-interaction --no-progress --prefer-dist"
                sh "mkdir -p aklogs/"
                sh "./bin/phpspec run --no-interaction --format=junit > aklogs/phpspec.xml"
            }
        } finally {
            sh "sed -i \"s/testcase name=\\\"/testcase name=\\\"[php-${version}] /\" aklogs/*.xml"
            junit "aklogs/*.xml"
            deleteDir()
        }
    }
}

def runPhpCsFixerTest(version) {
    node('docker') {
        deleteDir()
        try {
            docker.image("carcel/php:${version}").inside("-v /home/akeneo/.composer:/home/akeneo/.composer -e COMPOSER_HOME=/home/akeneo/.composer") {
                unstash "icecat_extension"

                if (version != "5.6") {
                    sh "composer require --no-update alcaeus/mongo-php-adapter"
                }

                sh "php -d memory_limit=3G /usr/local/bin/composer install --ignore-platform-reqs --optimize-autoloader --no-interaction --no-progress --prefer-dist"
                sh "mkdir -p aklogs/"
                sh "./bin/php-cs-fixer fix --diff --format=junit --config=.php_cs.php > aklogs/phpcs.xml"
            }
        } finally {
            sh "sed -i \"s/testcase name=\\\"/testcase name=\\\"[php-${version}] /\" aklogs/*.xml"
            junit "aklogs/*.xml"
            deleteDir()
        }
    }
}

def runIntegrationTestCe(version) {
    node('docker') {
        deleteDir()
        try {
            docker.image("mysql:5.5").withRun("--name mysql -e MYSQL_ROOT_PASSWORD=root -e MYSQL_USER=akeneo_pim -e MYSQL_PASSWORD=akeneo_pim -e MYSQL_DATABASE=akeneo_pim") {
                docker.image("carcel/php:${version}").inside("--link mysql:mysql -v /home/akeneo/.composer:/home/docker/.composer -e COMPOSER_HOME=/home/docker/.composer") {
                    unstash "pim_community"

                    if (version != "5.6") {
                        sh "composer require --no-update alcaeus/mongo-php-adapter"
                    }

                    sh "composer require --no-update phpunit/phpunit:5.4 akeneo/icecat-connector:${Globals.extensionBranch}"
                    sh "composer update --ignore-platform-reqs --optimize-autoloader --no-interaction --no-progress --prefer-dist"
                    dir("vendor/akeneo/extended-attribute-type") {
                        deleteDir()
                        unstash "icecat_extension"
                    }
                    sh 'ln -s $(pwd)/vendor/akeneo/extended-attribute-type/doc/example/Pim src/Pim'
                    sh 'composer dump-autoload -o'

                    sh "cp vendor/akeneo/extended-attribute-type/doc/example/Pim/Bundle/ExtendedCeBundle/Resources/config/config_test.yml app/config/config_test.yml"
                    sh "cp vendor/akeneo/extended-attribute-type/doc/example/Pim/Bundle/ExtendedCeBundle/Resources/config/parameters_test.yml app/config/parameters_test.yml"

                    sh "sed -i 's#// your app bundles should be registered here#\\0\\nnew Pim\\\\Bundle\\\\ExtendedCeBundle\\\\ExtendedCeBundle(),#' app/AppKernel.php"
                    sh "sed -i 's#// your app bundles should be registered here#\\0\\nnew Pim\\\\Bundle\\\\ExtendedAttributeTypeBundle\\\\PimExtendedAttributeTypeBundle(),#' app/AppKernel.php"
                    sh "cat app/AppKernel.php"


                    sh "rm ./app/cache/* -rf"
                    sh "./app/console --env=test pim:install --force"
                    sh "mkdir -p app/build/logs/"
                    sh "./bin/phpunit -c app/ --log-junit app/build/logs/phpunit.xml  vendor/akeneo/extended-attribute-type/Tests"
                }
            }
        } finally {
            sh "sed -i \"s/testcase name=\\\"/testcase name=\\\"[php-${version}] /\" app/build/logs/*.xml"
            junit "app/build/logs/*.xml"
            deleteDir()
        }
    }
}

def runIntegrationTestEe(version) {
    node('docker') {
        deleteDir()
        cleanUpEnvironment()

        sh "docker network create akeneo"
        sh """
            docker pull mysql:${mysqlVersion}
            docker pull carcel/akeneo:php-${phpVersion}
        """

        def workspace = "/home/docker/pim"

        unstash "pim_enterprise_full"

        sh "docker run -d --network akeneo --name mysql \
            -e MYSQL_ROOT_PASSWORD=root -e MYSQL_USER=akeneo_pim -e MYSQL_PASSWORD=akeneo_pim -e MYSQL_DATABASE=akeneo_pim \
            mysql:${mysqlVersion} \
            --sql-mode=ERROR_FOR_DIVISION_BY_ZERO,NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION"
        sh "docker run -d --network akeneo --name akeneo-behat \
            -e WORKSPACE=${workspace} -e COMPOSER_HOME=/home/docker/.composer \
            -v /home/akeneo/.composer:/home/docker/.composer -v \$(pwd):${workspace} \
            -w ${workspace} \
            carcel/akeneo:php-${phpVersion}"

        sh "docker ps -a"

        try {
            sh "docker exec akeneo ./app/console --env=test pim:install --force"
            sh "docker exec akeneo ./bin/phpunit -c app/ --log-junit app/build/logs/phpunit.xml  vendor/akeneo/extended-attribute-type/Tests"
        } finally {
            sh "sed -i \"s/testcase name=\\\"/testcase name=\\\"[php-${version}] /\" app/build/logs/*.xml"
            junit "app/build/logs/*.xml"
            deleteDir()
        }
    }
}

def buildApplication() {
    node('docker') {
        deleteDir()
        cleanUpEnvironment()

        sh "docker pull carcel/akeneo:php-${phpVersion}"

        def workspace = "/home/docker/pim"
        unstash "pim_enterprise"

        sh "docker run -d --name akeneo-behat \
            -e WORKSPACE=${workspace} -e COMPOSER_HOME=/home/docker/.composer \
            -v /home/akeneo/.composer:/home/docker/.composer -v \$(pwd):${workspace} \
            -w ${workspace} \
            carcel/akeneo:php-${phpVersion}"

        sh "docker ps -a"

        sh "docker exec akeneo composer require --no-update phpunit/phpunit:5.4 akeneo/icecat-connector:${Globals.extensionBranch}"
        sh "docker exec akeneo composer update --ignore-platform-reqs --optimize-autoloader --no-interaction --no-progress --prefer-dist"

        dir("vendor/akeneo/icecat-connector") {
            deleteDir()
            unstash "icecat_extension"
        }

        sh "ln -s ${workspace}/vendor/akeneo/extended-attribute-type/doc/example/Pim src/Pim"

        sh "docker exec akeneo composer dump-autoload -o"

        sh "cp vendor/akeneo/extended-attribute-type/doc/example/Pim/Bundle/ExtendedEeBundle/Resources/config/config_test.yml app/config/config_test.yml"
        sh "cp vendor/akeneo/extended-attribute-type/doc/example/Pim/Bundle/ExtendedEeBundle/Resources/config/parameters_test.yml app/config/parameters_test.yml"

        sh "sed -i 's#// your app bundles should be registered here#\\0\\nnew Pim\\\\Bundle\\\\ExtendedEeBundle\\\\ExtendedEeBundle(),#' app/AppKernel.php"
        sh "sed -i 's#// your app bundles should be registered here#\\0\\nnew Pim\\\\Bundle\\\\ExtendedAttributeTypeBundle\\\\PimExtendedAttributeTypeBundle(),#' app/AppKernel.php"
        sh "cat app/AppKernel.php"


        sh "rm ./app/cache/* -rf"
        sh "mkdir -p app/build/logs/"

       stash "pim_enterprise_full"
    }
}

def cleanUpEnvironment() {
    deleteDir()
    sh '''
        docker ps -a -q | xargs -n 1 -P 8 -I {} docker rm -f {} > /dev/null
        docker volume ls -q | xargs -n 1 -P 8 -I {} docker volume rm {} > /dev/null
        docker network ls --filter name=akeneo -q | xargs -n 1 -P 8 -I {} docker network rm {} > /dev/null
    '''
}
