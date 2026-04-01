import React from 'react';
import { TrendingUp, TrendingDown, Minus } from 'lucide-react';

interface StatsCardProps {
  title: string;
  value: string | number;
  change?: string;
  changeType?: 'positive' | 'negative' | 'neutral';
  icon: React.ReactNode;
  className?: string;
}

export const StatsCard: React.FC<StatsCardProps> = ({
  title,
  value,
  change,
  changeType = 'neutral',
  icon,
  className = ''
}) => {
  const changeStyles = {
    positive: 'text-green-600 bg-green-50',
    negative: 'text-red-600 bg-red-50',
    neutral: 'text-gray-600 bg-gray-50',
  };

  const ChangeIcon = changeType === 'positive' ? TrendingUp : changeType === 'negative' ? TrendingDown : Minus;

  return (
    <div className={`card p-6 ${className}`}>
      <div className="flex items-center justify-between mb-4">
        <div className="p-3 rounded-lg bg-[var(--primary-50)] text-[var(--primary-600)]">
          {icon}
        </div>
        {change && (
          <div className={`flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium ${changeStyles[changeType]}`}>
            <ChangeIcon className="w-3 h-3" />
            <span>{change}</span>
          </div>
        )}
      </div>
      
      <div>
        <p className="text-sm text-[var(--gray-600)] mb-1">{title}</p>
        <p className="text-2xl font-bold text-[var(--foreground)]">{value}</p>
      </div>
    </div>
  );
};
