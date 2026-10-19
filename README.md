# Datum — Magento 2

Loja Magento **2.4.6-p15 (Community Edition)** rodando via **DDEV** (Docker), com o módulo custom `Sidneydeus_FeaturedProduct`.

- PHP 8.2 (nginx-fpm)
- MariaDB 10.6
- OpenSearch 2.19
- Docroot: `pub/`
- URL do projeto DDEV: `datum`

## Requisitos

- [Docker](https://docs.docker.com/get-docker/)
- [DDEV](https://ddev.readthedocs.io/en/stable/) (>= 1.24)
- Credenciais do [Magento Marketplace](https://developer.adobe.com/commerce/contributor/resources/keys/) (chaves pública/privada) para o Composer

## Como inicializar

### 1. Credenciais do Composer

Copie o arquivo de exemplo e preencha com sua chave pública e privada do Marketplace:

```bash
cp auth.json.sample auth.json
```

### 2. Subir os containers

```bash
ddev start
```

### 3. Instalar as dependências PHP

```bash
ddev composer install
```

### 4. Banco de dados

**Opção A — usar um dump existente** (se você recebeu um backup):

```bash
ddev import-db --file=/caminho/para/db.sql.gz
ddev magento setup:upgrade
```

**Opção B — instalação limpa:**

```bash
ddev magento setup:install \
  --base-url=https://datum.ddev.site/ \
  --db-host=db --db-name=db --db-user=db --db-password=db \
  --admin-firstname=Admin --admin-lastname=User \
  --admin-email=admin@example.com --admin-user=admin --admin-password=Admin123 \
  --language=en_US --currency=USD --timezone=America/Sao_Paulo \
  --use-rewrites=1 \
  --search-engine=opensearch --opensearch-host=opensearch --opensearch-port=9200
```

### 5. Finalizar o build

```bash
ddev magento setup:upgrade
ddev magento setup:di:compile
ddev magento setup:static-content:deploy -f
ddev magento indexer:reindex
ddev magento cache:flush
```

## Acessar a loja

| Recurso | URL |
| --- | --- |
| Storefront | https://datum.ddev.site |
| Admin | https://datum.ddev.site/admin_1bssu1 |
| Mailpit (e-mails) | https://datum.ddev.site:8026 |
| OpenSearch Dashboards | https://datum.ddev.site:5602 |

Para abrir o storefront automaticamente: `ddev launch` (ou `ddev launch /admin_1bssu1` para o admin).

> O caminho do admin (`admin_1bssu1`) é definido em `app/etc/env.php` (`backend.frontName`).

### Criar/alterar usuário do admin

```bash
# criar
ddev magento admin:user:create \
  --admin-user=admin --admin-password=Admin123 \
  --admin-email=admin@example.com \
  --admin-firstname=Admin --admin-lastname=User

# trocar a senha de um usuário existente
ddev magento admin:user:change-password --username=admin --password=Admin123
```

## Módulo `Sidneydeus_FeaturedProduct`

Exibe um produto em destaque na home (`cms_index_index`) com nome, descrição, preço e estoque em tempo real (polling via customer-data).

### Configuração no admin

**Stores → Configuration → CATALOG → Featured Product**

Campos disponíveis (grupo `stock`):

- **Enabled** — liga/desliga o bloco na home
- **SKU** — SKU do produto exibido (padrão: `Produto XYZ`)
- **Poll Interval** — intervalo de atualização do estoque em ms (mínimo 1000)

Acesso direto: `https://datum.ddev.site/admin_1bssu1/system_config/edit/section/featured_product/`

Requer a permissão ACL `Sidneydeus_FeaturedProduct::config`.

## Comandos úteis

```bash
ddev start / ddev stop        # ligar/desligar os containers
ddev ssh                      # shell no container web
ddev magento <comando>        # bin/magento
ddev exec <comando>           # qualquer comando no container web
ddev describe                 # detalhes e URLs do projeto
ddev logs -f                  # logs dos containers
ddev xdebug on / off          # habilitar/desabilitar Xdebug
```

## Solução de problemas

- **Erros de DI / classes não encontradas:** `ddev magento setup:di:compile`
- **Alterações de CSS/JS não aparecem:** `ddev magento setup:static-content:deploy -f` (em produção) ou limpe o cache com `ddev magento cache:flush`
- **Falha no `composer install`:** confirme que o `auth.json` está preenchido com as chaves válidas do Marketplace
