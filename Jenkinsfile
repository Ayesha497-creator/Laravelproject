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

        stage('Install & Build') {
            steps {
                dir("${PROJECT_DIR}") {

                    echo "Installing PHP dependencies..."
                    sh 'composer install --no-interaction --prefer-dist --optimize-autoloader'

                    echo "Installing Node dependencies (npm install)..."
                    sh 'npm install'

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
