import React, { useState } from 'react';

interface Tab {
  id: string;
  label: string;
  icon?: React.ReactNode;
}

interface TabsProps {
  tabs: Tab[];
  defaultTab?: string;
  onChange?: (tabId: string) => void;
  variant?: 'pills' | 'underline';
  children: React.ReactNode;
}

interface TabPanelProps {
  tabId: string;
  children: React.ReactNode;
}

export function Tabs({
  tabs,
  defaultTab,
  onChange,
  variant = 'underline',
  children,
}: TabsProps) {
  const initial = defaultTab || tabs[0]?.id;
  const [activeTab, setActiveTab] = useState(initial);

  const handleChange = (tabId: string) => {
    setActiveTab(tabId);
    onChange?.(tabId);
  };

  const buttonBase = 'flex items-center gap-2 px-4 py-3 text-sm font-medium transition-colors';
  const variants = {
    pills: {
      container: 'flex flex-wrap gap-2 border-b border-gray-200 px-4 py-3',
      active: 'rounded-full bg-blue-600 text-white',
      inactive: 'rounded-full text-gray-600 hover:bg-gray-100',
    },
    underline: {
      container: 'flex gap-4 border-b border-gray-200 px-4',
      active: 'border-b-2 border-blue-600 text-blue-600',
      inactive: 'text-gray-600 hover:text-gray-900',
    },
  };

  return (
    <div>
      <div className={variants[variant].container}>
        {tabs.map((tab) => (
          <button
            key={tab.id}
            onClick={() => handleChange(tab.id)}
            className={`${buttonBase} ${
              activeTab === tab.id ? variants[variant].active : variants[variant].inactive
            }`}
          >
            {tab.icon && <span className="h-4 w-4">{tab.icon}</span>}
            {tab.label}
          </button>
        ))}
      </div>

      <div className="px-4 py-5">
        {React.Children.map(children, (child) => {
          if (!React.isValidElement<TabPanelProps>(child)) return null;
          return child.props.tabId === activeTab ? child : null;
        })}
      </div>
    </div>
  );
}

export function TabPanel({ children }: TabPanelProps) {
  return <div>{children}</div>;
}
