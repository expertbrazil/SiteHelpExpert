# Estrutura da Aplicação HelpExpert

## Estrutura de Diretórios Proposta

```
LP_HelpExpert/
├── app/                      # Núcleo da aplicação
│   ├── config/               # Arquivos de configuração
│   │   ├── database.php      # Configuração do banco de dados
│   │   ├── mail.php          # Configuração de email
│   │   └── app.php           # Configurações gerais da aplicação
│   │
│   ├── controllers/          # Controladores da aplicação
│   │   ├── AdminController.php
│   │   ├── ContactController.php
│   │   ├── KanbanController.php
│   │   └── LeadController.php
│   │
│   ├── models/               # Modelos da aplicação
│   │   ├── Contact.php
│   │   ├── Lead.php
│   │   ├── KanbanColumn.php
│   │   └── User.php
│   │
│   ├── helpers/              # Funções auxiliares
│   │   ├── auth_helper.php
│   │   ├── form_helper.php
│   │   └── validation_helper.php
│   │
│   └── services/             # Serviços da aplicação
│       ├── MailService.php
│       └── KanbanService.php
│
├── public/                   # Arquivos públicos acessíveis pelo navegador
│   ├── index.php             # Ponto de entrada principal
│   ├── admin.php             # Ponto de entrada do painel admin
│   ├── assets/               # Recursos estáticos
│   │   ├── css/              # Arquivos CSS
│   │   ├── js/               # Arquivos JavaScript
│   │   ├── img/              # Imagens
│   │   └── fonts/            # Fontes
│   │
│   └── uploads/              # Arquivos enviados pelos usuários
│
├── database/                 # Arquivos relacionados ao banco de dados
│   ├── migrations/           # Migrações do banco de dados
│   └── seeds/                # Seeds para popular o banco de dados
│
├── resources/                # Recursos da aplicação
│   └── views/                # Arquivos de visualização
│       ├── admin/            # Views do painel administrativo
│       ├── landing/          # Views da landing page
│       ├── emails/           # Templates de email
│       └── partials/         # Componentes parciais reutilizáveis
│
├── vendor/                   # Dependências de terceiros (gerenciadas pelo Composer)
│
├── .htaccess                 # Configurações do Apache
├── composer.json             # Configuração do Composer
├── README.md                 # Documentação do projeto
└── migrate.php               # Script para executar migrações
```

## Organização do Código

### Modelo MVC (Model-View-Controller)

A estrutura proposta segue o padrão MVC:

1. **Models (app/models/)**: Representam os dados e a lógica de negócios
2. **Views (resources/views/)**: Responsáveis pela apresentação dos dados
3. **Controllers (app/controllers/)**: Gerenciam as requisições e coordenam models e views

### Fluxo da Aplicação

1. As requisições entram pelos pontos de entrada em `public/`
2. Os controladores processam as requisições
3. Os modelos interagem com o banco de dados
4. As views renderizam a resposta para o usuário

## Benefícios da Nova Estrutura

1. **Organização**: Código mais organizado e fácil de manter
2. **Separação de Responsabilidades**: Cada componente tem uma função específica
3. **Escalabilidade**: Facilita a adição de novos recursos
4. **Segurança**: Arquivos sensíveis ficam fora do diretório público
5. **Manutenção**: Facilita a localização e correção de problemas
