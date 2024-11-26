# Dockerfile para Next.js
FROM node:20

# Define o diretório de trabalho
WORKDIR /usr/src/app

# Copia os arquivos do projeto
COPY . .

# Instala as dependências
RUN npm install

CMD ["npm", "run", "dev"]
 
