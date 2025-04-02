# HelpExpert - Landing Page e Sistema de Gestão de Leads

Landing page moderna e responsiva para o HelpExpert, um sistema de atendimento multicanal baseado no Whaticket, com painel administrativo completo para gestão de leads.

## Visão Geral

Este projeto consiste em uma landing page completa para o produto HelpExpert, desenvolvida com HTML, CSS e JavaScript, utilizando o framework Bootstrap 5 para garantir responsividade e uma experiência de usuário consistente em todos os dispositivos. Além disso, inclui um painel administrativo com sistema Kanban para gestão de leads.

## Tecnologias Utilizadas

- HTML5
- CSS3
- JavaScript
- PHP 7.4+
- MySQL
- Bootstrap 5
- Biblioteca de ícones Bootstrap Icons
- Animate On Scroll (AOS) para animações
- PHPMailer para envio de emails
- PDO para conexão segura com banco de dados

## Estrutura do Projeto

```
LP_HelpExpert/
├── admin/                  # Painel administrativo
│   ├── index.php           # Dashboard principal com Kanban
│   ├── login.php           # Página de login
│   ├── setup_kanban_columns.php # Configuração das colunas do Kanban
│   └── js/                 # Scripts do painel administrativo
├── css/
│   └── styles.css          # Estilos personalizados
├── database/               # Scripts de migração do banco de dados
│   └── migrations/         # Migrações individuais
├── includes/               # Arquivos de inclusão
│   ├── mailer.php          # Funções para envio de emails
│   └── smtp_config.php     # Configurações SMTP
├── js/
│   └── script.js           # Scripts personalizados
├── img/                    # Diretório de imagens
├── vendor/                 # Dependências (gerado pelo Composer)
├── composer.json           # Configuração do Composer
├── config.php              # Configuração do banco de dados
├── db_setup.php            # Script para configuração inicial do banco
├── index.html              # Página principal
├── process_contact.php     # Processamento do formulário de contato
├── process_lead.php        # Processamento do formulário de lead
└── README.md               # Documentação
```

## Paleta de Cores

- **Cores Principais**:
  - Azul Petróleo: `#007498`
  - Verde Água: `#00aba5`

- **Cores Complementares**:
  - Branco: `#FFFFFF`
  - Cinza Claro: `#F5F7F9`
  - Cinza Médio: `#E0E5E9`
  - Cinza Escuro: `#3A4A5A`
  - Cinza Suave: `#7A8A9A`

## Tipografia

- **Títulos**: Archivo
- **Corpo de texto**: Nunito

## Seções da Página

1. **Header/Navegação**: Menu de navegação com links para todas as seções
2. **Hero**: Apresentação principal do produto com chamada para ação
3. **Benefícios**: Principais vantagens do HelpExpert
4. **Recursos**: Detalhamento das funcionalidades principais
5. **Como Funciona**: Passo a passo de utilização do produto
6. **Planos e Preços**: Tabela de preços com diferentes opções
7. **Depoimentos**: Feedback de clientes
8. **FAQ**: Perguntas frequentes
9. **Contato**: Formulário e informações de contato
10. **CTA**: Chamada final para ação
11. **Footer**: Rodapé com links e informações adicionais

## Funcionalidades JavaScript

- Scroll suave para navegação interna
- Animações ao rolar a página
- Validação de formulário de contato
- Botão "Voltar ao topo"
- Destaque automático de links de navegação
- Sistema de drag-and-drop para o Kanban de leads

## Como Usar

1. Clone este repositório
2. Configure o servidor web (Apache/Nginx) e o banco de dados MySQL
3. Execute o script `db_setup.php` para criar o banco de dados e tabelas necessárias
4. Instale as dependências do Composer:
   ```
   composer install
   ```
5. Configure as credenciais SMTP no arquivo `includes/smtp_config.php`
6. Abra o arquivo `index.html` em seu navegador para visualizar o site
7. Acesse o painel administrativo em `/admin/login.php` (credenciais padrão: admin/helpexpert2025)

## Configuração SMTP para Envio de Emails

Para habilitar o envio de emails, é necessário configurar corretamente as credenciais SMTP no arquivo `includes/smtp_config.php`:

1. **Servidor SMTP**: Defina o servidor SMTP do seu provedor de email (ex: smtp.gmail.com, smtp.office365.com)
2. **Credenciais**: Insira seu email e senha (para Gmail, recomenda-se usar uma "Senha de App")
3. **Porta e Criptografia**: Geralmente 587 (TLS) ou 465 (SSL)
4. **Emails de Remetente e Administrador**: Configure os emails que serão usados como remetente e para receber notificações

Exemplo de configuração para Gmail:
```php
$smtp_config = [
    'host' => 'smtp.gmail.com',
    'username' => 'seu-email@gmail.com',
    'password' => 'sua-senha-de-app',
    'port' => 587,
    'encryption' => 'tls',
    'from_email' => 'contato@helpexpert.com.br',
    'from_name' => 'HelpExpert',
    'reply_to' => 'contato@helpexpert.com.br',
    'admin_emails' => ['admin@helpexpert.com.br']
];
```

### Notas sobre Segurança SMTP

- Para Gmail: É necessário habilitar a verificação em duas etapas e criar uma "Senha de App" específica
- Para servidores corporativos: Verifique com o administrador de TI as configurações corretas
- Nunca compartilhe suas credenciais SMTP em repositórios públicos

## Funcionalidades do Backend

- **Banco de Dados**: Armazenamento de contatos e leads em MySQL
- **Envio de Emails**: Notificações automáticas para administradores e confirmações para clientes
- **Painel Administrativo**: Visualização e gerenciamento de contatos e leads
- **Sistema Kanban**: Gerenciamento visual de leads com colunas personalizáveis
- **Segurança**: Proteção com login e senha para o painel administrativo, hash de senhas e proteção CSRF

## Sistema Kanban

O HelpExpert inclui um sistema Kanban completo para gerenciamento de leads:

- **Colunas padrão**: Novo, Contato, Negociação, Fechado, Perdido
- **Colunas personalizadas**: Possibilidade de criar novas colunas conforme necessidade
- **Drag-and-drop**: Movimentação intuitiva de leads entre colunas
- **Contadores**: Visualização rápida da quantidade de leads em cada coluna
- **Cores personalizadas**: Cada coluna pode ter sua própria cor para melhor identificação visual

## Próximos Passos

- Adicionar imagens reais do produto
- Implementar integração com sistema de envio de formulário
- Adicionar métricas de analytics
- Otimizar para SEO
- Adicionar funcionalidades de exportação de dados
- Implementar sistema de notificações em tempo real

## Autor

Desenvolvido por [Seu Nome] para HelpExpert.

## Licença

Todos os direitos reservados 2025 HelpExpert.
