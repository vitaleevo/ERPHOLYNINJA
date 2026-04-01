import React from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '../ui/Card';
import { clsx } from 'clsx';

interface StatsCardProps {
  title: string;
  value: string | number;
  change?: string;
  changeType?: 'positive' | 'negative' | 'neutral';
  icon?: React.ReactNode;
  className?: string;
}

export const StatsCard: React.FC<StatsCardProps> = ({
  title,
  value,
  change,
  changeType = 'neutral',
  icon,
  className,
}) => {
  const changeColors = {
    positive: 'text-green-600',
    negative: 'text-red-600',
    neutral: 'text-gray-600',
  };

  return (
    <Card className={className}>
      <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
        <CardTitle className="text-sm font-medium text-gray-600">
          {title}
        </CardTitle>
        {icon && <div className="text-gray-400">{icon}</div>}
      </CardHeader>
      <CardContent>
        <div className="text-2xl font-bold text-gray-900">{value}</div>
        {change && (
          <p className={`text-xs mt-1 ${changeColors[changeType]}`}>
            {change}
          </p>
        )}
      </CardContent>
    </Card>
  );
};

interface PatientCardProps {
  name: string;
  age?: number;
  gender?: 'M' | 'F';
  lastVisit?: string;
  nextAppointment?: string;
  status?: 'active' | 'inactive' | 'critical';
  onClick?: () => void;
  className?: string;
}

export const PatientCard: React.FC<PatientCardProps> = ({
  name,
  age,
  gender,
  lastVisit,
  nextAppointment,
  status = 'active',
  onClick,
  className,
}) => {
  const statusColors = {
    active: 'bg-green-500',
    inactive: 'bg-gray-500',
    critical: 'bg-red-500',
  };

  return (
    <Card hoverable onClick={onClick} className={className}>
      <CardContent className="p-4">
        <div className="flex items-start justify-between">
          <div className="flex items-center space-x-3">
            <div className={`w-3 h-3 rounded-full ${statusColors[status]}`} />
            <div>
              <h3 className="font-semibold text-gray-900">{name}</h3>
              {(age || gender) && (
                <p className="text-sm text-gray-500">
                  {age && `${age} anos`}
                  {age && gender && ' • '}
                  {gender === 'M' ? 'Masculino' : gender === 'F' ? 'Feminino' : ''}
                </p>
              )}
            </div>
          </div>
        </div>
        {(lastVisit || nextAppointment) && (
          <div className="mt-3 space-y-1 text-sm text-gray-600">
            {lastVisit && (
              <p>Última visita: {new Date(lastVisit).toLocaleDateString('pt-BR')}</p>
            )}
            {nextAppointment && (
              <p className="text-blue-600 font-medium">
                Próxima consulta: {new Date(nextAppointment).toLocaleDateString('pt-BR')}
              </p>
            )}
          </div>
        )}
      </CardContent>
    </Card>
  );
};

interface AppointmentCardProps {
  patientName: string;
  doctorName?: string;
  specialty?: string;
  dateTime: string;
  type?: 'consulta' | 'exame' | 'retorno' | 'emergencia';
  status?: 'agendado' | 'confirmado' | 'cancelado' | 'realizado';
  location?: string;
  onClick?: () => void;
  className?: string;
}

export const AppointmentCard: React.FC<AppointmentCardProps> = ({
  patientName,
  doctorName,
  specialty,
  dateTime,
  type = 'consulta',
  status = 'agendado',
  location,
  onClick,
  className,
}) => {
  const typeColors = {
    consulta: 'bg-blue-100 text-blue-800',
    exame: 'bg-purple-100 text-purple-800',
    retorno: 'bg-green-100 text-green-800',
    emergencia: 'bg-red-100 text-red-800',
  };

  const statusColors = {
    agendado: 'bg-yellow-100 text-yellow-800',
    confirmado: 'bg-green-100 text-green-800',
    cancelado: 'bg-red-100 text-red-800',
    realizado: 'bg-blue-100 text-blue-800',
  };

  return (
    <Card hoverable onClick={onClick} className={className}>
      <CardContent className="p-4">
        <div className="flex items-center justify-between mb-3">
          <h3 className="font-semibold text-gray-900">{patientName}</h3>
          <span className={`px-2 py-1 rounded-full text-xs font-medium ${typeColors[type]}`}>
            {type}
          </span>
        </div>
        
        <div className="space-y-2 text-sm text-gray-600">
          <div className="flex items-center justify-between">
            <span>Data:</span>
            <span className="font-medium text-gray-900">
              {new Date(dateTime).toLocaleString('pt-BR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
              })}
            </span>
          </div>
          
          {doctorName && (
            <div className="flex items-center justify-between">
              <span>Médico:</span>
              <span className="font-medium text-gray-900">{doctorName}</span>
            </div>
          )}
          
          {specialty && (
            <div className="flex items-center justify-between">
              <span>Especialidade:</span>
              <span className="font-medium text-gray-900">{specialty}</span>
            </div>
          )}
          
          {location && (
            <div className="flex items-center justify-between">
              <span>Local:</span>
              <span className="font-medium text-gray-900">{location}</span>
            </div>
          )}
        </div>
        
        <div className="mt-3 flex items-center justify-between">
          <span className={`px-2 py-1 rounded-full text-xs font-medium ${statusColors[status]}`}>
            {status}
          </span>
        </div>
      </CardContent>
    </Card>
  );
};
