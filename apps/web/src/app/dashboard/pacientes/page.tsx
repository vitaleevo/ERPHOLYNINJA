'use client';

import React from 'react';
import { 
  Plus, 
  Search, 
  Filter, 
  MoreVertical, 
  User, 
  Phone, 
  Calendar,
  FileText
} from 'lucide-react';
import { Button } from '../../../components/ui/Button';
import { Input } from '../../../components/ui/Input';

const mockPatients = [
  { id: 1, name: 'António Luanda', bi: '001234567LA045', phone: '923 000 000', lastVisit: '20-03-2026', status: 'Ativo' },
  { id: 2, name: 'Maria Teresa', bi: '005678901LA088', phone: '931 111 222', lastVisit: '25-03-2026', status: 'Ativo' },
  { id: 3, name: 'José Bento', bi: '002345678LA033', phone: '944 555 666', lastVisit: '28-03-2026', status: 'Inativo' },
];

export default function PacientesPage() {
  return (
    <div className="flex flex-col gap-6 animate-fade-in">
      <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-bold text-slate-900 tracking-tight">Gestão de Pacientes</h1>
          <p className="text-sm text-slate-500 font-medium">Visualize e gira todos os pacientes da clínica.</p>
        </div>
        <div className="flex items-center gap-3">
          <Button className="flex items-center gap-2">
            <Plus className="w-4 h-4" />
            Adicionar Paciente
          </Button>
        </div>
      </div>

      {/* Filters */}
      <div className="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex flex-col md:flex-row gap-4 items-center">
        <div className="flex-1 w-full">
          <Input 
            placeholder="Pesquisar por nome ou BI..." 
            icon={<Search className="w-4 h-4" />}
          />
        </div>
        <div className="flex items-center gap-3 w-full md:w-auto">
          <Button variant="outline" className="flex items-center gap-2 w-full md:w-auto">
            <Filter className="w-4 h-4" />
            Filtros
          </Button>
          <Button variant="outline" className="flex items-center gap-2 w-full md:w-auto text-slate-400">
            Exportar
          </Button>
        </div>
      </div>

      {/* Table List */}
      <div className="bg-white rounded-[2rem] border border-slate-100 shadow-premium overflow-hidden">
        <table className="w-full text-left border-collapse">
          <thead>
            <tr className="bg-slate-50 border-b border-slate-100">
              <th className="p-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest pl-8">Paciente</th>
              <th className="p-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Nº de BI</th>
              <th className="p-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Telemóvel</th>
              <th className="p-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Última Visita</th>
              <th className="p-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Status</th>
              <th className="p-5 text-[10px] font-bold text-slate-400 uppercase tracking-widest pr-8 text-right">Ações</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-slate-50">
            {mockPatients.map((p) => (
              <tr key={p.id} className="hover:bg-slate-50/50 transition-colors group">
                <td className="p-4 pl-8">
                  <div className="flex items-center gap-3">
                    <div className="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 font-bold text-sm">
                      {p.name.charAt(0)}
                    </div>
                    <div>
                      <p className="text-sm font-bold text-slate-900">{p.name}</p>
                      <p className="text-[10px] text-slate-400 font-medium uppercase tracking-tighter">ID: #{p.id}</p>
                    </div>
                  </div>
                </td>
                <td className="p-4 text-sm font-medium text-slate-500 font-mono tracking-tight">{p.bi}</td>
                <td className="p-4 text-sm font-medium text-slate-500">
                  <div className="flex items-center gap-2">
                    <Phone className="w-3.5 h-3.5 text-slate-300" />
                    {p.phone}
                  </div>
                </td>
                <td className="p-4 text-sm font-medium text-slate-500">
                  <div className="flex items-center gap-2">
                    <Calendar className="w-3.5 h-3.5 text-slate-300" />
                    {p.lastVisit}
                  </div>
                </td>
                <td className="p-4 text-center">
                   <span className={`px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-tighter border ${
                     p.status === 'Ativo' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-slate-50 text-slate-400 border-slate-100'
                   }`}>
                     {p.status}
                   </span>
                </td>
                <td className="p-4 pr-8 text-right">
                  <div className="flex items-center justify-end gap-2">
                    <button className="p-2 hover:bg-white rounded-lg text-slate-400 hover:text-blue-600 transition-all border border-transparent hover:border-slate-100 shadow-sm">
                      <FileText className="w-4 h-4" />
                    </button>
                    <button className="p-2 hover:bg-white rounded-lg text-slate-400 hover:text-slate-900 transition-all">
                      <MoreVertical className="w-4 h-4" />
                    </button>
                  </div>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
        
        <div className="p-6 bg-slate-50 flex items-center justify-between text-xs font-bold text-slate-400 uppercase tracking-widest pl-8 pr-8">
           <p>Mostrando 3 de 150 pacientes</p>
           <div className="flex gap-2">
              <Button size="sm" variant="outline" disabled>Anterior</Button>
              <Button size="sm" variant="outline">Próximo</Button>
           </div>
        </div>
      </div>
    </div>
  );
}
