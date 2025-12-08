pipeline {
    agent any

    tools {
        nodejs 'nodejs'
    }

    environment {
        PROJECT_DIR = "${WORKSPACE}/Laravelproject"
        DEPLOY_DIR = "/var/www/demo1.flowsoftware.ky"
        ENV_FILE = "${PROJECT_DIR}/.env"
        NPM_CACHE = "${PROJECT_DIR}/.npm-cache"
    }

    stages {

        stage('Checkout') {
            steps {
                dir("${PROJECT_DIR}") {
                    git branch: 'main',
                        url: 'https://github.com/Ayesha497-creator/Laravelproject.git',
                        credentialsId: 'github-token'
                }
            }
        }

        stage('Clean & Install Node Dependencies') {
            steps {
                dir("${PROJECT_DIR}") {

                    echo "Cleaning workspace..."
                    sh 'rm -rf node_modules'

                   echo "Installing Node dependencies..."
sh 'npm install --cache /var/lib/jenkins/workspace/laravelproject@2/Laravelproject/.npm-cache'

                }
            }
        }

        stage('Install & Build PHP') {
            steps {
                dir("${PROJECT_DIR}") {

                    echo "Installing PHP dependencies..."
                    sh 'composer install --no-interaction --prefer-dist --optimize-autoloader'

                    echo "Building assets..."
                    sh 'npm run prod || npm run build || true'
                }
            }
        }

        stage('Test') {
            steps {
                dir("${PROJECT_DIR}") {
                    sh 'php artisan test || true'
                }
            }
        }

        stage('Deploy') {
            steps {
                echo "Deploying project to the server..."
                sshagent(['deployserver']) {
                    sh "rsync -av --exclude='vendor' ${PROJECT_DIR}/ ubuntu@13.61.68.173:${DEPLOY_DIR}/"
                    sh "rsync -av ${PROJECT_DIR}/vendor/ ubuntu@13.61.68.173:${DEPLOY_DIR}/vendor/"
                    sh "scp ${ENV_FILE} ubuntu@13.61.68.173:${DEPLOY_DIR}/.env"
                }
            }
        }
    }

    post {
        success {
            echo '✅ Deployment Successful!'
        }
        failure {
            echo '❌ Build or Deploy Failed!'
        }
    }
}

