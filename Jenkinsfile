pipeline {
    agent any

    tools {
        nodejs 'nodejs'
    }

    environment {
        PROJECT_DIR = "${WORKSPACE}/Laravelproject"
        ENV_FILE = "${PROJECT_DIR}/.env"

        // Slack webhook split for security
        SLACK_WEBHOOK_PART1 = "https://hooks.slack.com/services/"
        SLACK_WEBHOOK_PART2 = "T09TC4RGERG/B0A32EG5S8H/"
        SLACK_WEBHOOK_PART3 = "iYrJ9vPwxK0Ab6lY7UQdKs8W"
    }

    stages {
        stage('Checkout') {
            steps {
                dir("${PROJECT_DIR}") {
                    git branch: "${BRANCH_NAME}",
                        url: 'https://github.com/Ayesha497-creator/Laravelproject.git',
                        credentialsId: 'github-token'
                }
            }
        }

        stage('Build Assets') {
            steps {
                dir("${PROJECT_DIR}") {
                    echo "Building assets..."
                    sh 'npm install --legacy-peer-deps'
                    sh 'npm run prod'
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
                script {
                    def DEPLOY_DIR = (BRANCH_NAME == "main") ? "/var/www/demo1.flowsoftware.ky/main" : "/var/www/demo1.flowsoftware.ky/${BRANCH_NAME}"

                    echo "Deploying branch ${BRANCH_NAME} to ${DEPLOY_DIR}..."

                    sshagent(['jenkins-deploy-key']) {
                        sh """
                            ssh -o StrictHostKeyChecking=no ubuntu@13.61.68.173 '
                                sudo mkdir -p ${DEPLOY_DIR} &&
                                sudo chown -R ubuntu:ubuntu ${DEPLOY_DIR}
                            '

                            # Sync vendor
                            rsync -av ${PROJECT_DIR}/vendor/ ubuntu@13.61.68.173:${DEPLOY_DIR}/vendor/

                            # Sync rest of project except vendor
                            rsync -av --exclude='vendor' ${PROJECT_DIR}/ ubuntu@13.61.68.173:${DEPLOY_DIR}/

                            # Copy .env
                            scp ${ENV_FILE} ubuntu@13.61.68.173:${DEPLOY_DIR}/.env
                        """
                    }
                }
            }
        }

        stage('Optimize & Permissions') {
            steps {
                script {
                    def DEPLOY_DIR = (BRANCH_NAME == "main") ? "/var/www/demo1.flowsoftware.ky/main" : "/var/www/demo1.flowsoftware.ky/${BRANCH_NAME}"

                    sshagent(['jenkins-deploy-key']) {
                        sh """
                            ssh -o StrictHostKeyChecking=no ubuntu@13.61.68.173 '
                                cd ${DEPLOY_DIR} &&

                                # Clear & cache Laravel config, routes, views
                                php artisan config:clear &&
                                php artisan cache:clear &&
                                php artisan route:clear &&
                                php artisan view:clear &&
                                php artisan config:cache &&
                                php artisan route:cache &&
                                php artisan view:cache &&

                                # Set proper permissions for runtime
                                sudo chown -R www-data:www-data ${DEPLOY_DIR} &&
                                sudo chmod -R 775 ${DEPLOY_DIR}/storage ${DEPLOY_DIR}/bootstrap/cache
                            '
                        """
                    }
                }
            }
        }
    }

    post {
        success {
            echo "✅ Deployment Successful for branch: ${BRANCH_NAME}"
           
        }

        failure {
            echo "❌ Deployment Failed for branch: ${BRANCH_NAME}"
           
        }
    }
}
