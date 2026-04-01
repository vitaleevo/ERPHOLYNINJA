# MedAngola Cloud API

API RESTful para gestão de clínicas médicas em Angola.

## 📋 Requisitos

- PHP 8.2+
- Composer
- PostgreSQL 16
- Redis 7 (opcional para cache/filas)
- Docker (opcional)

## 🚀 Instalação

### Opção 1: Usando Docker (Recomendado)

```bash
# Na raiz do projeto
docker-compose up -d

# Acessar o container da API
docker-compose exec api bash

# Instalar dependências
composer install

# Configurar ambiente
cp .env.example .env
php artisan key:generate

# Rodar migrations e seeders
php artisan migrate --seed

# Sair do container
exit
```

A API estará disponível em `http://localhost:8000`

### Opção 2: Instalação Local

```bash
# Instalar dependências
composer install

# Copiar arquivo de ambiente
cp .env.example .env

# Gerar chave da aplicação
php artisan key:generate

# Configurar banco de dados no .env
# DB_CONNECTION=pgsql
# DB_HOST=127.0.0.1
# DB_PORT=5432
# DB_DATABASE=medangola
# DB_USERNAME=postgres
# DB_PASSWORD=sua_senha

# Rodar migrations
php artisan migrate

# Popular banco com dados de teste
php artisan db:seed

# Iniciar servidor de desenvolvimento
php artisan serve
```

## 🔑 Autenticação

A API utiliza Laravel Sanctum para autenticação via tokens.

### Registrar nova clínica e admin

```bash
POST /api/auth/register
Content-Type: application/json

{
  "clinic_name": "Minha Clínica",
  "clinic_email": "contato@clinica.com",
  "clinic_phone": "+244 923 000 000",
  "clinic_nif": "500123456",
  "clinic_address": "Rua X, Luanda",
  "admin_name": "Dr. Admin",
  "admin_email": "admin@clinica.com",
  "admin_password": "senha_forte",
  "admin_password_confirmation": "senha_forte"
}
```

### Login

```bash
POST /api/auth/login
Content-Type: application/json

{
  "email": "admin@clinica.com",
  "password": "senha_forte",
  "clinic_id": 1
}

# Resposta
{
  "user": { ... },
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "token_type": "Bearer"
}
```

### Logout

```bash
POST /api/auth/logout
Authorization: Bearer {token}
X-Clinic-Id: 1
```

### Obter dados do usuário

```bash
GET /api/auth/me
Authorization: Bearer {token}
X-Clinic-Id: 1
```

## 📚 Endpoints da API

Todos os endpoints requerem autenticação, exceto login e registro.

**Headers obrigatórios:**
- `Authorization: Bearer {token}`
- `X-Clinic-Id: {id_da_clinica}`

### Pacientes

```bash
GET    /api/patients              # Listar pacientes
POST   /api/patients              # Criar paciente
GET    /api/patients/{id}         # Detalhes do paciente
PUT    /api/patients/{id}         # Atualizar paciente
DELETE /api/patients/{id}         # Remover paciente
```

### Agendamentos

```bash
GET    /api/appointments          # Listar agendamentos
POST   /api/appointments          # Criar agendamento
GET    /api/appointments/{id}     # Detalhes do agendamento
PUT    /api/appointments/{id}     # Atualizar agendamento
DELETE /api/appointments/{id}     # Cancelar agendamento
```

### Consultas

```bash
GET    /api/consultations                   # Listar consultas
POST   /api/consultations                   # Criar consulta
GET    /api/consultations/{id}              # Detalhes da consulta
PUT    /api/consultations/{id}              # Atualizar consulta
POST   /api/consultations/{id}/medical-records  # Adicionar prontuário
```

### Prescrições

```bash
GET    /api/prescriptions         # Listar prescrições
POST   /api/prescriptions         # Criar prescrição
GET    /api/prescriptions/{id}    # Detalhes da prescrição
PUT    /api/prescriptions/{id}    # Atualizar prescrição
```

## 🧪 Testes

```bash
# Rodar testes
composer test

# Rodar testes com coverage
composer test -- --coverage
```

## 📦 Dados de Teste

O seeders cria:
- 3 clínicas (Sagrada Esperança, Boa Vida, Hospital Privado)
- Usuários para cada clínica (admin, médicos, recepcionistas)
- 15 especialidades médicas
- 7 seguros/convênios

**Usuário para testes:**
- Email: `teste@clinica.com`
- Senha: `password`
- Clinic ID: 1

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
