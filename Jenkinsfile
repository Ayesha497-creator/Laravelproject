pipeline {
    agent any

    environment {
        REMOTE_USER = "ubuntu"
        REMOTE_HOST = "13.61.68.173"
        PROJECT = "laravel"
        ENV_NAME = "${BRANCH_NAME}"         
        SLACK_WEBHOOK = credentials('SLACK_WEBHOOK')
        // Hum sirf aik variable use karenge jo built-in hota hai lekin hum 
        // usay har stage ke start mein stage ka naam de denge.
        FAILURE_MSG = "Unknown Stage" 
    }

    stages {
        stage('SonarQube Analysis') {
            steps {
                script { env.FAILURE_MSG = STAGE_NAME }
                withSonarQubeEnv('SonarQube-Server') {
                    sh """
                    export NODE_OPTIONS="--max-old-space-size=4096"
                    ${tool 'sonar-scanner'}/bin/sonar-scanner \
                        -Dsonar.projectKey=${PROJECT}-project \
                        -Dsonar.sources=. \
                        -Dsonar.javascript.node.maxspace=4096 \
                        -Dsonar.exclusions=**/node_modules/**,**/vendor/**,**/public/packages/**
                    """
                }
            }
        }

        stage("Quality Gate") {
            steps {
                script {
                    env.FAILURE_MSG = STAGE_NAME
                    try {
                        timeout(time: 1, unit: 'HOURS') {
                            def qg = waitForQualityGate()
                            if (qg.status != 'OK') {
                                env.FAILURE_MSG = "Quality Gate (${qg.status})"
                                error "Quality Gate Failed"
                            }
                        }
                    } catch (e) {
                        // Agar sonar fail hua toh message update ho chuka hoga
                        error env.FAILURE_MSG
                    }
                }
            }
        }

        stage('Deploy') {
            when { expression { return currentBuild.result == null || currentBuild.result == 'SUCCESS' } }
            steps {
                script {
                    env.FAILURE_MSG = STAGE_NAME
                    def PROJECT_DIR = "/var/www/html/${ENV_NAME}/${PROJECT}"
                    sshagent(['jenkins-deploy-key']) {
                        sh """
                        ssh -o StrictHostKeyChecking=no ${REMOTE_USER}@${REMOTE_HOST} '
                            set -e
                            cd ${PROJECT_DIR}
                            echo "Starting Deployment for ${PROJECT}..."
                            git pull origin ${ENV_NAME}

                            if [ "${PROJECT}" = "vue" ] || [ "${PROJECT}" = "next" ]; then
                                npm install && npm run build 
                                if [ "${PROJECT}" = "next" ]; then
                                    pm2 restart "Next-${ENV_NAME}" || pm2 start npm --name "Next-${ENV_NAME}" -- start
                                    pm2 save
                                fi
                            elif [ "${PROJECT}" = "laravel" ]; then
                                php artisan optimize
                            fi
                        '
                        """
                    }
                }
            }
        }
    }

    post {
        success {
            sh "curl -X POST -H 'Content-type: application/json' --data '{\"text\":\"✅ *${PROJECT}* → *${ENV_NAME}* Deployed Successfully! 🚀\"}' $SLACK_WEBHOOK"
        }
        failure {
            // Sirf aik post block jo LAST failure message uthayega
            sh """
            curl -X POST -H 'Content-type: application/json' \
            --data '{"text":"❌ *${PROJECT}* → *${ENV_NAME}* Failed at: *${env.FAILURE_MSG}*"}' \
            ${SLACK_WEBHOOK}
            """
        }
    }
}
