# Slim Async - Framework Slim 4 + Swoole + MongoDB

Aplicação web assíncrona construída com Slim Framework 4, Swoole HTTP Server e MongoDB, com suporte completo a coroutines para operações não-bloqueantes.

## Descrição

Este projeto demonstra a integração entre o Slim Framework 4 e o Swoole HTTP Server, proporcionando uma arquitetura de alto desempenho para aplicações PHP com suporte a operações assíncronas através de coroutines. A aplicação utiliza MongoDB como banco de dados e implementa uma camada de infraestrutura para gerenciar conexões de forma eficiente.

## Características Principais

- **Slim Framework 4**: Microframework PHP moderno e leve para desenvolvimento de APIs
- **Swoole HTTP Server**: Servidor HTTP assíncrono de alta performance
- **MongoDB**: Banco de dados NoSQL com suporte a operações assíncronas via Swoole Coroutines
- **Coroutines**: Programação assíncrona não-bloqueante
- **PSR-7/PSR-15**: Conformidade com padrões PHP-FIG para mensagens HTTP e middlewares
- **Docker**: Ambiente completamente containerizado para desenvolvimento e produção
- **PHP 8.2+**: Utiliza recursos modernos da linguagem

## Requisitos

- PHP 8.2 ou superior
- Extensão Swoole
- Docker e Docker Compose (opcional, mas recomendado)
- Composer 2.x

## Estrutura do Projeto

```
slim-async/
├── src/
│   ├── infrastructure/
│   │   └── mongo/
│   │       └── MongoConnection.php
│   ├── routes.php
│   └── server.php
├── .env-example
├── composer.json
├── docker-compose.yml
└── Dockerfile
```

### Componentes Principais

#### `server.php`
Ponto de entrada da aplicação. Configura o servidor Swoole com:
- 4 workers para processamento paralelo
- Reinicialização automática após 5000 requisições
- Coroutines habilitadas com SWOOLE_HOOK_ALL
- Servidor HTTP escutando na porta 9501

#### `routes.php`
Define as rotas da aplicação:
- `GET /`: Rota de health check
- `GET /users`: Endpoint para consulta de usuários no MongoDB usando coroutines

#### `MongoConnection.php`
Classe de infraestrutura para gerenciar conexões com MongoDB:
- Utiliza cliente Swoole Coroutine para operações assíncronas
- Suporta configuração via variáveis de ambiente
- Fornece método para seleção de collections

## Instalação

### Usando Docker (Recomendado)

1. Clone o repositório:
```bash
git clone <repository-url>
cd slim-async
```

2. Crie um arquivo `.env` baseado no exemplo:
```bash
cp .env-example .env
```

3. Inicie os containers:
```bash
docker-compose up -d
```

A aplicação estará disponível em `http://localhost:9501`

### Instalação Manual

1. Certifique-se de que a extensão Swoole está instalada:
```bash
pecl install swoole
```

2. Instale as dependências do Composer:
```bash
composer install
```

3. Configure as variáveis de ambiente:
```bash
cp .env-example .env
# Edite o arquivo .env conforme necessário
```

4. Inicie o servidor:
```bash
composer start
# ou
php server.php
```

## Configuração

### Variáveis de Ambiente

O arquivo `.env-example` contém todas as configurações disponíveis:

**Aplicação:**
- `APP_NAME`: Nome da aplicação
- `APP_ENV`: Ambiente de execução (local, production)
- `APP_DEBUG`: Modo de debug (true/false)
- `APP_PORT`: Porta do servidor (padrão: 9501)

**MongoDB:**
- `MONGO_HOST`: Host do MongoDB (padrão: mongo)
- `MONGO_PORT`: Porta do MongoDB (padrão: 27017)
- `MONGO_DB`: Nome do banco de dados (padrão: meubanco)
- `MONGO_USERNAME`: Usuário do MongoDB (opcional)
- `MONGO_PASSWORD`: Senha do MongoDB (opcional)

**Swoole:**
- `SWOOLE_WORKER_NUM`: Número de workers (padrão: 4)
- `SWOOLE_MAX_REQUEST`: Máximo de requisições por worker antes de reiniciar (padrão: 5000)
- `SWOOLE_COROUTINE_HOOKS`: Hooks de coroutine (padrão: ALL)

## Uso

### Endpoints Disponíveis

#### Health Check
```bash
curl http://localhost:9501/
```

Resposta:
```
Olá! Slim + Swoole está rodando 
```

#### Listar Usuários
```bash
curl http://localhost:9501/users
```

Resposta:
```json
{
  "status": "consulta enviada"
}
```

