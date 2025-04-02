# Conectando ao GitHub

Siga estas etapas para conectar seu repositório local ao GitHub:

## 1. Crie um novo repositório no GitHub

1. Acesse [GitHub](https://github.com) e faça login na sua conta
2. Clique no botão "+" no canto superior direito e selecione "New repository"
3. Dê um nome ao seu repositório (por exemplo, "HelpExpert")
4. Adicione uma descrição: "Landing page e sistema de gestão de leads para o HelpExpert"
5. Mantenha o repositório como público ou privado, conforme sua preferência
6. **NÃO** inicialize o repositório com README, .gitignore ou licença
7. Clique em "Create repository"

## 2. Conecte seu repositório local ao GitHub

Após criar o repositório, o GitHub mostrará instruções. Copie e execute os seguintes comandos no terminal:

```bash
# Conectar ao repositório remoto (substitua 'SEU_USUARIO' pelo seu nome de usuário do GitHub)
git remote add origin https://github.com/SEU_USUARIO/HelpExpert.git

# Enviar o código para o GitHub
git push -u origin master
```

Você precisará inserir seu nome de usuário e senha do GitHub (ou token de acesso pessoal se tiver a autenticação de dois fatores ativada).

## 3. Verificar se deu certo

Após o push, acesse o link do seu repositório no GitHub para verificar se todos os arquivos foram enviados corretamente.

## 4. Configurando o .gitignore

Observe que configuramos o arquivo `.gitignore` para excluir arquivos sensíveis como:
- `config.php` (configurações do banco de dados)
- `includes/smtp_config.php` (configurações de email)

Criamos versões de exemplo desses arquivos (`config.example.php` e `smtp_config.example.php`) que foram incluídas no repositório para servir como referência.

## 5. Próximos passos

Após conectar ao GitHub, você pode:

1. Adicionar colaboradores ao projeto
2. Configurar GitHub Pages para hospedar a landing page
3. Configurar integração contínua/implantação contínua (CI/CD)
4. Adicionar issues para rastrear tarefas e bugs

## Comandos Git úteis para o dia a dia

```bash
# Ver status das alterações
git status

# Adicionar alterações
git add .

# Fazer commit
git commit -m "Descrição das alterações"

# Enviar alterações para o GitHub
git push

# Obter alterações do GitHub
git pull

# Criar uma nova branch
git checkout -b nome-da-branch

# Mudar de branch
git checkout nome-da-branch
```
