
pipeline {
    agent any

    environment {
        REMOTE_USER = "ubuntu"
        REMOTE_HOST = "13.62.178.120"
        PROJECT     = "laravelproject" 
        ENV_NAME    = "${BRANCH_NAME}"         
        TEST_BRANCH = "test" 
        SLACK_WEBHOOK = credentials('SLACK_WEBHOOK')
    }

    stages {
        stage('Quality Check') {
            when { branch "${TEST_BRANCH}" }
            steps {
                script {
                    withSonarQubeEnv('SonarQube-Server') {
                        sh "${tool 'sonar-scanner'}/bin/sonar-scanner -Dsonar.projectKey=${PROJECT}-project -Dsonar.sources=. -Dsonar.exclusions=**/node_modules/**,**/vendor/**"
                    }
                    timeout(time: 10, unit: 'MINUTES') {
                        def qg = waitForQualityGate()
                        if (qg.status != 'OK') {
                            error "QUALITY_GATE_FAILED" 
                        }
                    }
                }
            }
        }

        stage('Docker Deploy') {
            steps {
                script {
                    sshagent(['jenkins-deploy-key']) {
                        sh """
                        ssh -o StrictHostKeyChecking=no ${REMOTE_USER}@${REMOTE_HOST} '
                            set -e
                            cd /var/www/html/${ENV_NAME}/${PROJECT}
                       
                            git pull origin ${ENV_NAME}

                            docker network create my_app_net || true
                            docker build -t ${PROJECT}:${ENV_NAME} .

                            docker stop ${PROJECT}-${ENV_NAME} || true
                            docker rm ${PROJECT}-${ENV_NAME} || true
                           docker run -d --name ${PROJECT}-${ENV_NAME} --network my_app_net -v /home/ubuntu/configs/${ENV_NAME}/.env:/var/www/html/.env ${PROJECT}:${ENV_NAME}
                            docker image prune -f
                        '
                        """
                    }
                }
            }
        }
    }

    post {
        failure {
            script {
                def failureType = "Deployment Stage"
                
                // Agar test branch thi aur fail hui, to zahir hai quality check hi fail hua hoga
                if (env.BRANCH_NAME == env.TEST_BRANCH) {
                     failureType = "Quality Check"
                }

                sh "curl -X POST -H 'Content-type: application/json' --data '{\"text\":\"❌ *${PROJECT}* (${ENV_NAME}) - *${failureType} Failed!*\"}' ${SLACK_WEBHOOK}"
            }
        }
        success {
            sh "curl -X POST -H 'Content-type: application/json' --data '{\"text\":\"✅ *${PROJECT}* (${ENV_NAME}) - Deployed Successfully!\"}' ${SLACK_WEBHOOK}"
        }
    }
}
