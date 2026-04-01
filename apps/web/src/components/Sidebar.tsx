'use client';

import React from 'react';
import Link from 'next/link';
import { usePathname } from 'next/navigation';
import { 
  LayoutDashboard, 
  Users2, 
  Calendar, 
  Stethoscope, 
  ClipboardList, 
  Wallet, 
  Settings, 
  Plus, 
  Slack, 
  MessageSquare, 
  CheckSquare,
  ChevronDown,
  Activity,
  HeartPulse
} from 'lucide-react';
import { cn } from '../lib/utils';

const sidebarItems = [
  { icon: LayoutDashboard, label: 'Dashboard', href: '/dashboard' },
  { icon: Users2, label: 'Pacientes', href: '/dashboard/pacientes', subItems: [
    { label: 'Listagem', href: '/dashboard/pacientes' },
    { label: 'Novo Registo', href: '/dashboard/pacientes/novo' },
    { label: 'Histórico Clínico', href: '/dashboard/pacientes/historico' },
  ]},
  { icon: Calendar, label: 'Agendamentos', href: '/dashboard/agendamentos' },
  { icon: Stethoscope, label: 'Consultas', href: '/dashboard/consultas' },
  { icon: ClipboardList, label: 'Prontuários', href: '/dashboard/prontuarios' },
  { icon: Wallet, label: 'Financeiro', href: '/dashboard/financeiro' },
  { icon: Settings, label: 'Configurações', href: '/dashboard/configuracoes' },
];

const integrations = [
  { icon: Slack, label: 'Slack', color: 'text-blue-400' },
  { icon: MessageSquare, label: 'MS Teams', color: 'text-indigo-400' },
  { icon: CheckSquare, label: 'Clickup', color: 'text-purple-400' },
];

export function Sidebar() {
  const pathname = usePathname();

  return (
    <aside className="w-64 bg-[#1e293b] text-slate-400 h-screen flex flex-col flex-shrink-0 animate-slide-right">
      {/* Brand */}
      <div className="p-6 flex items-center gap-3">
        <div className="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-blue-500/20">
          <Activity className="w-6 h-6" />
        </div>
        <span className="text-xl font-bold text-white tracking-tight">MedAngola</span>
      </div>

      {/* Navigation */}
      <nav className="flex-1 px-4 py-4 space-y-1 overflow-y-auto">
        {sidebarItems.map((item) => (
          <div key={item.label}>
            <Link
              href={item.href}
              className={cn(
                "flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 group",
                pathname.startsWith(item.href) 
                  ? "bg-white/10 text-white" 
                  : "hover:bg-white/5 hover:text-white"
              )}
            >
              <item.icon className={cn(
                "w-5 h-5 transition-colors",
                pathname.startsWith(item.href) ? "text-blue-400" : "group-hover:text-blue-400"
              )} />
              <span className="flex-1 font-medium text-sm">{item.label}</span>
              {item.subItems && <ChevronDown className="w-4 h-4 opacity-50" />}
            </Link>
            
            {item.subItems && (
              <div className="mt-1 ml-11 space-y-1">
                {item.subItems.map((sub) => (
                  <Link
                    key={sub.label}
                    href={sub.href}
                    className="block py-1.5 text-sm hover:text-white transition-colors"
                  >
                    {sub.label}
                  </Link>
                ))}
              </div>
            )}
          </div>
        ))}

        <div className="pt-8 pb-4">
          <button className="flex items-center gap-3 px-3 py-2 text-sm font-medium hover:text-white transition-colors w-full">
            <Plus className="w-5 h-5 border border-dashed border-slate-600 rounded p-0.5" />
            Add new
          </button>
        </div>

        {/* Integrations */}
        <div className="space-y-1">
          {integrations.map((item) => (
            <button key={item.label} className="flex items-center gap-3 px-3 py-2 rounded-xl transition-all duration-200 hover:bg-white/5 w-full group text-left">
              <item.icon className={cn("w-5 h-5", item.color)} />
              <span className="text-sm font-medium group-hover:text-white">{item.label}</span>
            </button>
          ))}
        </div>
      </nav>

      {/* User Info */}
      <div className="p-4 border-t border-white/5">
        <div className="flex items-center gap-3 p-2 rounded-xl hover:bg-white/5 transition-colors cursor-pointer">
          <div className="w-10 h-10 rounded-full bg-slate-700 flex-shrink-0 overflow-hidden">
            <img 
              src="https://api.dicebear.com/7.x/avataaars/svg?seed=Alan" 
              alt="User" 
              className="w-full h-full object-cover"
            />
          </div>
          <div className="flex-1 min-w-0">
            <p className="text-sm font-semibold text-white truncate">Dr. Alan Wake</p>
            <p className="text-xs text-slate-500 truncate">Diretor Clínico</p>
          </div>
        </div>
      </div>
    </aside>
  );
}
