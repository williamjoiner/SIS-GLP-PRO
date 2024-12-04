# Sistema de Vendas Web

Sistema de gerenciamento de vendas com controle de usuários, clientes, produtos e entregadores.

## Requisitos do Sistema

- PHP 7.4 ou superior
- MySQL 5.7 ou superior
- Apache com mod_rewrite habilitado

## Instalação

1. Faça upload dos arquivos para seu servidor web
2. Importe o arquivo `database/schema.sql` para seu banco de dados MySQL
3. Configure as credenciais do banco de dados em `config/database.php`
4. Certifique-se que o Apache mod_rewrite está habilitado
5. Defina as permissões corretas nas pastas:
   ```
   chmod 755 -R /seu-diretorio
   chmod 777 -R /seu-diretorio/uploads (se existir)
   ```

## Acesso Inicial

- **Usuário:** admin
- **Senha:** Master1

## Estrutura do Sistema

- `/auth` - Sistema de autenticação
- `/config` - Configurações do sistema
- `/modules` - Módulos do sistema (clientes, produtos, etc)
- `/api` - Endpoints da API
- `/assets` - Arquivos CSS, JS e imagens

## Segurança

- Senhas criptografadas com bcrypt
- Proteção contra SQL Injection usando PDO
- Controle de acesso baseado em funções
- Headers de segurança configurados
- Proteção contra listagem de diretórios

## Suporte

Em caso de dúvidas ou problemas, entre em contato com o suporte técnico.
