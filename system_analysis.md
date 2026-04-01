# Análise do Sistema: Sodo Clínica (Sodo Diversos)

O sistema **Sodo Clínica** é uma plataforma robusta de gestão clínica desenvolvida com tecnologias modernas, agora configurada exclusivamente para **execução local**, sem o uso de containers (Docker), seguindo uma estrutura limpa e direta.

## 🏗️ Arquitetura e Estrutura

O projeto está organizado em uma estrutura de aplicações (`apps/`) na raiz:

- **Backend (API)**: Localizado em `/apps/api`. Baseado em Laravel 11/12+.
- **Frontend (Web)**: Localizado em `/apps/web`. Baseado em Next.js (App Router).

### 🖥️ Backend (Laravel + DDD)

A API segue padrões rigorosos de engenharia de software para garantir escalabilidade e manutenibilidade:

- **Modular Monolith**: Os domínios são separados em módulos (`app/Modules/`), como `Pharmacy`, `Financial`, `Patient`, e `Appointment`.
- **Domain-Driven Design (DDD)**:
  - **Domain Layer**: Entidades (`Entities`), Repositórios (Interfaces), Serviços de Domínio e Eventos.
  - **Infrastructure Layer**: Implementações de Repositórios (Eloquent), Persistência (Models) e integrações externas.
  - **Application Layer**: DTOs (Data Transfer Objects) e Serviços de Aplicação (orquestração).
  - **Interface Layer**: Controllers e Resources (API Transformers).
- **Tecnologias**: Laravel, PHP 8.2+, Composer, SQLite (Execução Local), Redis (Log/File Cache opcional).

### 🌐 Frontend (Next.js)

O frontend é uma aplicação moderna focada em UX/UI premium:

- **Framework**: Next.js (App Router).
- **Linguagem**: TypeScript.
- **Estilização**: Tailwind CSS.
- **Arquitetura**:
  - `src/app/`: Roteamento e páginas (`dashboard`, `pacientes`, `agendamentos`).
  - `src/components/`: Componentes reutilizáveis e UI components.
  - `src/lib/`: Funções utilitárias e clientes de API.
- **Configuração**: Aponta para `http://localhost:8000/api` via `.env.local`.

## 🚀 Funcionalidades Principais

1. **Gestão de Pacientes**: Cadastro completo e prontuário eletrônico.
2. **Agenda Médica**: Controle de consultas e agendamentos.
3. **Módulo de Farmácia**: Venda de medicamentos, controle de estoque e lotes.
4. **Módulo Financeiro**: Contas a pagar/receber, faturamento e comissões (em migração).
5. **Telemedicina**: Módulo avançado para consultas remotas.

## 🛠️ Infraestrutura (Execução Local)

O sistema foi simplificado para rodar nativamente no Windows (via PowerScript/Batch) ou WSL:

- **API**: `php artisan serve` (porta 8000).
- **Web**: `npm run dev` (porta 3000).
- **Database**: SQLite para facilitar o ambiente local sem necessidade de serviços externos pesados.
- **Limpeza**: Foram removidos todos os arquivos Docker (`docker-compose.yml`, `Dockerfile`, `.dockerignore`) e pastas redundantes.

## 📈 Próximos Passos Identificados

De acordo com a documentação interna (`DDD_ARCHITECTURE.md`):
- Finalizar a migração do módulo Financeiro.
- Implementar os módulos de Paciente e Agendamento na nova estrutura DDD.
- Implementar CQRS para operações complexas.
- Adicionar cobertura de testes unitários por módulo.

---
**Status da Análise**: Concluída ✅
**Data**: 01/04/2026
