pipeline {
    agent any

    environment {
        REMOTE_USER = "ubuntu"
        REMOTE_HOST = "13.61.68.173"
        PROJECT = "laravel"
        ENV_NAME = "${BRANCH_NAME}"
        
        // 1. Webhook ko uncomment karein
        SLACK_WEBHOOK = credentials('SLACK_WEBHOOK') 
        
        // 2. Default stage name set karein (agar shuru mein hi fail ho jaye)
        FAILED_STAGE = "Initialization" 
    }

    stages {  
        stage('SonarQube Analysis') {
            steps {
                // 3. Stage ka naam update karein
                script { env.FAILED_STAGE = "SonarQube Analysis" }
                
                withSonarQubeEnv('SonarQube-Server') {
                    sh """${tool 'sonar-scanner'}/bin/sonar-scanner \
                        -Dsonar.projectKey=laravel-project \
                        -Dsonar.sources=. \
                        -Dsonar.qualitygate.wait=true \
                        -Dsonar.exclusions=vendor/**,node_modules/**,public/packages/**,storage/**,bootstrap/cache/**,resources/assets/vendor/**
                    """
                }
            }
        }

        stage("Quality Gate") {
            steps {
                // 3. Stage ka naam update karein
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
                script {
                    // 3. Stage ka naam update karein
                    env.FAILED_STAGE = "Deploy Stage"
                    
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

    // 4. Post section ko uncomment kar diya hai
    post {
        success {
            sh """
            curl -X POST -H 'Content-type: application/json' \
            --data '{"text":"✅ ${PROJECT} → ${ENV_NAME} Deployed Successfully!"}' \
            $SLACK_WEBHOOK
            """
        }
        failure {
            // 5. Yahan ab hum 'env.FAILED_STAGE' use kar rahay hain
            sh """
            curl -X POST -H 'Content-type: application/json' \
            --data '{"text":"❌ ${PROJECT} → ${ENV_NAME} Deployment Failed!\\n⚠️ Failed at Stage: *${env.FAILED_STAGE}*"}' \
            $SLACK_WEBHOOK
            """
        }
    }
}
