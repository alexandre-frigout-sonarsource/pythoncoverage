node {
  stage('SonarQube analysis') {
    sh "ls -lrt"
    def scannerHome = tool 'SonarScanner 4.0';
    withSonarQubeEnv('sq') { // If you have configured more than one global server connection, you can specify its name
      sh "${scannerHome}/bin/sonar-scanner -Dsonar.login=admin -Dsonar.password=admin1 -X"
    }
  }
}

