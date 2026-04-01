/**
 * Exemplo de Componente React para Telemedicina
 * 
 * Este é um exemplo de como integrar a API de telemedicina no frontend
 * usando React e Jitsi Meet para videoconferência.
 */

import React, { useState, useEffect, useRef } from 'react';
import axios from 'axios';

// Componente Principal de Telemedicina
const TelemedicineSession = ({ sessionId, authToken }) => {
    const [session, setSession] = useState(null);
    const [messages, setMessages] = useState([]);
    const [newMessage, setNewMessage] = useState('');
    const [files, setFiles] = useState([]);
    const [jitsiAPI, setJitsiAPI] = useState(null);
    const jitsiContainerRef = useRef(null);
    const messagesEndRef = useRef(null);

    // Configurar API client
    const api = axios.create({
        baseURL: '/api',
        headers: {
            'Authorization': `Bearer ${authToken}`,
            'Accept': 'application/json',
        },
    });

    // Carregar dados da sessão
    useEffect(() => {
        loadSessionData();
        loadMessages();
        loadFiles();
        
        // Configurar WebSocket para mensagens em tempo real
        setupWebSocket();
        
        return () => {
            if (jitsiAPI) {
                jitsiAPI.dispose();
            }
        };
    }, [sessionId]);

    // Scroll automático para última mensagem
    useEffect(() => {
        messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
    }, [messages]);

    const loadSessionData = async () => {
        try {
            const response = await api.get(`/telemedicine/sessions/${sessionId}`);
            setSession(response.data);
        } catch (error) {
            console.error('Erro ao carregar sessão:', error);
        }
    };

    const loadMessages = async () => {
        try {
            const response = await api.get(`/telemedicine/sessions/${sessionId}/messages`);
            setMessages(response.data);
        } catch (error) {
            console.error('Erro ao carregar mensagens:', error);
        }
    };

    const loadFiles = async () => {
        try {
            const response = await api.get(`/telemedicine/sessions/${sessionId}/files`);
            setFiles(response.data);
        } catch (error) {
            console.error('Erro ao carregar arquivos:', error);
        }
    };

    // Configurar WebSocket (usando Laravel Echo + Pusher/Reverb)
    const setupWebSocket = () => {
        if (window.Echo) {
            window.Echo.join(`telemedicine.${sessionId}`)
                .here((users) => {
                    console.log('Participantes na sala:', users);
                })
                .joining((user) => {
                    console.log('Usuário entrou:', user);
                })
                .leaving((user) => {
                    console.log('Usuário saiu:', user);
                })
                .listen('MessageSent', (event) => {
                    setMessages(prev => [...prev, {
                        id: event.id,
                        message: event.message,
                        type: event.type,
                        sender: event.sender,
                        created_at: event.created_at,
                    }]);
                })
                .listen('SessionStarted', (event) => {
                    alert('Consulta iniciada!');
                })
                .listen('SessionEnded', (event) => {
                    alert('Consulta encerrada.');
                });
        }
    };

    // Iniciar vídeo conferência
    const startVideoConference = async () => {
        try {
            // Obter configurações da sessão
            const configResponse = await api.get(`/telemedicine/sessions/${sessionId}/config`);
            const config = configResponse.data;

            // Carregar script do Jitsi
            if (!window.JitsiMeetExternalAPI) {
                await loadJitsiScript();
            }

            // Inicializar Jitsi
            const domain = new URL(config.meeting_url).hostname;
            const options = {
                roomName: config.roomName,
                width: '100%',
                height: 600,
                parentNode: jitsiContainerRef.current,
                configOverwrite: config.configOverwrite,
                interfaceConfigOverwrite: config.interfaceConfigOverwrite,
                userInfo: {
                    displayName: config.userInfo.name,
                    email: config.userInfo.email,
                },
            };

            const jitsi = new window.JitsiMeetExternalAPI(domain, options);
            
            // Event listeners
            jitsi.addEventListener('videoConferenceJoined', () => {
                console.log('Entrou na conferência');
                // Iniciar sessão no backend
                api.post(`/telemedicine/sessions/${sessionId}/start`);
            });

            jitsi.addEventListener('videoConferenceLeft', () => {
                console.log('Saiu da conferência');
            });

            setJitsiAPI(jitsi);
        } catch (error) {
            console.error('Erro ao iniciar vídeo:', error);
        }
    };

    // Carregar script do Jitsi Meet
    const loadJitsiScript = () => {
        return new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = 'https://meet.jit.si/external_api.js';
            script.onload = resolve;
            script.onerror = reject;
            document.body.appendChild(script);
        });
    };

    // Enviar mensagem
    const sendMessage = async (e) => {
        e.preventDefault();
        if (!newMessage.trim()) return;

        try {
            await api.post(`/telemedicine/sessions/${sessionId}/messages`, {
                message: newMessage,
                type: 'text',
            });
            setNewMessage('');
        } catch (error) {
            console.error('Erro ao enviar mensagem:', error);
        }
    };

    // Upload de arquivo
    const uploadFile = async (e) => {
        const file = e.target.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append('file', file);
        formData.append('category', 'document');

        try {
            await api.post(`/telemedicine/sessions/${sessionId}/files`, formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
            });
            loadFiles(); // Recarregar lista de arquivos
        } catch (error) {
            console.error('Erro ao fazer upload:', error);
            alert('Erro ao enviar arquivo');
        }
    };

    // Encerrar consulta
    const endConsultation = async () => {
        if (!confirm('Deseja encerrar a consulta?')) return;

        try {
            await api.post(`/telemedicine/sessions/${sessionId}/end`);
            alert('Consulta encerrada com sucesso!');
            loadSessionData();
        } catch (error) {
            console.error('Erro ao encerrar:', error);
        }
    };

    if (!session) {
        return <div>Carregando...</div>;
    }

    return (
        <div className="telemedicine-container">
            {/* Área de Vídeo */}
            <div className="video-section">
                <div ref={jitsiContainerRef} id="jitsi-meet" />
                
                {!jitsiAPI && session.status === 'scheduled' && (
                    <button onClick={startVideoConference} className="btn-primary">
                        Iniciar Consulta
                    </button>
                )}

                {session.status === 'started' && !jitsiAPI && (
                    <button onClick={startVideoConference} className="btn-success">
                        Entrar na Sala
                    </button>
                )}

                {session.status === 'started' && jitsiAPI && (
                    <button onClick={endConsultation} className="btn-danger">
                        Encerrar Consulta
                    </button>
                )}
            </div>

            {/* Chat e Arquivos */}
            <div className="chat-files-section">
                {/* Chat */}
                <div className="chat-section">
                    <h3>Chat</h3>
                    <div className="messages-container" style={{ 
                        height: '300px', 
                        overflowY: 'auto',
                        border: '1px solid #ddd',
                        padding: '10px',
                        marginBottom: '10px'
                    }}>
                        {messages.map((msg) => (
                            <div key={msg.id} className={`message ${msg.type}`}>
                                <strong>{msg.sender?.name || 'Sistema'}:</strong>
                                <span>{msg.message}</span>
                                <small>{new Date(msg.created_at).toLocaleTimeString()}</small>
                            </div>
                        ))}
                        <div ref={messagesEndRef} />
                    </div>

                    <form onSubmit={sendMessage} className="message-form">
                        <input
                            type="text"
                            value={newMessage}
                            onChange={(e) => setNewMessage(e.target.value)}
                            placeholder="Digite sua mensagem..."
                            disabled={session.status !== 'started'}
                        />
                        <button type="submit" disabled={session.status !== 'started'}>
                            Enviar
                        </button>
                    </form>
                </div>

                {/* Arquivos */}
                <div className="files-section">
                    <h3>Arquivos Compartilhados</h3>
                    
                    <input
                        type="file"
                        onChange={uploadFile}
                        disabled={session.status !== 'started'}
                        style={{ marginBottom: '10px' }}
                    />

                    <div className="files-list">
                        {files.map((file) => (
                            <div key={file.id} className="file-item">
                                <a href={file.file_url} target="_blank" rel="noopener noreferrer">
                                    📎 {file.file_name}
                                </a>
                                <small>({file.getFormattedSize()}) - {file.file_category}</small>
                            </div>
                        ))}
                    </div>
                </div>
            </div>

            {/* Informações da Sessão */}
            <div className="session-info">
                <h4>Informações</h4>
                <p><strong>Status:</strong> {session.status}</p>
                <p><strong>Médico:</strong> {session.doctor?.name}</p>
                <p><strong>Paciente:</strong> {session.patient?.name}</p>
                <p><strong>Duração:</strong> {session.duration_minutes} minutos</p>
            </div>
        </div>
    );
};

export default TelemedicineSession;

// Estilos CSS (sugestão)
const styles = `
.telemedicine-container {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 20px;
    padding: 20px;
}

.video-section {
    grid-row: 1 / 2;
}

#jitsi-meet {
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.chat-files-section {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.message {
    padding: 8px;
    margin-bottom: 8px;
    border-radius: 4px;
}

.message.system {
    background-color: #f0f0f0;
    font-style: italic;
}

.btn-primary {
    background-color: #007bff;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.btn-success {
    background-color: #28a745;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.btn-danger {
    background-color: #dc3545;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}
`;
