'use client';

import React from 'react';
import { 
  ChevronRight, 
  Share2, 
  MoreHorizontal, 
  Info, 
  Calendar, 
  Users, 
  Stethoscope, 
  FileText,
  Copy,
  Plus,
  ArrowRight,
  UserPlus,
  Wallet,
  TrendingUp,
  Clock,
  CheckCircle2,
  ClipboardList,
  Activity
} from 'lucide-react';
import { cn } from '../../lib/utils';

const stats = [
  { label: 'Consultas Hoje', value: '24', icon: Stethoscope, color: 'text-blue-600', bg: 'bg-blue-50' },
  { label: 'Novos Pacientes', value: '12', icon: UserPlus, color: 'text-emerald-600', bg: 'bg-emerald-50' },
  { label: 'Faturação Total', value: '450k Kz', icon: Wallet, color: 'text-amber-600', bg: 'bg-amber-50' },
];

const urgentAppointments = [
  { patient: 'António Luanda', time: '14:30', service: 'Cardiologia', status: 'Em Espera', avatar: 'https://api.dicebear.com/7.x/avataaars/svg?seed=Antony' },
  { patient: 'Maria Teresa', time: '15:15', service: 'Pediatria', status: 'Confirmado', avatar: 'https://api.dicebear.com/7.x/avataaars/svg?seed=Maria' },
  { patient: 'José Bento', time: '16:00', service: 'Geral', status: 'Chegou', avatar: 'https://api.dicebear.com/7.x/avataaars/svg?seed=Jose' },
];

const teamOnDuty = [
  { name: 'Dr. Alan Wake', role: 'Diretor Clínico', status: 'Em Consulta', avatar: 'https://api.dicebear.com/7.x/avataaars/svg?seed=Alan' },
  { name: 'Enf. Sarah Croft', role: 'Enfermeira Chefe', status: 'Disponível', avatar: 'https://api.dicebear.com/7.x/avataaars/svg?seed=Sarah' },
];

