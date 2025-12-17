// 1. Variable ko Pipeline se BAHAR define kiya taake ye update ho sakay
def FAILED_STAGE = "Initialization"

pipeline {
    agent any

    environment {
        REMOTE_USER = "ubuntu"
        REMOTE_HOST = "13.61.68.173"
        PROJECT = "laravel"
        ENV_NAME = "${BRANCH_NAME}"
        
        // Webhook setup
        SLACK_WEBHOOK = credentials('SLACK_WEBHOOK') 
    }

    stages {  
        stage('SonarQube Analysis') {
            steps {
                script { 
                    // 2. Yahan variable update hoga (bina 'env.' ke)
                    FAILED_STAGE = "SonarQube Analysis" 
                }
                
                withSonarQubeEnv('SonarQube-Server') {
                    sh """${tool 'sonar-scanner'}/bin/sonar-scanner \
                        -Dsonar.projectKey=${PROJECT}-project \
                        -Dsonar.sources=. \
                        -Dsonar.qualitygate.wait=true \
                        -Dsonar.exclusions=vendor/**,node_modules/**,public/packages/**,storage/**,bootstrap/cache/**,resources/assets/vendor/**
                    """
                }
            }
        }

        stage("Quality Gate") {
            steps {
                script { FAILED_STAGE = "Quality Gate" }
                
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
                    FAILED_STAGE = "Deploy Stage"
                    
                    def PROJECT_DIR = "/var/www/html/${ENV_NAME}/${PROJECT}"

                    sshagent(['jenkins-deploy-key']) {
                        sh """
                        ssh -o StrictHostKeyChecking=no ${REMOTE_USER}@${REMOTE_HOST} '
                            set -e
                            
                            # Directory check
                            if [ ! -d "${PROJECT_DIR}" ]; then
                                echo "Directory ${PROJECT_DIR} does not exist!"
                                exit 1
                            fi

                            cd ${PROJECT_DIR}
                            echo "Gate Passed! Starting Deployment for ${PROJECT}..."

                            git pull origin ${ENV_NAME}

                            if [ "${PROJECT}" = "vue" ] || [ "${PROJECT}" = "next" ]; then
                                npm install
                                npm run build -- --mode ${ENV_NAME}
                                if [ "${PROJECT}" = "next" ]; then
                                    pm2 restart "Next-${ENV_NAME}"
                                    pm2 save
                                fi
                            elif [ "${PROJECT}" = "laravel" ]; then
                                composer install --no-interaction --prefer-dist --optimize-autoloader
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
            // 3. Ab ye updated 'FAILED_STAGE' use karega (bina 'env.' ke)
            sh """
            curl -X POST -H 'Content-type: application/json' \
            --data '{"text":"❌ *${PROJECT}* → *${ENV_NAME}* Deployment Failed! \\n⚠️ Failed at Stage: *${FAILED_STAGE}* \\n🔗 <${env.BUILD_URL}console|Check Logs Here>"}' \
            $SLACK_WEBHOOK
            """
        }
    }
}
