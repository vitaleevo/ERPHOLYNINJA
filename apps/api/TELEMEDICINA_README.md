# Módulo de Telemedicina - MedAngola Cloud

## Visão Geral

O módulo de Telemedicina permite a realização de consultas médicas remotas através de videoconferência, integrado com chat e compartilhamento de arquivos.

## Funcionalidades

- ✅ Criação de sessões de telemedicina a partir de agendamentos
- ✅ Videoconferência integrada (suporte para Jitsi, Zoom, Daily.co)
- ✅ Chat em tempo real via WebSocket
- ✅ Compartilhamento de arquivos (exames, documentos, prescrições)
- ✅ Controle de status da sessão (agendada, iniciada, encerrada, cancelada)
- ✅ Gravação de sessões (configurável por provedor)
- ✅ Histórico completo de teleconsultas
- ✅ Estatísticas e relatórios
- ✅ Integração com prontuário eletrônico

## Instalação

### 1. Executar Migrations

```bash
php artisan migrate
```

Isso criará as seguintes tabelas:
- `telemedicine_sessions` - Sessões de videoconferência
- `telemedicine_chats` - Mensagens do chat
- `telemedicine_files` - Arquivos compartilhados
- Atualização em `appointments` com campos `is_telemedicine` e `video_call_link`

### 2. Configurar Provedor de Vídeo

Adicione ao seu `.env`:

```env
VIDEOCONFERENCE_PROVIDER=jitsi
VIDEOCONFERENCE_BASE_URL=https://meet.jit.si
VIDEOCONFERENCE_API_KEY=sua_api_key
VIDEOCONFERENCE_API_SECRET=sua_api_secret
```

**Provedores Suportados:**
- **Jitsi** (padrão) - Gratuito, open-source, recomendado
- **Zoom** - Requer conta empresarial
- **Daily.co** - Pago, mas com mais recursos
- **WebRTC Custom** - Implementação própria

### 3. Configurar WebSocket (Opcional para Chat em Tempo Real)

Para habilitar o broadcasting em tempo real:

```env
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=seu_app_id
PUSHER_APP_KEY=seu_app_key
PUSHER_APP_SECRET=seu_app_secret
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1
```

Ou use Laravel Reverb (Laravel 11+):

```env
BROADCAST_DRIVER=reverb
REVERB_APP_ID=seu_app_id
REVERB_APP_KEY=seu_app_key
REVERB_APP_SECRET=seu_app_secret
```

## Endpoints da API

### Listar Sessões

```http
GET /api/telemedicine/sessions
```

**Parâmetros de Query:**
- `status` - Filtrar por status (scheduled, started, ended, cancelled)
- `doctor_id` - Filtrar por médico
- `patient_id` - Filtrar por paciente
- `start_date` - Data inicial
- `end_date` - Data final

### Criar Sessão a partir de Agendamento

```http
POST /api/telemedicine/appointments/{appointmentId}/create-session
```

**Requisitos:**
- O agendamento deve ter `is_telemedicine = true`

**Resposta:**
```json
{
  "message": "Sessão criada com sucesso",
  "session": {
    "id": 1,
    "session_id": "uuid-da-sessao",
    "meeting_url": "https://meet.jit.si/MedAngola-abc123",
    "moderator_password": "senha_medico",
    "attendee_password": "senha_paciente",
    "status": "scheduled"
  }
}
```

### Obter Detalhes da Sessão

```http
GET /api/telemedicine/sessions/{id}
```

**Retorna:**
- Dados completos da sessão
- Histórico de mensagens do chat
- Arquivos compartilhados
- Informações do médico e paciente

### Iniciar Sessão

```http
POST /api/telemedicine/sessions/{id}/start
```

**Permissão:** Apenas o médico pode iniciar a sessão

**Resposta:**
```json
{
  "message": "Sessão iniciada com sucesso",
  "session": {...},
  "meeting_url": "https://meet.jit.si/..."
}
```

### Entrar na Sessão

```http
POST /api/telemedicine/sessions/{id}/join
```

**Requisitos:**
- Usuário deve ser médico ou paciente da sessão
- Sessão deve estar com status `started`

### Encerrar Sessão

```http
POST /api/telemedicine/sessions/{id}/end
```

**Parâmetros:**
- `recording_url` (opcional) - URL da gravação da sessão

**Permissão:** Apenas o médico pode encerrar

### Cancelar Sessão

```http
POST /api/telemedicine/sessions/{id}/cancel
```

**Permissão:** Médico ou admin

### Enviar Mensagem no Chat

```http
POST /api/telemedicine/sessions/{sessionId}/messages
```

**Body:**
```json
{
  "message": "Olá, como está se sentindo hoje?",
  "type": "text" // text, file, system
}
```

### Obter Mensagens

```http
GET /api/telemedicine/sessions/{sessionId}/messages
```

**Retorna:** Últimas 100 mensagens ordenadas cronologicamente

