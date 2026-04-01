# 🏗️ Arquitetura DDD + Modular Monolith

## Visão Geral

Este projeto implementa uma arquitetura **DDD (Domain-Driven Design) + Modular Monolith** no Laravel, organizando o código por domínios de negócio e seguindo os padrões Service, Repository e DTO.

## 📁 Estrutura de Pastas

```
apps/api/
└── app/
    └── Modules/
        ├── Pharmacy/                    # Módulo Farmácia
        │   ├── Domain/                  # Camada de Domínio
        │   │   ├── Entities/            # Entidades de Domínio
        │   │   ├── Repositories/        # Interfaces de Repositórios
        │   │   ├── Services/            # Serviços de Domínio
        │   │   ├── ValueObjects/        # Objetos de Valor
        │   │   └── Events/              # Domain Events
        │   │
        │   ├── Infrastructure/          # Camada de Infraestrutura
        │   │   ├── Repositories/        # Implementação dos Repositórios
        │   │   └── Persistence/
        │   │       └── Models/          # Models Eloquent
        │   │
        │   ├── Application/             # Camada de Aplicação
        │   │   ├── DTOs/                # Data Transfer Objects
        │   │   └── Services/            # Serviços de Aplicação
        │   │
        │   ├── Interfaces/              # Camada de Interface
        │   │   ├── Controllers/         # Controllers API
        │   │   └── Resources/           # API Resources
        │   │
        │   └── PharmacyServiceProvider.php
        │
        ├── Financial/                   # Módulo Financeiro (mesma estrutura)
        ├── Patient/                     # Módulo Paciente (mesma estrutura)
        ├── Appointment/                 # Módulo Agendamento (mesma estrutura)
        │
        └── Core/                        # Núcleo Compartilhado
            ├── Shared/
            │   ├── Entities/            # Entidades base compartilhadas
            │   ├── ValueObjects/        # Value Objects compartilhados
            │   │   └── Money.php        # Exemplo: Value Object Money
            │   └── DomainEvent/         # Domain Events base
            │
            └── Base/
                └── BaseServiceProvider.php
```

## 🎯 Padrões Implementados

### 1. **Repository Pattern**

Separa a lógica de acesso a dados da lógica de domínio.

**Interface (Domain Layer):**
```php
interface PharmacySaleRepositoryInterface
{
    public function find(int $id): ?PharmacySale;
    public function create(PharmacySale $entity): PharmacySale;
    public function update(PharmacySale $entity): PharmacySale;
}
```

**Implementação (Infrastructure Layer):**
```php
class PharmacySaleRepository implements PharmacySaleRepositoryInterface
{
    public function __construct(
        private PharmacySaleModel $model
    ) {}
    
    public function find(int $id): ?PharmacySale
    {
        $model = $this->model->with('items')->find($id);
        return $model ? PharmacySale::fromModel($model) : null;
    }
}
```

### 2. **Service Layer Pattern**

Orquestra o fluxo de aplicação, coordenando repositórios e entidades.

```php
class PharmacySaleService
{
    public function __construct(
        private PharmacySaleRepositoryInterface $repository,
        private PharmacyStockService $stockService
    ) {}
    
    public function createSale(CreatePharmacySaleDTO $dto): PharmacySale
    {
        // 1. Validar estoque
        // 2. Criar venda
        // 3. Baixar estoque
        // 4. Disparar eventos
    }
}
```

### 3. **DTO (Data Transfer Object)**

Objetos imutáveis para transferência de dados entre camadas.

```php
final class CreatePharmacySaleDTO
{
    public function __construct(
        public readonly int $clinicId,
        public readonly int $patientId,
        public readonly array $items,
        public readonly string $paymentMethod = 'cash',
    ) {}
    
    public static function fromRequest(array $data): self
    {
        return new self(...);
    }
}
```

### 4. **Domain Events**

Eventos disparados quando algo significativo acontece no domínio.

```php
// Event
class SaleCreated implements DomainEvent
{
    public function __construct(
        public readonly PharmacySale $sale
    ) {}
}

// Listener
class SaleCreatedListener
{
    public function handle(SaleCreated $event): void
    {
        // Executar ações secundárias
    }
}
```

## 🔄 Fluxo de uma Requisição

```
HTTP Request → Controller → Service → Repository → Model → Database
                    ↓           ↓          ↓
                DTO      Entity    Domain Event
                                  ↓
                              Listener
```

### Exemplo: Criar Venda

1. **Controller** recebe request HTTP
2. Valida dados e cria **DTO**
3. Chama **Service** com DTO
4. **Service**:
   - Valida regras de negócio
   - Cria **Entity** de domínio
   - Usa **Repository** para persistir
   - Baixa estoque via **StockService**
   - Dispara **Domain Event**
5. **Repository** converte Entity em Model Eloquent
6. **Model** salva no banco
7. **Listener** do evento executa ações secundárias

