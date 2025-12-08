pipeline {
    agent any

    environment {
        PROJECT_DIR = "${WORKSPACE}/Laravelproject"
        DEPLOY_DIR = '/var/www/demo1.flowsoftware.ky'
        ENV_FILE = "${WORKSPACE}/Laravelproject/.env"
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

        stage('Build') {
            steps {
                dir("${PROJECT_DIR}") {
                    echo "Installing dependencies..."
                    sh 'composer install --no-interaction --prefer-dist --optimize-autoloader'

                    // Use NodeJS plugin wrapper to run npm commands
                    nodejs('NodeJS 25.2.1') {
                        sh 'npm install'
                        sh 'npm run prod || true'
                    }
                }
            }
        }

        stage('Test') {
            steps {
                dir("${PROJECT_DIR}") {
                    echo "Running Laravel tests..."
                    sh 'php artisan test || true'
                }
            }
        }

        stage('Deploy') {
            steps {
                echo "Deploying to remote AWS webroot..."
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
            echo '❌ Build failed!'
        }
    }
}