### Adicionando Novas Rotas

Para adicionar novas rotas, edite o arquivo `src/routes.php`:

```php
$app->get('/nova-rota', function (Request $request, Response $response) {
    // Sua lógica aqui
    return $response;
});
```

### Trabalhando com Coroutines

Exemplo de operação assíncrona:

```php
go(function () {
    $mongo = new MongoConnection();
    $collection = $mongo->collection('users');
    $result = $collection->find([]);
    // Processar resultado
});
```

## Arquitetura

### Modelo de Concorrência

O Swoole utiliza um modelo de workers + coroutines:
- **Workers**: Processos que tratam requisições
- **Coroutines**: Operações assíncronas dentro de cada worker
- **Event Loop**: Gerencia operações I/O não-bloqueantes

### Fluxo de Requisição

1. Cliente envia requisição HTTP
2. Swoole recebe e encaminha para um worker disponível
3. Worker processa usando Slim Framework
4. Operações I/O (MongoDB, etc.) executam em coroutines
5. Resposta é enviada ao cliente
6. Worker fica disponível para próxima requisição

## Performance

### Configurações de Otimização

- **Worker Number**: Ajuste `worker_num` baseado nos núcleos de CPU
- **Max Request**: Configure `max_request` para evitar memory leaks
- **Coroutine Hooks**: Use `SWOOLE_HOOK_ALL` para máxima compatibilidade
- **Autoloader**: Composer configurado com `optimize-autoloader`

### Benchmarks

O Swoole pode processar milhares de requisições por segundo, superando significativamente servidores tradicionais como Apache/Nginx + PHP-FPM em cenários de alta concorrência.

## Docker

### Serviços

O `docker-compose.yml` define dois serviços:

1. **app**: Aplicação PHP + Swoole
   - Porta: 9501
   - Volume: Código montado em `/var/www`
   - Dependência: MongoDB

2. **mongo**: MongoDB 6.0
   - Porta: 27017
   - Volume persistente: `mongo_data`

### Comandos Úteis

```bash
# Iniciar serviços
docker-compose up -d

# Ver logs
docker-compose logs -f app

# Parar serviços
docker-compose down

# Rebuild da imagem
docker-compose build --no-cache

# Acessar container
docker-compose exec app bash
```

## Desenvolvimento

### Estrutura de Código

O projeto segue o padrão PSR-4 para autoloading:
- Namespace raiz: `App\`
- Diretório base: `src/`

### Boas Práticas

1. **Coroutines**: Use coroutines para operações I/O (DB, HTTP, File)
2. **Dependency Injection**: Implemente DI para melhor testabilidade
3. **Error Handling**: Adicione middleware para tratamento de erros
4. **Logging**: Implemente sistema de logs estruturado
5. **Validation**: Valide entradas de usuário
6. **Security**: Implemente autenticação e autorização

### Próximos Passos

- Implementar sistema de autenticação JWT
- Adicionar middleware de validação
- Criar sistema de logging estruturado
- Implementar testes automatizados
- Adicionar documentação OpenAPI/Swagger
- Implementar cache com Redis
- Criar sistema de migrations para MongoDB

## Troubleshooting

### Swoole não instalado
```bash
pecl install swoole
echo "extension=swoole.so" > /etc/php/conf.d/swoole.ini
```

### Erro de conexão com MongoDB
Verifique se o serviço MongoDB está rodando:
```bash
docker-compose ps
```

### Porta 9501 em uso
Altere a porta no `docker-compose.yml` e no `.env`:
```yaml
ports:
  - "8080:9501"
```

## Contribuindo

Contribuições são bem-vindas! Por favor:

1. Faça fork do projeto
2. Crie uma branch para sua feature (`git checkout -b feature/MinhaFeature`)
3. Commit suas mudanças (`git commit -m 'Adiciona MinhaFeature'`)
4. Push para a branch (`git push origin feature/MinhaFeature`)
5. Abra um Pull Request

## Licença

Este projeto é um exemplo educacional e está disponível para uso livre.

## Recursos Adicionais

- [Documentação Slim Framework](https://www.slimframework.com/)
- [Documentação Swoole](https://www.swoole.co.uk/)
- [MongoDB PHP Driver](https://www.mongodb.com/docs/drivers/php/)
- [PSR-7: HTTP Message Interface](https://www.php-fig.org/psr/psr-7/)
- [PSR-15: HTTP Server Request Handlers](https://www.php-fig.org/psr/psr-15/)

## Contato

Para questões ou sugestões, abra uma issue no repositório do projeto.

