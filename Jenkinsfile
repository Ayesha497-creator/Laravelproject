pipeline {
    agent any

    environment {
        REMOTE_USER = "ubuntu"
        REMOTE_HOST = "13.61.68.173"
        PROJECT     = "laravel"
        ENV_NAME    = "${BRANCH_NAME}"
        SLACK_WEBHOOK = credentials('SLACK_WEBHOOK') 
        // 1. Ek global variable define karein
        CURRENT_STAGE = "Initialization"
    }

    stages {  
        stage('SonarQube Analysis') {
            steps {
                script { env.CURRENT_STAGE = "SonarQube Analysis" } // Update stage name
                withSonarQubeEnv('SonarQube-Server') {
                    sh """${tool 'sonar-scanner'}/bin/sonar-scanner \
                        -Dsonar.projectKey=${PROJECT}-project \
                        -Dsonar.sources=. \
                        -Dsonar.exclusions=vendor/**,node_modules/**,public/packages/**,storage/**,bootstrap/cache/**,resources/assets/vendor/**
                    """
                }
            }
        }

        stage("Quality Gate") {
            steps {
                script { env.CURRENT_STAGE = "Quality Gate" } // Update stage name
                timeout(time: 1, unit: 'HOURS') {
                    waitForQualityGate abortPipeline: true
                }
            }
        }

        stage('Deploy') {
            steps {
                script { 
                    env.CURRENT_STAGE = "Deploy" // Update stage name
                    def PROJECT_DIR = "/var/www/html/${ENV_NAME}/${PROJECT}"
                    
                    sshagent(['jenkins-deploy-key']) {
                        sh """
                            ssh -o StrictHostKeyChecking=no ${REMOTE_USER}@${REMOTE_HOST} '
                                set -e
                                cd ${PROJECT_DIR}
                                echo "Gate Passed! Starting Deployment for ${PROJECT}..."
                                git pull origin ${ENV_NAME}
                                if [ "${PROJECT}" = "vue" ] || [ "${PROJECT}" = "next" ]; then
                                    npm run build -- --mode ${ENV_NAME}
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
    } 

    post {
        success {
            sh """
            curl -X POST -H 'Content-type: application/json' \
            --data '{"text":"✅ *${PROJECT}* → *${ENV_NAME}* Deployed Successfully! 🚀"}' \
            $SLACK_WEBHOOK
            """
        }
        failure {
            script {
                // 2. Ab yahan wahi stage aayegi jahan error aaya tha
                sh """
                curl -X POST -H 'Content-type: application/json' \
                --data '{"text":"❌ *${PROJECT}* → *${ENV_NAME}* Deployment Failed! \\n⚠️ Failed at Stage: *${env.CURRENT_STAGE}*"}' \
                $SLACK_WEBHOOK
                """
            }
        }
    }
}
