pipeline {
    agent any

    tools {
        nodejs 'nodejs'
    }

    environment {
        PROJECT_DIR = "${WORKSPACE}/Laravelproject"
        DEPLOY_DIR = "/var/www/demo1.flowsoftware.ky"
        ENV_FILE = "${PROJECT_DIR}/.env"
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

        stage('Install Node Dependencies') {
            steps {
                dir("${PROJECT_DIR}") {
                    echo "Installing Node dependencies..."
                    sh 'rm -rf node_modules'
                    sh 'npm install'
                }
            }
        }

        stage('Install PHP Dependencies & Build') {
            steps {
                dir("${PROJECT_DIR}") {
                    echo "Installing PHP dependencies..."
                    sh 'composer install --no-interaction --prefer-dist --optimize-autoloader'

                    echo "Building assets..."
                    sh 'npm run prod || true'
                }
            }
        }

        stage('Prepare Laravel') {
            steps {
                dir("${PROJECT_DIR}") {
                    echo "Setting permissions and environment..."
                    sh 'sudo chown -R jenkins:jenkins storage bootstrap/cache || true'
                    sh 'sudo chmod -R 775 storage bootstrap/cache || true'

                    sh '''
                        cp ${ENV_FILE} .env || true
                        php artisan config:clear || true
                    '''

                    sh '''
                        if ! php artisan key:generate --show; then
                            php artisan key:generate || true
                        fi
                    '''
                }
            }
        }

        stage('Run Tests') {
            steps {
                dir("${PROJECT_DIR}") {
                    echo "Running tests..."
                    sh 'php artisan test || true'
                }
            }
        }

        stage('Test SSH Connection') {
            steps {
                echo "Testing SSH connection to remote server..."
                sshagent(['jenkins-deploy-key']) {
                    sh "ssh -o StrictHostKeyChecking=no ubuntu@13.61.68.173 'uptime'"
                }
            }
        }
        stage('Prepare .env') {
    steps {
        dir("${PROJECT_DIR}") {
            sh '''
                if [ ! -f .env ]; then
                    cp .env.example .env
                fi
            '''
        }
    }
}


       stage('Deploy') {
    steps {
        echo "Deploying to server..."
        sshagent(['jenkins-deploy-key']) {
            sh """
                ssh -o StrictHostKeyChecking=no ubuntu@13.61.68.173 'sudo mkdir -p ${DEPLOY_DIR} && sudo chown -R ubuntu:ubuntu ${DEPLOY_DIR}'
                rsync -av --exclude='vendor' ${PROJECT_DIR}/ ubuntu@13.61.68.173:${DEPLOY_DIR}/
                rsync -av ${PROJECT_DIR}/vendor/ ubuntu@13.61.68.173:${DEPLOY_DIR}/vendor/
                scp ${ENV_FILE} ubuntu@13.61.68.173:${DEPLOY_DIR}/.env
            """
        }
    }
}

    } // end stages

    post {
        success {
            echo '✅ Deployment Successful!'
        }
        failure {
            echo '❌ Build or Deploy Failed!'
        }
    }
}
