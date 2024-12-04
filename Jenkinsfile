pipeline {
    agent any
    environment {
		PROJECT_NAME = "fitalmx"
		PROJECT_DIR = "/var/project/paymoney"
		PROJECT_RUN = "/var/project/paymoney"
		DEPLOY_ENV = "backend"
        GIT_REPO_CREDS = "merehead_gitlab_service_user"
		GIT_REPO_TO_BUILD ="git@gitlab.com:merehead/fitalmx/fitalmx.git"
        GIT_BRANCH_TO_BUILD_DEV = "development"
        SERVER_IP_DEV = "fitalmx.corp.merehead.xyz"
    }
	stages {

	    stage('Clone repository development') {
			when {
                branch 'development'
            }
            steps {
                git branch: "${GIT_BRANCH_TO_BUILD_DEV}", url: "${GIT_REPO_TO_BUILD}", credentialsId: "${GIT_REPO_CREDS}"
				script {
					env.GIT_SHA = sh(returnStdout: true, script: "git rev-parse --short HEAD").replaceAll("\\s","")
					sh 'ls -la'
					sh 'pwd .git'
				}
            }
        }

		stage ('Deploy_development') {
			when {
                branch 'development'
            }
		    steps {
		        sshagent(credentials : ['merehead_ssh_key_for_servers']){
                    sh 'rsync -avz --exclude ".git*" --exclude ".env*" --exclude ".vscode*" -D -e "ssh" ${WORKSPACE}/. root@${SERVER_IP_DEV}:${PROJECT_DIR} -r'
                    sh 'ssh -o StrictHostKeyChecking=no root@${SERVER_IP_DEV} "chown -R www-data:www-data ${PROJECT_DIR}"'
		        }
            }
        }

		stage ('Reload_development') {
			when {
                branch 'development'
            }
            steps {
                sshagent(credentials : ['merehead_ssh_key_for_servers']){
                    sh 'ssh -o StrictHostKeyChecking=no root@${SERVER_IP_DEV} "cd ${PROJECT_RUN}; systemctl restart apache2"'
                }

	        }
        }

		stage ('Workspace_Clean_Up') {
            steps {
				script {
                    cleanWs()
                }
	        }
        }
    }
}
