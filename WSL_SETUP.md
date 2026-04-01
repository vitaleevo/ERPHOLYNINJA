# Guia de Configuração - MedAngola no WSL2 (Windows)

## Pré-requisitos
- Windows 10/11 com WSL2 ativado
- Docker Desktop instalado e configurado para usar WSL2

---

## Passo 1: Ativar WSL2 (se ainda não estiver)

Abrir PowerShell como Administrador e executar:

```powershell
wsl --install
```

Reiniciar o computador após este comando.

---

## Passo 2: Instalar Ubuntu no WSL

Abrir Microsoft Store e instalar "Ubuntu 22.04 LTS" (ou versão mais recente).

Após instalar, abrir o Ubuntu e criar seu usuário e senha.

---

## Passo 3: Configurar Docker Desktop

1. Abrir Docker Desktop
2. Ir para Settings > Resources > WSL Integration
3. Ativar integração com Ubuntu
4. Aplicar e reiniciar Docker

---

## Passo 4: Configurar o Projeto no Ubuntu

Copiar o projeto para o Ubuntu:

```bash
# No Ubuntu, criar diretório para o projeto
cd ~
mkdir -p projects/medangola
cd projects/medangola

# Copiar projeto do Windows (assumindo que está em D:/projects/medangola)
# Mount do Windows geralmente está em /mnt/
cp -r /mnt/d/projects/medangola/* ./
```

---

## Passo 5: Executar com Docker

No diretório do projeto:

```bash
cd medangola

# Iniciar todos os serviços
docker-compose up -d

# Ver logs
docker-compose logs -f

# Ver status dos containers
docker-compose ps
```

---

## Passo 6: Acessar os Serviços

| Serviço | URL |
|---------|-----|
| Frontend Next.js | http://localhost:3000 |
| Backend Laravel | http://localhost:8000 |
| PostgreSQL | localhost:5432 |

---

## Comandos Úteis

```bash
# Parar serviços
docker-compose down

# Rebuild (após alterações)
docker-compose down
docker-compose build --no-cache
docker-compose up -d

# Ver logs de um serviço específico
docker-compose logs -f api
docker-compose logs -f web

# Acessar container
docker exec -it medangola-api bash
docker exec -it medangola-web sh
```

---

## Credenciais de Teste

- **Email:** admin@medangola.com
- **Senha:** password

---

## Resolução de Problemas

### "Cannot connect to Docker daemon"
```bash
# No Ubuntu
sudo service docker start
# ou
sudo dockerd &
```

### Problemas de permissão
```bash
sudo chmod -R 755 .
sudo chown -R $USER:$USER .
```

### Banco de dados não conecta
Verificar se o serviço PostgreSQL está a funcionar:
```bash
docker-compose ps
docker-compose logs database
```