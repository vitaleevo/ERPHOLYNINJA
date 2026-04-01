import { useState } from 'react';
import api from './api';

interface UploadProgress {
  loaded: number;
  total: number;
  percentage: number;
}

interface UseFileUploadOptions {
  endpoint?: string;
  fieldName?: string;
  onProgress?: (progress: UploadProgress) => void;
}

export function useFileUpload(options: UseFileUploadOptions = {}) {
  const {
    endpoint = '/upload',
    fieldName = 'file',
    onProgress,
  } = options;

  const [uploading, setUploading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [uploadedFiles, setUploadedFiles] = useState<any[]>([]);

  const upload = async (files: File[], additionalData?: Record<string, any>) => {
    if (!files || files.length === 0) {
      setError('Nenhum arquivo selecionado');
      return null;
    }

    try {
      setUploading(true);
      setError(null);

      const formData = new FormData();
      
      // Add files to FormData
      files.forEach((file) => {
        formData.append(fieldName, file);
      });

      // Add additional data
      if (additionalData) {
        Object.entries(additionalData).forEach(([key, value]) => {
          formData.append(key, String(value));
        });
      }

      const response = await api.post(endpoint, formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
        onUploadProgress: (progressEvent) => {
          if (progressEvent.total && onProgress) {
            onProgress({
              loaded: progressEvent.loaded,
              total: progressEvent.total,
              percentage: Math.round((progressEvent.loaded / progressEvent.total) * 100),
            });
          }
        },
      });

      const uploadedData = response.data.files || response.data.file || response.data;
      setUploadedFiles(prev => [...prev, ...uploadedData]);
      
      return uploadedData;
    } catch (err: any) {
      const errorMessage = err.response?.data?.message || 'Erro ao fazer upload';
      setError(errorMessage);
      throw new Error(errorMessage);
    } finally {
      setUploading(false);
    }
  };

  const clearError = () => setError(null);

  const removeFile = (fileIndex: number) => {
    setUploadedFiles(prev => prev.filter((_, index) => index !== fileIndex));
  };

  return {
    upload,
    uploading,
    error,
    uploadedFiles,
    clearError,
    removeFile,
  };
}
