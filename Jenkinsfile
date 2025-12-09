pipeline {
    agent any
    tools {
        nodejs 'nodejs'
    }
environment {
        PROJECT_DIR = "${WORKSPACE}/Laravelproject"
        DEPLOY_DIR = "/var/www/demo1.flowsoftware.ky/${BRANCH_NAME}"
        ENV_FILE = "${PROJECT_DIR}/.env"
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
                        rsync -av --exclude='vendor' ${PROJECT_DIR}/ ubuntu@13.61.68.173:${DEPLOY_DIR}/
                        rsync -av ${PROJECT_DIR}/vendor/ ubuntu@13.61.68.173:${DEPLOY_DIR}/vendor/
                        scp ${ENV_FILE} ubuntu@13.61.68.173:${DEPLOY_DIR}/.env
                    """
                }
            }
        }
    }

    post {
        success {
            echo "✅ Deployment Successful for branch: ${BRANCH_NAME}"
        }
        failure {
            echo "❌ Build or Deploy Failed for branch: ${BRANCH_NAME}"
        }
    }
}
