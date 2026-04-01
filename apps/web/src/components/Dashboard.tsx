'use client';

import { useState } from 'react';
import { format } from 'date-fns';
import { ptBR } from 'date-fns/locale';
import { Calendar, Clock, User, Phone, Mail, Search, Plus, ChevronLeft, ChevronRight } from 'lucide-react';
import { useStore } from '@/lib/store';
import api from '@/lib/api';
import type { Patient, Appointment, User as UserType } from '@/types';

interface SidebarProps {
  children: React.ReactNode;
}

export default function DashboardLayout({ children }: SidebarProps) {
  const [sidebarOpen, setSidebarOpen] = useState(true);
  
  return (
    <div className="flex h-screen bg-gray-50">
      <aside className={`${sidebarOpen ? 'w-64' : 'w-16'} bg-white border-r transition-all duration-300`}>
        <div className="p-4 border-b">
          <h1 className="text-xl font-bold text-blue-600">MedAngola</h1>
        </div>
        <nav className="p-2">
          <NavItem icon={<Calendar />} label="Agenda" href="/agenda" />
          <NavItem icon={<User />} label="Pacientes" href="/pacientes" />
          <NavItem icon={<Clock />} label="Consultas" href="/consultas" />
        </nav>
      </aside>
      <main className="flex-1 overflow-auto">{children}</main>
    </div>
  );
}

function NavItem({ icon, label, href }: { icon: React.ReactNode; label: string; href: string }) {
  return (
    <a href={href} className="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-100 text-gray-700">
      <span className="w-5 h-5">{icon}</span>
      {label}
    </a>
  );
}

export function AppointmentCard({ appointment }: { appointment: Appointment }) {
  const statusColors = {
    scheduled: 'bg-blue-100 text-blue-800',
    confirmed: 'bg-green-100 text-green-800',
    in_progress: 'bg-yellow-100 text-yellow-800',
    completed: 'bg-gray-100 text-gray-800',
    cancelled: 'bg-red-100 text-red-800',
    no_show: 'bg-orange-100 text-orange-800',
  };

  return (
    <div className="bg-white p-4 rounded-lg shadow-sm border hover:shadow-md transition">
      <div className="flex justify-between items-start mb-2">
        <span className={`px-2 py-1 rounded text-xs font-medium ${statusColors[appointment.status]}`}>
          {appointment.status}
        </span>
        <span className="text-sm text-gray-500">{appointment.room}</span>
      </div>
      <h3 className="font-semibold">{appointment.patient?.name}</h3>
      <p className="text-sm text-gray-600">{appointment.doctor?.name}</p>
      <div className="flex items-center gap-2 mt-2 text-sm text-gray-500">
        <Calendar className="w-4 h-4" />
        {format(new Date(appointment.scheduled_at), 'dd/MM/yyyy', { locale: ptBR })}
        <Clock className="w-4 h-4 ml-2" />
        {format(new Date(appointment.scheduled_at), 'HH:mm')}
      </div>
    </div>
  );
}

export function PatientForm({ onSuccess }: { onSuccess?: () => void }) {
  const [formData, setFormData] = useState({
    name: '',
    email: '',
    phone: '',
    birth_date: '',
    gender: '',
    address: '',
    allergies: '',
    medical_history: '',
  });

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      await api.post('/patients', formData);
      onSuccess?.();
    } catch (error) {
      console.error(error);
    }
  };

  return (
    <form onSubmit={handleSubmit} className="space-y-4">
      <div className="grid grid-cols-2 gap-4">
        <Input label="Nome" name="name" value={formData.name} onChange={(e) => setFormData({...formData, name: e.target.value})} required />
        <Input label="Email" name="email" type="email" value={formData.email} onChange={(e) => setFormData({...formData, email: e.target.value})} />
        <Input label="Telefone" name="phone" value={formData.phone} onChange={(e) => setFormData({...formData, phone: e.target.value})} required />
        <Input label="Data de Nascimento" name="birth_date" type="date" value={formData.birth_date} onChange={(e) => setFormData({...formData, birth_date: e.target.value})} />
        <Select label="Género" name="gender" value={formData.gender} onChange={(e) => setFormData({...formData, gender: e.target.value})} options={[
          { value: '', label: 'Selecione' },
          { value: 'male', label: 'Masculino' },
          { value: 'female', label: 'Feminino' },
          { value: 'other', label: 'Outro' },
        ]} />
      </div>
      <div>
        <label className="block text-sm font-medium mb-1">Endereço</label>
        <textarea name="address" value={formData.address} onChange={(e) => setFormData({...formData, address: e.target.value})} className="w-full p-2 border rounded-lg" rows={2} />
      </div>
      <div className="grid grid-cols-2 gap-4">
        <div>
          <label className="block text-sm font-medium mb-1">Alergias</label>
          <textarea name="allergies" value={formData.allergies} onChange={(e) => setFormData({...formData, allergies: e.target.value})} className="w-full p-2 border rounded-lg" rows={2} />
        </div>
        <div>
          <label className="block text-sm font-medium mb-1">Histórico Médico</label>
          <textarea name="medical_history" value={formData.medical_history} onChange={(e) => setFormData({...formData, medical_history: e.target.value})} className="w-full p-2 border rounded-lg" rows={2} />
        </div>
      </div>
      <button type="submit" className="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
        <Plus className="w-4 h-4 inline mr-2" /> Cadastrar Paciente
      </button>
    </form>
  );
}

function Input({ label, name, type = 'text', value, onChange, required }: any) {
  return (
    <div>
      <label className="block text-sm font-medium mb-1">{label}</label>
      <input type={type} name={name} value={value} onChange={onChange} required={required} className="w-full p-2 border rounded-lg" />
    </div>
  );
}

function Select({ label, name, value, onChange, options }: any) {
  return (
    <div>
      <label className="block text-sm font-medium mb-1">{label}</label>
      <select name={name} value={value} onChange={onChange} className="w-full p-2 border rounded-lg">
        {options.map((opt: any) => <option key={opt.value} value={opt.value}>{opt.label}</option>)}
      </select>
    </div>
  );
}
