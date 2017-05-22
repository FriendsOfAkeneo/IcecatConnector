#!groovy

def launchUnitTests = "yes"
def launchIntegrationTests = "no"

class Globals {
    static pimVersion = "1.7"
    static extensionBranch = "dev-master"
    static mysqlVersion = "5.5"
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
        def tasks = [:]

        tasks["phpunit-5.6-ce"] = {runIntegrationTest("5.6", "5.5")}

        parallel tasks
    }
}

def runPhpSpecTest(phpVersion) {
    node('docker') {
        deleteDir()
        cleanUpEnvironment()
        try {
            docker.image("carcel/php:${phpVersion}").inside("-v /home/akeneo/.composer:/home/doker/.composer -e COMPOSER_HOME=/home/doker/.composer") {
                unstash "icecat_extension"

                if (phpVersion != "5.6") {
                    sh "composer require --no-update alcaeus/mongo-php-adapter"
                }

                sh "php -d memory_limit=3G /usr/local/bin/composer install --optimize-autoloader --no-interaction --no-progress --prefer-dist"
                sh "mkdir -p aklogs/"
                sh "./bin/phpspec run --no-interaction --format=junit > aklogs/phpspec.xml"
            }
        } finally {
            sh "sed -i \"s/testcase name=\\\"/testcase name=\\\"[php-${phpVersion}] /\" aklogs/*.xml"
            junit "aklogs/*.xml"
            deleteDir()
        }
    }
}

def runPhpCsFixerTest(phpVersion) {
    node('docker') {
        deleteDir()
        cleanUpEnvironment()
        try {
            docker.image("carcel/php:${phpVersion}").inside("-v /home/akeneo/.composer:/home/doker/.composer -e COMPOSER_HOME=/home/doker/.composer") {
                unstash "icecat_extension"

                if (phpVersion != "5.6") {
                    sh "composer require --no-update alcaeus/mongo-php-adapter"
                }

                sh "php -d memory_limit=3G /usr/local/bin/composer install --ignore-platform-reqs --optimize-autoloader --no-interaction --no-progress --prefer-dist"
                sh "mkdir -p aklogs/"
                sh "./bin/php-cs-fixer fix --diff --format=junit --config=.php_cs.php > aklogs/phpcs.xml"
            }
        } finally {
            sh "sed -i \"s/testcase name=\\\"/testcase name=\\\"[php-${phpVersion}] /\" aklogs/*.xml"
            junit "aklogs/*.xml"
            deleteDir()
        }
    }
}

def runIntegrationTest(phpVersion, mysqlVersion) {
    node('docker') {
        deleteDir()
        cleanUpEnvironment()

        def workspace = "/home/docker/pim"

        sh "docker network create akeneo"
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
