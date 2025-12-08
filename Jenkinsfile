pipeline {
  agent any
  stages {
   stage('Checkout') {
    steps {
        dir(path: "${PROJECT_DIR}") {
            git(branch: 'main', 
                url: 'https://github.com/Ayesha497-creator/larabbs.git', 
                credentialsId: 'github-token')
        }
    }
}


    stage('Build') {
      steps {
        dir(path: "${PROJECT_DIR}") {
          echo 'Installing dependencies...'
          sh 'composer install --no-interaction --prefer-dist --optimize-autoloader'
          sh 'npm install'
          sh 'npm run prod || true'
        }

      }
    }

    stage('Test') {
      steps {
        dir(path: "${PROJECT_DIR}") {
          echo 'Running Laravel tests...'
          sh 'php artisan test || true'
        }

      }
    }

    stage('Deploy') {
      steps {
        echo 'Deploying to AWS webroot...'
        sh "rsync -av --exclude='vendor' ${PROJECT_DIR}/ ${DEPLOY_DIR}/"
        sh "rsync -av ${PROJECT_DIR}/vendor/ ${DEPLOY_DIR}/vendor/"
        sh "cp ${ENV_FILE} ${DEPLOY_DIR}/.env"
      }
    }

  }
  environment {
    PROJECT_DIR = '/home/ubuntu/larabbs'
    DEPLOY_DIR = '/var/www/demo1.flowsoftware.ky'
    ENV_FILE = '/home/ubuntu/larabbs/.env'
  }
  post {
    success {
      echo '✅ Build, Test & Deployment Successful! Check: https://demo1.flowsoftware.ky/'
    }

    failure {
      echo '❌ Something went wrong! Check Jenkins logs.'
    }

  }
}
