pipeline {
    agent any

    environment {
        // 1. Variable yahan define karein (Global access ke liye)
        FAILED_STAGE = "Initialization" 
        
        REMOTE_USER = "ubuntu"
        REMOTE_HOST = "13.61.68.173"
        PROJECT = "laravel"
        ENV_NAME = "${BRANCH_NAME}"
        SLACK_WEBHOOK = credentials('SLACK_WEBHOOK') 
    }

    stages {  
        stage('SonarQube Analysis') {
            steps {
                // 2. Variable update karein (env. use karke)
                script { env.FAILED_STAGE = "SonarQube Analysis" }
                
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
                script { env.FAILED_STAGE = "Quality Gate" }
                
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
                script { env.FAILED_STAGE = "Deploy Stage" }
                
                // Variable for cleaner script
                script {
                   PROJECT_DIR = "/var/www/html/${ENV_NAME}/${PROJECT}"
                }

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

    post {
        success {
            sh """
            curl -X POST -H 'Content-type: application/json' \
            --data '{"text":"✅ *${PROJECT}* → *${ENV_NAME}* Deployed Successfully! 🚀"}' \
            $SLACK_WEBHOOK
            """
        }
        failure {
            // 3. Yahan Global variable access hoga
            sh """
            curl -X POST -H 'Content-type: application/json' \
            --data '{"text":"❌ *${PROJECT}* → *${ENV_NAME}* Deployment Failed! \\n⚠️ Failed at Stage: *${env.FAILED_STAGE}*"}' \
            $SLACK_WEBHOOK
            """
        }
    }
}
