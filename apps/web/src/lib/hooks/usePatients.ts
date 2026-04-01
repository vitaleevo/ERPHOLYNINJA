import { useApi } from '../useApi';

export interface Patient {
  id: number;
  clinic_id: number;
  name: string;
  email?: string;
  phone?: string;
  date_of_birth?: string;
  gender?: 'M' | 'F';
  blood_type?: string;
  address?: string;
  emergency_contact?: string;
  notes?: string;
  created_at: string;
  updated_at: string;
}

export function usePatients() {
  const api = useApi<{ patients: Patient[] }>();

  const getAll = async () => {
    const response = await api.get('/patients');
    return response.patients;
  };

  const getById = async (id: number) => {
    const response = await api.get(`/patients/${id}`);
    return response.patient;
  };

  const create = async (data: Partial<Patient>) => {
    const response = await api.post('/patients', data);
    return response.patient;
  };

  const update = async (id: number, data: Partial<Patient>) => {
    const response = await api.put(`/patients/${id}`, data);
    return response.patient;
  };

  const remove = async (id: number) => {
    await api.delete(`/patients/${id}`);
  };

  return {
    ...api,
    getAll,
    getById,
    create,
    update,
    remove,
  };
}
