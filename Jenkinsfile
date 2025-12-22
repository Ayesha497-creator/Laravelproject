pipeline {
    agent any

    environment {
        REMOTE_USER = "ubuntu"
        REMOTE_HOST = "13.61.68.173"
        PROJECT = "laravel"
        ENV_NAME = "${env.BRANCH_NAME}"
        SLACK_WEBHOOK = credentials('SLACK_WEBHOOK')
    }

    stages {
        /* Commented for testing deployment speed
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
                                error "Quality Gate Failed"
                            }
                        }
                    } catch (e) {
                        env.FAILURE_MSG = "Quality Gate Failed"
                        error "Quality Gate Failed"
                    }
                }
            }
        }
        */

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
                            echo "Starting Deployment for ${PROJECT} in ${ENV_NAME} environment..."

                            git pull origin ${ENV_NAME}

                            if [ "${PROJECT}" = "vue" ] || [ "${PROJECT}" = "Next" ]; then
                                npm run build
                                if [ "${PROJECT}" = "Next" ]; then
                                    pm2 restart "Next-${ENV_NAME}" || pm2 start npm --name "Next-${ENV_NAME}" -- start
                                    pm2 save
                                fi
                            elif [ "${PROJECT}" = "laravel" ]; then
                                php artisan optimize
                                php artisan config:cache
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
            script {
                def finalStage = env.FAILURE_MSG ?: "Initial Setup"
                sh """
                curl -X POST -H 'Content-type: application/json' \
                --data '{"text":"❌ *${PROJECT}* → *${ENV_NAME}* Failed at: *${finalStage}*"}' \
                ${SLACK_WEBHOOK}
                """
            }
        }
    }
}
