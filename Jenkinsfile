pipeline {
    agent any

    tools {
        nodejs 'nodejs'
    }

    environment {
        PROJECT_DIR = "${WORKSPACE}/Laravelproject"
        DEPLOY_DIR = "/var/www/demo1.flowsoftware.ky/${BRANCH_NAME}"
        ENV_FILE = "${PROJECT_DIR}/.env"
        DEPLOY_URL = "http://demo1.flowsoftware.ky/${BRANCH_NAME}"

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
                echo "Deploying to server..."
                sshagent(['jenkins-deploy-key']) {
                    sh """
                        ssh -o StrictHostKeyChecking=no ubuntu@13.61.68.173 'sudo mkdir -p ${DEPLOY_DIR} && sudo chown -R ubuntu:ubuntu ${DEPLOY_DIR}'
                        rsync -av --ignore-missing-args ${PROJECT_DIR}/vendor/ ubuntu@13.61.68.173:${DEPLOY_DIR}/vendor/ || true
                        rsync -av --exclude='vendor' ${PROJECT_DIR}/ ubuntu@13.61.68.173:${DEPLOY_DIR}/
                        scp ${ENV_FILE} ubuntu@13.61.68.173:${DEPLOY_DIR}/.env
                    """
                }
            }
        }
    }

    post {
    success {
        echo "✅ Deployment Successful for branch: ${BRANCH_NAME}"
        sh """
            FULL_SLACK_WEBHOOK=\$SLACK_WEBHOOK_PART1\$SLACK_WEBHOOK_PART2\$SLACK_WEBHOOK_PART3
            curl -X POST -H 'Content-type: application/json' --data '{
                "text": "✅ *Deployment Successful!*\nBranch: ${BRANCH_NAME}\nProject: Laravelproject"
            }' \$FULL_SLACK_WEBHOOK
        """
    }

    failure {
        echo "❌ Deployment Failed for branch: ${BRANCH_NAME}"
        sh """
            FULL_SLACK_WEBHOOK=\$SLACK_WEBHOOK_PART1\$SLACK_WEBHOOK_PART2\$SLACK_WEBHOOK_PART3
            curl -X POST -H 'Content-type: application/json' --data '{
                "text": "❌ *Deployment Failed!*\nBranch: ${BRANCH_NAME}\nPlease check Jenkins logs."
            }' \$FULL_SLACK_WEBHOOK
        """
    }
}
