# Configuração do Ambiente - Windows (Sem Docker)

## passo 1: Instalar PHP 8.2

### Método 1: Usar Chocolatey (Recomendado)
1. Abrir PowerShell como Administrador
2. Instalar Chocolatey:
```powershell
Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://community.chocolatey.org/install.ps1'))
```

3. Instalar PHP:
```powershell
choco install php --version=8.2 -y
```

### Método 2: Manual
1. Baixar PHP 8.2 de: https://windows.php.net/download/
2. Extrair para `C:\php`
3. Adicionar ao PATH: Painel Controle > Sistema > Variáveis de Ambiente
4. Adicionar `C:\php` ao PATH do sistema

---

## Passo 2: Instalar Node.js 20

1. Baixar de: https://nodejs.org/
2. Instalador .msi (LTS version)
3. Seguinte, seguinte... Finish

Verificar instalação:
```powershell
php -v
node -v
npm -v
```

---

## Passo 3: Instalar Composer

1. Baixar: https://getcomposer.org/download/
2. Instalar composer-setup.exe
3. Escolher php.exe路径 (geralmente `C:\php\php.exe`)

Verificar:
```powershell
composer -V
```

---

## Passo 4: Configurar o Projeto

### 4.1 Preparar Backend (Laravel)

```powershell
cd "C:\Users\alexa\Documents\Systenas\Sodo clinica\Sodo clinica\apps\api"

# Copiar ficheiro de ambiente
copy .env.example .env

# Gerar chave
php artisan key:generate

# Criar base de dados SQLite (mais fácil para Windows)
# OU configurar MySQL/XAMPP
```

### 4.2 Se usar XAMPP (MySQL)
```powershell
# Editar .env:
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=medangola
DB_USERNAME=root
DB_PASSWORD=

# Criar base de dados no phpMyAdmin
# Executar migrações
php artisan migrate
php artisan db:seed
```

### 4.3 Preparar Frontend (Next.js)
```powershell
cd "C:\Users\alexa\Documents\Systenas\Sodo clinica\apps\web"

# Instalar dependências
npm install

# Executar
npm run dev
```

---

## Passo 5: Iniciar os Serviços

### Terminal 1 - Backend (Laravel):
```powershell
cd "C:\Users\alexa\Documents\Systenas\Sodo clinica\Sodo clinica\apps\api"
php artisan serve --port=8000
```

### Terminal 2 - Frontend (Next.js):
```powershell
cd "C:\Users\alexa\Documents\Systenas\Sodo clinica\apps\web"
npm run dev
```

---

## Acesso

| Serviço | URL |
|---------|-----|
| Frontend | http://localhost:3000 |
| Backend API | http://localhost:8000/api |

### Credenciais
- Email: admin@medangola.com
- Senha: password

---

## Notas Importantes

1. **Extensões PHP necessárias:**
   - ext-mbstring
   - ext-curl
   - ext-json
   - ext-xml
   - ext-zip
   - ext-pdo
   - ext-pgsql (se usar PostgreSQL) ou ext-mysql (se usar MySQL)

2. **Erros comuns:**
   - "php não reconhecido" → adicionar ao PATH
   - "composer não reconhecido" → reinstalar

Queres que execute algum destes passos agora?