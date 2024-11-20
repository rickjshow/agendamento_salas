import axios from "axios";

// Criando a instância do Axios
const api = axios.create({
  baseURL: "http://localhost:8000/api", // URL base do Laravel
  withCredentials: true, // Habilita o envio de cookies com as requisições
});

export default api;
