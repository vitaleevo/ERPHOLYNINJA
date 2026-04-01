'use client';

import React from 'react';
import { Settings, Info } from 'lucide-react';
import { Button } from '../../components/ui/Button';

export default function GenericModulePage() {
  return (
    <div className="flex flex-col items-center justify-center min-h-[60vh] gap-6 animate-fade-in text-center p-8">
      <div className="w-24 h-24 bg-blue-50 rounded-[2.5rem] flex items-center justify-center text-blue-600 mb-4 shadow-premium">
        <Settings className="w-10 h-10 animate-spin-slow" />
      </div>
      <div>
        <h1 className="text-3xl font-bold text-slate-900 tracking-tight mb-2">Módulo em Desenvolvimento</h1>
        <p className="text-slate-500 font-medium max-w-md mx-auto">
          Esta secção do MedAngola Cloud está a ser preparada. Em breve poderá gerir todos os dados clínicos aqui.
        </p>
      </div>
      <div className="flex gap-4">
        <Button variant="outline" onClick={() => window.history.back()}>Voltar</Button>
        <Button onClick={() => window.location.href = '/dashboard'}>Ir para Dashboard</Button>
      </div>
      
      <div className="mt-12 p-4 bg-amber-50 border border-amber-100 rounded-2xl flex items-start gap-3 max-w-lg text-left">
        <Info className="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" />
        <p className="text-xs font-bold text-amber-700 uppercase tracking-tight">
          Estamos a migrar as tabelas do seu WampServer MySQL para este novo layout premium. A sincronização de dados estará disponível em breve.
        </p>
      </div>
    </div>
  );
}
