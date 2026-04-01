'use client';

import React, { useState } from 'react';
import { Button } from '../../components/ui/Button';
import { Input } from '../../components/ui/Input';
import { Mail, Lock, Activity, ArrowRight, Github, Chrome, MessageSquare, Slack } from 'lucide-react';
import { cn } from '../../lib/utils';

export default function LoginPage() {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState('');

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');
    setIsLoading(true);

    try {
      // Direct redirect to dashboard for demo purposes as requested by "faça isso" and "eu quero ver"
      window.location.href = '/dashboard';
    } catch (err) {
      setError('Credenciais inválidas. Por favor, tente novamente.');
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="min-h-screen bg-[#f8fafc] flex items-center justify-center p-6 relative overflow-hidden">
      {/* Decorative Blobs */}
      <div className="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-blue-400/20 rounded-full blur-[120px] animate-blob"></div>
      <div className="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-cyan-400/20 rounded-full blur-[120px] animate-blob animation-delay-2000"></div>

      <div className="w-full max-w-[1100px] grid lg:grid-cols-2 gap-8 items-center relative z-10">
        
        {/* Left Side: Branding & Info */}
        <div className="hidden lg:flex flex-col gap-8 pr-12 animate-slide-right">
          <div className="flex items-center gap-3">
            <div className="w-12 h-12 bg-blue-600 rounded-2xl flex items-center justify-center text-white shadow-xl shadow-blue-500/20">
              <Activity className="w-7 h-7" />
            </div>
            <span className="text-3xl font-extrabold text-slate-900 tracking-tighter">MedAngola</span>
          </div>
          
          <div className="space-y-6">
            <h1 className="text-5xl font-bold text-slate-900 leading-[1.1] tracking-tight">
              Gestão clínica <br />
              <span className="text-blue-600">inteligente</span> e <br />
              humanizada.
            </h1>
            <p className="text-lg text-slate-500 leading-relaxed font-medium">
              Acesse a plataforma mais moderna de Angola para gestão de prontuários, 
              agendamentos e telemedicina em um só lugar.
            </p>
          </div>

          <div className="flex items-center gap-4 pt-4">
             <div className="flex -space-x-3">
               {[1,2,3,4].map(i => (
                 <img 
                  key={i} 
                  src={`https://api.dicebear.com/7.x/avataaars/svg?seed=${i * 123}`} 
                  className="w-10 h-10 rounded-full border-2 border-white bg-slate-100" 
                  alt="User"
                 />
               ))}
             </div>
             <p className="text-sm font-semibold text-slate-400">
               <span className="text-slate-900">+500 unidades</span> já utilizam
             </p>
          </div>
        </div>

        {/* Right Side: Login Form (Glass Card) */}
        <div className="w-full max-w-md mx-auto lg:mx-0 animate-fade-in">
          <div className="glass bg-white/70 border border-white/50 rounded-[2.5rem] p-10 shadow-premium backdrop-blur-2xl">
            <div className="mb-10">
              <h2 className="text-2xl font-bold text-slate-900 mb-2">Bem-vindo</h2>
              <p className="text-sm font-medium text-slate-400">Entre com as suas credenciais de acesso.</p>
            </div>

            {error && (
              <div className="mb-6 p-4 bg-red-50 border border-red-100 rounded-2xl text-red-600 text-xs font-semibold animate-shake">
                {error}
              </div>
            )}

            <form onSubmit={handleSubmit} className="space-y-5">
              <div className="space-y-1.5">
                <label className="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">E-mail Corporativo</label>
                <div className="relative group">
                  <Mail className="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 group-focus-within:text-blue-500 transition-colors" />
                  <input
                    type="email"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    className="w-full h-12 pl-11 pr-4 bg-white/50 border border-slate-200 rounded-2xl text-sm font-medium focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 focus:bg-white outline-none transition-all"
                    placeholder="exemplo@medangola.ao"
                    required
                  />
                </div>
              </div>

              <div className="space-y-1.5">
                <div className="flex justify-between items-center ml-1">
                  <label className="text-xs font-bold text-slate-500 uppercase tracking-wider">Palavra-passe</label>
                  <button type="button" className="text-[10px] font-bold text-blue-600 hover:text-blue-700 uppercase tracking-tighter">Esqueceu?</button>
                </div>
                <div className="relative group">
                  <Lock className="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 group-focus-within:text-blue-500 transition-colors" />
                  <input
                    type="password"
                    value={password}
                    onChange={(e) => setPassword(e.target.value)}
                    className="w-full h-12 pl-11 pr-4 bg-white/50 border border-slate-200 rounded-2xl text-sm font-medium focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 focus:bg-white outline-none transition-all"
                    placeholder="••••••••"
                    required
                  />
                </div>
              </div>

              <Button
                type="submit"
                isLoading={isLoading}
                className="w-full h-12 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl font-bold shadow-lg shadow-blue-500/20 text-sm flex items-center justify-center gap-2 group transform active:scale-[0.98] transition-all"
              >
                Aceder ao Painel
                <ArrowRight className="w-4 h-4 group-hover:translate-x-1 transition-transform" />
              </Button>
            </form>

            <div className="mt-8 relative">
              <div className="absolute inset-0 flex items-center">
                <span className="w-full border-t border-slate-100"></span>
              </div>
              <div className="relative flex justify-center text-[10px] uppercase font-bold text-slate-300 bg-transparent px-4 tracking-[0.2em]">
                Ou entrar com
              </div>
            </div>

            <div className="mt-8 grid grid-cols-4 gap-3">
              {[
                { icon: Chrome, color: 'text-orange-500' },
                { icon: Slack, color: 'text-blue-400' },
                { icon: MessageSquare, color: 'text-indigo-400' },
                { icon: Github, color: 'text-slate-900' },
              ].map((social, i) => (
                <button
                  key={i}
                  type="button"
                  className="flex items-center justify-center h-12 bg-white border border-slate-100 rounded-2xl hover:border-blue-200 hover:bg-blue-50 transition-all shadow-sm"
                >
                  <social.icon className={cn("w-5 h-5", social.color)} />
                </button>
              ))}
            </div>

            <p className="mt-10 text-center text-[11px] font-bold text-slate-400 uppercase tracking-widest">
              Não tem conta? <a href="/register" className="text-blue-600 hover:underline">Solicite acesso</a>
            </p>
          </div>
        </div>
      </div>
    </div>
  );
}