### Upload de Arquivo

```http
POST /api/telemedicine/sessions/{sessionId}/files
```

**Multipart Form:**
- `file` (obrigatório) - Arquivo para upload (máx 10MB)
- `category` (opcional) - document, exam, prescription, other

**Resposta:**
```json
{
  "message": "Arquivo enviado com sucesso",
  "file": {
    "id": 1,
    "file_name": "exame_sangue.pdf",
    "file_type": "application/pdf",
    "file_size": 1024567,
    "file_category": "exam",
    "file_url": "https://api.medangola.com/storage/telemedicine/1/exame.pdf"
  }
}
```

### Obter Arquivos

```http
GET /api/telemedicine/sessions/{sessionId}/files
```

### Obter Configurações do Cliente de Vídeo

```http
GET /api/telemedicine/sessions/{sessionId}/config
```

**Retorna:** Configurações para inicializar o cliente de vídeo no frontend

**Exemplo de Resposta:**
```json
{
  "provider": "jitsi",
  "roomName": "MedAngola-abc123",
  "userInfo": {
    "id": 1,
    "name": "Dr. João Silva",
    "email": "dr.joao@medangola.com"
  },
  "configOverwrite": {
    "startWithAudioMuted": false,
    "startWithVideoMuted": false,
    "disableModerator": false
  },
  "password": "senha123"
}
```

### Histórico do Paciente

```http
GET /api/telemedicine/patients/{patientId}/history
```

**Retorna:** Últimas 10 sessões do paciente

### Estatísticas

```http
GET /api/telemedicine/statistics
```

**Parâmetros:**
- `start_date` - Data inicial
- `end_date` - Data final

**Resposta:**
```json
{
  "total": 50,
  "completed": 45,
  "cancelled": 3,
  "scheduled": 2,
  "completion_rate": 90.00
}
```

## Fluxo de Uso

### 1. Agendar Consulta de Telemedicina

No momento de criar o agendamento, marque como telemedicina:

```javascript
// Exemplo no frontend
const appointment = {
  patient_id: 1,
  doctor_id: 2,
  scheduled_at: '2026-04-01 10:00:00',
  is_telemedicine: true, // Importante!
  duration_minutes: 30
};

await api.post('/appointments', appointment);
```

### 2. Criar Sessão de Telemedicina

Antes da consulta (ou no momento), crie a sessão:

```javascript
const response = await api.post(
  `/telemedicine/appointments/${appointmentId}/create-session`
);

const { session, meeting_url } = response.data;
```

### 3. Iniciar Sessão (Médico)

No horário da consulta, o médico inicia:

```javascript
await api.post(`/telemedicine/sessions/${sessionId}/start`);
```

### 4. Participar da Videochamada

Use uma biblioteca de vídeo no frontend:

**Exemplo com Jitsi Meet External API:**

```html
<script src='https://meet.jit.si/external_api.js'></script>
<div id="meet"></div>

<script>
const domain = 'meet.jit.si';
const options = {
  roomName: 'MedAngola-abc123',
  width: '100%',
  height: 600,
  parentNode: document.querySelector('#meet'),
  configOverwrite: {
    startWithAudioMuted: false,
    startWithVideoMuted: false
  },
  userInfo: {
    displayName: 'Dr. João Silva',
    email: 'dr.joao@medangola.com'
  }
};

const api = new JitsiMeetExternalAPI(domain, options);

// Escutar eventos
api.addEventListener('videoConferenceJoined', () => {
  console.log('Entrou na conferência');
});

api.addEventListener('videoConferenceLeft', () => {
  console.log('Saiu da conferência');
});
</script>
```

### 5. Enviar Mensagens no Chat

```javascript
// Enviar mensagem
await api.post(`/telemedicine/sessions/${sessionId}/messages`, {
  message: 'Como posso ajudar?',
  type: 'text'
});

// Ouvir mensagens em tempo real (com WebSocket/Pusher)
Echo.join(`telemedicine.${sessionId}`)
  .here((users) => {
    console.log('Participantes:', users);
  })
  .joining((user) => {
    console.log('Usuário entrou:', user);
  })
  .leaving((user) => {
    console.log('Usuário saiu:', user);
  })
  .listen('MessageSent', (e) => {
    console.log('Nova mensagem:', e.message);
    adicionarMensagemNaTela(e);
  })
  .listen('SessionStarted', (e) => {
    alert('Consulta iniciada!');
  })
  .listen('SessionEnded', (e) => {
    alert('Consulta encerrada.');
  });
```

### 6. Compartilhar Arquivos

```javascript
const fileInput = document.querySelector('#file-input');
const file = fileInput.files[0];

const formData = new FormData();
formData.append('file', file);
formData.append('category', 'exam');

await api.post(`/telemedicine/sessions/${sessionId}/files`, formData, {
  headers: { 'Content-Type': 'multipart/form-data' }
});
```

