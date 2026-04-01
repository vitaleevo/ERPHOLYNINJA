import React, { useRef, useState } from 'react';
import { clsx } from 'clsx';
import { Upload, File, X, CheckCircle } from 'lucide-react';

export interface FileUploadProps {
  label?: string;
  accept?: string;
  multiple?: boolean;
  maxSize?: number; // in MB
  onFileSelect?: (files: File[]) => void;
  className?: string;
  disabled?: boolean;
}

export const FileUpload: React.FC<FileUploadProps> = ({
  label = 'Arraste e solte arquivos aqui ou clique para selecionar',
  accept = '*',
  multiple = false,
  maxSize = 10, // 10MB default
  onFileSelect,
  className,
  disabled = false,
}) => {
  const fileInputRef = useRef<HTMLInputElement>(null);
  const [selectedFiles, setSelectedFiles] = useState<File[]>([]);
  const [isDragging, setIsDragging] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const validateFile = (file: File): boolean => {
    const fileSizeInMB = file.size / (1024 * 1024);
    
    if (fileSizeInMB > maxSize) {
      setError(`Arquivo ${file.name} excede o tamanho máximo de ${maxSize}MB`);
      return false;
    }

    setError(null);
    return true;
  };

  const handleFiles = (files: FileList) => {
    const filesArray = Array.from(files);
    const validFiles = filesArray.filter(validateFile);

    if (validFiles.length === 0) return;

    const newSelectedFiles = multiple 
      ? [...selectedFiles, ...validFiles]
      : validFiles;

    setSelectedFiles(newSelectedFiles);
    onFileSelect?.(newSelectedFiles);
  };

  const handleDragOver = (e: React.DragEvent) => {
    e.preventDefault();
    if (!disabled) {
      setIsDragging(true);
    }
  };

  const handleDragLeave = (e: React.DragEvent) => {
    e.preventDefault();
    setIsDragging(false);
  };

  const handleDrop = (e: React.DragEvent) => {
    e.preventDefault();
    setIsDragging(false);
    
    if (!disabled && e.dataTransfer.files) {
      handleFiles(e.dataTransfer.files);
    }
  };

  const handleClick = () => {
    if (!disabled && fileInputRef.current) {
      fileInputRef.current.click();
    }
  };

  const handleFileInput = (e: React.ChangeEvent<HTMLInputElement>) => {
    if (e.target.files) {
      handleFiles(e.target.files);
    }
  };

  const removeFile = (index: number) => {
    const newFiles = selectedFiles.filter((_, i) => i !== index);
    setSelectedFiles(newFiles);
    onFileSelect?.(newFiles);
  };

  const formatFileSize = (bytes: number): string => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
  };

  return (
    <div className={clsx('w-full', className)}>
      {/* Drop Zone */}
      <div
        onClick={handleClick}
        onDragOver={handleDragOver}
        onDragLeave={handleDragLeave}
        onDrop={handleDrop}
        className={clsx(
          'border-2 border-dashed rounded-xl p-8 text-center cursor-pointer transition-all duration-200',
          isDragging
            ? 'border-blue-500 bg-blue-50'
            : 'border-gray-300 hover:border-blue-400 hover:bg-gray-50',
          disabled && 'opacity-50 cursor-not-allowed bg-gray-100'
        )}
      >
        <input
          ref={fileInputRef}
          type="file"
          accept={accept}
          multiple={multiple}
          onChange={handleFileInput}
          className="hidden"
          disabled={disabled}
        />

        <Upload className="w-12 h-12 mx-auto text-gray-400 mb-4" />
        
        <p className="text-lg font-medium text-gray-700 mb-2">
          {label}
        </p>
        
        <p className="text-sm text-gray-500">
          {multiple ? 'Múltiplos arquivos' : 'Um único arquivo'} • Tamanho máximo: {maxSize}MB
        </p>
      </div>

      {/* Error Message */}
      {error && (
        <p className="mt-2 text-sm text-red-600 flex items-center gap-2">
          <X className="w-4 h-4" />
          {error}
        </p>
      )}

      {/* Selected Files List */}
      {selectedFiles.length > 0 && (
        <div className="mt-4 space-y-2">
          <p className="text-sm font-medium text-gray-700">
            {selectedFiles.length} arquivo(s) selecionado(s)
          </p>
          
          {selectedFiles.map((file, index) => (
            <div
              key={index}
              className="flex items-center justify-between p-3 bg-white border border-gray-200 rounded-lg"
            >
              <div className="flex items-center gap-3 flex-1">
                <File className="w-5 h-5 text-blue-600" />
                <div className="flex-1 min-w-0">
                  <p className="text-sm font-medium text-gray-900 truncate">
                    {file.name}
                  </p>
                  <p className="text-xs text-gray-500">
                    {formatFileSize(file.size)}
                  </p>
                </div>
              </div>
              
              <div className="flex items-center gap-2">
                <CheckCircle className="w-5 h-5 text-green-600" />
                <button
                  onClick={() => removeFile(index)}
                  className="text-gray-400 hover:text-red-600 transition-colors"
                  disabled={disabled}
                >
                  <X className="w-5 h-5" />
                </button>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
};
