pipeline {
    agent any

    tools {
        nodejs 'nodejs'
    }

    environment {
        PROJECT_DIR = "${WORKSPACE}/Laravelproject"
        DEPLOY_DIR = "/var/www/demo1.flowsoftware.ky/${BRANCH_NAME}"
        ENV_FILE = "${PROJECT_DIR}/.env"
      SLACK_WEBHOOK = "https://hooks.slack.com/services/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"

    }

    stages {
        stage('Checkout') {
            steps {
                dir("${PROJECT_DIR}") {
                    git branch: "${BRANCH_NAME}",
                        url: 'https://github.com/Ayesha497-creator/Laravelproject.git',
                        credentialsId: 'github-token'
                }
            }
        }

        stage('Build') {
            steps {
                dir("${PROJECT_DIR}") {
                    echo "Building assets..."
                    sh 'npm run prod || true'
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
        echo "Deploying to server..."
        sshagent(['jenkins-deploy-key']) {
            sh """
                ssh -o StrictHostKeyChecking=no ubuntu@13.61.68.173 'sudo mkdir -p ${DEPLOY_DIR} && sudo chown -R ubuntu:ubuntu ${DEPLOY_DIR}'
                # Rsync vendor folder, ignore if it doesn't exist
                rsync -av --ignore-missing-args ${PROJECT_DIR}/vendor/ ubuntu@13.61.68.173:${DEPLOY_DIR}/vendor/ || true
                # Rsync rest of the project excluding vendor
                rsync -av --exclude='vendor' ${PROJECT_DIR}/ ubuntu@13.61.68.173:${DEPLOY_DIR}/
                scp ${ENV_FILE} ubuntu@13.61.68.173:${DEPLOY_DIR}/.env
            """
        }
    }
}


    }

    post {
        success {
            echo "✅ Deployment Successful for branch: ${BRANCH_NAME}"
            sh """
                curl -X POST -H 'Content-type: application/json' --data '{
                    "text": "✅ *Deployment Successful!*\nBranch: ${BRANCH_NAME}\nProject: Laravelproject"
                }' ${SLACK_WEBHOOK}
            """
        }

        failure {
            echo "❌ Build or Deploy Failed for branch: ${BRANCH_NAME}"
            sh """
                curl -X POST -H 'Content-type: application/json' --data '{
                    "text": "❌ *Deployment Failed!*\nBranch: ${BRANCH_NAME}\nPlease check Jenkins logs."
                }' ${SLACK_WEBHOOK}
            """
        }
    }
}
