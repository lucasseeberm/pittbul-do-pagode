# Partimos de uma imagem base oficial do Jenkins para agentes
FROM jenkins/inbound-agent:latest

# Define um argumento que podemos passar durante o build
ARG DOCKER_GID

# Trocamos para o usuário root para poder instalar pacotes
USER root

# ---- INÍCIO DA ALTERAÇÃO FINAL ----
# Adiciona o usuário 'jenkins' ao grupo correto para dar permissão ao Docker
RUN if [ -n "$DOCKER_GID" ]; then \
      # Se o GID for 0 (root no Docker Desktop), adiciona jenkins ao grupo root
      if [ "$DOCKER_GID" = "0" ]; then \
        adduser jenkins root; \
      # Senão, cria o grupo 'docker' com o GID passado e adiciona jenkins
      else \
        addgroup --gid ${DOCKER_GID} docker && \
        adduser jenkins docker; \
      fi; \
    fi
# ---- FIM DA ALTERAÇÃO FINAL ----

# Instala o Docker Client e o Kubectl (o restante do arquivo continua igual)
RUN apt-get update && \
    apt-get install -y apt-transport-https ca-certificates curl gnupg && \
    install -m 0755 -d /etc/apt/keyrings && \
    curl -fsSL https://download.docker.com/linux/debian/gpg | gpg --dearmor -o /etc/apt/keyrings/docker.gpg && \
    chmod a+r /etc/apt/keyrings/docker.gpg && \
    echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/debian $(. /etc/os-release && echo "$VERSION_CODENAME") stable" | tee /etc/apt/sources.list.d/docker.list > /dev/null && \
    curl -LO "https://dl.k8s.io/release/$(curl -L -s https://dl.k8s.io/release/stable.txt)/bin/linux/amd64/kubectl" && \
    install -o root -g root -m 0755 kubectl /usr/local/bin/kubectl && \
    apt-get update && \
    apt-get install -y docker-ce-cli && \
    apt-get clean

# Volta para o usuário padrão do Jenkins
USER jenkins