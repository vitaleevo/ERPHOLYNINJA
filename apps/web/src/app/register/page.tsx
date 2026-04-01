'use client';

import { useState } from 'react';
import { useRouter } from 'next/navigation';
import Link from 'next/link';
import { Building, User, Mail, Phone, Lock, IdCard } from 'lucide-react';
import api from '@/lib/api';

export default function RegisterPage() {
  const router = useRouter();
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  
  // Dados da clínica
  const [clinicName, setClinicName] = useState('');
  const [clinicEmail, setClinicEmail] = useState('');
  const [clinicPhone, setClinicPhone] = useState('');
  const [clinicNif, setClinicNif] = useState('');
  const [clinicAddress, setClinicAddress] = useState('');
  
  // Dados do admin
  const [adminName, setAdminName] = useState('');
  const [adminEmail, setAdminEmail] = useState('');
  const [adminPassword, setAdminPassword] = useState('');
  const [adminPasswordConfirmation, setAdminPasswordConfirmation] = useState('');

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setError('');

    if (adminPassword !== adminPasswordConfirmation) {
      setError('As senhas não coincidem');
      setLoading(false);
      return;
    }

    try {
      const response = await api.post('/auth/register', {
        clinic_name: clinicName,
        clinic_email: clinicEmail,
        clinic_phone: clinicPhone,
        clinic_nif: clinicNif,
        clinic_address: clinicAddress,
        admin_name: adminName,
        admin_email: adminEmail,
        admin_password: adminPassword,
        admin_password_confirmation: adminPasswordConfirmation,
      });
      
      // Salvar dados do usuário e token
      localStorage.setItem('token', response.data.token);
      localStorage.setItem('clinic_id', response.data.clinic.id.toString());
      localStorage.setItem('user', JSON.stringify(response.data.user));
      
      // Redirecionar para dashboard
      router.push('/dashboard');
    } catch (err: any) {
      const errors = err.response?.data?.errors;
      if (errors) {
        const messages = Object.values(errors).flat().join('\n');
        setError(messages);
      } else {
        setError(err.response?.data?.message || 'Erro ao registrar clínica');
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-green-600 to-blue-800 py-12 px-4">
      <div className="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-2xl">
        <div className="text-center mb-8">
          <h1 className="text-3xl font-bold text-green-600">Criar Nova Clínica</h1>
          <p className="text-gray-500 mt-2">Registe sua clínica no MedAngola Cloud</p>
        </div>

        <form onSubmit={handleSubmit} className="space-y-6">
          {/* Dados da Clínica */}
          <div className="space-y-4">
            <h3 className="text-lg font-semibold text-gray-800 border-b pb-2">Dados da Clínica</h3>
            
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">Nome da Clínica</label>
              <div className="relative">
                <Building className="absolute left-3 top-3 w-5 h-5 text-gray-400" />
                <input
                  type="text"
                  value={clinicName}
                  onChange={(e) => setClinicName(e.target.value)}
                  className="w-full pl-10 px-4 py-3 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                  placeholder="Ex: Clínica Sagrada Esperança"
                  required
                />
              </div>
            </div>

            <div className="grid grid-cols-2 gap-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">Email</label>
                <div className="relative">
                  <Mail className="absolute left-3 top-3 w-5 h-5 text-gray-400" />
                  <input
                    type="email"
                    value={clinicEmail}
                    onChange={(e) => setClinicEmail(e.target.value)}
                    className="w-full pl-10 px-4 py-3 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                    placeholder="contato@clinica.com"
                    required
                  />
                </div>
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">Telefone</label>
                <div className="relative">
                  <Phone className="absolute left-3 top-3 w-5 h-5 text-gray-400" />
                  <input
                    type="tel"
                    value={clinicPhone}
                    onChange={(e) => setClinicPhone(e.target.value)}
                    className="w-full pl-10 px-4 py-3 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                    placeholder="+244 923 000 000"
                    required
                  />
                </div>
              </div>
            </div>

            <div className="grid grid-cols-2 gap-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">NIF</label>
                <div className="relative">
                  <IdCard className="absolute left-3 top-3 w-5 h-5 text-gray-400" />
                  <input
                    type="text"
                    value={clinicNif}
                    onChange={(e) => setClinicNif(e.target.value)}
                    className="w-full pl-10 px-4 py-3 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                    placeholder="500123456"
                    required
                  />
                </div>
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">Endereço</label>
                <input
                  type="text"
                  value={clinicAddress}
                  onChange={(e) => setClinicAddress(e.target.value)}
                  className="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                  placeholder="Rua, Cidade"
                />
              </div>
            </div>
          </div>

          {/* Dados do Admin */}
          <div className="space-y-4">
            <h3 className="text-lg font-semibold text-gray-800 border-b pb-2">Dados do Administrador</h3>
            
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">Nome Completo</label>
              <div className="relative">
                <User className="absolute left-3 top-3 w-5 h-5 text-gray-400" />
                <input
                  type="text"
                  value={adminName}
                  onChange={(e) => setAdminName(e.target.value)}
                  className="w-full pl-10 px-4 py-3 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                  placeholder="Dr. Administrador"
                  required
                />
              </div>
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">Email</label>
              <div className="relative">
                <Mail className="absolute left-3 top-3 w-5 h-5 text-gray-400" />
                <input
                  type="email"
                  value={adminEmail}
                  onChange={(e) => setAdminEmail(e.target.value)}
                  className="w-full pl-10 px-4 py-3 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                  placeholder="admin@clinica.com"
                  required
                />
              </div>
            </div>

            <div className="grid grid-cols-2 gap-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">Senha</label>
                <div className="relative">
                  <Lock className="absolute left-3 top-3 w-5 h-5 text-gray-400" />
                  <input
                    type="password"
                    value={adminPassword}
                    onChange={(e) => setAdminPassword(e.target.value)}
                    className="w-full pl-10 px-4 py-3 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                    placeholder="••••••••"
                    required
                    minLength={8}
                  />
                </div>
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">Confirmar Senha</label>
                <div className="relative">
                  <Lock className="absolute left-3 top-3 w-5 h-5 text-gray-400" />
                  <input
                    type="password"
                    value={adminPasswordConfirmation}
                    onChange={(e) => setAdminPasswordConfirmation(e.target.value)}
                    className="w-full pl-10 px-4 py-3 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                    placeholder="••••••••"
                    required
                    minLength={8}
                  />
                </div>
              </div>
            </div>
          </div>

          {error && (
            <div className="bg-red-50 text-red-600 p-3 rounded-lg text-sm whitespace-pre-line">{error}</div>
          )}

          <div className="flex gap-4">
            <Link
              href="/login"
              className="flex-1 bg-gray-200 text-gray-700 py-3 rounded-lg font-semibold hover:bg-gray-300 transition text-center"
            >
              Voltar ao Login
            </Link>
            
            <button
              type="submit"
              disabled={loading}
              className="flex-1 bg-green-600 text-white py-3 rounded-lg font-semibold hover:bg-green-700 transition disabled:opacity-50"
            >
              {loading ? 'Registrando...' : 'Registrar Clínica'}
            </button>
          </div>
        </form>

        <p className="text-center text-gray-500 text-sm mt-6">
          Ao registrar, você concorda com nossos termos de uso
        </p>
      </div>
    </div>
  );
}
