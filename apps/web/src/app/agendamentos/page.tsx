'use client';

import { useEffect, useState } from 'react';
import { 
  Button, 
  Card, 
  CardContent, 
  CardHeader, 
  CardTitle,
  DataTable,
  Modal,
  Input
} from '@/components';
import { Calendar, Plus, Pencil, Trash2, Clock, User, FileText } from 'lucide-react';
import api from '@/lib/api';

interface Appointment {
  id: number;
  clinic_id: number;
  patient_id: number;
  doctor_id: number;
  date: string;
  time: string;
  status: 'agendado' | 'confirmado' | 'cancelado' | 'realizado';
  type: 'consulta' | 'exame' | 'retorno' | 'emergencia';
  notes?: string;
  patient_name?: string;
  doctor_name?: string;
  created_at: string;
  updated_at: string;
}

export default function AppointmentsPage() {
  const [appointments, setAppointments] = useState<Appointment[]>([]);
  const [loading, setLoading] = useState(true);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [editingAppointment, setEditingAppointment] = useState<Appointment | null>(null);
  const [filterStatus, setFilterStatus] = useState<string>('all');

  // Form state
  const [formData, setFormData] = useState<Partial<Appointment>>({
    patient_id: 0,
    doctor_id: 0,
    date: new Date().toISOString().split('T')[0],
    time: '09:00',
    status: 'agendado',
    type: 'consulta',
    notes: '',
  });

  // Patients and doctors lists
  const [patients, setPatients] = useState<any[]>([]);
  const [doctors, setDoctors] = useState<any[]>([]);

  useEffect(() => {
    loadAppointments();
    loadPatients();
    loadDoctors();
  }, []);

  const loadAppointments = async () => {
    try {
      setLoading(true);
      const response = await api.get('/appointments');
      setAppointments(response.data.appointments || []);
    } catch (error) {
      console.error('Erro ao carregar agendamentos:', error);
    } finally {
      setLoading(false);
    }
  };

  const loadPatients = async () => {
    try {
      const response = await api.get('/patients');
      setPatients(response.data.patients || []);
    } catch (error) {
      console.error('Erro ao carregar pacientes:', error);
    }
  };

  const loadDoctors = async () => {
    try {
      const response = await api.get('/users');
      setDoctors(response.data.users?.filter((u: any) => u.role === 'doctor') || []);
    } catch (error) {
      console.error('Erro ao carregar médicos:', error);
    }
  };

  const handleOpenModal = (appointment?: Appointment) => {
    if (appointment) {
      setEditingAppointment(appointment);
      setFormData(appointment);
    } else {
      setEditingAppointment(null);
      setFormData({
        patient_id: patients[0]?.id || 0,
        doctor_id: doctors[0]?.id || 0,
        date: new Date().toISOString().split('T')[0],
        time: '09:00',
        status: 'agendado',
        type: 'consulta',
        notes: '',
      });
    }
    setIsModalOpen(true);
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    try {
      if (editingAppointment) {
        await api.put(`/appointments/${editingAppointment.id}`, formData);
      } else {
        await api.post('/appointments', formData);
      }
      
      setIsModalOpen(false);
      loadAppointments();
    } catch (error: any) {
      alert(error.response?.data?.message || 'Erro ao salvar agendamento');
    }
  };

  const handleDelete = async (id: number) => {
    if (!confirm('Tem certeza que deseja excluir este agendamento?')) return;
    
    try {
      await api.delete(`/appointments/${id}`);
      loadAppointments();
    } catch (error: any) {
      alert(error.response?.data?.message || 'Erro ao excluir agendamento');
    }
  };

  const filteredAppointments = filterStatus === 'all'
    ? appointments
    : appointments.filter(a => a.status === filterStatus);

  const columns = [
    {
      key: 'patient_name',
      header: 'Paciente',
      sortable: true,
      render: (value: string, item: Appointment) => (
        <div className="flex items-center gap-3">
          <div className="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
            <User className="w-4 h-4 text-blue-600" />
          </div>
          <div>
            <div className="font-medium text-gray-900">{value || 'N/A'}</div>
            <div className="text-xs text-gray-500">{item.type}</div>
          </div>
        </div>
      ),
    },
    {
      key: 'date',
      header: 'Data/Hora',
      sortable: true,
      render: (value: string, item: Appointment) => (
        <div>
          <div className="font-medium text-gray-900">
            {new Date(value).toLocaleDateString('pt-BR')}
          </div>
          <div className="text-xs text-gray-500 flex items-center gap-1 mt-1">
            <Clock className="w-3 h-3" />
            {item.time}
          </div>
        </div>
      ),
    },
    {
      key: 'status',
      header: 'Status',
      render: (value: string) => {
        const colors: Record<string, string> = {
          agendado: 'bg-yellow-100 text-yellow-800',
          confirmado: 'bg-green-100 text-green-800',
          cancelado: 'bg-red-100 text-red-800',
          realizado: 'bg-blue-100 text-blue-800',
        };
        return (
          <span className={`px-2 py-1 rounded-full text-xs font-medium ${colors[value] || 'bg-gray-100 text-gray-800'}`}>
            {value}
          </span>
        );
      },
    },
    {
      key: 'notes',
      header: 'Observações',
      render: (value: string) => (
        <div className="max-w-xs truncate" title={value}>
          {value || '-'}
        </div>
      ),
    },
    {
      key: 'actions',
      header: 'Ações',
      render: (_: any, item: Appointment) => (
        <div className="flex gap-2">
          <button
            onClick={() => handleOpenModal(item)}
            className="text-blue-600 hover:text-blue-800 transition-colors"
            title="Editar"
          >
            <Pencil className="w-4 h-4" />
          </button>
          <button
            onClick={() => handleDelete(item.id)}
            className="text-red-600 hover:text-red-800 transition-colors"
            title="Excluir"
          >
            <Trash2 className="w-4 h-4" />
          </button>
        </div>
      ),
    },
  ];

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Header */}
      <div className="bg-white border-b border-gray-200 px-6 py-4">
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-2xl font-bold text-gray-900">Agendamentos</h1>
            <p className="text-sm text-gray-500 mt-1">
              Gerencie as consultas e exames da clínica
            </p>
          </div>
          <Button
            variant="primary"
            leftIcon={<Plus className="w-5 h-5" />}
            onClick={() => handleOpenModal()}
          >
            Novo Agendamento
          </Button>
        </div>
      </div>

      {/* Main Content */}
      <div className="p-6 max-w-7xl mx-auto space-y-6">
        {/* Stats Cards */}
        <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
          <Card>
            <CardContent className="p-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-gray-600">Hoje</p>
                  <p className="text-2xl font-bold text-gray-900">
                    {appointments.filter(a => 
                      new Date(a.date).toDateString() === new Date().toDateString()
                    ).length}
                  </p>
                </div>
                <div className="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                  <Calendar className="w-6 h-6 text-blue-600" />
                </div>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardContent className="p-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-gray-600">Agendados</p>
                  <p className="text-2xl font-bold text-gray-900">
                    {appointments.filter(a => a.status === 'agendado').length}
                  </p>
                </div>
                <div className="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                  <Clock className="w-6 h-6 text-yellow-600" />
                </div>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardContent className="p-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-gray-600">Confirmados</p>
                  <p className="text-2xl font-bold text-gray-900">
                    {appointments.filter(a => a.status === 'confirmado').length}
                  </p>
                </div>
                <div className="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                  <FileText className="w-6 h-6 text-green-600" />
                </div>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardContent className="p-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-gray-600">Total</p>
                  <p className="text-2xl font-bold text-gray-900">
                    {appointments.length}
                  </p>
                </div>
                <div className="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                  <User className="w-6 h-6 text-purple-600" />
                </div>
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Filters */}
        <Card>
          <CardHeader>
            <CardTitle>Todos os Agendamentos</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="mb-4 flex gap-2 flex-wrap">
              <Button
                variant={filterStatus === 'all' ? 'primary' : 'secondary'}
                size="sm"
                onClick={() => setFilterStatus('all')}
              >
                Todos
              </Button>
              <Button
                variant={filterStatus === 'agendado' ? 'primary' : 'secondary'}
                size="sm"
                onClick={() => setFilterStatus('agendado')}
              >
                Agendados
              </Button>
              <Button
                variant={filterStatus === 'confirmado' ? 'primary' : 'secondary'}
                size="sm"
                onClick={() => setFilterStatus('confirmado')}
              >
                Confirmados
              </Button>
              <Button
                variant={filterStatus === 'realizado' ? 'primary' : 'secondary'}
                size="sm"
                onClick={() => setFilterStatus('realizado')}
              >
                Realizados
              </Button>
              <Button
                variant={filterStatus === 'cancelado' ? 'primary' : 'secondary'}
                size="sm"
                onClick={() => setFilterStatus('cancelado')}
              >
                Cancelados
              </Button>
            </div>

            {/* Table */}
            <DataTable
              data={filteredAppointments}
              columns={columns}
              loading={loading}
              search
              searchPlaceholder="Buscar por paciente..."
              emptyMessage="Nenhum agendamento encontrado"
              pagination
              pageSize={10}
            />
          </CardContent>
        </Card>
      </div>

      {/* Modal Form */}
      <Modal
        isOpen={isModalOpen}
        onClose={() => setIsModalOpen(false)}
        title={editingAppointment ? 'Editar Agendamento' : 'Novo Agendamento'}
        size="lg"
        footer={
          <div className="flex justify-end gap-3">
            <Button
              variant="secondary"
              onClick={() => setIsModalOpen(false)}
            >
              Cancelar
            </Button>
            <Button
              variant="primary"
              onClick={(e: any) => handleSubmit(e)}
              type="submit"
            >
              {editingAppointment ? 'Salvar Alterações' : 'Agendar'}
            </Button>
          </div>
        }
      >
        <form onSubmit={handleSubmit} className="space-y-4">
          <div className="grid grid-cols-2 gap-4">
            <div className="col-span-2">
              <label className="block text-sm font-medium text-gray-700 mb-1">
                Paciente
              </label>
              <select
                value={formData.patient_id}
                onChange={(e) =>
                  setFormData({ ...formData, patient_id: Number(e.target.value) })
                }
                className="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                required
              >
                <option value="">Selecione um paciente</option>
                {patients.map(p => (
                  <option key={p.id} value={p.id}>{p.name}</option>
                ))}
              </select>
            </div>

            <div className="col-span-2">
              <label className="block text-sm font-medium text-gray-700 mb-1">
                Médico
              </label>
              <select
                value={formData.doctor_id}
                onChange={(e) =>
                  setFormData({ ...formData, doctor_id: Number(e.target.value) })
                }
                className="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                required
              >
                <option value="">Selecione um médico</option>
                {doctors.map(d => (
                  <option key={d.id} value={d.id}>{d.name}</option>
                ))}
              </select>
            </div>

            <Input
              label="Data"
              type="date"
              value={formData.date}
              onChange={(e) =>
                setFormData({ ...formData, date: e.target.value })
              }
              required
            />

            <Input
              label="Hora"
              type="time"
              value={formData.time}
              onChange={(e) =>
                setFormData({ ...formData, time: e.target.value })
              }
              required
            />

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">
                Tipo
              </label>
              <select
                value={formData.type}
                onChange={(e) =>
                  setFormData({ ...formData, type: e.target.value as any })
                }
                className="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="consulta">Consulta</option>
                <option value="exame">Exame</option>
                <option value="retorno">Retorno</option>
                <option value="emergencia">Emergência</option>
              </select>
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">
                Status
              </label>
              <select
                value={formData.status}
                onChange={(e) =>
                  setFormData({ ...formData, status: e.target.value as any })
                }
                className="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="agendado">Agendado</option>
                <option value="confirmado">Confirmado</option>
                <option value="realizado">Realizado</option>
                <option value="cancelado">Cancelado</option>
              </select>
            </div>

            <div className="col-span-2">
              <label className="block text-sm font-medium text-gray-700 mb-1">
                Observações
              </label>
              <textarea
                rows={4}
                value={formData.notes}
                onChange={(e) =>
                  setFormData({ ...formData, notes: e.target.value })
                }
                className="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Informações adicionais sobre o agendamento..."
              />
            </div>
          </div>
        </form>
      </Modal>
    </div>
  );
}
