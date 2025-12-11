pipeline {
    agent any

    tools {
        nodejs 'nodejs'
    }

    environment {
        // Use workspace directly, Laravelproject folder might not exist in Multibranch
        PROJECT_DIR = "${WORKSPACE}"
        ENV_FILE = "${PROJECT_DIR}/.env"

        SLACK_WEBHOOK_PART1 = "https://hooks.slack.com/services/"
        SLACK_WEBHOOK_PART2 = "T09TC4RGERG/B0A32EG5S8H/"
        SLACK_WEBHOOK_PART3 = "iYrJ9vPwxK0Ab6lY7UQdKs8W"
    }

    stages {

        stage('Checkout') {
            steps {
                git branch: "${BRANCH_NAME}",
                    url: 'https://github.com/Ayesha497-creator/Laravelproject.git',
                    credentialsId: 'github-token'
            }
        }

        stage('Install & Build Assets') {
            steps {
                dir("${PROJECT_DIR}") {
                    echo "📦 Installing npm dependencies..."
                    sh "npm install --legacy-peer-deps"

                    echo '🎨 Building Laravel Mix assets...'
                    sh "npm run production"
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
                    def DEPLOY_DIR = (BRANCH_NAME == "main") ?
                        "/var/www/demo1.flowsoftware.ky/main" :
                        "/var/www/demo1.flowsoftware.ky/${BRANCH_NAME}"

                    echo "🚀 Deploying ${BRANCH_NAME} → ${DEPLOY_DIR}"

                    sshagent(['jenkins-deploy-key']) {
                        sh """
                            ssh -o StrictHostKeyChecking=no ubuntu@13.61.68.173 '
                                sudo rm -rf ${DEPLOY_DIR} &&
                                sudo mkdir -p ${DEPLOY_DIR} &&
                                sudo chown -R ubuntu:ubuntu ${DEPLOY_DIR}
                            '

                            rsync -av --exclude='node_modules' \
                                      --exclude='.git' \
                                      --exclude='storage/framework/sessions' \
                                      ${PROJECT_DIR}/ ubuntu@13.61.68.173:${DEPLOY_DIR}/

                            scp ${ENV_FILE} ubuntu@13.61.68.173:${DEPLOY_DIR}/.env
                        """
                    }
                }
            }
        }

    }

    post {
        success {
            echo "✅ Deployment Successful"
            // Slack notification enabled
            sh """
                FULL_SLACK_WEBHOOK=\$SLACK_WEBHOOK_PART1\$SLACK_WEBHOOK_PART2\$SLACK_WEBHOOK_PART3
                curl -X POST -H 'Content-type: application/json' --data '{
                    "text": "✅ *Deployment Successful!*\nBranch: ${BRANCH_NAME}\nProject: Laravelproject"
                }' \$FULL_SLACK_WEBHOOK
            """
        }

        failure {
            echo "❌ Deployment Failed"
            // Slack notification enabled
            sh """
                FULL_SLACK_WEBHOOK=\$SLACK_WEBHOOK_PART1\$SLACK_WEBHOOK_PART2\$SLACK_WEBHOOK_PART3
                curl -X POST -H 'Content-type: application/json' --data '{
                    "text": "❌ *Deployment Failed!*\nBranch: ${BRANCH_NAME}\nPlease check Jenkins logs."
                }' \$FULL_SLACK_WEBHOOK
            """
        }
    }
}
