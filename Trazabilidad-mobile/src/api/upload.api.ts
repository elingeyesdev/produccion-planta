import { apiClient } from './client';

export interface UploadResponse {
  success: boolean;
  imageUrl: string;
  publicId?: string;
  message?: string;
}

export const uploadApi = {
  uploadImage: async (uri: string): Promise<UploadResponse> => {
    try {
      const formData = new FormData();
      
      // Get filename from URI
      const uriParts = uri.split('.');
      const fileType = uriParts[uriParts.length - 1];
      const fileName = `photo.${fileType}`;

      // Append file to FormData
      // @ts-ignore - React Native FormData expects an object with uri, name, type
      formData.append('image', {
        uri,
        name: fileName,
        type: `image/${fileType}`,
      });

      // Add folder if needed (optional, backend defaults to 'trazabilidad')
      formData.append('folder', 'trazabilidad/maquinas');

      const response = await apiClient.post('/upload', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });

      return response.data;
    } catch (error: any) {
      console.error('Upload error:', error);
      throw error;
    }
  },
};
