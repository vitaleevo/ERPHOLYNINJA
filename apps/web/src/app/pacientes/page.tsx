'use client';

import { useEffect, useState } from 'react';
import { useRouter } from 'next/navigation';
import { 
  Button, 
  Input, 
  Card, 
  CardContent, 
  CardHeader, 
  CardTitle,
  DataTable,
  Modal,
  Tabs,
  TabPanel
} from '@/components';
import { usePatients, type Patient } from '@/lib/hooks/usePatients';
import { 
  Users, 
  Plus, 
  Pencil, 
  Trash2, 
  Search, 
  UserCheck,
  FileText,
  Calendar
} from 'lucide-react';

export default function PatientsPage() {
  const router = useRouter();
  const patientsApi = usePatients();
  const [patients, setPatients] = useState<Patient[]>([]);
  const [loading, setLoading] = useState(true);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [editingPatient, setEditingPatient] = useState<Patient | null>(null);
  const [activeTab, setActiveTab] = useState('list');

  // Form state
  const [formData, setFormData] = useState<Partial<Patient>>({
    name: '',
    email: '',
    phone: '',
    date_of_birth: '',
    gender: 'M',
    blood_type: '',
    address: '',
    emergency_contact: '',
    notes: '',
  });

  useEffect(() => {
    loadPatients();
  }, []);

  const loadPatients = async () => {
    try {
      setLoading(true);
      const data = await patientsApi.getAll();
      setPatients(data || []);
    } catch (error) {
      console.error('Erro ao carregar pacientes:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleOpenModal = (patient?: Patient) => {
    if (patient) {
      setEditingPatient(patient);
      setFormData(patient);
    } else {
      setEditingPatient(null);
      setFormData({
        name: '',
        email: '',
        phone: '',
        date_of_birth: '',
        gender: 'M',
        blood_type: '',
        address: '',
        emergency_contact: '',
        notes: '',
      });
    }
    setIsModalOpen(true);
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    try {
      if (editingPatient) {
        await patientsApi.update(editingPatient.id, formData);
      } else {
        await patientsApi.create(formData);
      }
      
      setIsModalOpen(false);
      loadPatients();
      setActiveTab('list');
    } catch (error: any) {
      alert(error.message || 'Erro ao salvar paciente');
    }
  };

  const handleDelete = async (id: number) => {
    if (!confirm('Tem certeza que deseja excluir este paciente?')) return;
    
    try {
      await patientsApi.remove(id);
      loadPatients();
    } catch (error: any) {
      alert(error.message || 'Erro ao excluir paciente');
    }
  };

  const columns = [
    {
      key: 'name',
      header: 'Nome',
      sortable: true,
      render: (value: string, item: Patient) => (
        <div className="flex items-center gap-3">
          <div className="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
            <Users className="w-4 h-4 text-blue-600" />
          </div>
          <div>
            <div className="font-medium text-gray-900">{value}</div>
            {item.email && (
              <div className="text-xs text-gray-500">{item.email}</div>
            )}
          </div>
        </div>
      ),
    },
    {
      key: 'phone',
      header: 'Telefone',
      sortable: true,
    },
    {
      key: 'date_of_birth',
      header: 'Data Nascimento',
      sortable: true,
      render: (value: string) => {
        if (!value) return '-';
        return new Date(value).toLocaleDateString('pt-BR');
      },
    },
    {
      key: 'gender',
      header: 'Gênero',
      render: (value: string) => value === 'M' ? 'Masculino' : 'Feminino',
    },
    {
      key: 'actions',
      header: 'Ações',
      render: (_: any, item: Patient) => (
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
            <h1 className="text-2xl font-bold text-gray-900">Pacientes</h1>
            <p className="text-sm text-gray-500 mt-1">
              Gerencie os pacientes da clínica
            </p>
          </div>
          <Button
            variant="primary"
            leftIcon={<Plus className="w-5 h-5" />}
            onClick={() => handleOpenModal()}
          >
            Novo Paciente
          </Button>
        </div>
      </div>

      {/* Main Content */}
      <div className="p-6 max-w-7xl mx-auto">
        {/* Tabs */}
        <Card className="mb-6">
          <CardContent className="p-0">
            <Tabs
              tabs={[
                { id: 'list', label: 'Lista de Pacientes', icon: <Users /> },
                { id: 'stats', label: 'Estatísticas', icon: <FileText /> },
              ]}
              defaultTab="list"
              onChange={setActiveTab}
              variant="pills"
            >
              <TabPanel tabId="list">
                {/* Patients Table */}
                <DataTable
                  data={patients}
                  columns={columns}
                  loading={loading}
                  search
                  searchPlaceholder="Buscar paciente por nome, email ou telefone..."
                  emptyMessage="Nenhum paciente cadastrado"
                  pagination
                  pageSize={10}
                />
              </TabPanel>

              <TabPanel tabId="stats">
                {/* Stats */}
                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                  <Card>
                    <CardContent className="p-6">
                      <div className="flex items-center gap-4">
                        <div className="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                          <Users className="w-6 h-6 text-blue-600" />
                        </div>
                        <div>
                          <p className="text-sm text-gray-600">Total Pacientes</p>
                          <p className="text-2xl font-bold text-gray-900">
                            {patients.length}
                          </p>
                        </div>
                      </div>
                    </CardContent>
                  </Card>

                  <Card>
                    <CardContent className="p-6">
                      <div className="flex items-center gap-4">
                        <div className="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                          <UserCheck className="w-6 h-6 text-green-600" />
                        </div>
                        <div>
                          <p className="text-sm text-gray-600">Pacientes Ativos</p>
                          <p className="text-2xl font-bold text-gray-900">
                            {patients.filter(p => p.created_at).length}
                          </p>
                        </div>
                      </div>
                    </CardContent>
                  </Card>

                  <Card>
                    <CardContent className="p-6">
                      <div className="flex items-center gap-4">
                        <div className="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                          <Calendar className="w-6 h-6 text-purple-600" />
                        </div>
                        <div>
                          <p className="text-sm text-gray-600">Média Idade</p>
                          <p className="text-2xl font-bold text-gray-900">
                            {patients.length > 0
                              ? Math.round(
                                  patients.reduce((acc, p) => {
                                    const birthDate = p.date_of_birth
                                      ? new Date(p.date_of_birth)
                                      : new Date();
                                    const age = new Date().getFullYear() -
                                      birthDate.getFullYear();
                                    return acc + age;
                                  }, 0) / patients.length
                                )
                              : 0}{' '}
                            anos
                          </p>
                        </div>
                      </div>
                    </CardContent>
                  </Card>
                </div>
              </TabPanel>
            </Tabs>
          </CardContent>
        </Card>
      </div>

      {/* Modal Form */}
      <Modal
        isOpen={isModalOpen}
        onClose={() => setIsModalOpen(false)}
        title={editingPatient ? 'Editar Paciente' : 'Novo Paciente'}
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
              {editingPatient ? 'Salvar Alterações' : 'Cadastrar'}
            </Button>
          </div>
        }
      >
        <form onSubmit={handleSubmit} className="space-y-4">
          <div className="grid grid-cols-2 gap-4">
            <div className="col-span-2">
              <Input
                label="Nome Completo"
                placeholder="Digite o nome completo"
                value={formData.name}
                onChange={(e) =>
                  setFormData({ ...formData, name: e.target.value })
                }
                required
              />
            </div>

            <Input
              label="Email"
              type="email"
              placeholder="email@exemplo.com"
              value={formData.email}
              onChange={(e) =>
                setFormData({ ...formData, email: e.target.value })
              }
            />

            <Input
              label="Telefone"
              placeholder="(XX) XXXXX-XXXX"
              value={formData.phone}
              onChange={(e) =>
                setFormData({ ...formData, phone: e.target.value })
              }
            />

            <Input
              label="Data de Nascimento"
              type="date"
              value={formData.date_of_birth}
              onChange={(e) =>
                setFormData({ ...formData, date_of_birth: e.target.value })
              }
            />

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">
                Gênero
              </label>
              <select
                value={formData.gender}
                onChange={(e) =>
                  setFormData({ ...formData, gender: e.target.value as 'M' | 'F' })
                }
                className="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="M">Masculino</option>
                <option value="F">Feminino</option>
              </select>
            </div>

            <Input
              label="Tipo Sanguíneo"
              placeholder="O+"
              value={formData.blood_type}
              onChange={(e) =>
                setFormData({ ...formData, blood_type: e.target.value })
              }
            />

            <div className="col-span-2">
              <Input
                label="Endereço"
                placeholder="Rua, número, bairro, cidade"
                value={formData.address}
                onChange={(e) =>
                  setFormData({ ...formData, address: e.target.value })
                }
              />
            </div>

            <div className="col-span-2">
              <Input
                label="Contato de Emergência"
                placeholder="Nome e telefone"
                value={formData.emergency_contact}
                onChange={(e) =>
                  setFormData({ ...formData, emergency_contact: e.target.value })
                }
              />
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
                placeholder="Informações adicionais sobre o paciente..."
              />
            </div>
          </div>
        </form>
      </Modal>
    </div>
  );
}
