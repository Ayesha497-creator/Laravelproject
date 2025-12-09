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
stage('Run Tests') {
    steps {
        dir("${PROJECT_DIR}") {
            echo "Running tests..."
            sh '''
            cp ${ENV_FILE} .env
            php artisan config:clear
            php artisan test || true
            '''
        }
    }
}



        stage('Deploy') {
    steps {
        echo "Deploying to server..."
        sshagent(['deploy-server']) { // updated ID
            sh "rsync -av --exclude='vendor' ${PROJECT_DIR}/ ubuntu@13.61.68.173:${DEPLOY_DIR}/"
            sh "rsync -av ${PROJECT_DIR}/vendor/ ubuntu@13.61.68.173:${DEPLOY_DIR}/vendor/"
            sh "scp ${ENV_FILE} ubuntu@13.61.68.173:${DEPLOY_DIR}/.env"
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
