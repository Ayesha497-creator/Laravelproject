pipeline {
    agent any

    environment {
        REMOTE_USER = "ubuntu"
        REMOTE_HOST = "13.61.68.173"
        PROJECT = "laravel"
        ENV_NAME = "${BRANCH_NAME}"         
        SLACK_WEBHOOK = credentials('SLACK_WEBHOOK')
    }

    stages {  // <--- Ye missing tha
        stage('SonarQube Analysis') {
            steps {
                script { env.ACTUAL_STAGE = "SonarQube Analysis" }
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
                script { env.ACTUAL_STAGE = "Quality Gate" }
                timeout(time: 1, unit: 'HOURS') {
                    waitForQualityGate abortPipeline: true
                }
            }
        }

        stage('Deploy') {
            when {
                expression { 
                    return currentBuild.result == null || currentBuild.result == 'SUCCESS' 
                }
            }
            steps {
                script {
                    env.ACTUAL_STAGE = "Deploy" 
                    def PROJECT_DIR = "/var/www/html/${ENV_NAME}/${PROJECT}"

                    sshagent(['jenkins-deploy-key']) {
                        sh """
                        ssh -o StrictHostKeyChecking=no ${REMOTE_USER}@${REMOTE_HOST} '
                            set -e
                            cd ${PROJECT_DIR}
                            echo "Gate Passed! Starting Deployment for ${PROJECT}..."

                            git pull origin ${ENV_NAME}

                            if [ "${PROJECT}" = "vue" ] || [ "${PROJECT}" = "next" ]; then
                                npm run build 
                                if [ "${PROJECT}" = "next" ]; then
                                    pm2 restart "Next-${ENV_NAME}"
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
    } // <--- Stages block yahan band ho raha hai

    post {
        success {
            sh "curl -X POST -H 'Content-type: application/json' --data '{\"text\":\"✅ *${PROJECT}* → *${ENV_NAME}* Deployed Successfully! 🚀\"}' $SLACK_WEBHOOK"
        }
        failure {
            script {
                def failedAt = env.ACTUAL_STAGE ?: "Pipeline Initialization"
                sh """
                curl -X POST -H 'Content-type: application/json' \
                --data '{"text":"❌ *${PROJECT}* → *${ENV_NAME}* Deployment Failed! \\n⚠️ Failed at Stage: *${failedAt}*"}' \
                ${SLACK_WEBHOOK}
                """
            }
        }
    }
}
