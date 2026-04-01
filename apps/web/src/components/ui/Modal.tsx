import React from 'react';

interface ModalProps {
  isOpen: boolean;
  onClose: () => void;
  title?: string;
  size?: 'sm' | 'md' | 'lg' | 'xl';
  footer?: React.ReactNode;
  children: React.ReactNode;
}

const sizeStyles: Record<NonNullable<ModalProps['size']>, string> = {
  sm: 'max-w-md',
  md: 'max-w-lg',
  lg: 'max-w-2xl',
  xl: 'max-w-4xl',
};

export function Modal({
  isOpen,
  onClose,
  title,
  size = 'md',
  footer,
  children,
}: ModalProps) {
  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center px-4">
      <div
        className="absolute inset-0 bg-black/40"
        onClick={onClose}
        aria-hidden="true"
      />
      <div className={`relative w-full ${sizeStyles[size]} rounded-2xl bg-white shadow-2xl`}>
        <div className="flex items-center justify-between border-b border-gray-200 px-6 py-4">
          {title && <h2 className="text-lg font-semibold text-gray-900">{title}</h2>}
          <button
            onClick={onClose}
            className="rounded-full p-2 text-gray-500 hover:bg-gray-100"
            aria-label="Fechar"
          >
            ✕
          </button>
        </div>
        <div className="px-6 py-5">{children}</div>
        {footer && (
          <div className="border-t border-gray-200 px-6 py-4">{footer}</div>
        )}
      </div>
    </div>
  );
}
