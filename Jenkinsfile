pipeline {
    agent any

    environment {
        REMOTE_USER = "ubuntu"
        REMOTE_HOST = "13.61.68.173"
        PROJECT     = "laravel"
        ENV_NAME    = "${BRANCH_NAME}"
        SLACK_WEBHOOK = credentials('SLACK_WEBHOOK') 
        // Isay yahan define na karein agar update nahi ho raha
    }

    stages {  
        stage('SonarQube Analysis') {
            steps {
                script { 
                    // Global environment update
                    env.ACTUAL_STAGE = "SonarQube Analysis" 
                }
                withSonarQubeEnv('SonarQube-Server') {
                    sh """${tool 'sonar-scanner'}/bin/sonar-scanner \
                        -Dsonar.projectKey=${PROJECT}-project \
                        -Dsonar.sources=. \
                        -Dsonar.exclusions=vendor/**,node_modules/**,storage/**
                    """
                }
            }
        }

        stage("Quality Gate") {
            steps {
                script { 
                    env.ACTUAL_STAGE = "Quality Gate" 
                }
                timeout(time: 1, unit: 'HOURS') {
                    waitForQualityGate abortPipeline: true
                }
            }
        }

        stage('Deploy') {
            steps {
                script { 
                    env.ACTUAL_STAGE = "Deploy" 
                    def PROJECT_DIR = "/var/www/html/${ENV_NAME}/${PROJECT}"
                    
                    sshagent(['jenkins-deploy-key']) {
                        sh "ssh -o StrictHostKeyChecking=no ${REMOTE_USER}@${REMOTE_HOST} 'cd ${PROJECT_DIR} && git pull origin ${ENV_NAME}'"
                        // ... baaki deploy logic ...
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
                // Agar ACTUAL_STAGE null hai (shuru mein hi fail ho gaya), to fallback use karein
                def failedAt = env.ACTUAL_STAGE ?: "Pipeline Initialization/Setup"
                
                sh """
                curl -X POST -H 'Content-type: application/json' \
                --data '{"text":"❌ *${PROJECT}* → *${ENV_NAME}* Deployment Failed! \\n⚠️ Failed at Stage: *${failedAt}*"}' \
                $SLACK_WEBHOOK
                """
            }
        }
    }
}