export default function DashboardPage() {
  return (
    <div className="flex flex-col gap-8 w-full max-w-[1400px] mx-auto animate-fade-in p-2">
      
      {/* Header & Controls */}
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold text-slate-900 tracking-tight">Painel de Gestão Clínica</h1>
          <p className="text-sm text-slate-500 font-medium">Bem-vindo ao MedAngola Cloud. Aqui está o resumo de hoje.</p>
        </div>
        <div className="flex items-center gap-3">
          <button className="flex items-center gap-2 px-4 py-2 border border-slate-200 rounded-xl bg-white text-sm font-bold text-slate-700 hover:bg-slate-50 transition-all shadow-sm">
            <Calendar className="w-4 h-4" />
            01 de Abril, 2026
          </button>
          <button className="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-bold hover:bg-blue-700 transition-all shadow-lg shadow-blue-500/20">
            <Plus className="w-4 h-4" />
            Novo Agendamento
          </button>
        </div>
      </div>

      {/* Grid Quick Stats */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        {stats.map((stat) => (
          <div key={stat.label} className="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-premium flex items-center justify-between group hover:border-blue-200 transition-all cursor-pointer">
            <div>
              <p className="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">{stat.label}</p>
              <h3 className="text-2xl font-black text-slate-900">{stat.value}</h3>
            </div>
            <div className={cn("w-12 h-12 rounded-2xl flex items-center justify-center transition-transform group-hover:scale-110", stat.bg, stat.color)}>
              <stat.icon className="w-6 h-6" />
            </div>
          </div>
        ))}
      </div>

      <div className="grid grid-cols-12 gap-8 items-start">
        {/* Left Column (8 cols): Agenda e Gráfico */}
        <div className="col-span-12 lg:col-span-8 flex flex-col gap-8">
          
          {/* Main Chart Section (Generic Clinic Metrics) */}
          <div className="bg-white rounded-[2.5rem] border border-slate-100 p-8 shadow-premium overflow-hidden relative">
            <div className="flex items-center justify-between mb-10">
               <div>
                 <h2 className="text-lg font-bold text-slate-900 flex items-center gap-2">
                   Frequência de Pacientes
                   <TrendingUp className="w-4 h-4 text-emerald-500" />
                 </h2>
                 <p className="text-xs text-slate-400 font-medium tracking-tight">Variação semanal de atendimentos</p>
               </div>
               <div className="flex bg-slate-100 p-1 rounded-xl gap-1">
                 {['Dia', 'Semana', 'Mês'].map((t, i) => (
                   <button key={t} className={cn("px-4 py-1.5 text-[10px] font-bold rounded-lg transition-all", i === 1 ? "bg-white text-blue-600 shadow-sm" : "text-slate-500 hover:text-slate-900")}>
                     {t}
                   </button>
                 ))}
               </div>
            </div>

            {/* Mock Graph (Tailwind Bars as seen in Growly design) */}
            <div className="h-64 flex items-end justify-between gap-4 px-2 relative mb-8">
               {[40, 65, 30, 85, 45, 95, 70, 55, 90, 60, 75, 40].map((val, i) => (
                 <div key={i} className="flex-1 flex flex-col items-center gap-4 group">
                    <div className="w-full relative">
                      <div 
                        style={{ height: `${val}%` }} 
                        className={cn(
                          "w-full rounded-full transition-all duration-500 transform origin-bottom hover:scale-y-105 cursor-pointer relative overflow-hidden",
                          i === 5 ? "bg-blue-600 shadow-xl shadow-blue-500/20" : "bg-slate-100 group-hover:bg-blue-100"
                        )}
                      >
                         {i === 5 && <div className="absolute inset-x-0 top-0 h-1/2 bg-white/20" />}
                      </div>
                    </div>
                    <span className="text-[9px] font-bold text-slate-400 uppercase tracking-tighter">
                      {['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'][i] || i}
                    </span>
                 </div>
               ))}
            </div>

            {/* Info Metrics Table style bottom */}
            <div className="grid grid-cols-4 gap-8 pt-6 border-t border-slate-50">
               {[
                 { label: 'Pontualidade', value: '94%', color: 'text-emerald-600' },
                 { label: 'Cancelamentos', value: '3%', color: 'text-red-500' },
                 { label: 'Média/Paciente', value: '45m', color: 'text-slate-900' },
                 { label: 'Retornos', value: '78%', color: 'text-blue-600' },
               ].map(m => (
                 <div key={m.label}>
                    <p className="text-[10px] font-bold text-slate-400 uppercase tracking-[0.1em] mb-1">{m.label}</p>
                    <p className={cn("text-base font-black truncate", m.color)}>{m.value}</p>
                 </div>
               ))}
            </div>
          </div>

          {/* Table: Próximos Atendimentos */}
          <div className="bg-white rounded-[2.5rem] border border-slate-100 p-8 shadow-premium">
             <div className="flex items-center justify-between mb-8">
                <h2 className="text-lg font-bold text-slate-900">Agenda para Hoje</h2>
                <button className="text-xs font-bold text-blue-600 hover:underline uppercase tracking-tight">Ver Agenda Completa</button>
             </div>
             
             <div className="space-y-4">
                {urgentAppointments.map((app) => (
                  <div key={app.patient} className="flex items-center gap-4 p-4 rounded-2xl hover:bg-slate-50 transition-all border border-transparent hover:border-slate-100 group cursor-pointer">
                    <img src={app.avatar} alt="Avatar" className="w-12 h-12 rounded-2xl p-0.5 border border-slate-100" />
                    <div className="flex-1 min-w-0">
                      <p className="text-sm font-bold text-slate-900 truncate">{app.patient}</p>
                      <p className="text-[11px] text-slate-400 font-medium truncate uppercase tracking-tighter">{app.service}</p>
                    </div>
                    <div className="text-right flex items-center gap-6">
                       <div>
                         <p className="text-xs font-black text-slate-900 flex items-center gap-1.5 justify-end">
                            <Clock className="w-3 h-3 text-blue-500" />
                            {app.time}
                         </p>
                         <p className="text-[10px] font-bold text-slate-400 uppercase">Horário</p>
                       </div>
                       <div className={cn(
                         "px-4 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-tighter border",
                         app.status === 'Chegou' ? "bg-emerald-50 text-emerald-600 border-emerald-100" :
                         app.status === 'Confirmado' ? "bg-blue-50 text-blue-600 border-blue-100" :
                         "bg-amber-50 text-amber-600 border-amber-100"
                       )}>
                         {app.status}
                       </div>
                    </div>
                  </div>
                ))}
             </div>
          </div>
        </div>

        {/* Right Column (4 cols) */}
        <div className="col-span-12 lg:col-span-4 flex flex-col gap-8">
          
          {/* Quick Actions Card */}
          <div className="bg-[#1e293b] rounded-[2.5rem] p-8 text-white shadow-2xl shadow-slate-900/10">
             <h3 className="text-sm font-bold uppercase tracking-[0.2em] mb-6 text-slate-400">Atalhos Clínicos</h3>
             <div className="grid grid-cols-1 gap-3">
               {[
                 { label: 'Novo Prontuário', icon: FileText, color: 'bg-white/10' },
                 { label: 'Exames Pendentes', icon: ClipboardList, color: 'bg-white/10' },
                 { label: 'Faturar Guia', icon: Wallet, color: 'bg-white/10' },
               ].map(action => (
                 <button key={action.label} className="flex items-center gap-4 p-4 bg-white/5 hover:bg-white/10 rounded-2xl transition-all group text-left border border-white/5">
                    <div className="w-10 h-10 rounded-xl bg-blue-600/20 flex items-center justify-center text-blue-400 group-hover:scale-110 transition-transform">
                       <action.icon className="w-5 h-5" />
                    </div>
                    <span className="text-sm font-bold tracking-tight">{action.label}</span>
                 </button>
               ))}
             </div>
          </div>

          {/* Team on Duty Card */}
          <div className="bg-white rounded-[2.5rem] border border-slate-100 p-8 shadow-premium">
             <h3 className="text-sm font-bold text-slate-900 mb-8 flex items-center gap-2">
               Corpo Clínico Ativo
               <span className="w-2 h-2 bg-emerald-500 rounded-full animate-pulse" />
             </h3>
             <div className="space-y-6">
               {teamOnDuty.map((member) => (
                 <div key={member.name} className="flex items-center gap-4 group cursor-pointer">
                    <div className="relative">
                      <img src={member.avatar} alt="Doctor" className="w-12 h-12 rounded-2xl p-0.5 border border-slate-100 group-hover:border-blue-400 transition-all" />
                      <div className="absolute -bottom-1 -right-1 w-4 h-4 bg-white rounded-full flex items-center justify-center border border-slate-100">
                        <div className={cn("w-2 h-2 rounded-full", member.status === 'Disponível' ? 'bg-emerald-500' : 'bg-amber-500')} />
                      </div>
                    </div>
                    <div>
                      <h4 className="text-sm font-bold text-slate-900">{member.name}</h4>
                      <p className="text-[10px] text-slate-400 font-bold uppercase tracking-tight">{member.role}</p>
                    </div>
                 </div>
               ))}
             </div>
             
             <button className="w-full mt-10 py-4 rounded-2xl bg-slate-50 text-slate-400 text-xs font-bold hover:bg-slate-100 hover:text-slate-900 transition-all uppercase tracking-widest flex items-center justify-center gap-2">
                Escala do Dia
                <ArrowRight className="w-4 h-4" />
             </button>
          </div>

          {/* Storage / Usage simple card */}
          <div className="bg-blue-600 rounded-[2.5rem] p-8 text-white relative overflow-hidden group cursor-pointer shadow-xl shadow-blue-500/20">
             <div className="relative z-10">
               <h3 className="text-sm font-bold mb-4">MedAngola Cloud Storage</h3>
               <div className="flex items-baseline gap-2 mb-6">
                 <span className="text-4xl font-black">78%</span>
                 <span className="text-xs font-bold text-blue-200">em uso</span>
               </div>
               <div className="h-2 w-full bg-white/20 rounded-full overflow-hidden mb-4">
                  <div className="h-full w-[78%] bg-white rounded-full translate-x-[-100%] animate-[slideRight_1.5s_ease-out_forwards]" />
               </div>
               <p className="text-[10px] font-bold text-blue-100 uppercase tracking-widest">
                 Backup sincronizado agora
               </p>
             </div>
             <Activity className="absolute -bottom-8 -right-8 w-32 h-32 text-white/5 rotate-12 group-hover:scale-110 transition-transform" />
          </div>

        </div>
      </div>
    </div>
  );
}
