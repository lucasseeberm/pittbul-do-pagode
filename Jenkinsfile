// Jenkinsfile - Versão para Repositório Local
pipeline {
    agent any

    stages {
        stage('Checkout') {
            steps {
                echo 'Baixando o código do repositório...'
                // IMPORTANTE: Substitua pela URL do seu repositório Git
                git branch: 'main', url: 'https://github.com/lucasseeberm/pittbul-do-pagode.git'
            }
        }

        stage('Build Local Docker Images') {
            steps {
                script {
                    def services = ['usuarios', 'estoque', 'vendas']
                    for (service in services) {
                        echo "Construindo imagem local para: app-${service}"
                        // Constrói a imagem e dá uma tag com o número da build (ex: app-usuarios:1)
                        sh "docker build -t app-${service}:${env.BUILD_NUMBER} ./modulos/${service}"
                    }
                }
            }
        }

        stage('Deploy to Kubernetes') {
            steps {
                echo 'Iniciando o deploy no Kubernetes...'

                // Aplica todas as configurações de banco de dados e volumes
                sh 'kubectl apply -f 00-secrets.yaml'
                sh 'kubectl apply -f 01-volumes.yaml'
                sh 'kubectl apply -f 03-sql-configmaps.yaml'
                sh 'kubectl apply -f 02-db-deployments-services.yaml'

                echo 'Atualizando as imagens das aplicações...'

                // Atualiza o arquivo de deployment com as novas tags de imagem
                sh "sed -i 's|image: app-usuarios:latest|image: app-usuarios:${env.BUILD_NUMBER}|g' 04-app-deployments-services.yaml"
                sh "sed -i 's|image: app-estoque:latest|image: app-estoque:${env.BUILD_NUMBER}|g' 04-app-deployments-services.yaml"
                sh "sed -i 's|image: app-vendas:latest|image: app-vendas:${env.BUILD_NUMBER}|g' 04-app-deployments-services.yaml"

                // Aplica o arquivo de deployment com as imagens locais atualizadas
                sh 'kubectl apply -f 04-app-deployments-services.yaml'

                echo 'Deploy finalizado!'
            }
        }
    }
}