### 7. Encerrar Consulta

```javascript
await api.post(`/telemedicine/sessions/${sessionId}/end`, {
  recording_url: 'https://storage.medangola.com/gravacoes/session-123.mp4'
});
```

## Modelos de Dados

### TelemedicineSession

```php
{
  "id": 1,
  "clinic_id": 1,
  "appointment_id": 1,
  "doctor_id": 1,
  "patient_id": 1,
  "session_id": "uuid",
  "meeting_url": "https://...",
  "moderator_password": "***",
  "attendee_password": "***",
  "status": "started",
  "started_at": "2026-04-01T10:00:00Z",
  "ended_at": null,
  "duration_minutes": 30,
  "recording_url": null,
  "settings": {}
}
```

### TelemedicineChat

```php
{
  "id": 1,
  "session_id": 1,
  "user_id": 1,
  "message": "Olá!",
  "type": "text", // text, file, system
  "file_url": null,
  "file_name": null,
  "is_read": false,
  "read_at": null,
  "created_at": "2026-04-01T10:05:00Z"
}
```

### TelemedicineFile

```php
{
  "id": 1,
  "session_id": 1,
  "user_id": 1,
  "file_name": "exame.pdf",
  "file_path": "telemedicine/1/exame.pdf",
  "file_type": "application/pdf",
  "file_size": 1024567,
  "file_category": "exam", // document, exam, prescription, other
  "created_at": "2026-04-01T10:10:00Z"
}
```

## Eventos WebSocket

### MessageSent

Disparado quando uma mensagem é enviada.

```javascript
.listen('MessageSent', (data) => {
  console.log('Mensagem:', data.message);
  console.log('Remetente:', data.sender.name);
});
```

### SessionStarted

Disparado quando a sessão é iniciada.

```javascript
.listen('SessionStarted', (data) => {
  console.log('Sessão iniciada às', data.started_at);
  console.log('URL:', data.meeting_url);
});
```

### SessionEnded

Disparado quando a sessão é encerrada.

```javascript
.listen('SessionEnded', (data) => {
  console.log('Sessão encerrada às', data.ended_at);
  if (data.recording_url) {
    console.log('Gravação:', data.recording_url);
  }
});
```

## Permissões e Segurança

### Regras de Acesso

1. **Criar sessão:** Apenas médicos podem criar sessões
2. **Iniciar sessão:** Apenas o médico responsável pode iniciar
3. **Encerrar sessão:** Apenas o médico responsável pode encerrar
4. **Cancelar sessão:** Médico ou admin podem cancelar
5. **Participar:** Apenas médico e paciente da sessão
6. **Ver arquivos:** Apenas participantes da sessão
7. **Enviar mensagens:** Apenas participantes ativos

### Senhas das Salas

- **Moderador (Médico):** Tem controle total da sala
- **Participante (Paciente):** Acesso limitado

## Personalização

### Trocar Provedor de Vídeo

Edite `config/services.php`:

```php
'videoconference' => [
    'provider' => env('VIDEOCONFERENCE_PROVIDER', 'zoom'),
    'base_url' => env('VIDEOCONFERENCE_BASE_URL'),
    'api_key' => env('ZOOM_API_KEY'),
    'api_secret' => env('ZOOM_API_SECRET'),
],
```

### Customizar Configurações da Sala

No `VideoConferenceService`, personalize `getClientConfig()`:

```php
'configOverwrite' => [
    'startWithAudioMuted' => true,
    'startWithVideoMuted' => true,
    'disableModerator' => false,
    'prejoinPageEnabled' => true,
],
```

## Troubleshooting

### Problemas Comuns

**1. Sessão não inicia:**
- Verifique se o agendamento é telemedicina (`is_telemedicine = true`)
- Verifique se usuário é o médico responsável
- Verifique se sessão está no status `scheduled`

**2. Chat não funciona:**
- Configure corretamente o broadcasting
- Execute `php artisan reverb:start` (se usar Reverb)
- Verifique credenciais do Pusher/Redis

**3. Vídeo não carrega:**
- Verifique conexão com provedor (Jitsi, etc)
- Teste em diferentes navegadores
- Verifique permissões de câmera/microfone

**4. Upload falha:**
- Verifique limite de upload no `php.ini`
- Ajuste `upload_max_filesize` e `post_max_size`
- Verifique permissões da pasta `storage/`

## Próximos Passos

- [ ] Integração com WhatsApp para notificações
- [ ] Receituário digital integrado
- [ ] Laudos e exames digitais
- [ ] Agenda de disponibilidade para telemedicina
- [ ] Validação de receita com QR Code
- [ ] Exportação de sessões para PDF
- [ ] Analytics avançado de teleconsultas

## Suporte

Para dúvidas ou problemas, abra uma issue no repositório ou contate a equipe de desenvolvimento.

---

**MedAngola Cloud** - Sistema de Gestão Clínica © 2026