## 🧱 Componentes Principais

### Value Objects

Objetos imutáveis que representam conceitos do domínio:

- `Money` - Representa valores monetários
- `Address` - Representa endereços (futuro)
- `DateRange` - Representa períodos (futuro)

### Entities

Objetos com identidade única e ciclo de vida:

- `PharmacySale` - Venda de farmácia
- `PharmacySaleItem` - Item da venda
- `Patient` - Paciente (futuro)

### Services

**Application Services:** Orquestram fluxo de aplicação
- `PharmacySaleService`
- `PharmacyStockService`

**Domain Services:** Lógica de domínio que não pertence a uma entidade
- Validações complexas
- Cálculos específicos

### Repositories

Comunicam camada de domínio com infraestrutura:
- `PharmacySaleRepository`
- `PatientRepository` (futuro)

## 📦 Módulos Implementados

### 1. Módulo Farmácia (`Pharmacy`)

**Funcionalidades:**
- Venda de medicamentos
- Controle de estoque
- Gestão de lotes e validade

**Arquivos principais:**
- `Domain/Entities/PharmacySale.php`
- `Domain/Repositories/PharmacySaleRepositoryInterface.php`
- `Infrastructure/Repositories/PharmacySaleRepository.php`
- `Application/Services/PharmacySaleService.php`
- `Application/DTOs/CreatePharmacySaleDTO.php`
- `Interfaces/Controllers/PharmacySaleController.php`

### 2. Módulo Financeiro (`Financial`) - *Em migração*

**Funcionalidades:**
- Contas a pagar/receber
- Faturamento
- Comissões

### 3. Módulo Paciente (`Patient`) - *Planejado*

**Funcionalidades:**
- Cadastro de pacientes
- Prontuários eletrônicos

### 4. Módulo Agendamento (`Appointment`) - *Planejado*

**Funcionalidades:**
- Agenda de consultas
- Prescrições médicas

## 🔧 Configuração

### Registrar Módulo

Adicione o Service Provider em `config/app.php`:

```php
'providers' => [
    // ... outros providers
    App\Modules\Pharmacy\PharmacyServiceProvider::class,
],
```

### Rotas

As rotas são registradas automaticamente pelo Service Provider:

```
GET    /api/pharmacy/sales          # Listar vendas
GET    /api/pharmacy/sales/{id}     # Buscar venda
POST   /api/pharmacy/sales          # Criar venda
POST   /api/pharmacy/sales/{id}/cancel  # Cancelar venda
GET    /api/pharmacy/sales/summary  # Resumo
```

## 🧪 Testes

Cada módulo deve ter seus próprios testes:

```
tests/
└── Unit/
    └── Modules/
        ├── Pharmacy/
        │   ├── Domain/
        │   │   └── Entities/
        │   │       └── PharmacySaleTest.php
        │   └── Application/
        │           └── Services/
        │               └── PharmacySaleServiceTest.php
```

## 📚 Boas Práticas

### ✅ Faça

- Mantenha as entities livres de dependências do Laravel
- Use Value Objects para conceitos do domínio
- Repositórios retornam apenas Entities, nunca Models
- Services orquestram, não implementam regras de negócio
- DTOs são imutáveis (readonly)
- Domain Events para efeitos colaterais

### ❌ Não faça

- Não misture layers (ex: Controller chamando Model diretamente)
- Não use Eloquent dentro das Entities
- Não crie dependências circulares entre módulos
- Não exponha Entities nas respostas HTTP (use Resources)
- Não coloque lógica de negócio em Controllers

## 🚀 Migração de Módulos Existentes

Para migrar um módulo existente para a nova estrutura:

1. **Criar estrutura de pastas** do módulo
2. **Mover Models** para `Infrastructure/Persistence/Models`
3. **Criar Entities** em `Domain/Entities`
4. **Criar interfaces** de Repository
5. **Implementar Repositories** concretos
6. **Criar DTOs** para transferência de dados
7. **Criar Services** de aplicação
8. **Refatorar Controllers** para usar Services
9. **Criar Service Provider** e registrar dependências
10. **Atualizar rotas** se necessário

## 📖 Referências

- **Domain-Driven Design** - Eric Evans
- **Implementing Domain-Driven Design** - Vaughn Vernon
- **Laravel Beyond CRUD** - Spatie
- **Clean Architecture** - Robert C. Martin

## 🎯 Próximos Passos

1. ✅ Módulo Farmácia implementado
2. ⏳ Migrar Módulo Financeiro
3. ⏳ Implementar Módulo Paciente
4. ⏳ Implementar Módulo Agendamento
5. ⏳ Adicionar testes unitários
6. ⏳ Implementar CQRS para operações complexas
7. ⏳ Adicionar Event Sourcing (opcional)

---

**Status:** Implementação inicial concluída ✅  
**Última atualização:** Março 2026
