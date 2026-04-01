'use client';

import { Activity, Users, FileText, Shield, TrendingUp, CheckCircle } from 'lucide-react';
import { Button } from '../components/ui/Button';

export default function HomePage() {
  const features = [
    {
      icon: <Activity className="w-6 h-6" />,
      title: 'Gestão Completa',
      description: 'Controle total de consultas, pacientes e prontuários em um só lugar.',
    },
    {
      icon: <Users className="w-6 h-6" />,
      title: 'Multi-usuário',
      description: 'Acesso diferenciado para médicos, recepcionistas e administradores.',
    },
    {
      icon: <FileText className="w-6 h-6" />,
      title: 'Prontuários Eletrônicos',
      description: 'Histórico completo dos pacientes com segurança e privacidade.',
    },
    {
      icon: <Shield className="w-6 h-6" />,
      title: 'Segurança Total',
      description: 'Dados criptografados e backup automático na nuvem.',
    },
    {
      icon: <TrendingUp className="w-6 h-6" />,
      title: 'Relatórios Detalhados',
      description: 'Analytics completo para melhor gestão da sua clínica.',
    },
    {
      icon: <CheckCircle className="w-6 h-6" />,
      title: 'Suporte Dedicado',
      description: 'Equipe especializada pronta para ajudar você.',
    },
  ];

  return (
    <div className="min-h-screen bg-gradient-to-br from-[var(--primary-50)] via-white to-[var(--secondary-50)]">
      {/* Header */}
      <header className="border-b border-[var(--border)] bg-white/80 backdrop-blur-md sticky top-0 z-50">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex items-center justify-between h-16">
            <div className="flex items-center gap-2">
              <div className="w-8 h-8 bg-gradient-to-br from-[var(--primary-600)] to-[var(--primary-800)] rounded-lg flex items-center justify-center">
                <Activity className="w-5 h-5 text-white" />
              </div>
              <span className="text-xl font-bold text-gradient">MedAngola Cloud</span>
            </div>
            
            <div className="flex items-center gap-4">
              <a href="/login" className="text-sm font-medium text-[var(--gray-700)] hover:text-[var(--primary-600)] transition-colors">
                Entrar
              </a>
              <Button variant="primary" size="sm" onClick={() => window.location.href = '/dashboard'}>
                Ir para o Dashboard
              </Button>
            </div>
          </div>
        </div>
      </header>

      {/* Hero Section */}
      <section className="py-20 px-4 sm:px-6 lg:px-8">
        <div className="max-w-7xl mx-auto text-center">
          <h1 className="text-5xl md:text-6xl font-bold text-[var(--foreground)] mb-6">
            Gestão Inteligente para<br />
            <span className="text-gradient">Clínicas Modernas</span>
          </h1>
          <p className="text-xl text-[var(--gray-600)] mb-8 max-w-3xl mx-auto">
            A plataforma completa para administrar sua clínica com eficiência, 
            segurança e tecnologia de ponta.
          </p>
          <div className="flex gap-4 justify-center">
            <Button variant="primary" size="lg" onClick={() => window.location.href = '/register'}>
              Testar Gratuitamente
            </Button>
            <Button variant="outline" size="lg" onClick={() => window.location.href = '/login'}>
              Fazer Login
            </Button>
          </div>
        </div>
      </section>

      {/* Features Section */}
      <section className="py-20 px-4 sm:px-6 lg:px-8 bg-white">
        <div className="max-w-7xl mx-auto">
          <div className="text-center mb-16">
            <h2 className="text-3xl md:text-4xl font-bold text-[var(--foreground)] mb-4">
              Tudo o que você precisa
            </h2>
            <p className="text-lg text-[var(--gray-600)] max-w-2xl mx-auto">
              Funcionalidades completas para otimizar a gestão da sua clínica
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            {features.map((feature, index) => (
              <div key={index} className="card p-6 hover:shadow-lg transition-shadow">
                <div className="w-12 h-12 bg-[var(--primary-50)] rounded-xl flex items-center justify-center text-[var(--primary-600)] mb-4">
                  {feature.icon}
                </div>
                <h3 className="text-xl font-semibold text-[var(--foreground)] mb-2">
                  {feature.title}
                </h3>
                <p className="text-[var(--gray-600)]">{feature.description}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* CTA Section */}
      <section className="py-20 px-4 sm:px-6 lg:px-8 bg-gradient-to-r from-[var(--primary-600)] to-[var(--primary-800)]">
        <div className="max-w-4xl mx-auto text-center">
          <h2 className="text-3xl md:text-4xl font-bold text-white mb-6">
            Pronto para transformar sua clínica?
          </h2>
          <p className="text-xl text-[var(--primary-100)] mb-8">
            Junte-se a centenas de clínicas que já usam MedAngola Cloud
          </p>
          <Button 
            variant="secondary" 
            size="lg" 
            className="bg-white text-[var(--primary-600)] hover:bg-[var(--primary-50)]"
            onClick={() => window.location.href = '/register'}
          >
            Começar Teste Gratuito
          </Button>
        </div>
      </section>

      {/* Footer */}
      <footer className="bg-[var(--gray-900)] text-white py-12 px-4 sm:px-6 lg:px-8">
        <div className="max-w-7xl mx-auto">
          <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div>
              <div className="flex items-center gap-2 mb-4">
                <div className="w-8 h-8 bg-gradient-to-br from-[var(--primary-600)] to-[var(--primary-800)] rounded-lg flex items-center justify-center">
                  <Activity className="w-5 h-5 text-white" />
                </div>
                <span className="text-xl font-bold">MedAngola Cloud</span>
              </div>
              <p className="text-[var(--gray-400)] text-sm">
                Plataforma SaaS para gestão de clínicas em Angola.
              </p>
            </div>
            
            <div>
              <h3 className="font-semibold mb-4">Links Rápidos</h3>
              <ul className="space-y-2 text-sm text-[var(--gray-400)]">
                <li><a href="/login" className="hover:text-white transition-colors">Login</a></li>
                <li><a href="/register" className="hover:text-white transition-colors">Registro</a></li>
                <li><a href="#" className="hover:text-white transition-colors">Sobre</a></li>
              </ul>
            </div>
            
            <div>
              <h3 className="font-semibold mb-4">Contato</h3>
              <ul className="space-y-2 text-sm text-[var(--gray-400)]">
                <li>contato@medangola.com</li>
                <li>+244 923 000 000</li>
                <li>Luanda, Angola</li>
              </ul>
            </div>
          </div>
          
          <div className="mt-8 pt-8 border-t border-[var(--gray-800)] text-center text-sm text-[var(--gray-400)]">
            <p>&copy; 2026 MedAngola Cloud. Todos os direitos reservados.</p>
          </div>
        </div>
      </footer>
    </div>
  );
}
